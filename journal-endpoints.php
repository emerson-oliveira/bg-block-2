<?php

require_once __DIR__ . "/installation.php";
require_once __DIR__ . "/../../../wp-admin/includes/plugin.php";

function check_wpdb_is_fine() {
	global $wpdb;
	if ( $wpdb->last_error != "" ) {
		http_response_code( 500 );
		die( $wpdb->last_error );
	}
}

function ensure_log_installed() {
	if ( !check_log_installed() ) {
		http_response_code( 400 );
		die( "Log not installed." );
	}
}

/**
 * Clean a MySQL date into a ISO8601 date.
 * "0000-00-00 00:00:00" is interpreted as null.
 *
 * @param $date_string string Date received from MySQL.
 * @return string|null ISO8601 string with date.
 */
function clean_date_utc($date_string) {
	if ( $date_string != "0000-00-00 00:00:00" ) {
		$date = date_create_from_format( "Y-m-d H:i:s" , $date_string ,
			new DateTimeZone( "UTC" ) );
		return ($date)?  $date->format( DATE_ATOM ): date(DATE_ATOM);

	} else {
		return null;
	}
}

function clean_string_to_array($string){
    return (empty($string))? array() : explode(",", $string);
}


function clean_enum_on_off($string){
    // Check if the value is from enum
    return (in_array($string, ['on', 'off']))? $string: 'off' ;
}

function format_term_info($term) {
    $term_id = $term->term_id;
    $term_language = null;
	if ( function_exists('pll_get_term_language') ) {
		$term_language = pll_get_term_language( $term_id );
	}
    return [
        "id" 			            => $term_id                                                                             ,
        "name" 			            => $term->name                                                                          ,
        "slug"			            => $term->slug                                                                          ,
        "description"	            => $term->description                                                                   ,
        "disable_ads"               => get_term_meta( $term_id, '_mc_ef_disable_ads', true ) == 'on' ? 'off' : 'on'         ,
        "disable_marfeel"           => get_term_meta( $term_id, '_mc_ef_disable_marfeel', true ) == 'on' ? 'off' : 'on'     ,
        "is_highlight"              => get_term_meta( $term_id, '_mc_ef_is_highlight', true ) == 'on' ? 'off' : 'on'        ,
        "category_style"            => get_term_meta( $term_id, '_mc_ef_category_style', true )                             ,
        "highlight_media"           => esc_url( get_term_meta( $term_id, '_mc_ef_highlight_media', true ) )                 ,
        "category_media"            => esc_url( get_term_meta( $term_id, '_mc_ef_category_media', true ) )                  ,
        "category_illustration"     => esc_url( get_term_meta( $term_id, '_mc_ef_category_illustration', true ) )           ,
        "category_icon"             => esc_url( get_term_meta( $term_id, '_mc_ef_category_icon', true ) )                   ,
        "widget_style"              => get_term_meta( $term_id, '_mc_ef_widget_style', true )                               ,
        "language"                  => $term_language                                                                       ,
        "link"                      => get_term_link($term_id)                                                              ,
    ];
}

function get_post_info_categories($post_id) {
    $search = wp_get_post_categories( $post_id );
    $categories = array();
    foreach($search as $item){
        $category = get_category( $item );
        $categories[ count( $categories ) ] = format_term_info($category);
    }
    return $categories;
}

function get_post_info_tags($post_id) {
    $search = wp_get_post_tags( $post_id );
	$tags = array();
	foreach($search as $item){
		$tag = get_tag( $item );
		$tags[ count( $tags ) ] = format_term_info($tag);
	}
    return $tags;
}

function clean_post_into_log_format($data) {
    $post_id = intval( $data['post_id'] );
    $post_language = null;
	if ( function_exists('pll_get_post_language') ) {
		$post_language = pll_get_post_language( $post_id );
    }

	return [
        "post_id" => $post_id ,
        "post_title" => $data["post_title"] ,
		"post_subtitle" => $data["post_subtitle"] ,
        "post_bibliography" => $data["post_bibliography"] ,
        "post_hreflang" => get_post_meta( $post_id, 'mc_hreflang', true ) ?: null,
        "post_image" => get_the_post_thumbnail_url($post_id, 'large'),
        "post_status" => $data["post_status"] ,
        "post_created_date" => clean_date_utc( $data["post_created_date"] ) ,
        "post_date" => clean_date_utc( $data["post_date"] ) ,
        "post_name" => urldecode(get_permalink( intval( $post_id )  )) ,
        "url_original" => urlencode($data["url_original"]) ,
        "post_content" => $data["post_content"] ,
        "post_author" => $data["post_author"]  ,
        "post_writer" => $data["post_writer"]  ,
        "post_editor" => $data["post_editor"]  ,
        "post_professional" => $data["post_professional"]  ,
        "post_signed_date" => clean_date_utc( $data["post_signed_date"] ) ,
        "post_categories" => get_post_info_categories( $post_id )  ,
        "post_tags" => get_post_info_tags( $post_id )  ,
        "post_dfp_topic" => $data["post_dfp_topic"]  ,
        "post_disable_ads" => clean_enum_on_off($data["post_disable_ads"])  ,
        "post_disable_author" => clean_enum_on_off($data["post_disable_author"])  ,
        "post_language" => $post_language  ,
        "headlines" => get_post_headlines_by_id( $post_id ) ,
        "keyword" => get_post_meta( $post_id, '_yoast_wpseo_focuskw', true ) ?: null ,
        "post_babel" => clean_enum_on_off( get_post_meta( $post_id , 'mc_ef_babel' , true )),
        "post_exclude_from_packs" => clean_enum_on_off($data["post_exclude_from_packs"])
	];
}

