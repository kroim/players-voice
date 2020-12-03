<?php

add_action('add_meta_boxes_attachment', 'relevanssi_add_pdf_metaboxes');
add_action('edit_attachment', 'relevanssi_save_pdf_box');
add_filter('relevanssi_index_custom_fields', 'relevanssi_add_pdf_customfield');
add_action('add_attachment', 'relevanssi_read_attachment', 10);

function relevanssi_read_attachment($post) {
    $post_status = get_post_status($post);
    if ('auto-draft' == $post_status) return;

    if (get_option('relevanssi_read_new_files') !== "on") return;

    $mime_type = get_post_mime_type($post);
    $valid_mime_types = array('application/pdf');
    if (in_array($mime_type, $valid_mime_types)) {
        $result = relevanssi_index_pdf($post);
        
        if (is_array($result) && isset($result['success']) && $result['success'] === true) {
            // Remove the usual relevanssi_publish action because relevanssi_index_pdf() indexes the post
            remove_action('add_attachment', 'relevanssi_publish', 12);
        }
    }
}

function relevanssi_add_pdf_customfield($custom_fields) {
    if (!is_array($custom_fields)) $custom_fields = array();
    $custom_fields[] = "_relevanssi_pdf_content";
    return $custom_fields;
}

add_filter('relevanssi_pre_excerpt_content', 'relevanssi_add_pdf_content_to_excerpt', 10, 2);
function relevanssi_add_pdf_content_to_excerpt($content, $post) {
    $pdf_content = get_post_meta($post->ID, '_relevanssi_pdf_content', true);
    $content .= " " . $pdf_content;
    return $content;
}

function relevanssi_add_pdf_metaboxes($post) {
    // Only display on PDF pages
	if ($post->post_mime_type != 'application/pdf') return;

	add_meta_box(
        'relevanssi_pdf_box',
        __( 'Relevanssi PDF controls', 'relevanssi' ),
    	'relevanssi_pdf_metabox',
     	$post->post_type
   	 );

     add_action('future_to_publish',
 	  function($post) {
     	remove_action('edit_attachment', 'relevanssi_save_pdf_box');
 	  }
 	);
}

function relevanssi_pdf_metabox() {
	wp_nonce_field(plugin_basename(__FILE__), 'relevanssi_pdf_index');

	global $post;
	$url = wp_get_attachment_url($post->ID);
    $id = $post->ID;
    $button_text = __('Index PDF content', 'relevanssi');
    $api_key = get_site_option('relevanssi_api_key');
    $admin_url = esc_url( admin_url('admin-post.php') );
    $action = "sendUrl";
    $explanation = __("Indexer will fetch the file from your server.", "relevanssi");
    if (get_option('relevanssi_send_pdf_files') === "on") {
        $action = "sendPdf";
        $explanation = __("The file will be uploaded to the indexer.", "relevanssi");
    }
    
    if (!$api_key) {
        printf("<p>%s</p>", __('No API key set. API key is required for PDF indexing.', 'relevanssi'));
    }
    else {
    	echo <<<EOH
<p><input type="button" id="$action" value="$button_text" class="button-primary button-large" data-api_key="$api_key" data-post_id="$id" data-url="$url" title="$explanation"/>
</p>
EOH;

        $pdf_content = get_post_meta($post->ID, '_relevanssi_pdf_content', true);
        if ($pdf_content) {
            $pdf_content_title = __('PDF Content', 'relevanssi');
            echo <<<EOH
<h3>$pdf_content_title</h3>
<p><textarea cols="80" rows="4" readonly>$pdf_content</textarea></p>
EOH;
        }

        $pdf_error = get_post_meta($post->ID, '_relevanssi_pdf_error', true);
        if ($pdf_error) {
            $pdf_error_title = __('PDF error message', 'relevanssi');
            echo <<<EOH
<h3>$pdf_error_title</h3>
<p><textarea cols="80" rows="4" readonly>$pdf_error</textarea></p>
EOH;
        }

        if (empty($pdf_content) && empty($pdf_error)) {
            printf("<p>%s</p>", __('This page will reload after indexing and you can see the response from the PDF extracting server.', 'relevanssi'));
        }
    }

}

function relevanssi_save_pdf_box($post_id) {
	// verify if this is an auto save routine.
	// If it is our form has not been submitted, so we dont want to do anything
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;

	if (isset($_POST['relevanssi_pdf_index'])) {
		if (!wp_verify_nonce($_POST['relevanssi_pdf_index'], plugin_basename( __FILE__ )))
			return;
	}

	// Check permissions
	if (!current_user_can('edit_attachment', $post_id)) return;
}

function relevanssi_index_pdf($post_id, $ajax = false, $send_file = null) {
    $hide_post = get_post_meta($post_id, '_relevanssi_hide_post', true);
    if ($hide_post) {
        $error = "Post excluded from the index by the user.";
        delete_post_meta($post_id, '_relevanssi_pdf_content');
        update_post_meta($post_id, '_relevanssi_pdf_error', $error);
        
        $result = array(
            'success' => false,
            'error' => $error,
        );
    
        return $result;    
    }

    if (is_null($send_file)) {
        $send_file = get_option('relevanssi_send_pdf_files');
    }

    $api_key = get_site_option('relevanssi_api_key');

    if ($send_file) {
        $file_name = get_attached_file( $post_id );

        $file = @fopen( $file_name, 'r' );
        if ($file === false) {
            $response = new WP_Error('fopen', "Could not open the file for reading.");
        }
        else {
            $file_size = filesize( $file_name );
            $file_data = fread( $file, $file_size );
            $args = array(
                'headers'     => array(
                    'accept'        => 'application/json', // The API returns JSON
                    'content-type'  => 'application/binary', // Set content type to binary
                ),
                'body'      => $file_data,
                'timeout'   => apply_filters('relevanssi_pdf_read_timeout', 45),
            );
            $response = wp_safe_remote_post( RELEVANSSI_SERVICE_URL . 'index.php?key=' . $api_key . '&upload=true', $args );
        }
    }
    else {
        $url = wp_get_attachment_url($post_id);

        $args = array(
            'body' => array(
                'key' => $api_key,
                'url' => $url,
            ),
            'method' => 'POST',
            'timeout' => apply_filters('relevanssi_pdf_read_timeout', 45),
        );

        $response = wp_safe_remote_post(RELEVANSSI_SERVICE_URL, $args);
    }

    $result = relevanssi_process_server_response($response, $post_id);

    if ($ajax) {
        echo json_encode($result);
        wp_die();
    }

    return $result;
}

