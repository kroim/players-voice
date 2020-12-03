<?php
/*
Plugin Name: Relevanssi Premium
Plugin URI: https://www.relevanssi.com/
Description: This premium plugin replaces WordPress search with a relevance-sorting search.
Version: 2.0.5
Author: Mikko Saari
Author URI: http://www.mikkosaari.fi/
Text Domain: relevanssi
*/

/*  Copyright 2018 Mikko Saari  (email: mikko@mikkosaari.fi)

    This file is part of Relevanssi Premium, a search plugin for WordPress.

    Relevanssi Premium is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Relevanssi Premium is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Relevanssi Premium.  If not, see <http://www.gnu.org/licenses/>.
*/

// For debugging purposes
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
// define('WP-DEBUG', true);

add_action('init', 'relevanssi_premium_init');
add_action('init', 'relevanssi_wptuts_activate_au');
add_action('profile_update', 'relevanssi_profile_update');
add_action('edit_user_profile_update', 'relevanssi_profile_update');
add_action('user_register', 'relevanssi_profile_update');
add_action('delete_user', 'relevanssi_delete_user');
add_action('created_term', 'relevanssi_add_term', 9999, 3);
add_action('edited_term', 'relevanssi_edit_term', 9999, 3);
add_action('delete_term', 'relevanssi_delete_taxonomy_term', 9999, 3);
add_action('wpmu_new_blog', 'relevanssi_new_blog', 10, 6);
add_action('save_post', 'relevanssi_save_postdata');
add_action('edit_attachment', 'relevanssi_save_postdata');
add_filter('wpmu_drop_tables', 'relevanssi_wpmu_drop');
add_action('network_admin_menu', 'relevanssi_network_menu');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'relevanssi_action_links');
add_filter('attachment_link', 'relevanssi_post_link_replace', 10, 2);
add_action('admin_enqueue_scripts', 'relevanssi_premium_add_admin_scripts');

global $wpdb;
global $relevanssi_variables;

$relevanssi_variables['relevanssi_table'] = $wpdb->prefix . "relevanssi";
$relevanssi_variables['stopword_table'] = $wpdb->prefix . "relevanssi_stopwords";
$relevanssi_variables['log_table'] = $wpdb->prefix . "relevanssi_log";
$relevanssi_variables['post_type_weight_defaults']['post_tag'] = 0.5;
$relevanssi_variables['post_type_weight_defaults']['category'] = 0.5;
$relevanssi_variables['content_boost_default'] = 5;
$relevanssi_variables['title_boost_default'] = 5;
$relevanssi_variables['link_boost_default'] = 0.75;
$relevanssi_variables['comment_boost_default'] = 0.75;
$relevanssi_variables['database_version'] = 18;
$relevanssi_variables['plugin_version'] = "2.0.5";
$relevanssi_variables['plugin_dir'] = plugin_dir_path(__FILE__);
$relevanssi_variables['plugin_basename'] = plugin_basename(__FILE__);
$relevanssi_variables['file'] = __FILE__;

define('RELEVANSSI_PREMIUM', true);
define('RELEVANSSI_SERVICE_URL', 'https://www.relevanssiservices.com/');

require_once('lib/install.php');
require_once('lib/init.php');
require_once('lib/interface.php');
require_once('lib/indexing.php');
require_once('lib/stopwords.php');
require_once('lib/search.php');
require_once('lib/excerpts-highlights.php');
require_once('lib/shortcodes.php');
require_once('lib/common.php');
require_once('lib/admin_ajax.php');

require_once('premium/autoupdate.php');
require_once('premium/SpellCorrector.php');
require_once('premium/common.php');
require_once('premium/pdf_upload.php');
require_once('premium/admin_ajax.php');
require_once('premium/interface.php');
require_once('premium/indexing.php');
require_once('premium/search.php');

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once('premium/wp-cli.php');
}