function clean_db_log_entry($data) {
    $post_language = null;
	if ( function_exists('pll_get_post_language') ) {
		$post_language = pll_get_post_language( $data["post_id"] );
    }

	return [
		// log specific fields
		"log_entry_id" => intval( $data["log_entry_id"] ) ,
		"log_entry_type" => $data["log_entry_type"] ,
		"log_entry_date" => clean_date_utc( $data["log_entry_date"] ) ,

		// ... same as clean_post_into_log_format()
		"post_id" => intval( $data["post_id"] ) ,
		"post_title" => $data["post_title"] ,
		"post_subtitle" => $data["post_subtitle"] ,
        "post_bibliography" => $data["post_bibliography"] ,
        "post_hreflang" => get_post_meta( $data["post_id"], 'mc_hreflang', true ) ?: null,
        "post_image" => get_the_post_thumbnail_url($data["post_id"], 'large'),
		"post_status" => $data["post_status"] ,
        "post_created_date" => clean_date_utc( $data["post_created_date"] ) ,
		"post_date" => clean_date_utc( $data["post_date"] ) ,
        "post_name" => urldecode(get_permalink( intval( $data["post_id"] )  )) ,
		"url_original" => urlencode($data["url_original"]) ,
		"post_content" => $data["post_content"] ,
        "post_author" =>  $data["post_author"] ,
        "post_writer" => $data["post_writer"]  ,
        "post_editor" => $data["post_editor"]  ,
        "post_professional" => $data["post_professional"]  ,
        "post_signed_date" => clean_date_utc( $data["post_signed_date"] ) ,
        "post_categories" => get_post_info_categories( $data["post_id"] )  ,
        "post_tags" => get_post_info_tags( $data["post_id"] )  ,
        "post_dfp_topic" => $data["post_dfp_topic"]  ,
        "post_disable_ads" => clean_enum_on_off($data["post_disable_ads"])  ,
        "post_disable_author" => clean_enum_on_off($data["post_disable_author"])  ,
        "post_language" => $post_language  ,
        "headlines" => get_post_headlines_by_id( $data["post_id"] ) ,
        "keyword" => get_post_meta( $data["post_id"], '_yoast_wpseo_focuskw', true ) ?: null ,
        "post_babel" => clean_enum_on_off( get_post_meta( $data["post_id"] , 'mc_ef_babel' , true )),
        "post_exclude_from_packs" => clean_enum_on_off($data["post_exclude_from_packs"])
	];
}

function check_log_installed() {
	global $wpdb;

	$log_installed = (0 != count( $wpdb->get_results( "SHOW TABLES LIKE 'wp_mc_api_db1_installed'" ) ));
	check_wpdb_is_fine();

	// Log not installed, attempt to install it now.
	if ( !$log_installed ) {
		mc_api_install_db();
		$log_installed = (0 != count( $wpdb->get_results( "SHOW TABLES LIKE 'wp_mc_api_db1_installed'" ) ));
		check_wpdb_is_fine();
	}

	return $log_installed;
}

function check_log_status() {
	global $wpdb;

	$log_installed = check_log_installed();
	if ( !$log_installed ) {
		$log_enabled = false;
	} else {
		$log_enabled = boolval( $wpdb->get_results(
			"SELECT enabled FROM wp_mc_log_status" , ARRAY_N )[0][0] );
	}

	// Note we need to reference the file when the plugin comment manifest is present.
	$mc_api_enabled = is_plugin_active( "mc-api/index.php" );
	$mc_packs_enabled = is_plugin_active( "mc-packs/mc-packs.php" );

	return [
		"log_installed" => $log_installed ,
		"log_enabled" => $log_enabled ,
		"mc_api_enabled" => $mc_api_enabled ,
		"mc_packs_enabled" => $mc_packs_enabled ,
	];
}

