<?php
/*
Plugin Name: Web Portfolio
Plugin URI: http://weblumia.com/web-portfolio
Description: Web portfolio plugin allows you to display portfolio to your websites.
Version: 2.1.8
Author: Jinesh.P.V
Author URI: http://www.weblumia.com/
*/
/**
	Copyright 2013 Jinesh.P.V (email: jinuvijay5@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
 
if ( basename( $_SERVER['PHP_SELF'] ) == basename( __FILE__ ) ) {
	die( 'Sorry, but you cannot access this page directly.' );
}

if ( version_compare( PHP_VERSION, '5', '<' ) ) {
	$out			=	"<div id='message' style='width:94%' class='message error'>";
	$out 			.=	sprintf( "<p><strong>Your PHP version is '%s'.<br>The Ajax Event Calendar WordPress plugin requires PHP 5 or higher.</strong></p><p>Ask your web host how to enable PHP 5 on your site.</p>", PHP_VERSION );
	$out 			.=	"</div>";
	print $out;
}

class Web_Portfolio {
	
	/* constructor function for class*/
	public function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
		register_activation_hook( __FILE__, array( &$this, 'lumia_activation' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'lumia_deactivation' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'portfolio_scripts' ), 20 );
		add_action( 'wp_footer', array( &$this, 'load_portfoilio_scripts' ), 20 );
		add_shortcode( 'web_portfolio', array( &$this, 'web_portfolio_list' ) );
		//add_shortcode( 'lumia_portfolio_widget', array( &$this, 'lumia_portfolio_widget' ) );
		register_uninstall_hook( __FILE__, array( 'lumia_portfolio', 'lumia_uninstall' ) );
	}
	
	/* init function for lumia portfolios*/
	
	public function init(){
		self::lumia_styles();
		self::create_portfolio_page();
	}
	
	public function lumia_activation() {
		if ( ! current_user_can( 'activate_plugins' ) )
			return;

		$plugin			=	isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "activate-plugin_{$plugin}" );
		self::init();

		flush_rewrite_rules();
	}
	
	public function create_portfolio_page(){
		
		global $wpdb;
		
		$pageArray		=	$wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_content = '[web_portfolio]' AND post_status = 'publish' AND post_type = 'page'" );
		$portfolio		=	array(
			'post_title'    => 'Portfolio',
			'post_content'  => '[web_portfolio]',
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_date'		=>	date('Y-m-d H:i:s'),
			'post_author'   => 1,
		);
		
		if( count( $pageArray ) < 0 || count( $pageArray ) == 0 ){
			$post_id		=	wp_insert_post( $portfolio );
		}
	}

	public function lumia_deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) )
			return;

		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );

		flush_rewrite_rules();
	}
	
	public function uninstall() {
		if ( ! current_user_can( 'activate_plugins' ) )
			return;

		if ( __FILE__ !=	WP_UNINSTALL_PLUGIN )
			return;

		check_admin_referer( 'bulk-plugins' );

		global $wpdb;

		self::lumia_delete_portfolios();

		$wpdb->query( "OPTIMIZE TABLE `" . $wpdb->options . "`" );
		$wpdb->query( "OPTIMIZE TABLE `" . $wpdb->postmeta . "`" );
		$wpdb->query( "OPTIMIZE TABLE `" . $wpdb->posts . "`" );
	}
	
	public static function lumia_delete_portfolios() {
		global $wpdb;

		$query					= "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'portfolio'";
		$posts					= $wpdb->get_results( $query );

		foreach( $posts as $post ) {
			$post_id			= $post->ID;
			self::lumia_delete_attachments( $post_id );

			wp_delete_post( $post_id, true );
		}
	}


	public static function lumia_delete_attachments( $post_id = false ) {
		global $wpdb;

		$post_id				= $post_id ? $post_id : 0;
		$query					= "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent = {$post_id}";
		$attachments			= $wpdb->get_results( $query );

		foreach( $attachments as $attachment ) {
			// true is force delete
			wp_delete_attachment( $attachment->ID, true );
		}
	}
	
	public static function lumia_styles() {
		if( !is_admin() ){
			wp_register_style( 'lumia-portfolio', plugins_url( 'css/portfolio-style.css', __FILE__ ) );
			wp_enqueue_style( 'lumia-portfolio' );
			if( isset( $_REQUEST['slug'] ) ){
				wp_register_style( 'sliderkit-core', plugins_url( 'css/sliderkit-core.css', __FILE__ ) );
				wp_enqueue_style( 'sliderkit-core' );
				wp_register_style( 'sliderkit-demos', plugins_url( 'css/sliderkit-demos.css', __FILE__ ) );
				wp_enqueue_style( 'sliderkit-demos' );
			}
		}
	}
	
	public function web_portfolio_list(){
		require_once 'web-portfolio-functions.php';
		$portFunctions		=	new Web_Portfolio_Functions;
		if( isset( $_REQUEST['slug'] ) ){
			$portFunctions->web_portfolio_single();
		}else{
			$portFunctions->web_portfolio_tags();
			$portFunctions->web_portfolio_all();
		}
	}
	
	public function portfolio_scripts(){
		if( !is_admin() ){
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'portfolio', WP_PLUGIN_URL . '/web-portfolio/js/portfolio.js', array( 'jquery' ), '1.2' );
			wp_enqueue_script( 'wlslider', WP_PLUGIN_URL . '/web-portfolio/js/jquery.wlslider.js', array( 'jquery' ), '1.1.0' );
		}
	}
	
	public function load_portfoilio_scripts(){
		if( !is_admin() ){
			?>
			<script type="text/javascript">
				jQuery( document ).ready( function(){ 				
					jQuery( '.portfolios' ).wlSlider({
						mode			:	'fade',
						speed			:	500,
						controls		:	false,
						auto			:	true,
						pause			:	5000,
						pagerCustom		:	'#portfolio-pager'
					});
				});	
			</script>
		<?php
		}
    }
}

