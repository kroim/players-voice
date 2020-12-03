<?php

function relevanssi_profile_update($user) {
	if (get_option('relevanssi_index_users') == 'on') {
		$update = true;
		relevanssi_index_user($user, $update);
	}
}

function relevanssi_edit_term($term, $tt_id, $taxonomy) {
	$update = true;
	relevanssi_do_term_indexing($term, $taxonomy, $update);
}

function relevanssi_add_term($term, $tt_id, $taxonomy) {
	$update = false;
	relevanssi_do_term_indexing($term, $taxonomy, $update);
}

function relevanssi_do_term_indexing($term, $taxonomy, $update) {
	if (get_option('relevanssi_index_taxonomies') === 'on') {
		$taxonomies = get_option('relevanssi_index_terms');
		if (in_array($taxonomy, $taxonomies)) {
			relevanssi_index_taxonomy_term($term, $taxonomy, $update);
		}
	}
}

function relevanssi_delete_user($user) {
	global $wpdb, $relevanssi_variables;
	$wpdb->query("DELETE FROM " . $relevanssi_variables['relevanssi_table'] . " WHERE item = $user AND type = 'user'");
}

function relevanssi_delete_taxonomy_term($term, $tt_id, $taxonomy) {
	// $tt_id is passed by the WP filter hook, but not needed for Relevanssi
	global $wpdb, $relevanssi_variables;
	$wpdb->query("DELETE FROM " . $relevanssi_variables['relevanssi_table'] . " WHERE item = $term AND type = '$taxonomy'");
}

function relevanssi_customfield_detail($insert_data, $token, $count, $field) {
	isset($insert_data[$token]['customfield_detail']) ? $cfdetail = unserialize($insert_data[$token]['customfield_detail']) : $cfdetail = array();
	isset($cfdetail[$field]) ? $cfdetail[$field] += $count : $cfdetail[$field] = $count;
	$insert_data[$token]['customfield_detail'] = serialize($cfdetail);
	return $insert_data;
}

function relevanssi_index_mysql_columns($insert_data, $id) {
	$custom_columns = get_option('relevanssi_mysql_columns');
	if (!empty($custom_columns)) {
		global $wpdb;
		$custom_column_array = explode(",", $custom_columns);
		$custom_column_list = implode(", ", array_filter($custom_column_array)); // this is to remove problems where the list ends in a comma
		$custom_column_data = $wpdb->get_row("SELECT $custom_column_list FROM $wpdb->posts WHERE ID=$id", ARRAY_A);
		if (is_array($custom_column_data)) {
			foreach ($custom_column_data as $data) {
				$data = relevanssi_tokenize($data);
				if (count($data) > 0) {
					foreach ($data as $term => $count) {
						isset($insert_data[$term]['mysqlcolumn']) ? $insert_data[$term]['mysqlcolumn'] += $count : $insert_data[$term]['mysqlcolumn'] = $count;
					}
				}
			}
		}
	}
	return $insert_data;
}