function do_initial_log_enable() {
	global $wpdb;

	// Enable logging (will begin on commit)
	$wpdb->query( "UPDATE wp_mc_log_status SET enabled = TRUE" );
	check_wpdb_is_fine();
	$wpdb->query( "COMMIT" );
	check_wpdb_is_fine();
}

function do_initial_synchronization($limit, $last_post_id) {
	global $wpdb;
	ensure_log_installed();
	// Before running this endpoint we need to check if the server
    // has an option value that permit the mysql change the php memory limit
    // See more details: https://stackoverflow.com/questions/5061917/ini-setmemory-limit-in-php-5-3-3-is-not-working-at-all
	ini_set( "memory_limit" , "1024M" );

	$wpdb->query( "START TRANSACTION" );
	check_wpdb_is_fine();

	// Wait for currently running triggers to finish (they also request FOR UPDATE)
	// and make other future triggers wait until we finish the transaction
	// before they decide if they will write in the log.
	$wpdb->query( "SELECT enabled FROM wp_mc_log_status FOR UPDATE" );
	check_wpdb_is_fine();

	// Build a snapshot of the database as is now, before any logging takes place.
	$query = $wpdb->prepare( "
SELECT
  ID                     AS post_id,
  post_title,
  (SELECT meta_value FROM wp_postmeta 
          WHERE wp_postmeta.post_id = wp_posts.ID 
          AND wp_postmeta.meta_key = 'headline' LIMIT 1) AS post_subtitle,
  (SELECT meta_value FROM wp_postmeta 
          WHERE wp_postmeta.post_id = wp_posts.ID 
          AND wp_postmeta.meta_key = 'mc_bibliography' LIMIT 1) AS post_bibliography, 
          post_status,
  post_date              AS post_created_date,
  post_date_gmt          AS post_date,
  post_name,
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_postlink') AS url_original,
  post_content,
  (SELECT wp_users.user_login FROM 
          wp_users 
          WHERE wp_users.ID = wp_posts.post_author LIMIT 1) AS post_author,
  (SELECT u.user_login FROM
      wp_postmeta pm
      INNER JOIN wp_users u ON u.ID = pm.meta_value
      WHERE pm.post_id = wp_posts.ID
      AND pm.meta_key = 'mc_ef_writer_assigned') as post_writer,
  (SELECT u.user_login FROM
      wp_postmeta pm
      INNER JOIN wp_users u ON u.ID = pm.meta_value
      WHERE pm.post_id = wp_posts.ID
      AND pm.meta_key ='mc_ef_editor_assigned') as post_editor,
  (SELECT u.user_login FROM
      wp_postmeta pm
      INNER JOIN wp_users u ON u.ID = pm.meta_value
      WHERE pm.post_id = wp_posts.ID
      AND pm.meta_key ='mc_ef_professional_assigned') as post_professional,
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_ef_box_signed_date') as post_signed_date,
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_dfp_topic') as post_dfp_topic,
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_ef_disable_ads') as post_disable_ads,  
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_ef_disable_author') as post_disable_author,
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_ef_exclude_from_packs') as post_exclude_from_packs
FROM wp_posts
WHERE post_status IN ('draft', 'pending', 'publish', 'future') AND
      post_type = 'post'AND ID > %d
  ORDER BY ID
  LIMIT %d;", $last_post_id, $limit );

	$posts = $wpdb->get_results( $query, ARRAY_A );
	$posts = array_map( "clean_post_into_log_format" , $posts );

	$redirections = $wpdb->get_results( <<<SQLEND
SELECT
  url         AS 'from_url',
  action_data AS 'to_url'
FROM wp_redirection_items
WHERE match_type = 'url' AND status = 'enabled';
SQLEND
		, ARRAY_A );

	return [
		"posts" => $posts ,
		"redirections" => $redirections ,
	];
}

function reset_synchronization() {
	global $wpdb;
	ensure_log_installed();

	$wpdb->query( "START TRANSACTION" );
	check_wpdb_is_fine();

	// Wait for pending triggers and stop further writing.
	$wpdb->query( "SELECT enabled FROM wp_mc_log_status FOR UPDATE" );
	check_wpdb_is_fine();

	// Clear the logs
	$wpdb->query( "DELETE FROM wp_mc_log;" );
	check_wpdb_is_fine();
	$wpdb->query( "DELETE FROM wp_mc_log_post;" );
	check_wpdb_is_fine();

	// Disable logging
	$wpdb->query( "UPDATE wp_mc_log_status SET enabled = FALSE" );
	check_wpdb_is_fine();

	// Pending triggers will wake up at this point and see the log disabled,
	// so they will not write anything.
	$wpdb->query( "COMMIT" );
	check_wpdb_is_fine();
}

function do_incremental_synchronization($last_known_log_id) {
	global $wpdb;
	ensure_log_installed();

	$query = $wpdb->prepare( "
           SELECT
                log_entry_id,   
                log_entry_type, 
                log_entry_date, 
                post_id,        
                post_title,
				post_subtitle,     
				post_bibliography,
                post_status,   
                post_created_date,    
                post_date,      
                post_name,      
                url_original,
                post_content,
                post_author,
                post_writer,
                post_editor,
                post_professional,
                post_signed_date,
                post_categories, 
                post_tags,
                post_dfp_topic,
                post_disable_ads,  
                post_disable_author
           FROM wp_mc_log_post
           WHERE log_entry_id > %d
           LIMIT 1000" , $last_known_log_id );

	$ret = $wpdb->get_results( $query , ARRAY_A );
	check_wpdb_is_fine();

	return [
		"changes" => array_map( "clean_db_log_entry" , $ret ) ,
	];
}

function do_synchronization_article($post_id) {
    global $wpdb;
    ensure_log_installed();
    $wpdb->query( "START TRANSACTION" );
    check_wpdb_is_fine();

    // Wait for currently running triggers to finish (they also request FOR UPDATE)
    // and make other future triggers wait until we finish the transaction
    // before they decide if they will write in the log.
    $wpdb->query( "SELECT enabled FROM wp_mc_log_status FOR UPDATE" );
    check_wpdb_is_fine();
	$query = $wpdb->prepare( "
SELECT
  ID                     AS post_id,
  post_title,
  (SELECT meta_value FROM wp_postmeta 
          WHERE wp_postmeta.post_id = wp_posts.ID 
          AND wp_postmeta.meta_key = 'headline' LIMIT 1) AS post_subtitle,
  (SELECT meta_value FROM wp_postmeta 
          WHERE wp_postmeta.post_id = wp_posts.ID 
          AND wp_postmeta.meta_key = 'mc_bibliography' LIMIT 1) AS post_bibliography, 
          post_status,
  post_date              AS post_created_date,
  post_date_gmt          AS post_date,
  post_name,
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_postlink') AS url_original,
  post_content,
  (SELECT wp_users.user_login FROM 
          wp_users 
          WHERE wp_users.ID = wp_posts.post_author LIMIT 1) AS post_author,
  (SELECT u.user_login FROM
      wp_postmeta pm
      INNER JOIN wp_users u ON u.ID = pm.meta_value
      WHERE pm.post_id = wp_posts.ID
      AND pm.meta_key = 'mc_ef_writer_assigned') as post_writer,
  (SELECT u.user_login FROM
      wp_postmeta pm
      INNER JOIN wp_users u ON u.ID = pm.meta_value
      WHERE pm.post_id = wp_posts.ID
      AND pm.meta_key ='mc_ef_editor_assigned') as post_editor,
  (SELECT u.user_login FROM
      wp_postmeta pm
      INNER JOIN wp_users u ON u.ID = pm.meta_value
      WHERE pm.post_id = wp_posts.ID
      AND pm.meta_key ='mc_ef_professional_assigned') as post_professional,
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_ef_box_signed_date') as post_signed_date,
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_dfp_topic') as post_dfp_topic,
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_ef_disable_ads') as post_disable_ads,  
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_ef_disable_author') as post_disable_author,
  (SELECT meta_value FROM
      wp_postmeta
      WHERE post_id = wp_posts.ID
      AND meta_key = 'mc_ef_exclude_from_packs') as post_exclude_from_packs
FROM wp_posts
WHERE post_status IN ('draft', 'pending', 'publish', 'future') AND
      post_type = 'post' AND ID = %d;" , $post_id );

    $post = $wpdb->get_results( $query, ARRAY_A );
    $post = array_map( "clean_post_into_log_format" , $post );

    $redirections = $wpdb->get_results( <<<SQLEND
SELECT
  url         AS 'from_url',
  action_data AS 'to_url'
FROM wp_redirection_items
WHERE match_type = 'url' AND status = 'enabled';
SQLEND
        , ARRAY_A );

    return [
        "posts" => $post ,
        "redirections" => $redirections ,
    ];
}

function get_post_headlines_by_id($post_id) {
	global $wpdb;

	$sql = $wpdb -> prepare(
		"SELECT wp.meta_id, wp.post_id, wp.meta_value
		FROM wp_postmeta AS wp
		WHERE wp.meta_key = %s 
		AND wp.post_id = %d
		GROUP BY wp.post_id 
		HAVING COUNT(*) > %d;", 'headline', $post_id, 0
	);

	$items = array();

	foreach( $wpdb -> get_results( $sql ) as $key => $row ) {
		$items[ count( $items ) ]= $row->meta_value;
	}

	return $items;
}
