<?php
define('WP_USE_THEMES', false);
require ('../../../../wp-blog-header.php');
query_posts('showposts=1');

header("Content-type: text/css");
header('Cache-control: must-revalidate');

$args = array(
	'post_type' => 'bgscontent'
);
$contents = get_posts( $args );  

if(isset($_GET['cod']) && $_GET['cod'] == 'adm') { $css = ''; }
else { $css = '::before'; }

foreach ($contents as $content) {
	$custom = get_post_custom($content->ID);
	$bg_classname = $custom["bg_classname"][0];

	$img = wp_get_attachment_image_src( get_post_thumbnail_id($content->ID), 'full' );
  $thumbFull = $img[0]; 

	print(".is-style-".$bg_classname.$css." {background: transparent url('".$thumbFull."') center top no-repeat;background-size: cover;}");
}

