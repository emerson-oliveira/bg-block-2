<?php
/*
 * Plugin name: BG Block
 * Plugin URI: #
 * Version: 1.1
 * Author: HSikorski, ESilva, SAlmeida  - MContigo
 * Author URI: https://mcontigo.com
 */


 /*
 TODO
 - maybe, add just used styles, inserting before the code html from the block <style></style>, to not load all bgs styles
 - check if is necessary a flag for background-size: cover || contain; 
 - check if is necessary creat a new type of thumb, for the backgrounds
 */

// blocks styles bgs
add_action('init', function() {
	$args = array(
		'post_type' => 'bgscontent'
	);
	$contents = get_posts( $args );  
	$count = 0;												
	foreach ($contents as $content) {
		$custom = get_post_custom($content->ID);
		$bg_classname = $custom["bg_classname"][0];
		
		register_block_style('mcontigo/bgcontent', [
			'name' 			=> $bg_classname,
			'label' 		=> __($content->post_title, 'txtdomain'),
			'is_default'=> $count === 0 ? true : false,
			'inline_style' => '.wp-block-quote.is-style-blue-quote { color: blue; }',
		]);
		$count++;
	}			
});

// bgs content
add_action('init', 'init_bgs');
function init_bgs() {
	global $user;	
  $labels = array(
    'name'			 	       => _x('Bgs Contenidos', 'post type general name'),
    'singular_name'  	   => _x('Bgs Contenidos', 'post type singular name'),
    'add_new' 		 	     => _x('Añadir Bg', 'case'),
    'add_new_item' 	 	   => __('Añadir Bg'),
    'edit_item' 	 	     => __('Cambiar Bg'),
    'new_item' 		 	     => __('Nuevo Bg'),
    'view_item' 	 	     => __('Mirar Bg'),
    'search_items' 	 	   => __('Buscar Bg'),
    'not_found' 	 	     => __('Nengun Bg encontrado'),
    'not_found_in_trash' => __('Nengun Bg encontrado en la papelera'), 
    'parent_item_colon'  => '',
		'menu_name'			     => 'Bgs Contenidos'
  );
  $args = array(
    'labels' 			       => $labels,
    'public' 			       => true,
    'publicly_queryable' => true,
    'show_ui' 			     => true, 
    'show_in_menu' 		   => true, 
    'query_var' 		     => true,
    'rewrite' 			     => true,
    'has_archive' 		   => false, 
    'hierarchical' 		   => false,
	  'menu_position' 	   => 4,
	  'menu_icon' 		     => 'dashicons-format-image',
    'supports' 			     => array('title','thumbnail'),
	  'show_in_rest'		   => true,
	  'taxonomies'		     => array('')
  ); 
  register_post_type('bgscontent',$args);
}
function metabox_bg_classname(){ 
  global $post;
  $custom = get_post_custom($post->ID);
  $bg_classname = $custom["bg_classname"][0];
?>
  <input type="text" disabled name="bg_classname" value="<?php echo $bg_classname; ?>" size="50" />
<?php
}
function init_metaboxes(){
	add_meta_box("bg_classname", "The css class name", "metabox_bg_classname", "bgscontent", "normal", "high");
}
add_action("admin_init", "init_metaboxes");

function cmc_save_bgclassname_metabox() {
	global $post;
	// Doing revision, exit earlier **can be removed**
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  
		return;

	// Doing revision, exit earlier
	if ( 'revision' == $post->post_type )
		return;

	if($post->post_type == 'bgscontent'){	
		$classname = preg_replace('/\W+/','_',strtolower(strip_tags(get_the_title($post->ID))));
		update_post_meta($post->ID, "bg_classname", $classname);		
	}
}
add_action('save_post', 'cmc_save_bgclassname_metabox');

// manage_edit-POSTTYPE_columns
add_filter('manage_bgscontent_posts_columns', 'posts_columns', 5);
add_action('manage_bgscontent_posts_custom_column', 'posts_custom_columns', 5, 2);
 
function posts_columns($defaults){
    $defaults['post_thumbs'] = __('Thumbs');
    return $defaults;
} 
function posts_custom_columns($column_name, $id){
    if($column_name === 'post_thumbs'){
        echo the_post_thumbnail( 'post-list-thumbnail' );
    }
}

// on admin
add_action( 'enqueue_block_editor_assets', 'mcontigo_block_assets' );
function mcontigo_block_assets(){

	wp_enqueue_script(
 		'mcontigo-bgcontent',
		plugin_dir_url( __FILE__ ) . 'assets/block-bgcontent.js',
		array( 'wp-blocks', 'wp-element' ),
		filemtime( dirname( __FILE__ ) . '/assets/block-bgcontent.js' )
	);

	wp_enqueue_style(
		'mcontigo-bgcontent-css',
		plugin_dir_url( __FILE__ ) . 'assets/block-bgcontent.css',
		array( 'wp-edit-blocks' ),
		filemtime( dirname( __FILE__ ) . '/assets/block-bgcontent.css' )
	);

	wp_enqueue_style(
		'mcontigo-bgcontent-dinamic-css',
		plugin_dir_url( __FILE__ ) . 'assets/block-dinamic-styles.php?cod=adm',
		array( 'wp-edit-blocks' ),
		filemtime( dirname( __FILE__ ) . '/assets/block-dinamic-styles.php' )
	);
}

// on web
add_action( 'wp_enqueue_scripts', 'mcontigo_block_front_end_assets' );
function mcontigo_block_front_end_assets(){
	wp_enqueue_style(
		'wp-block-mcontigo-bgcontent-css',
		plugin_dir_url( __FILE__ ) . 'assets/bgcontent.css',
		array(),
		filemtime( dirname( __FILE__ ) . '/assets/bgcontent.css' )
	);
	wp_enqueue_style(
		'wp-block-mcontigo-bgcontent-dinamic-css',
		plugin_dir_url( __FILE__ ) . 'assets/block-dinamic-styles.php',
		array(),
		filemtime( dirname( __FILE__ ) . '/assets/block-dinamic-styles.php' )
	);
}


