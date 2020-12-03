<?php

function relevanssi_recognize_negatives($q) {
	$term = strtok($q, " ");
	$negative_terms = array();
	while ($term !== false) {
		if (substr($term, 0, 1) == '-') array_push($negative_terms, substr($term, 1));
		$term = strtok(" ");
	}
	return $negative_terms;
}

function relevanssi_recognize_positives($q) {
	$term = strtok($q, " ");
	$positive_terms = array();
	while ($term !== false) {
		if (substr($term, 0, 1) == '+') {
			$term_part = substr($term, 1);
			if (!empty($term_part)) array_push($positive_terms, $term_part);
			// to avoid problems with just plus signs
		}
		$term = strtok(" ");
	}
	return $positive_terms;
}

function relevanssi_negatives_positives($negative_terms, $positive_terms, $relevanssi_table) {
	$query_restrictions = "";
	if ($negative_terms) {
		for ($i = 0; $i < sizeof($negative_terms); $i++) {
			$negative_terms[$i] = "'" . esc_sql($negative_terms[$i]) . "'";
		}
		$negatives = implode(',', $negative_terms);
		$query_restrictions .= " AND doc NOT IN (SELECT DISTINCT(doc) FROM $relevanssi_table WHERE term IN ($negatives))";
		// Clean: escaped
	}

	if ($positive_terms) {
		for ($i = 0; $i < sizeof($positive_terms); $i++) {
			$positive_term = esc_sql($positive_terms[$i]);
			$query_restrictions .= " AND doc IN (SELECT DISTINCT(doc) FROM $relevanssi_table WHERE term = '$positive_term')";
			// Clean: escaped
		}
	}
	return $query_restrictions;
}

function relevanssi_get_recency_bonus() {
	$recency_bonus = get_option('relevanssi_recency_bonus');
	if (empty($recency_bonus['days']) OR empty($recency_bonus['bonus'])) {
		$recency_bonus = false;
		$recency_cutoff_date = false;
	}
	if ($recency_bonus) {
		$recency_cutoff_date = time() - 60 * 60 * 24 * $recency_bonus['days'];
	}
	return array($recency_bonus, $recency_cutoff_date);
}

/*  Pinning

    relevanssi_hits_filter function that adds the posts matching the pinned
    terms to the results.
*/
add_filter('relevanssi_hits_filter', 'relevanssi_pinning');
function relevanssi_pinning($hits) {
	global $wpdb;

	global $wp_filter;

	if (isset($wp_filter['relevanssi_stemmer'])) {
		$callbacks = $wp_filter['relevanssi_stemmer']->callbacks;
		$wp_filter['relevanssi_stemmer']->callbacks = null;
	}

	$terms = relevanssi_tokenize($hits[1], false);

	if (isset($wp_filter['relevanssi_stemmer'])) {
		$wp_filter['relevanssi_stemmer']->callbacks = $callbacks;
	}

	$escaped_terms = array();
	foreach (array_keys($terms) as $term) {
		$escaped_terms[] = esc_sql($term);
	}

	$term_list = array();
	for ($length = 1; $length <= count($escaped_terms); $length++) {
		for ($offset = 0; $offset <= count($escaped_terms) - $length; $offset++) {
			$slice = array_slice($escaped_terms, $offset, $length);
			$term_list[] = implode(" ", $slice);
		}
	}
	
	if (is_array($term_list)) {
		$term_list = implode("','", $term_list);
		$term_list = "'$term_list'";

		$positive_ids = array();
		$negative_ids = array();

		$pins_fetched = false;

		$pinned_posts = array();
		$other_posts = array();
		foreach ($hits[0] as $hit) {
			$blog_id = 0;
			if (isset($hit->blog_id)) {
                // Multisite, so switch_to_blog() to correct blog and process
                // the pinned hits per blog.
				$blog_id = $hit->blog_id;
				switch_to_blog($blog_id);
				if (!isset($pins_fetched[$blog_id])) {
					$q = "SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_relevanssi_pin' AND meta_value IN ($term_list)";
					$positive_ids[$blog_id] = $wpdb->get_col($q);

					$q = "SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_relevanssi_unpin' AND meta_value IN ($term_list)";
					$negative_ids[$blog_id] = $wpdb->get_col($q);

					$pins_fetched[$blog_id] = true;
				}
				restore_current_blog();
			}
			else {
                // Single site
				if (!$pins_fetched) {
					$q = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_pin' AND meta_value IN ($term_list)";
					$positive_ids[0] = $wpdb->get_col($q);

					$q = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_unpin' AND meta_value IN ($term_list)";
					$negative_ids[0] = $wpdb->get_col($q);

					$pins_fetched = true;
				}
			}

			if (is_array($positive_ids[$blog_id]) && count($positive_ids[$blog_id]) > 0 && in_array($hit->ID, $positive_ids[$blog_id])) {
				$pinned_posts[] = $hit;
			}
			else {
				if (is_array($negative_ids[$blog_id]) && count($negative_ids[$blog_id]) > 0) {
					if (!in_array($hit->ID, $negative_ids[$blog_id])) {
						$other_posts[] = $hit;
					}
				}
				else {
					$other_posts[] = $hit;
				}
			}
		}
		$hits[0] = array_merge($pinned_posts, $other_posts);
	}
	return $hits;
}