function relevanssi_process_server_response($response, $post_id) {
    $success = null;
    $response_error = "";
    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        $response_error .= $error_message . "\n";
        delete_post_meta($post_id, '_relevanssi_pdf_content');
        update_post_meta($post_id, '_relevanssi_pdf_error', $error_message);
        $success = false;
    }
    else {
        if ( isset($response['body']) ) {
            $content = $response['body'];

            $content = json_decode($content);

            if (isset($content->error)) {
                delete_post_meta($post_id, '_relevanssi_pdf_content');
                update_post_meta($post_id, '_relevanssi_pdf_error', $content->error);
                
                $response_error .= $content->error;
                $success = false;
            }
            else {
                delete_post_meta($post_id, '_relevanssi_pdf_error');
                update_post_meta($post_id, '_relevanssi_pdf_content', $content);
                relevanssi_index_doc($post_id, false, relevanssi_get_custom_fields(), true);

                $success = true;
            }
        }
    }

    $response = array(
        'success' => $success,
        'error' => $response_error,
    );

    return $response;    
}

function relevanssi_get_posts_with_pdfs($limit = -1, $include_timeouts = true) {
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => $limit,
        'fields' => 'ids',
        'post_mime_type' => 'application/pdf',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => '_relevanssi_pdf_content',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'relation' => 'OR',
                array(
                    'key' => '_relevanssi_pdf_error',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key' => '_relevanssi_pdf_error',
                    'compare' => 'LIKE',
                    'value' => 'cURL error 7:'
                ),
                array(
                    'key' => '_relevanssi_pdf_error',
                    'compare' => 'LIKE',
                    'value' => 'cURL error 28:'
                ),
            ),
        )
    );

    $pdf_attachments = get_posts($args);
    
    return $pdf_attachments;
}

function relevanssi_pdf_action_javascript() { ?>
	<script type="text/javascript" >
    var time = 0;
    var intervalID = 0;

    function relevanssiUpdateClock() {
        time++;
        var time_formatted = rlv_format_time(Math.round(time));
        document.getElementById("relevanssi_elapsed").innerHTML = time_formatted;
    }

    jQuery(document).ready(function($) {
        $("#index").click(function() {
            $("#relevanssi-progress").show();
            $("#relevanssi_results").show();
            $("#relevanssi-timer").show();
		    $("#stateofthepdfindex").html(relevanssi.reload_state);

            intervalID = window.setInterval(relevanssiUpdateClock, 1000);

            var data = {
			    'action': 'relevanssi_list_pdfs',
		    };

            console.log("Getting a list of pdfs.");

            var pdf_ids;

		    jQuery.post(ajaxurl, data, function(response) {
                pdf_ids = JSON.parse(response);
                console.log(pdf_ids);
                console.log("Fetching response: " + response);
                console.log("Heading into step 0");
                console.log(pdf_ids.length);
                process_step(0, pdf_ids.length, 0);
            });
        });
        $("#reset").click(function($) {
            if (confirm( relevanssi.pdf_reset_confirm )) {
                var data = {
                    'action': 'relevanssi_wipe_pdfs'
                }
                jQuery.post(ajaxurl, data, function(response) {
                    alert( relevanssi.pdf_reset_done + response );
                    jQuery("#stateofthepdfindex").html(relevanssi.reload_state);
                });
            }
            else {
                return false;
            }
        });
	});

    function process_step(completed, total, total_seconds) {
        var t0 = performance.now();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'relevanssi_index_pdfs',
                completed: completed,
                total: total,
            },
            dataType: 'json',
            success: function(response) {
                console.log(response);
                var relevanssi_results = document.getElementById("relevanssi_results");
                if (response.completed == 'done') {
                    relevanssi_results.value += response.feedback;
                    jQuery('.rpi-progress div').animate({
		                width: response.percentage + '%',
	                    }, 50, function() {
		                // Animation complete.
                    });

                    clearInterval(intervalID);
                }
                else {
                    var t1 = performance.now();
		    		var time_seconds = (t1 - t0) / 1000;
	    			time_seconds = Math.round(time_seconds * 100) / 100;
                    total_seconds += time_seconds;
                    
                    var estimated_time = rlv_format_approximate_time(Math.round(total_seconds / response.percentage * 100 - total_seconds));
                    document.getElementById("relevanssi_estimated").innerHTML = estimated_time;

                    relevanssi_results.value += response.feedback;
                    relevanssi_results.scrollTop = relevanssi_results.scrollHeight;
                    jQuery('.rpi-progress div').animate({
		                width: response.percentage + '%',
	                    }, 50, function() {
		                // Animation complete.
	                });
                    console.log("Heading into step " + response.completed);
                    process_step(parseInt(response.completed), total, total_seconds);                 
                }
            }
        })        
    }

 	</script> <?php
}