require_once( 'multiple-thumbnails.php' );
add_action( 'init', 'init_post_type' );

function init_post_type() {
	$label						=	'All Porfolio';
	$labels = array(
		'name' 					=>	_x( $label, 'post type general name' ),
		'singular_name' 		=>	_x( $label, 'post type singular name' ),
		'add_new'				=>	_x( 'Add New', 'lumia-porfolio' ),
		'add_new_item' 			=>	__( 'Add New Porfolio', 'lumia-porfolio' ),
		'edit_item' 			=>	__( 'Edit Porfolio', 'lumia-porfolio'),
		'new_item' 				=>	__( 'New Porfolio' , 'lumia-porfolio' ),
		'view_item' 			=>	__( 'View Porfolio', 'lumia-porfolio' ),
		'search_items'			=>	__( 'Search ' . $label ),
		'not_found'				=>	__( 'Nothing found' ),
		'not_found_in_trash'	=>	__( 'Nothing found in Trash' ),
		'parent_item_colon'		=>	''
	);
	
	$cats = array(
		'name'              => _x( 'Porfolio Categories', 'taxonomy general name' ),
		'singular_name'     => _x( 'Porfolio Category', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Porfolio Categories' ),
		'all_items'         => __( 'All Porfolio Categories' ),
		'parent_item'       => __( 'Parent Porfolio Category' ),
		'parent_item_colon' => __( 'Parent Porfolio Category:' ),
		'edit_item'         => __( 'Edit Porfolio Category' ), 
		'update_item'       => __( 'Update Porfolio Category' ),
		'add_new_item'      => __( 'Add New Porfolio Category' ),
		'new_item_name'     => __( 'New Porfolio Category' ),
		'menu_name'         => __( 'Porfolio Categories' ),
	);
	
	$args = array(
		'labels' => $cats,
		'hierarchical' => true,
	);
	
	register_post_type( 'lumia_porfolio', 
					   		array(
								'labels'				=>	$labels,
								'public'				=>	true,
								'publicly_queryable'	=>	true,
								'show_ui'				=>	true,
								'exclude_from_search'	=>	true,
								'query_var'				=>	true,
								'rewrite'				=>	true,
								'capability_type'		=>	'post',
								'has_archive'			=>	true,
								'hierarchical'			=>	false,
								'menu_position'			=>	65,
								'supports'				=>	array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
								'menu_icon'				=>	plugins_url( '/', __FILE__ ) . 'images/portfolio.png',
								'register_meta_box_cb'	=> 'lumia_porfolio_meta_boxes',
								)
						);
	register_taxonomy( 'porfolio_category', 'lumia_porfolio', $args );
}

function lumia_porfolio_meta_boxes() {
	add_meta_box( 	
					'display_lumia_porfolio_meta_box',
					'Porfolio Information',
					'display_lumia_porfolio_meta_box',
					'lumia_porfolio',
					'normal', 
					'high'
				 );
}

function display_lumia_porfolio_meta_box() {
	$post_id					=	get_the_ID();
	$porfolio_data				=	get_post_meta( $post_id, '_porfolio', true );
	$link						=	( empty( $porfolio_data['link'] ) )               ? '' : $porfolio_data['link'];
	$technology_used			=	( empty( $porfolio_data['technology_used'] ) )    ? '' : $porfolio_data['technology_used'];

	wp_nonce_field( 'lumia_porfolio', 'lumia_porfolio' );
	?>
    <table>
        <tr>
            <td style="width: 150px">link : </td>
            <td><input type="text" size="130" name="porfolio[link]" value="<?php echo $link; ?>" /></td>
        </tr>
        <tr>
            <td style="width: 150px">Technologies Used: </td>
            <td><input type="text" size="130" name="porfolio[technology_used]" value="<?php echo $technology_used; ?>" /></td>
        </tr>
    </table>
	<?php
} 

add_action( 'save_post', 'lumia_porfolio_save_post' );

function lumia_porfolio_save_post( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if ( ! empty( $_POST['lumia_porfolio'] ) && ! wp_verify_nonce( $_POST['lumia_porfolio'], 'lumia_porfolio' ) )
		return;

	if ( ! empty( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
			return;
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;
	}

	if ( ! empty( $_POST['porfolio'] ) ) {
		$porfolio_data['link']				=	( empty( $_POST['porfolio']['link'] ) )            ? '' : sanitize_text_field( $_POST['porfolio']['link'] );
		$porfolio_data['technology_used']	=	( empty( $_POST['porfolio']['technology_used'] ) ) ? '' : sanitize_text_field( $_POST['porfolio']['technology_used'] );

		update_post_meta( $post_id, '_porfolio', $porfolio_data );
	} else {
		delete_post_meta( $post_id, '_porfolio' );
	}
}

add_filter( 'manage_edit-lumia_porfolio_columns', 'lumia_porfolio_edit_columns' );

function lumia_porfolio_edit_columns( $columns ) {
	$columns = array(
		'cb'						=>	'<input type="checkbox" />',
		'title'						=>	'Title',
		'porfolio-link'				=>	'link',
		'porfolio-technology-used'  =>	'Technology Used',
		'porfolio-category'			=>	'Categories',
		'date'						=>	'Date'
	);

	return $columns;
}

add_action( 'manage_posts_custom_column', 'lumia_porfolio_columns', 10, 2 );

function lumia_porfolio_columns( $column, $post_id ) {
	
	$cats				=	'';
	$porfolio_data		=	get_post_meta( $post_id, '_porfolio', true );
	$porfolio_category	=	wp_get_object_terms( $post_id, 'porfolio_category' );
	foreach( $porfolio_category as $c ){
		$cat			=	get_category( $c );
		$cats			.=	$cat->name . ',';
	}
	switch ( $column ) {
		case 'porfolio-link':
			if ( ! empty( $porfolio_data['link'] ) )
				echo $porfolio_data['link'];
			break;
		case 'porfolio-technology-used':
			if ( ! empty( $porfolio_data['technology_used'] ) )
				echo $porfolio_data['technology_used'];
			break;
		case 'porfolio-category':
			if ( ! empty( $cats ) )
				echo '<a>' . rtrim ( $cats, ',' ) . '</a>';
			break;
	}
}

if ( class_exists( 'MultipleThumbnails' ) ) {
	
    new MultipleThumbnails( array(
        'label' => 'Portfolio Image 1',

        'id' => 'portfolio-image1',
        'post_type' => 'lumia_porfolio'
        )
    );
    new MultipleThumbnails( array(
        'label' => 'Portfolio Image 2',
        'id' => 'portfolio-image2',
        'post_type' => 'lumia_porfolio'
        )
    );
    new MultipleThumbnails( array(
        'label' => 'Portfolio Image 3',
        'id' => 'portfolio-image3',
        'post_type' => 'lumia_porfolio'
        )
    );
};


$webPortfolio = new Web_Portfolio;
?>