/*	Handles the multisite searching when the "searchblogs" parameter is present.
	Has slightly limited set of options compared to the single-site searches.
*/
function relevanssi_search_multi($multi_args) {
	global $relevanssi_variables, $wpdb;

	$filtered_values = apply_filters( 'relevanssi_search_filters', $multi_args );
	$q               = $filtered_values['q'];
	isset( $filtered_values['post_type'] ) 		? $post_type = $filtered_values['post_type'] : $post_type = "";
	isset( $filtered_values['search_blogs'] ) 	? $search_blogs = $filtered_values['search_blogs'] : $search_blogs = "";
	isset( $filtered_values['operator'] ) 		? $operator = $filtered_values['operator'] : $operator = "";
	isset( $filtered_values['meta_query'] ) 	? $meta_query = $filtered_values['meta_query'] : $meta_query = "";
	isset( $filtered_values['orderby'] ) 		? $orderby = $filtered_values['orderby'] : $orderby = "";
	isset( $filtered_values['order'] ) 			? $order = $filtered_values['order'] : $order = "";
	
	$hits = array();

	$remove_stopwords = false;
	$terms = relevanssi_tokenize($q, $remove_stopwords);

	if (count($terms) < 1) {
		// Tokenizer killed all the search terms.
		return $hits;
	}
	$terms = array_keys($terms); // don't care about tf in query

	$total_hits = 0;

	$title_matches = array();
	$tag_matches = array();
	$link_matches = array();
	$comment_matches = array();
	$body_matches = array();
	$scores = array();
	$term_hits = array();
	$hitsbyweight = array();

	$fuzzy = get_option('relevanssi_fuzzy');

	if ($search_blogs === "all") {
		$raw_blog_list = get_sites(array('number' => 2000));
		$blog_list = array();
		foreach ($raw_blog_list as $blog) {
			$blog_list[] = $blog->blog_id;
		}
		$search_blogs = implode(",", $blog_list);
	}

	$search_blogs = explode(",", $search_blogs);
	if (!is_array($search_blogs)) {
		return $hits;
	}

	$post_type_weights = get_option('relevanssi_post_type_weights');

	$orig_blog = $wpdb->blogid;
	foreach ($search_blogs as $blogid) {
		// Only search blogs that are publicly available (unless filter says otherwise)
		$public_status = get_blog_status($blogid, 'public');
		if ($public_status === NULL) continue;
		if (apply_filters('relevanssi_multisite_public_status', $public_status, $blogid) === false) continue;
		
		// Don't search blogs that are marked "spam" or "deleted".
		if (get_blog_status($blogid, 'spam')) continue;
		if (get_blog_status($blogid, 'delete')) continue;

		// Ok, we should have a valid blog.
		switch_to_blog($blogid);
		$relevanssi_table = $wpdb->prefix . "relevanssi";

		// See if Relevanssi tables exist
		$table_exists_query = "SELECT count(*) FROM information_schema.TABLES WHERE (TABLE_SCHEMA = '" . DB_NAME . "') AND (TABLE_NAME = '$relevanssi_table')";
		$exists = $wpdb->get_var($table_exists_query);
		if ($exists < 1) {
			restore_current_blog();
			continue;
		}

		$query_join = "";
		$query_restrictions = "";

		// If $post_type is not set, see if there are post types to exclude from the search.
		// If $post_type is set, there's no need to exclude, as we only include.
		!$post_type ? $negative_post_type = relevanssi_get_negative_post_type() : $negative_post_type = NULL;

		$non_post_post_types_array = array();
		if (function_exists('relevanssi_get_non_post_post_types')) {
			$non_post_post_types_array = relevanssi_get_non_post_post_types();
		}

		$non_post_post_type = NULL;
		$site_post_type = NULL;
		if ($post_type) {
			if ($post_type == -1) $post_type = null; // Facetious sets post_type to -1 if not selected
			if (!is_array($post_type)) {
				$post_types = explode(',', $post_type);
			}
			else {
				$post_types = $post_type;
			}
			// This array will contain all regular post types involved in the search parameters.
			$post_post_types = array_diff($post_types, $non_post_post_types_array);

			// This array has the non-post post types involved.
			$non_post_post_types = array_intersect($post_types, $non_post_post_types_array);

			// Escape both for SQL queries, just in case.
			$non_post_post_types = esc_sql($non_post_post_types);
			$post_types = esc_sql($post_post_types);

			// Implode to a parameter string, or set to NULL if empty.
			$non_post_post_type = count($non_post_post_types) ? "'" . implode( "', '", $non_post_post_types) . "'" : NULL;
			$site_post_type = count($post_types) ? "'" . implode( "', '", $post_types) . "'" : NULL;
		}

		if ($site_post_type) {
			// A post type is set: add a restriction
			$restriction = " AND (
				relevanssi.doc IN (
					SELECT DISTINCT(posts.ID) FROM $wpdb->posts AS posts
					WHERE posts.post_type IN ($site_post_type)
				) *np*
			)";
			// Clean: $post_type is escaped

			// There are post types involved that are taxonomies or users, so can't
			// match to wp_posts. Add a relevanssi.type restriction.
			if ($non_post_post_type) {
				$restriction = str_replace('*np*', "OR (relevanssi.type IN ($non_post_post_type))", $restriction);
				// Clean: $non_post_post_types is escaped
			} else {
				// No non-post post types, so remove the placeholder.
				$restriction = str_replace('*np*', '', $restriction);
			}
			$query_restrictions .= $restriction;
		}
		else {
			// No regular post types
			if ($non_post_post_type) {
				// But there is a non-post post type restriction.
				$query_restrictions .= " AND (relevanssi.type IN ($non_post_post_type))";
				// Clean: $non_post_post_types is escaped
			}
		}

		if ($negative_post_type) {
			$query_restrictions .= " AND ((relevanssi.doc IN (SELECT DISTINCT(posts.ID) FROM $wpdb->posts AS posts
				WHERE posts.post_type NOT IN ($negative_post_type))) OR (relevanssi.doc = -1))";
			// Clean: $negative_post_type is escaped
		}

		$query_restrictions = apply_filters('relevanssi_where', $query_restrictions); // Charles St-Pierre

		// handle the meta query
		if ( ! empty( $meta_query ) ) {
			$mq = new WP_Meta_Query();
			$mq->parse_query_vars( array( 'meta_query' => $meta_query ) );
			$meta_sql = $mq->get_sql( 'post', 'relevanssi', 'doc' );
			if ( $meta_sql ) {
				$query_join .= $meta_sql['join'];
				$query_restrictions .= $meta_sql['where'];
			}
		}
		$D = $wpdb->get_var("SELECT COUNT(DISTINCT(doc)) FROM $relevanssi_table");

		$no_matches = true;
		if ("always" == $fuzzy) {
			$o_term_cond = "(term LIKE '%#term#' OR term LIKE '#term#%') ";
		}
		else {
			$o_term_cond = " term = '#term#' ";
		}

		$min_length = get_option('relevanssi_min_word_length');
		$search_again = false;
		do {
			foreach ($terms as $term) {
				$term = trim($term);	// numeric search terms will start with a space
				if (strlen($term) < $min_length) continue;
				if (method_exists($wpdb, 'esc_like')) {
					$term = esc_sql($wpdb->esc_like($term));
				}
				else {
					// Compatibility for pre-4.0 WordPress
					$term = esc_sql(like_escape($term));
				}
				$term_cond = str_replace('#term#', $term, $o_term_cond);

				$query = "SELECT *, title + content + comment + tag + link + author + category + excerpt + taxonomy + customfield AS tf
				FROM $relevanssi_table AS relevanssi $query_join WHERE $term_cond $query_restrictions";
				$query = apply_filters('relevanssi_query_filter', $query);
				// Clean: $term is escaped, as are $query_restrictions

				$matches = $wpdb->get_results($query);
				if (count($matches) < 1) {
					continue;
				}
				else {
					$no_matches = false;
				}

				$total_hits += count($matches);

				$query = "SELECT COUNT(DISTINCT(doc)) FROM $relevanssi_table AS relevanssi $query_join WHERE $term_cond $query_restrictions";
				$query = apply_filters('relevanssi_df_query_filter', $query);

				$df = $wpdb->get_var($query);
				// Clean: $term is escaped, as are $query_restrictions

				if ($df < 1 && "sometimes" == $fuzzy) {
					$query = "SELECT COUNT(DISTINCT(doc)) FROM $relevanssi_table AS relevanssi $query_join
						WHERE (term LIKE '%$term' OR term LIKE '$term%') $query_restrictions";
					$query = apply_filters('relevanssi_df_query_filter', $query);
					$df = $wpdb->get_var($query);
					// Clean: $term is escaped, as are $query_restrictions
				}

				$title_boost = floatval(get_option('relevanssi_title_boost'));
				isset($post_type_weights['post_tag']) ? $tag_boost = $post_type_weights['post_tag'] : 1;
				$link_boost = floatval(get_option('relevanssi_link_boost'));
				$comment_boost = floatval(get_option('relevanssi_comment_boost'));

				$doc_weight = array();
				$scores = array();
				$term_hits = array();

				$idf = log($D / (1 + $df));
				foreach ($matches as $match) {
					if ('user' == $match->type) {
						$match->doc = 'u_' . $match->item;
					}
					else if (!in_array($match->type, array('post', 'attachment'))) {
						$match->doc = '**' . $match->type . '**' . $match->item;
					}

					$match->tf =
						$match->title * $title_boost +
						$match->content +
						$match->comment * $comment_boost +
						$match->tag * $tag_boost +
						$match->link * $link_boost +
						$match->author +
						$match->category +
						$match->excerpt +
						$match->taxonomy +
						$match->customfield;

					$term_hits[$match->doc][$term] =
						$match->title +
						$match->content +
						$match->comment +
						$match->tag +
						$match->link +
						$match->author +
						$match->category +
						$match->excerpt +
						$match->taxonomy +
						$match->customfield;

					if ($idf < 1) $idf = 1;
					$match->weight = $match->tf * $idf;

					$type = relevanssi_get_post_type($match->doc);
					if (!empty($post_type_weights[$type])) {
						$match->weight = $match->weight * $post_type_weights[$type];
					}

					$match = apply_filters('relevanssi_match', $match, $idf, $term);

					if ($match->weight == 0) continue; // the filters killed the match

					$doc_terms[$match->doc][$term] = true; // count how many terms are matched to a doc
					isset($doc_weight[$match->doc]) ?
						$doc_weight[$match->doc] += $match->weight :
						$doc_weight[$match->doc] = $match->weight;
					isset($scores[$match->doc]) ?
						$scores[$match->doc] += $match->weight :
						$scores[$match->doc] = $match->weight;

					$body_matches[$match->doc] = $match->content;
					$title_matches[$match->doc] = $match->title;
					$link_matches[$match->doc] = $match->link;
					$tag_matches[$match->doc] = $match->tag;
					$comment_matches[$match->doc] = $match->comment;
				}
			}

			if ($no_matches) {
				if ($search_again) {
					// no hits even with fuzzy search!
					$search_again = false;
				}
				else {
					if ("sometimes" == $fuzzy) {
						$search_again = true;
						$o_term_cond = "(term LIKE '%#term#' OR term LIKE '#term#%') ";
					}
				}
			}
			else {
				$search_again = false;
			}
		} while ($search_again);

		$strip_stops = true;
		$terms_without_stops = array_keys(relevanssi_tokenize(implode(' ', $terms), $strip_stops));
		$total_terms = count($terms_without_stops);

		if (isset($doc_weight) && count($doc_weight) > 0 && !$no_matches) {
			arsort($doc_weight);
			$i = 0;
			foreach ($doc_weight as $doc => $weight) {
				if (count($doc_terms[$doc]) < $total_terms && $operator == "AND") {
					// AND operator in action:
					// doc didn't match all terms, so it's discarded
					continue;
				}
				$status = relevanssi_get_post_status($doc);
				$post_ok = true;
				if ('private' == $status) {
					$post_ok = false;

					if (function_exists('awp_user_can')) {
						// Role-Scoper
						$current_user = wp_get_current_user();
						$post_ok = awp_user_can('read_post', $doc, $current_user->ID);
					}
					else {
						// Basic WordPress version
						$type = get_post_type($doc);
						$cap = 'read_private_' . $type . 's';
						if (current_user_can($cap)) {
							$post_ok = true;
						}
					}
				} else if ( 'publish' != $status ) {
					$post_ok = false;
				}

				$post_ok = apply_filters('relevanssi_post_ok', $post_ok, $doc);

				if ($post_ok) {
					$post_object = relevanssi_get_multisite_post($blogid, $doc);
					$post_object->blog_id = $blogid;

					$object_id = $blogid . '|' . $doc;
					$hitsbyweight[$object_id] = $weight;
					$post_objects[$object_id] = $post_object;
				}
			}
		}

		restore_current_blog();
	}

	arsort($hitsbyweight);
	$i = 0;
	foreach ($hitsbyweight as $hit => $weight) {
		$hit = $post_objects[$hit];
		$hits[intval($i)] = $hit;
		$hits[intval($i)]->relevance_score = round($weight, 2);
		$i++;
	}

	if (count($hits) < 1) {
		if ($operator == "AND" AND get_option('relevanssi_disable_or_fallback') != 'on') {
			$or_args = $multi_args;
			$or_args['operator'] = "OR";
			$return = relevanssi_search_multi($or_args);
			extract($return);
		}
	}

	global $wp;
	$default_order = get_option('relevanssi_default_orderby', 'relevance');
	if (empty($orderby)) $orderby = $default_order;
	// the sorting function checks for non-existing keys, cannot whitelist here

	if (is_array($orderby)) {
		$orderby = apply_filters('relevanssi_orderby', $orderby);
		
		relevanssi_object_sort($hits, $orderby);
	}
	else {
		if (empty($order)) $order = 'desc';
		$order = strtolower($order);
		$order_accepted_values = array('asc', 'desc');
		if (!in_array($order, $order_accepted_values)) $order = 'desc';

		$orderby = apply_filters('relevanssi_orderby', $orderby);
		$order   = apply_filters('relevanssi_order', $order);

		if ($orderby != 'relevance') {
			$orderby_array = array($orderby => $order);
			relevanssi_object_sort($hits, $orderby_array);
		}
	}

	$return = array('hits' => $hits, 'body_matches' => $body_matches, 'title_matches' => $title_matches,
		'tag_matches' => $tag_matches, 'comment_matches' => $comment_matches, 'scores' => $scores,
		'term_hits' => $term_hits, 'query' => $q, 'link_matches' => $link_matches);

	return $return;
}

add_filter('query_vars', 'relevanssi_premium_query_vars');
function relevanssi_premium_query_vars($qv) {
	$qv[] = 'searchblogs';
	$qv[] = 'customfield_key';
	$qv[] = 'customfield_value';
	$qv[] = 'operator';
	$qv[] = 'include_attachments';
	return $qv;
}

function relevanssi_set_operator($query) {
	isset($query->query_vars['operator']) ?
		$operator = $query->query_vars['operator'] :
		$operator = get_option("relevanssi_implicit_operator");
	return $operator;
}

function relevanssi_process_taxonomies($taxonomy, $taxonomy_term, $tax_query) {
	$taxonomies = explode('|', $taxonomy);
	$terms = explode('|', $taxonomy_term);
	$i = 0;
	foreach ($taxonomies as $taxonomy) {
		$term_tax_id = null;
		$taxonomy_terms = explode(',', $terms[$i]);
		foreach ($taxonomy_terms as $taxonomy_term) {
			if (!empty($taxonomy_term))
				$tax_query[] = array('taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $taxonomy_term);
		}
		$i++;
	}
	return $tax_query;
}