/*  Internal link processing

    Process the internal links the way user wants: no indexing, indexing, stripping.
*/
function relevanssi_process_internal_links($contents, $id) {
	$internal_links_behaviour = get_option('relevanssi_internal_links', 'noindex');

	if ($internal_links_behaviour != 'noindex') {
		global $relevanssi_variables, $wpdb;
		$min_word_length = get_option('relevanssi_min_word_length', 3);
		// index internal links
		$internal_links = relevanssi_get_internal_links($contents);
		if ( !empty( $internal_links ) ) {

			foreach ( $internal_links as $link => $text ) {
				$link_id = url_to_postid( $link );
				if ( !empty( $link_id ) ) {
				$link_words = relevanssi_tokenize($text, true, $min_word_length);
					if ( count( $link_words > 0 ) ) {
						foreach ( $link_words as $word => $count ) {
							$wpdb->query($wpdb->prepare("INSERT IGNORE INTO " . $relevanssi_variables['relevanssi_table'] . " (doc, term, term_reverse, link, item)
							VALUES (%d, %s, REVERSE(%s), %d, %d)", $link_id, $word, $word, $count, $id));
						}
					}
				}
			}

			if ('strip' == $internal_links_behaviour)
				$contents = relevanssi_strip_internal_links($contents);
		}
	}

	return $contents;
}

/*	Find internal links

	A function to find all internal links in the parameter text.
*/
function relevanssi_get_internal_links($text) {
	$links = array();
    if ( preg_match_all( '@<a[^>]*?href="(' . home_url() . '[^"]*?)"[^>]*?>(.*?)</a>@siu', $text, $m ) ) {
		foreach ( $m[1] as $i => $link ) {
			if ( !isset( $links[$link] ) )
				$links[$link] = '';
			$links[$link] .= ' ' . $m[2][$i];
		}
	}
    if ( preg_match_all( '@<a[^>]*?href="(/[^"]*?)"[^>]*?>(.*?)</a>@siu', $text, $m ) ) {
		foreach ( $m[1] as $i => $link ) {
			if ( !isset( $links[$link] ) )
				$links[$link] = '';
			$links[$link] .= ' ' . $m[2][$i];
		}
	}
	if (count($links) > 0)
		return $links;
	return false;
}

/*	Strip internal links

	A function to strip all internal links from the parameter text.
*/
function relevanssi_strip_internal_links($text) {
	$text = preg_replace(
		array(
			'@<a[^>]*?href="' . home_url() . '[^>]*?>.*?</a>@siu',
		),
		' ',
		$text );
	$text = preg_replace(
		array(
			'@<a[^>]*?href="/[^>]*?>.*?</a>@siu',
		),
		' ',
		$text );
	return $text;
}

/*  Thousand separator

    Find numbers separated by the chosen thousand separator and combine them.
*/
function relevanssi_thousandsep($str) {
	$thousandsep = get_option('relevanssi_thousand_separator', '');
	if (!empty($thousandsep)) {
		$pattern = "/(\d+)" . $thousandsep . "(\d+)/u";
		$str = preg_replace($pattern, "$1$2", $str);
	}
	return $str;
}

/*  Stemmer-enabling filter

    A simple helper function that enables the stemmer.
*/
add_filter('relevanssi_premium_tokenizer', 'relevanssi_enable_stemmer');
function relevanssi_enable_stemmer($t) {
	$t = apply_filters('relevanssi_stemmer', $t);
	return $t;
}

/*  Simple English stemmer

    A simple suffix stripper that can be used to stem English texts.
*/
function relevanssi_simple_english_stemmer($term) {
	$len = strlen($term);

	$end1 = substr($term, -1, 1);
	if ("s" == $end1 && $len > 3) {
		$term = substr($term, 0, -1);
	}
	$end = substr($term, -3, 3);

	if ("ing" == $end && $len > 5) {
		return substr($term, 0, -3);
	}
	if ("est" == $end && $len > 5) {
		return substr($term, 0, -3);
	}

	$end = substr($end, 1);
	if ("es" == $end && $len > 3) {
		return substr($term, 0, -2);
	}
	if ("ed" == $end && $len > 3) {
		return substr($term, 0, -2);
	}
	if ("en" == $end && $len > 3) {
		return substr($term, 0, -2);
	}
	if ("er" == $end && $len > 3) {
		return substr($term, 0, -2);
	}
	if ("ly" == $end && $len > 4) {
		return substr($term, 0, -2);
	}

	return $term;
}

/*  Synonym indexing

    Indexes synonyms in posts in order to use synonyms in AND searches.
*/
function relevanssi_index_synonyms($post) {
	global $relevanssi_variables;

	if (!isset($relevanssi_variables['synonyms'])) relevanssi_create_synonym_replacement_array();

	if (!empty($relevanssi_variables['synonyms'])) {
		$search = array_keys($relevanssi_variables['synonyms']);
		$replace = array_values($relevanssi_variables['synonyms']);

		$post_content = relevanssi_strtolower($post->post_content);
		$post_title = relevanssi_strtolower($post->post_title);

		$boundary_search = array();
		foreach ($search as $term) {
			$boundary_search[] = '/\b'. str_replace('/', '\/', preg_quote($term)) .'\b/u';
		}

		$post->post_content = preg_replace($boundary_search, $replace, $post_content);
		$post->post_title = preg_replace($boundary_search, $replace, $post_title);
	}

	return $post;
}

/*  Create synonym replacement array

    A helper function that generates a synonym replacement array. The array
    is then stored in a global variable, so that it only needs to generated
    once per running the script.
*/
function relevanssi_create_synonym_replacement_array() {
	global $relevanssi_variables;

	$synonym_data = get_option('relevanssi_synonyms');
	if ($synonym_data) {
		$synonyms = array();
		$synonym_data = relevanssi_strtolower($synonym_data);
		$pairs = explode(";", $synonym_data);
		foreach ($pairs as $pair) {
			$parts = explode("=", $pair);
			$key = strval(trim($parts[0]));
			$value = trim($parts[1]);
			if (!isset($synonyms[$value])) {
				$synonyms[$value] = "$value $key";
			}
			else {
				$synonyms[$value] .= " $key";
			}
		}
		$relevanssi_variables['synonyms'] = $synonyms;
	}
}

/*  Indexing for pinned terms

    Adds pinned terms to post content to make sure posts are found with the
    pinned terms.
*/
add_filter('relevanssi_content_to_index', 'relevanssi_index_pinning_words', 10, 2);
function relevanssi_index_pinning_words($content, $post) {
	$pin_words = get_post_meta($post->ID, '_relevanssi_pin', false);
	foreach ($pin_words as $word) {
		$content .= " $word";
	}
	return $content;
}

/* 	ACF Repeater field support

	Goes through custom fields, finds fields that match the fieldname_%_subfieldname
	pattern, finds the number of fields from the fieldname custom field and then
	adds the fieldname_0_subfieldname... fields to the list of custom fields.
*/
function relevanssi_add_repeater_fields(&$custom_fields, $post_id) {
	$repeater_fields = array();
	foreach ($custom_fields as $field) {
		if (substr_count($field, '%') === 1) { 	// Only one level of repeaters
			$field = str_replace('/', '\/', $field);
			preg_match('/([a-z0-9\_\-]+)_\%_([a-z0-9\_\-]+)/i', $field, $matches);
			$field_name = "";
			$subfield_name = "";
			if (count($matches) > 1) {
				$field_name = $matches[1];
				$subfield_name = $matches[2];
			}
			if ($field_name) {
				$num_fields = get_post_meta($post_id, $field_name, true);
				for ($i = 0; $i < $num_fields; $i++) {
					$repeater_fields[] = $field_name . '_' . $i . '_' . $subfield_name;
				}
			}
		}
		else {
			continue;
		}
	}
	$custom_fields = array_merge($custom_fields, $repeater_fields);
}

function relevanssi_index_pdf_for_parent($insert_data, $post_id) {
	$option = get_option('relevanssi_index_pdf_parent');
	if (empty($option) || $option === "off") return $insert_data;

	global $wpdb;

    $pdf_content = $wpdb->get_col("SELECT meta_value FROM $wpdb->postmeta AS pm, $wpdb->posts AS p WHERE pm.post_id = p.ID AND p.post_parent = $post_id AND meta_key = '_relevanssi_pdf_content'");

    if (is_array($pdf_content)) {
        foreach ($pdf_content as $row) {
            $data = relevanssi_tokenize($row, true, get_option('relevanssi_min_word_length', 3));
            if (count($data) > 0) {
                foreach ($data as $term => $count) {
                    isset($insert_data[$term]['customfield']) ? $insert_data[$term]['customfield'] += $count : $insert_data[$term]['customfield'] = $count;
                    $insert_data = relevanssi_customfield_detail($insert_data, $term, $count, '_relevanssi_pdf_content');
                }
            }
        }
    }

    return $insert_data;
}

function relevanssi_index_users() {
	global $wpdb, $relevanssi_variables;

	$wpdb->query("DELETE FROM " . $relevanssi_variables['relevanssi_table'] . " WHERE type = 'user'");
	if (function_exists('get_users')) {
		$users_list = get_users();
	}
	else {
		$users_list = get_users_of_blog();
	}

	$users = array();
	foreach ($users_list as $user) {
		$users[] = get_userdata($user->ID);
	}

	$index_subscribers = get_option('relevanssi_index_subscribers');
	if ( defined( 'WP_CLI' ) && WP_CLI ) $progress = WP_CLI\Utils\make_progress_bar( 'Indexing users', count($users) );
	foreach ($users as $user) {
		if ($index_subscribers == 'off') {
			$vars = get_object_vars($user);
			$subscriber = false;
			if (is_array($vars["caps"])) {
				foreach ($vars["caps"] as $role => $val) {
					if ($role == 'subscriber') {
						$subscriber = true;
						break;
					}
				}
			}
			if ($subscriber) continue;
		}

		$update = false;

		$index_this_user = apply_filters('relevanssi_user_index_ok', true, $user);
		if ($index_this_user) {
			relevanssi_index_user($user, $update);
		}
		if ( defined( 'WP_CLI' ) && WP_CLI ) $progress->tick();
	}
	if ( defined( 'WP_CLI' ) && WP_CLI ) $progress->finish();
}

function relevanssi_index_users_ajax($limit, $offset) {
	global $wpdb, $relevanssi_variables;

	$args = array(
		'number' => $limit,
		'offset' => $offset,
	);
	
	$index_subscribers = get_option('relevanssi_index_subscribers');
	if ($index_subscribers !== 'on') {
		$args['role__not_in'] = array('subscriber');
	}

	$users_list = get_users($args);

	$users = array();
	foreach ($users_list as $user) {
		$users[] = get_userdata($user->ID);
	}

	$indexed_users = 0;
	foreach ($users as $user) {
		$update = false;
		if (empty($user->roles)) continue;
		$index_this_user = apply_filters('relevanssi_user_index_ok', true, $user);
		if ($index_this_user) {
			relevanssi_index_user($user, $update);
			$indexed_users++;
		}
	}

	$response = array(
		'indexed' => $indexed_users,
	);
	
	return $response;
}

function relevanssi_index_user($user, $remove_first = false) {
	global $wpdb, $relevanssi_variables;

	if (is_numeric($user)) {
		$user = get_userdata($user);
	}

	if ($remove_first)
		relevanssi_delete_user($user->ID);

	$user = apply_filters('relevanssi_user_add_data', $user);

	$insert_data = array();
	$min_length = get_option('relevanssi_min_word_length', 3);

	$user_meta = get_option('relevanssi_index_user_meta');
	if ($user_meta) {
		$user_meta_fields = explode(',', $user_meta);
		foreach ($user_meta_fields as $key) {
			$key = trim($key);
			$values = get_user_meta($user->ID, $key, false);
			foreach($values as $value) {
				$tokens = relevanssi_tokenize($value, true, $min_length); // true = remove stopwords
				foreach($tokens as $term => $tf) {
					isset($insert_data[$term]['content']) ? $insert_data[$term]['content'] += $tf : $insert_data[$term]['content'] = $tf;
				}
			}
		}
	}

	$extra_fields = get_option('relevanssi_index_user_fields');
	if ($extra_fields) {
		$extra_fields = explode(',', $extra_fields);
		$user_vars = get_object_vars($user);
		foreach ($extra_fields as $field) {
			$field = trim($field);
			if (isset($user_vars[$field]) || isset($user_vars['data']->$field) || get_user_meta($user->ID, $field, true)) {
				$to_tokenize = "";
				if (isset($user_vars[$field])) {
					$to_tokenize = $user_vars[$field];
				}
				if (empty($to_tokenize) && isset($user_vars['data']->$field)) {
					$to_tokenize = $user_vars['data']->$field;
				}
				if (empty($to_tokenize)) {
					$to_tokenize = get_user_meta($user->ID, $field, true);
				}
				$tokens = relevanssi_tokenize($to_tokenize, true, $min_length); // true = remove stopwords
				foreach($tokens as $term => $tf) {
					isset($insert_data[$term]['content']) ? $insert_data[$term]['content'] += $tf : $insert_data[$term]['content'] = $tf;
				}
			}
		}
	}

	if (isset($user->description) && $user->description != "") {
		$tokens = relevanssi_tokenize($user->description, true, $min_length); // true = remove stopwords
		foreach($tokens as $term => $tf) {
			isset($insert_data[$term]['content']) ? $insert_data[$term]['content'] += $tf : $insert_data[$term]['content'] = $tf;
		}
	}

	if (isset($user->first_name) && $user->first_name != "") {
		$parts = explode(" ", $user->first_name);
		foreach($parts as $part) {
			isset($insert_data[$part]['title']) ? $insert_data[$part]['title']++ : $insert_data[$part]['title'] = 1;
		}
	}

	if (isset($user->last_name) && $user->last_name != "") {
		$parts = explode(" ", $user->last_name);
		foreach($parts as $part) {
			isset($insert_data[$part]['title']) ? $insert_data[$part]['title']++ : $insert_data[$part]['title'] = 1;
		}
	}

	if (isset($user->display_name) && $user->display_name != "") {
		$parts = explode(" ", $user->display_name);
		foreach($parts as $part) {
			isset($insert_data[$part]['title']) ? $insert_data[$part]['title']++ : $insert_data[$part]['title'] = 1;
		}
	}

	$insert_data = apply_filters('relevanssi_user_data_to_index', $insert_data, $user);

	foreach ($insert_data as $term => $data) {
		$content = 0;
		$title = 0;
		$comment = 0;
		$tag = 0;
		$link = 0;
		$author = 0;
		$category = 0;
		$excerpt = 0;
		$taxonomy = 0;
		$customfield = 0;
		extract($data);

		$query = $wpdb->prepare("INSERT IGNORE INTO " . $relevanssi_variables['relevanssi_table'] . "
			(item, doc, term, term_reverse, content, title, comment, tag, link, author, category, excerpt, taxonomy, customfield, type, customfield_detail, taxonomy_detail, mysqlcolumn_detail)
			VALUES (%d, %d, %s, REVERSE(%s), %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %s, %s)",
			$user->ID, -1, $term, $term, $content, $title, $comment, $tag, $link, $author, $category, $excerpt, $taxonomy, $customfield, 'user', '', '', '');
		$wpdb->query($query);
	}
}

function relevanssi_count_users() {
	$index_users = get_option('relevanssi_index_users');
	if (empty($index_users) || $index_users === "off") return -1;
	
	global $wpdb;

	$users = count_users('time');
	$index_subscribers = get_option('relevanssi_index_subscribers');
	
	$count_users = $users['total_users'];
	if (empty($index_subscribers) || $index_subscribers === 'off') $count_users -= $users['avail_roles']['subscriber'];
	$count_users -= $users['avail_roles']['none'];

	return $count_users;
}

function relevanssi_count_taxonomy_terms() {
	$index_taxonomies = get_option('relevanssi_index_taxonomies');
	if (empty($index_taxonomies) || $index_taxonomies === "off") return -1;

	global $wpdb;

	$taxonomies = get_option('relevanssi_index_terms');
	if (empty($taxonomies)) return -1;
	$count_terms = 0;
	foreach ($taxonomies as $taxonomy) {
		$hide_empty = apply_filters('relevanssi_hide_empty_terms', true);
		$hide_empty ? $count = "AND tt.count > 0" : $count = "";
		$terms = $wpdb->get_col("SELECT t.term_id FROM $wpdb->terms AS t, $wpdb->term_taxonomy AS tt WHERE t.term_id = tt.term_id $count AND tt.taxonomy = '$taxonomy'");
		$count_terms += count($terms);
	}
	return $count_terms;
}

function relevanssi_list_taxonomies() {
	return get_option('relevanssi_index_terms');
}

function relevanssi_index_taxonomies_ajax($taxonomy, $limit, $offset) {
	global $wpdb, $relevanssi_variables, $wp_version;

	$indexed_terms = 0;
	$relevanssi_table = $relevanssi_variables['relevanssi_table'];
	$end_reached = false;
	$hide_empty = apply_filters('relevanssi_hide_empty_terms', true);
	$hide_empty ? $count = "AND tt.count > 0" : $count = "";
	$terms = $wpdb->get_col("SELECT t.term_id FROM $wpdb->terms AS t, $wpdb->term_taxonomy AS tt WHERE t.term_id = tt.term_id $count AND tt.taxonomy = '$taxonomy' LIMIT $limit OFFSET $offset");
	if (count($terms) < $limit) $end_reached = true;

	foreach ($terms as $term_id) {
		$update = false;
		$term = get_term($term_id, $taxonomy);
		$term = apply_filters('relevanssi_term_add_data', $term, $taxonomy);
		relevanssi_index_taxonomy_term($term, $taxonomy, $update);
		$indexed_terms++;
	}
	
	$response = array(
		'indexed' => $indexed_terms,
		'taxonomy_completed' => 'not',
	);
	if ($end_reached) $response['taxonomy_completed'] = "done";

	return $response;
}

function relevanssi_index_taxonomies($is_ajax = false) {
	global $wpdb, $relevanssi_variables;

	$wpdb->query("DELETE FROM " . $relevanssi_variables['relevanssi_table'] . " WHERE type = 'taxonomy'");

	$taxonomies = get_option('relevanssi_index_terms');
	$indexed_terms = 0;
	foreach ($taxonomies as $taxonomy) {
		$args = apply_filters('relevanssi_index_taxonomies_args', array());
		$terms = get_terms($taxonomy, $args);
		if ( defined( 'WP_CLI' ) && WP_CLI ) $progress = WP_CLI\Utils\make_progress_bar( "Indexing $taxonomy", count($terms) );
		foreach ($terms as $term) {
			$update = false;
			$term = apply_filters('relevanssi_term_add_data', $term, $taxonomy);
			relevanssi_index_taxonomy_term($term, $taxonomy, $update);
			$indexed_terms++;
			if ( defined( 'WP_CLI' ) && WP_CLI ) $progress->tick();
		}
		if ( defined( 'WP_CLI' ) && WP_CLI ) $progress->finish();
	}
	
	if ($is_ajax) {
		if ($indexed_terms > 0) {
			return sprintf(__("Indexed %d taxonomy terms.", "relevanssi"), $indexed_terms);
		}
		else {
			return __("No taxonomies to index.", "relevanssi");
		}
	} 
}

function relevanssi_index_taxonomy_term($term, $taxonomy, $remove_first = false) {
	global $wpdb, $relevanssi_variables;

	if (is_numeric($term)) {
		$term = get_term($term, $taxonomy);
	}

	$temp_post = new stdClass();
	$temp_post->post_content = $term->description;
	$temp_post->post_title = $term->name;
	$temp_post = apply_filters('relevanssi_post_to_index', $temp_post, $term);
	$term->description = $temp_post->post_content;
	$term->name = $temp_post->post_title;

	$index_this_post = true;

	if (true == apply_filters('relevanssi_do_not_index_term', false, $term, $taxonomy)) {
		// filter says no
		if ($debug) relevanssi_debug_echo("relevanssi_do_not_index_term returned true.");
		$index_this_post = false;
	}

	if ($remove_first)
		relevanssi_delete_taxonomy_term($term->term_id, 0, $taxonomy);
		// the 0 doesn't mean anything, but because of a WP hook parameters, it needs to be there
		// so the taxonomy can be passed as the third parameter.

	// This needs to be here, after the call to relevanssi_remove_doc(), because otherwise
	// a post that's in the index but shouldn't be there won't get removed.
	if (!$index_this_post) {
		return "donotindex";
	}

	$insert_data = array();

	$min_length = get_option('relevanssi_min_word_length', 3);
	if (!isset($term->description)) {
  		$term->description = "";
	}
	$description = apply_filters('relevanssi_tax_term_additional_content', $term->description, $term);
	if (!empty($description)) {
  		$tokens = relevanssi_tokenize($description, true, $min_length); // true = remove stopwords
  		foreach ($tokens as $t_term => $tf) {
    		isset($insert_data[$t_term]['content']) ? $insert_data[$t_term]['content'] += $tf : $insert_data[$t_term]['content'] = $tf;
  		}
	}

	if (isset($term->name) && $term->name != "") {
		$tokens = relevanssi_tokenize($term->name, true, $min_length); // true = remove stopwords
		foreach ($tokens as $t_term => $tf) {
			isset($insert_data[$t_term]['title']) ? $insert_data[$t_term]['title'] += $tf : $insert_data[$t_term]['title'] = $tf;
		}
	}

	foreach ($insert_data as $t_term => $data) {
		$t_term = trim($t_term); // Numeric terms start with a space
		$content = 0;
		$title = 0;
		$comment = 0;
		$tag = 0;
		$link = 0;
		$author = 0;
		$category = 0;
		$excerpt = 0;
		$customfield = 0;
		extract($data);

		$query = $wpdb->prepare("INSERT IGNORE INTO " . $relevanssi_variables['relevanssi_table'] . "
			(item, doc, term, term_reverse, content, title, comment, tag, link, author, category, excerpt, taxonomy, customfield, type, customfield_detail, taxonomy_detail, mysqlcolumn_detail)
			VALUES (%d, %d, %s, REVERSE(%s), %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %s, %s)",
			$term->term_id, -1, $t_term, $t_term, $content, $title, $comment, $tag, $link, $author, $category, $excerpt, '', $customfield, $taxonomy, '', '', '');

		$wpdb->query($query);
	}
}

function relevanssi_premium_remove_doc($id, $keep_internal_links = false) {
	global $wpdb, $relevanssi_variables;

	if (empty($id)) return; // can't delete anything, if ID is not specified

	$and = $keep_internal_links ? 'AND link = 0' : '';

	$D = get_option( 'relevanssi_doc_count');

 	$q = "DELETE FROM " . $relevanssi_variables['relevanssi_table'] . " WHERE doc=$id";
	$wpdb->query($q);
	$rows_updated = $wpdb->query($q);

	if($rows_updated && $rows_updated > 0) {
		update_option('relevanssi_doc_count', $D - $rows_updated);
	}
}

function relevanssi_remove_item($id, $type) {
	global $wpdb, $relevanssi_variables;

	if ($id == 0 && $type == 'post') {
		return;
		// this should never happen, but in case it does, let's not empty the whole database
	}

	$q = "DELETE FROM " . $relevanssi_variables['relevanssi_table'] . " WHERE item = $id AND type = '$type'";
	$wpdb->query($q);
}

function relevanssi_hide_post($id) {
	$hide = get_post_meta($id, '_relevanssi_hide_post', true);
	if ($hide == "on") return true;
	return false;
}