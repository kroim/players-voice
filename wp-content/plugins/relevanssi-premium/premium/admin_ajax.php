<?php

add_action( 'wp_ajax_relevanssi_list_pdfs', 'relevanssi_list_pdfs_action' );
add_action( 'wp_ajax_relevanssi_wipe_pdfs', 'relevanssi_wipe_pdfs_action' );
add_action( 'wp_ajax_relevanssi_index_pdfs', 'relevanssi_index_pdfs_action' );
add_action( 'wp_ajax_relevanssi_index_pdf', 'relevanssi_index_pdf_action' );
add_action( 'wp_ajax_relevanssi_send_pdf', 'relevanssi_send_pdf' );
add_action( 'wp_ajax_relevanssi_send_url', 'relevanssi_send_url' );
add_action( 'wp_ajax_relevanssi_update_post_meta', 'relevanssi_update_post_meta' );
add_action( 'wp_ajax_relevanssi_update_error', 'relevanssi_update_error' );
add_action( 'wp_ajax_relevanssi_get_pdf_errors', 'relevanssi_get_pdf_errors_action' );
add_action( 'wp_ajax_relevanssi_index_taxonomies', 'relevanssi_index_taxonomies_ajax_wrapper' );
add_action( 'wp_ajax_relevanssi_count_taxonomies', 'relevanssi_count_taxonomies_ajax_wrapper' );
add_action( 'wp_ajax_relevanssi_index_users', 'relevanssi_index_users_ajax_wrapper' );
add_action( 'wp_ajax_relevanssi_count_users', 'relevanssi_count_users_ajax_wrapper' );
add_action( 'wp_ajax_relevanssi_list_taxonomies', 'relevanssi_list_taxonomies_wrapper' );

function relevanssi_list_pdfs_action() {
    $limit = 0;
    if (isset($_POST['limit'])) $limit = $_POST['limit'];
	$pdfs = relevanssi_get_posts_with_pdfs();
    echo json_encode($pdfs);

	wp_die();
}

function relevanssi_wipe_pdfs_action() {
    global $wpdb;
    $num_rows_content = $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_pdf_content'");
    $num_rows_error = $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_pdf_error'");

    $num_rows = 0;
    if ($num_rows_content) $num_rows += $num_rows_content;
    if ($num_rows_error) $num_rows += $num_rows_error;
    
    echo $num_rows;

	wp_die();
}

function relevanssi_index_pdf_action() {
	$post_id = $_POST['post_id'];
    $echo_and_die = false;
    $send_files = get_option('relevanssi_send_pdf_files');
    if ($send_files == "off") $send_files = false;

    $response = relevanssi_index_pdf($post_id, $echo_and_die, $send_files);

    if ($response['success']) {
        printf(__("Successfully indexed %d.", 'relevanssi'), $post_id);
    }
    else {
        printf(__("Failed to index %d: %s.", 'relevanssi'), $post_id, $response['error']);
    }

	wp_die();
}

function relevanssi_index_pdfs_action() {
    $pdfs = relevanssi_get_posts_with_pdfs(3);

    $completed = absint( $_POST['completed'] );
    $total = absint( $_POST['total'] );

    $response = array();
    $response['feedback'] = "";

    if (empty($pdfs)) {
        $response['feedback'] = __("Indexing complete!", "relevanssi");
        $response['completed'] = "done";
        $response['percentage'] = 100;
    }
    else {
        foreach ($pdfs as $post_id) {
            $echo_and_die = false;
            $send_files = get_option('relevanssi_send_pdf_files');
            if ($send_files == "off") $send_files = false;

            $index_response = relevanssi_index_pdf($post_id, $echo_and_die, $send_files);
            $completed++;

            if ($index_response['success']) {
                $response['feedback'] .= sprintf(__("Successfully indexed attachment id %d.", "relevanssi"), $post_id) . "\n";
            }
            else {
                $response['feedback'] .= sprintf(__("Failed to index attachment id %d: %s", "relevanssi"), $post_id, $index_response['error']) . "\n";
            }
        }
        $response['completed'] = $completed;
        $total > 0 ? $response['percentage'] = round($completed / $total * 100, 0) : $response['percentage'] = 0;
    }

    echo json_encode($response);

	wp_die();
}

function relevanssi_send_pdf() {
    $post_id = $_REQUEST['post_id'];
    $echo_and_die = true;
    $send_file = true;
    relevanssi_index_pdf( $post_id, $echo_and_die, $send_file );
    
    // Just for sure; relevanssi_index_pdf() should echo necessary responses and die, so don't expect this to ever happen.
    wp_die();
}

function relevanssi_send_url() {
    $post_id = $_REQUEST['post_id'];
    $echo_and_die = true;
    $send_file = false;
    relevanssi_index_pdf( $post_id, $echo_and_die, $send_file );
    
    // Just for sure; relevanssi_index_pdf() should echo necessary responses and die, so don't expect this to ever happen.
    wp_die();
}

