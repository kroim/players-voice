<?php

/*  Related searches

    Example:

    relevanssi_related(get_search_query(), '<h3>Related Searches:</h3><ul><li>', '</li><li>', '</li></ul>');

    Function written by John Blackbourn.
*/
function relevanssi_related($query, $pre = '<ul><li>', $sep = '</li><li>', $post = '</li></ul>', $number = 5) {
	global $wpdb, $relevanssi_variables;
	$output = $related = array();
	$tokens = relevanssi_tokenize($query);
	if (empty($tokens))
		return;
	/* Loop over each token in the query and return logged queries which:
	 *
	 *  - Contain a matching token
	 *  - Don't match the query or the token exactly
	 *  - Have at least 2 hits
	 *  - Have been queried at least twice
	 *
	 * then order by most queried with a max of $number results.
	 */
	foreach ($tokens as $token => $count) {
		$sql = $wpdb->prepare("
			SELECT query
			FROM " . $relevanssi_variables['log_table'] . "
			WHERE query LIKE '%%%s%%'
			AND query NOT IN (%s, %s)
			AND hits > 1
			GROUP BY query
			HAVING count(query) > 1
			ORDER BY count(query) DESC
			LIMIT %d
		", $token, $token, $query, $number);
		foreach ($wpdb->get_results($sql) as $result)
			$related[] = $result->query;
	}
	if (empty($related))
		return;
	/* Order results by most matching tokens
	 * then slice to a maximum of $number results:
	 */
	$related = array_keys(array_count_values($related));
	$related = array_slice($related, 0, $number);
	foreach ($related as $rel) {
		$url = add_query_arg(array(
			's' => urlencode($rel)
		), home_url());
		$rel = esc_attr($rel);
		$output[] = "<a href='$url'>$rel</a>";
	}
	echo $pre;
	echo implode($sep, $output);
	echo $post;
}

/* 	Custom-made get_posts() replacement that creates post objects for
	users and taxonomy terms. For regular posts, the function uses
	a caching mechanism.
*/
function relevanssi_premium_get_post($id) {
	global $relevanssi_post_array;

	$type = substr($id, 0, 2);
	switch ($type) {
		case 'u_':
			list($throwaway, $id) = explode('_', $id);
			$user = get_userdata($id);

			$post = new stdClass;
			$post->post_title = $user->display_name;
			$post->post_content = $user->description;
			$post->post_type = 'user';
			$post->ID = $id;
			$post->link = get_author_posts_url($id);
			$post->post_status = 'publish';
			$post->post_date = date("Y-m-d H:i:s");
			$post->post_author = 0;
			$post->post_name = '';
			$post->post_excerpt = '';
			$post->comment_status = '';
			$post->ping_status = '';
			$post->user_id = $id;

			$post = apply_filters('relevanssi_user_profile_to_post', $post);
			break;
		case '**':
			list($throwaway, $taxonomy, $id) = explode('**', $id);
			$term = get_term($id, $taxonomy);

			$post = new stdClass;
			$post->post_title = $term->name;
			$post->post_content = $term->description;
			$post->post_type = $taxonomy;
			$post->ID = -1;
			$post->post_status = 'publish';
			$post->post_date = date("Y-m-d H:i:s");
			$post->link = get_term_link($term, $taxonomy);
			$post->post_author = 0;
			$post->post_name = '';
			$post->post_excerpt = '';
			$post->comment_status = '';
			$post->ping_status = '';
			$post->term_id = $id;
			$post->post_parent = $term->parent;

			$post = apply_filters('relevanssi_taxonomy_term_to_post', $post);
			break;
		default:
			if (isset($relevanssi_post_array[$id])) {
				$post = $relevanssi_post_array[$id];
			}
			else {
				$post = get_post($id);
			}
			if (get_option('relevanssi_link_pdf_files') === "on" && $post->post_mime_type === 'application/pdf') {
				$post->link = $post->guid;
			}
	}
	return $post;
}

/*	Returns a list of indexed taxonomies (and "user", if user profiles are
	indexed).
*/
function relevanssi_get_non_post_post_types() {
	// These post types are not posts, ie. they are taxonomy terms and user profiles.
	$non_post_post_types_array = array();
	if (get_option('relevanssi_index_taxonomies')) {
		$taxonomies = get_option('relevanssi_index_terms');
		if (is_array($taxonomies)) {
			$non_post_post_types_array = $taxonomies;
		}
	}
	if (get_option('relevanssi_index_users')) {
		$non_post_post_types_array[] = "user";
	}
	return $non_post_post_types_array;
}

function relevanssi_get_child_pdf_content($post_id) {
	global $wpdb;
	$pdf_content = $wpdb->get_col("SELECT meta_value FROM $wpdb->postmeta AS pm, $wpdb->posts AS p WHERE pm.post_id = p.ID AND p.post_parent = $post_id AND meta_key = '_relevanssi_pdf_content'");
	return implode(" ", $pdf_content);
}

function relevanssi_nonlocal_highlighting($content) {
	if (isset($_SERVER['HTTP_REFERER'])) {
		$referrer = preg_replace('@(http|https)://@', '', stripslashes(urldecode($_SERVER['HTTP_REFERER'])));
		$args     = explode('?', $referrer);
		$query    = array();

		if ( count( $args ) > 1 )
			parse_str( $args[1], $query );

		if (get_option('relevanssi_highlight_docs_external', 'off') != 'off') {
			$query = relevanssi_add_synonyms($query);
			if (strpos($referrer, 'google') !== false) {
				$content = relevanssi_highlight_terms($content, $query, true);
			} elseif (strpos($referrer, 'bing') !== false) {
				$content = relevanssi_highlight_terms($content, $query, true);
			} elseif (strpos($referrer, 'ask') !== false) {
				$content = relevanssi_highlight_terms($content, $query, true);
			} elseif (strpos($referrer, 'aol') !== false) {
				$content = relevanssi_highlight_terms($content, $query, true);
			} elseif (strpos($referrer, 'yahoo') !== false) {
				$content = relevanssi_highlight_terms($content, $query, true);
			}
		}
	}

	return $content;
}

function relevanssi_premium_didyoumean($query, $pre, $post, $n = 5) {
	global $wpdb, $relevanssi_variables, $wp_query;

	$total_results = $wp_query->found_posts;
	$result = "";

	if ($total_results > $n) return $result;

	$suggestion = "";
	$suggestion_enc = "";
	$exact_match = false;

	if (class_exists('SpellCorrector')) {
		$query = htmlspecialchars_decode($query);
		$tokens = relevanssi_tokenize($query);

		$sc = new SpellCorrector();

		$correct = array();
		$exact_matches = 0;
		foreach ($tokens as $token => $count) {
			$token = trim($token);
			$c = $sc->correct($token);
			if (!empty($c) && $c !== strval($token)) {
				array_push($correct, $c);
				$query = str_ireplace($token, $c, $query); // Replace misspelled word in query with suggestion
			}
			else if ($c !== null) {
				$exact_matches++;
			}
		}
		if ($exact_matches === count($tokens)) {
			// All tokens are correct.
			return "";
		}
		if (count($correct) > 0) {
			$suggestion = $query;
			$suggestion_enc = urlencode($suggestion);
		}
	}

	if (empty($suggestion)) {
		$q = "SELECT query, count(query) as c, AVG(hits) as a FROM " . $relevanssi_variables['log_table'] . " WHERE hits > 1 GROUP BY query ORDER BY count(query) DESC";
		$q = apply_filters('relevanssi_didyoumean_query', $q);

		$data = $wpdb->get_results($q);

		$query = htmlspecialchars_decode($query);
		$tokens = relevanssi_tokenize($query);
		$suggestions_made = false;
		$suggestion = "";
		
		foreach ($tokens as $token => $count) {
			$closest = "";
			$distance = -1;
			foreach ($data as $row) {
				if ($row->c < 2) break;
			
				if ($token === $row->query) {
					$closest = "";
					break;
				}
				else {
					$lev = levenshtein($token, $row->query);
	
					if ($lev < $distance || $distance < 0) {
						if ($row->a > 0) {
							$distance = $lev;
							$closest = $row->query;
							if ($lev < 2) break; // get the first with distance of 1 and go
						}
					}
				}
			}
			if (!empty($closest)) {
				$query = str_ireplace($token, $closest, $query);
				$suggestions_made = true;
			} 
		}

		if ($suggestions_made) {
			$suggestion = $query;
			$suggestion_enc = urlencode($suggestion);
		}
	}

	$result = null;
	if ($suggestion) {
 		$url = get_bloginfo('url');
		$url = esc_attr(add_query_arg(array(
			's' => $suggestion_enc
			), $url));
		$url = apply_filters('relevanssi_didyoumean_url', $url, $query, $suggestion);

		// Escape the suggestion to avoid XSS attacks
		$suggestion = htmlspecialchars($suggestion);

		$result = apply_filters('relevanssi_didyoumean_suggestion', "$pre<a href='$url'>$suggestion</a>$post");
 	}
	return $result;
}

function relevanssi_get_multisite_post($blogid, $id) {
	switch_to_blog($blogid);
	if (!is_numeric(mb_substr($id, 0, 1))) wp_suspend_cache_addition(true); 
	$post = relevanssi_get_post($id);
	restore_current_blog();
	return $post;
}


function relevanssi_premium_init() {
	$show_post_controls = true;
	if (get_option('relevanssi_hide_post_controls') == 'on') {
		$show_post_controls = false;
		if (get_option('relevanssi_show_post_controls') === 'on' && current_user_can(apply_filters('relevanssi_options_capability', 'manage_options'))) {
			$show_post_controls = true;
		}
	}
	if ($show_post_controls) add_action('add_meta_boxes', 'relevanssi_add_metaboxes');

	if (get_option('relevanssi_index_synonyms') == 'on') {
		add_filter('relevanssi_post_to_index', 'relevanssi_index_synonyms', 10);
	}

	add_action('future_to_publish',
	  function($post) {
    	remove_action('save_post', 'relevanssi_save_postdata');
	  }
	);

	return;
}

function relevanssi_post_link_replace($permalink, $post_id) {
	$post = get_post($post_id);
	if (isset($post->link)) $permalink = $post->link;
	return $permalink;
}

function relevanssi_correct_query($q) {
	if (class_exists('SpellCorrector')) {
		$tokens = relevanssi_tokenize($q, false);
		$sc = new SpellCorrector();
		$correct = array();
		foreach ($tokens as $token => $count) {
			$c = $sc->correct($token);
			if ($c !== $token) array_push($correct, $c);
		}
		if (count($correct) > 0) $q = implode(' ', $correct);
	}
	return $q;
}

function relevanssi_get_words() {
	global $wpdb, $relevanssi_variables;

	$count = apply_filters('relevanssi_get_words_having', 1);
	if (!is_numeric($count)) $count = 1;
	$q = "SELECT term, SUM(title + content + comment + tag + link + author + category + excerpt + taxonomy + customfield) as c FROM " . $relevanssi_variables['relevanssi_table'] . " GROUP BY term HAVING c > $count";
	// Safe: $count is numeric

	$results = $wpdb->get_results($q);

	$words = array();
	foreach ($results as $result) {
		$words[$result->term] = $result->c;
	}

	return $words;
}

function relevanssi_premium_install() {
	global $relevanssi_variables;

	add_option('relevanssi_link_boost', $relevanssi_variables['link_boost_default']);
	add_option('relevanssi_post_type_weights', '');
	add_option('relevanssi_index_users', 'off');
	add_option('relevanssi_index_subscribers', 'off');
	add_option('relevanssi_index_taxonomies', 'off');
	add_option('relevanssi_internal_links', 'noindex');
	add_option('relevanssi_thousand_separator', '');
	add_option('relevanssi_disable_shortcodes', '');
	add_option('relevanssi_api_key', '');
	add_option('relenvassi_recency_bonus', array('bonus' => '', 'days' => ''));
	add_option('relevanssi_mysql_columns', '');
	add_option('relevanssi_hide_post_controls', 'off');
	add_option('relevanssi_show_post_controls', 'off');
	add_option('relevanssi_index_taxonomies_list', array());
	add_option('relevanssi_index_terms', array());
	add_option('relevanssi_index_synonyms', 'off');
	add_option('relevanssi_index_pdf_parent', 'off');
	add_option('relevanssi_read_new_files', 'off');
	add_option('relevanssi_send_pdf_files', 'off');
	add_option('relevanssi_link_pdf_files', 'off');
}