function relevanssi_update_post_meta() {
    $post_id = intval( $_POST['post_id'] );
    $content = $_POST['content'];

	delete_post_meta($post_id, '_relevanssi_pdf_error');
    update_post_meta($post_id, '_relevanssi_pdf_content', $content);
    relevanssi_index_doc($post_id, false, relevanssi_get_custom_fields(), true);
    wp_die();
}

function relevanssi_update_error() {
    $post_id = intval( $_POST['post_id'] );
    $content = $_POST['content'];

    $content = json_decode(stripslashes($content));
    $error_message = $content->error;

	delete_post_meta($post_id, '_relevanssi_pdf_content');
    update_post_meta($post_id, '_relevanssi_pdf_error', $error_message);
    wp_die();
}

function relevanssi_get_pdf_errors_action() {
    global $wpdb;

    $errors = $wpdb->get_results("SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_pdf_error'");
    $error_message = array();
    foreach ($errors as $error) {
        $row = __("Attachment ID", 'relevanssi') . " " . $error->post_id . ": " . $error->meta_value;
        $row = str_replace("PDF Processor error: ", "", $row);
        $error_message[] = $row;
    }
    echo json_encode(implode("\n", $error_message));
    wp_die();
}

function relevanssi_list_taxonomies_wrapper() {
    $taxonomies = array();
    if (function_exists('relevanssi_list_taxonomies')) {
        $taxonomies = relevanssi_list_taxonomies();
    }
    echo json_encode($taxonomies);
    wp_die();
}

function relevanssi_index_taxonomies_ajax_wrapper() {
    $completed = absint( $_POST['completed'] );
    $total = absint( $_POST['total'] );
    $taxonomy = $_POST['taxonomy'];
    $offset = $_POST['offset'];
    $limit = $_POST['limit'];

    $response = array();

    $indexing_response = relevanssi_index_taxonomies_ajax($taxonomy, $limit, $offset);

    $completed += $indexing_response['indexed'];
    if ($completed === $total) {
        $response['completed'] = "done";
        $response['total_posts'] = $completed;
        $response['percentage'] = 100;
        $response['feedback'] = sprintf(_n("%d taxonomy term, total %d / %d.", "%d taxonomy terms, total %d / %d.", $indexing_response['indexed'], 'relevanssi'), $indexing_response['indexed'], $completed, $total) . "\n";
    } 
    else {
        $response['completed'] = $completed;
        $response['feedback'] = sprintf(_n("%d taxonomy term, total %d / %d.", "%d taxonomy terms, total %d / %d.", $indexing_response['indexed'], 'relevanssi'), $indexing_response['indexed'], $completed, $total) . "\n";
        $total > 0 ? $response['percentage'] = $completed / $total * 100 : $response['percentage'] = 0;
        $response['new_taxonomy'] = false;
        if ($indexing_response['taxonomy_completed'] == "done") $response['new_taxonomy'] = true;
    }
    $response['offset'] = $offset + $limit;

    echo json_encode($response);
    wp_die();
}

function relevanssi_index_users_ajax_wrapper() {
    $completed = absint( $_POST['completed'] );
    $total = absint( $_POST['total'] );
    $offset = $_POST['offset'];
    $limit = $_POST['limit'];

    $response = array();

    $indexing_response = relevanssi_index_users_ajax($limit, $offset);

    $completed += $indexing_response['indexed'];
    if ($completed === $total) {
        $response['completed'] = "done";
        $response['total_posts'] = $completed;
        $response['percentage'] = 100;
        $processed = $total;
    } 
    else {
        $response['completed'] = $completed;
        $offset = $offset + $limit;
        $processed = $offset;
        $total > 0 ? $response['percentage'] = $completed / $total * 100 : $response['percentage'] = 0;
    }

    $response['feedback'] = sprintf(_n("Indexed %d user (total %d), processed %d / %d.", "Indexed %d users (total %d), processed %d / %d.", $indexing_response['indexed'], 'relevanssi'), $indexing_response['indexed'], $completed, $processed, $total) . "\n";
    $response['offset'] = $offset;

    echo json_encode($response);
    wp_die();
}

function relevanssi_count_users_ajax_wrapper() {
    $count = -1;
    if (function_exists('relevanssi_count_users')) {
        $count = relevanssi_count_users();
    }
    echo json_encode($count);
    wp_die();
}

function relevanssi_count_taxonomies_ajax_wrapper() {
    $count = -1;
    if (function_exists('relevanssi_count_taxonomy_terms')) {
        $count = relevanssi_count_taxonomy_terms();
    }
    echo json_encode($count);
    wp_die();
}

?>