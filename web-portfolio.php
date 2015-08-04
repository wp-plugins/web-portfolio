<?php
/*
Plugin Name: Web Portfolio
Plugin URI: http://weblumia.com/web-portfolio
Description: Web portfolio plugin allows you to display responsive and attractive portfolio to your websites.
Version: 2.3
Author: Jinesh.P.V
Author URI: http://www.weblumia.com/
*/
/**
	Copyright 2015-2016 Jinesh.P.V (email: jinuvijay5@gmail.com)

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

/**
 * Main Web_Portfolio Class
 *
 * @class Web_Portfolio
 * @version	2.2
 */
if ( ! class_exists( 'Web_Portfolio' ) ) : 

	final class Web_Portfolio {
		
		private $options;
		
		/**
		 * Web_Portfolio Constructor.
		 * @since 2.2
		 */
		 
		public function __construct() {
			
			// Include required frontend files
			self::portfolio_includes();
			
			// Default hook
			register_activation_hook( __FILE__, array( &$this, 'lumia_activation' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'lumia_deactivation' ) );
			register_uninstall_hook( __FILE__, array( 'lumia_portfolio', 'lumia_uninstall' ) );
			
			// Action  Hook
			add_action( 'init', array( &$this, 'init' ) );
			add_action( 'admin_init', array( &$this, 'admin_page_init' ) );
			add_action( 'admin_menu', array( &$this, 'portfolio_settings' ) );
			add_action( 'after_setup_theme', array( $this, 'add_image_sizes' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'portfolio_scripts' ), 20 );
			add_action( 'wp_footer', array( &$this, 'load_portfoilio_scripts' ), 20 );
			
			// Filter  Hook
			add_filter( 'page_template', array( &$this, 'portfolio_page_template' ) );
			add_filter( 'single_template', array( &$this, 'portfolio_single_template' ) );
			
			// Shortcode  Hook
			add_shortcode( 'web_portfolio', array( &$this, 'web_portfolio_list' ) );
		}
		
		/**
		 * Init web portfolio when WordPress initialises.
		 * @since 2.2
		 */
		
		public function init(){
			self::lumia_styles();
			self::create_portfolio_page();
			ob_start( array( &$this, "web_portfolio_do_output_buffer_callback" ) );
		}
		
		/**
		 * Callback function
		 * @since 2.2
		 */
		 
		public function web_portfolio_do_output_buffer_callback( $buffer ){
			return $buffer;
		}
		
		/**
		 * Clean all output buffers
		 *
		 * @param (default: null)
		 */
		 
		public function web_portfolio_flush_ob_end(){
			ob_end_flush();
		}
		
		/**
		 * portfolio_includes
		 * @since 2.2
		 */
		 
		public function portfolio_includes(){
			
			require_once( 'includes/web-portfolio-functions.php' );
		}
		
		/**
		 * Add web portfolio image sizes to WP
		 * @since 2.2
		 */
		public function add_image_sizes() {
			add_image_size( 'web_portfolio', 549, 411, true );
		}
	
		/**
		 * lumia_activation
		 * @since 2.2
		 */

 
		public function lumia_activation() {
			if ( ! current_user_can( 'activate_plugins' ) )
				return;
	
			$plugin			=	isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
			check_admin_referer( "activate-plugin_{$plugin}" );
			self::init();
		}
		
		/**
		 * Dynamic creation for portfolio page
		 * @since 2.2
		 */
		 
		public function create_portfolio_page(){
			
			global $wpdb;
			
			$pageArray		=	$wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_content = '[web_portfolio]' AND post_status = 'publish' AND post_type = 'page'" );
			$portfolio		=	array(
				'post_title'    => 'Portfolio',
				'post_name'     => 'web_portfolio',
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
		
		/**
		 * Deactivation hook
		 * @since 2.2
		 */
		 
		public function lumia_deactivation() {
			if ( ! current_user_can( 'activate_plugins' ) )
				return;
	
			$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
			check_admin_referer( "deactivate-plugin_{$plugin}" );
		}
		
		/**
		 * Uninstall hook
		 * @since 2.2
		 */
		 
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
		
		/**
		 * lumia_delete_portfolios
		 * @since 2.2
		 */
		 
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
	
		/**
		 * lumia_delete_attachments
		 * @since 2.2
		 */
		 
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
		
		/**
		 * portfolio_settings
		 * @since 2.2
		 */
		 
		public function portfolio_settings() {
		
			add_submenu_page(
				'edit.php?post_type=lumia_porfolio', 
				'Porfolio Settings', 
				'Settings',
				'manage_options', 
				'lumia-porfolio-setting', 
				array( $this, 'create_lumia_porfolio_admin_page' )
			);
		} 
		
		/**
		 * Options page callback
		 * @since 2.2
		 */
		 
		public function create_lumia_porfolio_admin_page() {
						
			$this->options = get_option( 'porfolio_settings' );
			
			// General option values
			$display_mode = isset( $this->options['display_mode'] ) ? esc_attr( $this->options['display_mode'] ) : '';
			$font_family = isset( $this->options['font_family'] ) ? esc_attr( $this->options['font_family'] ) : '';
			
			// Navigation option values
			$nav_bg_color = isset( $this->options['nav_bg_color'] ) ? esc_attr( $this->options['nav_bg_color'] ) : '';
			$nav_hover_color = isset( $this->options['nav_hover_color'] ) ? esc_attr( $this->options['nav_hover_color'] ) : '';			
			$nav_font_size = isset( $this->options['nav_font_size'] ) ? esc_attr( $this->options['nav_font_size'] ) : '';
			$nav_font_color = isset( $this->options['nav_font_color'] ) ? esc_attr( $this->options['nav_font_color'] ) : '';
			$nav_font_hover_color = isset( $this->options['nav_font_hover_color'] ) ? esc_attr( $this->options['nav_font_hover_color'] ) : '';
			
			// Navigation option values
			$font_size = isset( $this->options['font_size'] ) ? esc_attr( $this->options['font_size'] ) : '';
			$font_color = isset( $this->options['font_color'] ) ? esc_attr( $this->options['font_color'] ) : '';
			$content_font_size = isset( $this->options['content_font_size'] ) ? esc_attr( $this->options['content_font_size'] ) : '';
			$content_font_color = isset( $this->options['content_font_color'] ) ? esc_attr( $this->options['content_font_color'] ) : '';
			?>
			<style>
			.form-table {
				width: 100%;
			}
			.clear {
				clear:both;
			}
			.form-table .row-table {
				margin-bottom:10px;
			}
			.form-table .row-table label {
				width: 15%;
				float: left;
			}
			.form-table .row-table select,
			.form-table .row-table input {
				width: 20%;
				float: left;
			}
			.form-table .row-table input {
				width: 5%;
			}
			.form-widefat {
				background:#fff;
				padding: 10px 25px 15px 25px;
				margin-bottom:25px;
			}
			</style>
			<div class="wrap">
				<h2>Porfolio Settings</h2>           
				<form method="post" action="options.php">
					<?php settings_fields( 'lumia_porfolio' ); ?>
                    <div class="form-table">
                    	<div class="form-widefat">
                            <h3>General Settings</h3>
                            <div class="row-table">
                                <label>Display Mode: </label>
                                <select name="porfolio_settings[display_mode]" id="display_mode">
                                    <option>Select Display Mode</option>
                                    <option value="2" <?php selected( $display_mode, '2' ); ?>>2 Column</option>
                                    <option value="3" <?php selected( $display_mode, '3' ); ?>>3 Column</option>
                                    <option value="4" <?php selected( $display_mode, '4' ); ?>>4 Column</option>
                                </select>
                                <div class="clear"></div>
                            </div>
                            <div id="lumia_porfolio_settings">
                            <?php
                            if( !empty( $display_mode ) ) {
                                for( $i = 1; $i <= $display_mode; $i++ ) {
                                    ?>
                                <div class="row-table">
                                    <label>Box <?php echo $i;?>: </label>
                                    <input type="color" name="porfolio_settings[box<?php echo $i;?>_color]" value="<?php echo esc_attr( $this->options['box' . $i . '_color'] );?>" class="small" />
                                    <div class="clear"></div>
                                </div>
                                <?php
                                }
                            }
                            ?>
                            </div>
                            <div class="row-table">
                                <label>Font Family: </label>
                                <select id="font_family" name="porfolio_settings[font_family]">
                                    <option value="Arial" <?php selected( $font_family, 'Arial' ); ?>>Arial</option>
                                    <option value="Verdana" <?php selected( $font_family, 'Verdana' ); ?>>Verdana</option>
                                    <option value="Helvetica" <?php selected( $font_family, 'Helvetica' ); ?>>Helvetica</option>
                                    <option value="Comic Sans MS" <?php selected( $font_family, 'Comic Sans MS' ); ?>>Comic Sans MS</option>
                                    <option value="Georgia" <?php selected( $font_family, 'Georgia' ); ?>>Georgia</option>
                                    <option value="Trebuchet MS" <?php selected( $font_family, 'Trebuchet MS' ); ?>>Trebuchet MS</option>
                                    <option value="Times New Roman" <?php selected( $font_family, 'Times New Roman' ); ?>>Times New Roman</option>
                                    <option value="Tahoma" <?php selected( $font_family, 'Tahoma' ); ?>>Tahoma</option>
                                    <option value="Oswald" <?php selected( $font_family, 'Oswald' ); ?>>Oswald</option>
                                    <option value="Open Sans" <?php selected( $font_family, 'Open Sans' ); ?>>Open Sans</option>
                                    <option value="Fontdiner Swanky" <?php selected( $font_family, 'Fontdiner Swanky' ); ?>>Fontdiner Swanky</option>
                                    <option value="Crafty Girls" <?php selected( $font_family, 'Crafty Girls' ); ?>>Crafty Girls</option>
                                    <option value="Pacifico" <?php selected( $font_family, 'Pacifico' ); ?>>Pacifico</option>
                                    <option value="Satisfy" <?php selected( $font_family, 'Satisfy' ); ?>>Satisfy</option>
                                    <option value="Gloria Hallelujah" <?php selected( $font_family, 'TGloria Hallelujah' ); ?>>TGloria Hallelujah</option>
                                    <option value="Bangers" <?php selected( $font_family, 'Bangers' ); ?>>Bangers</option>
                                    <option value="Audiowide" <?php selected( $font_family, 'Audiowide' ); ?>>Audiowide</option>
                                    <option value="Sacramento" <?php selected( $font_family, 'Sacramento' ); ?>>Sacramento</option>
                                </select>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="form-widefat">
                        	<h3>Navigation Settings</h3>                            
                            <div class="row-table">
                                <label>Background Color: </label>
                                <input type="color" name="porfolio_settings[nav_bg_color]" value="<?php echo $nav_bg_color;?>" class="small" />
                                <div class="clear"></div>
                            </div>
                            <div class="row-table">
                                <label>Hover Color: </label>
                                <input type="color" name="porfolio_settings[nav_hover_color]" value="<?php echo $nav_hover_color;?>" class="small" />
                                <div class="clear"></div>
                            </div>
                        	<div class="row-table">
                                <label>Font Size: </label>
                                <select name="porfolio_settings[nav_font_size]">
                                    <?php for( $i = 10; $i < 21; $i++ ) { ?> 
                                    <option value="<?php echo $i;?>px" <?php selected( $nav_font_size, $i . 'px' ); ?>><?php echo $i;?>px</option>
                                    <?php } ?>
                                </select>
                                <div class="clear"></div>
                            </div>
                            <div class="row-table">
                                <label>Font Color: </label>
                                <input type="color" name="porfolio_settings[nav_font_color]" value="<?php echo $nav_font_color;?>" class="small" />
                                <div class="clear"></div>
                            </div>
                            <div class="row-table">
                                <label>Font Hover Color: </label>
                                <input type="color" name="porfolio_settings[nav_font_hover_color]" value="<?php echo $nav_font_hover_color;?>" class="small" />
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="form-widefat">
                        	<h3>Content Settings</h3>     
                            <div class="row-table">
                                <label>Heading Font Size: </label>
                                <select name="porfolio_settings[font_size]">
                                    <?php for( $j = 16; $j < 33; $j++ ) { ?> 
                                    <option value="<?php echo $j;?>px" <?php selected( $font_size, $j . 'px' ); ?>><?php echo $j;?>px</option>
                                    <?php } ?>
                                </select>
                                <div class="clear"></div>
                            </div>
                            <div class="row-table">
                                <label>Heading Font Color: </label>
                                <input type="color" name="porfolio_settings[font_color]" value="<?php echo $font_color;?>" class="small" />
                                <div class="clear"></div>
                            </div>
                            <div class="row-table">
                                <label>Content Font Size: </label>
                                <select name="porfolio_settings[content_font_size]">
                                    <?php for( $k = 10; $k < 21; $k++ ) { ?> 
                                    <option value="<?php echo $k;?>px" <?php selected( $content_font_size, $k . 'px' ); ?>><?php echo $k;?>px</option>
                                    <?php } ?>
                                </select>
                                <div class="clear"></div>
                            </div>
                            <div class="row-table">
                                <label>Content Font Color: </label>
                                <input type="color" name="porfolio_settings[content_font_color]" value="<?php echo $content_font_color;?>" class="small" />
                                <div class="clear"></div>
                            </div>	                        
                        </div>
                    </div>	                				
					<?php submit_button(); ?>
				</form>
			</div>
            <script type="text/javascript">
			jQuery( document ).ready(function( $ ) {
				$( document ).on( 'change', "#display_mode", function() {
					var $value = $( this ).val(),
					$html = '';
					
					for( var $i = 1; $i <= $value; $i++ ) {
						$html += '<div class="row-table">' +
									'<label>Box ' + $i + ' Color: </label>' +
									'<input type="color" name="porfolio_settings[box' + $i + '_color]" value="" class="small" />' +
									'<div class="clear"></div>' + 
								'</div>';
					}
					
					$( '#lumia_porfolio_settings' ).html( $html );
				});
			});	
			</script>
			<?php
		}
	
		/**
		 * Register and add settings
		 * @since 2.2
		 */
		 
		public function admin_page_init() {   
			
			if( !empty( $_POST["submit"] ) && $_POST["submit"] != 'Save Changes' ) {
				
				// General option values
				$options['display_mode'] = '4';
				$options['font_family'] = 'Open Sans';
				$options['box1_color'] = '#F37338';
				$options['box2_color'] = '#FDB632';
				$options['box3_color'] = '#027878';
				$options['box4_color'] = '#801638';
								
				// Navigation option values
				$options['nav_bg_color'] = '#FDB632';
				$options['nav_hover_color'] = '#801638';
				$options['nav_font_size'] = '13px';
				$options['nav_font_color'] = '#ffffff';
				$options['nav_font_hover_color'] = '#ffffff';
				
				// Content option values
				$options['font_size'] = '24px';
				$options['font_color'] = '#ffffff';
				$options['content_font_size'] = '14px';
				$options['content_font_color'] = '#222222';
				
				update_option( 'porfolio_settings', $options );
			}
			
			register_setting(
				'lumia_porfolio', // Option group
				'porfolio_settings', // Option name
				array( &$this, 'sanitize' ) // Sanitize
			);
		 
		}
		
		/**
		 * Sanitize each setting field as needed
		 * @since 2.2
		 */
		 
		public function sanitize( $input ) {
			
			 
			$new_input = array();
			
			// General option values
			if( isset( $input['display_mode'] ) )
				$new_input['display_mode'] = sanitize_text_field( $input['display_mode'] );
			
			if( !empty( $new_input['display_mode'] ) ) {
				for( $i = 1; $i <= $new_input['display_mode']; $i++ ) {
					$new_input['box' . $i . '_color'] = sanitize_text_field( $input['box' . $i . '_color'] );
				}
			}
				
			if( isset( $input['font_family'] ) )
				$new_input['font_family'] = sanitize_text_field( $input['font_family'] );
				
				
			// Navigation option values
			if( isset( $input['nav_bg_color'] ) )
				$new_input['nav_bg_color'] = sanitize_text_field( $input['nav_bg_color'] );
				
			if( isset( $input['nav_hover_color'] ) )
				$new_input['nav_hover_color'] = sanitize_text_field( $input['nav_hover_color'] );	
				
			if( isset( $input['nav_font_size'] ) )
				$new_input['nav_font_size'] = sanitize_text_field( $input['nav_font_size'] );
				
			if( isset( $input['nav_font_color'] ) )
				$new_input['nav_font_color'] = sanitize_text_field( $input['nav_font_color'] );	
				
			if( isset( $input['nav_font_hover_color'] ) )
				$new_input['nav_font_hover_color'] = sanitize_text_field( $input['nav_font_hover_color'] );	
								
							
			// Content option values
			if( isset( $input['font_size'] ) )
				$new_input['font_size'] = sanitize_text_field( $input['font_size'] );
				
			if( isset( $input['font_color'] ) )
				$new_input['font_color'] = sanitize_text_field( $input['font_color'] );	
				
			if( isset( $input['content_font_size'] ) )
				$new_input['content_font_size'] = sanitize_text_field( $input['content_font_size'] );
				
			if( isset( $input['content_font_color'] ) )
				$new_input['content_font_color'] = sanitize_text_field( $input['content_font_color'] );		
				
			return $new_input;
		}
	
		/**
		 * lumia_delete_attachments
		 * @since 2.2
		 */
		 
		public static function lumia_styles() {
			
			if( !is_admin() ){
				
				$options = get_option( 'porfolio_settings' );

				// General option values
				$display_mode = isset( $options['display_mode'] ) ? esc_attr( $options['display_mode'] ) : '';
				if( $display_mode == 4 ) {
					$width = '25%';
				} elseif( $display_mode == 3 ) {
					$width = '33.333%';
				} else{
					$width = '50%';
				}
				$font_family = isset( $options['font_family'] ) ? esc_attr( $options['font_family'] ) : '';
				$box1_color = isset( $options['box1_color'] ) ? esc_attr( $options['box1_color'] ) : '';
				$box2_color = isset( $options['box2_color'] ) ? esc_attr( $options['box2_color'] ) : '';
				$box3_color = isset( $options['box3_color'] ) ? esc_attr( $options['box3_color'] ) : '';
				$box4_color = isset( $options['box4_color'] ) ? esc_attr( $options['box4_color'] ) : '';
				
				// Navigation option values
				$nav_bg_color = isset( $options['nav_bg_color'] ) ? esc_attr( $options['nav_bg_color'] ) : '';
				$nav_hover_color = isset( $options['nav_hover_color'] ) ? esc_attr( $options['nav_hover_color'] ) : '';
				$nav_font_size = isset( $options['nav_font_size'] ) ? esc_attr( $options['nav_font_size'] ) : '';
				$nav_font_color = isset( $options['nav_font_color'] ) ? esc_attr( $options['nav_font_color'] ) : '';
				$nav_font_hover_color = isset( $options['nav_font_hover_color'] ) ? esc_attr( $options['nav_font_hover_color'] ) : '';
				
				// Content option values
				$font_size = isset( $options['font_size'] ) ? esc_attr( $options['font_size'] ) : '';
				$font_color = isset( $options['font_color'] ) ? esc_attr( $options['font_color'] ) : '';
				$content_font_size = isset( $options['content_font_size'] ) ? esc_attr( $options['content_font_size'] ) : '';
				$content_font_color = isset( $options['content_font_color'] ) ? esc_attr( $options['content_font_color'] ) : '';
			
				wp_register_style( 'lumia-google-fonts', 'http://fonts.googleapis.com/css?family=Oswald|Open+Sans|Fontdiner+Swanky|Crafty+Girls|Pacifico|Satisfy|Gloria+Hallelujah|Bangers|Audiowide|Sacramento');
        		wp_enqueue_style( 'lumia-google-fonts' );
				wp_register_style( 'lumia-portfolio', plugins_url( 'css/portfolio-style.css', __FILE__ ) );
				$custom_css   = ".web_portfolio li a {
									background: " . $nav_bg_color . ";
									color: " . $nav_font_color . ";
									font: " . $nav_font_size . " " . $font_family . ";
								}
								.web_portfolio li a.active , .web_portfolio li a:hover{
									background: " . $nav_hover_color . ";
									color: " . $nav_font_hover_color . ";
								}
								.portfolio_grid li{
									width: " . $width . ";
								}
								.portfolio_lft h3{
									font: " . $font_size . ";/1.2em " . $font_family . ";
									color: " . $font_color . ";
								}
								.portfolio_lft p{
									font: " . $content_font_size . " " . $font_family . ";
									color: " . $content_font_color . ";
								}
								.portfolio_lft h4{
									font:1.5em/35px " . $font_family . ";
								}
								.portfolio_lft span{
									display:block;
									font: " . $content_font_size . " " . $font_family . ";
									color: " . $content_font_color . ";
								}
								.portfolio_lft a{
									font:1.1em " . $font_family . ";
								}";
								
				if( isset( $box1_color ) ) {
					
					$custom_css   .= ".box1_color {
									background: " . $box1_color . ";
								}
								.portfolio_lft h4.box1_color {
									border-left: 5px solid " . $box1_color . ";
									color: " . $box1_color . ";
								}
								.technology.box1_color {
									background: none;
								}
								.technology.box1_color span {
									background: " . $box1_color . ";
									display: inline-block;
									font: 13px/17px Open Sans;
									margin-right: 4px;
									padding: 3px;
									color: #fff;
								}";
				}
				
				if( isset( $box2_color ) ) {
					
					$custom_css   .= ".box2_color {
									background: " . $box2_color . ";
								}
								.portfolio_lft h4.box2_color {
									border-left: 5px solid " . $box2_color . ";
									color: " . $box2_color . ";
								}
								.technology.box2_color {
									background: none;
								}
								.technology.box2_color span {
									background: " . $box2_color . ";
									display: inline-block;
									font: 13px/17px Open Sans;
									margin-right: 4px;
									padding: 3px;
									color: #fff;
								}";
				}
				
				if( isset( $box3_color ) ) {
					
					$custom_css   .= ".box3_color {
									background: " . $box3_color . ";
								}
								.portfolio_lft h4.box3_color {
									border-left: 5px solid " . $box3_color . ";
									color: " . $box3_color . ";
								}
								.technology.box3_color {
									background: none;
								}
								.technology.box3_color span {
									background: " . $box3_color . ";
									display: inline-block;
									font: 13px/17px Open Sans;
									margin-right: 4px;
									padding: 3px;
									color: #fff;
								}";
				}
				
				if( isset( $box4_color ) ) {
					$custom_css   .= ".box4_color {
									background: " . $box4_color . ";
								}
								.portfolio_lft h4.box4_color {
									border-left: 5px solid " . $box4_color . ";
									color: " . $box4_color . ";
								}
								.technology.box4_color {
									background: none;
								}
								.technology.box4_color span {
									background: " . $box4_color . ";
									display: inline-block;
									font: 13px/17px Open Sans;
									margin-right: 4px;
									padding: 3px;
									color: #fff;
								}";
				}
				
				$custom_css   .= ".portfolio_grid li .portfolio_item_bottom h3 {
									font: " . $font_size . "/23px " . $font_family . ";	
									margin:0 0 10px 0;
								}
								.portfolio_grid li .portfolio_item_bottom h3 a {
									text-decoration:none;
									color: " . $font_color . ";
									margin:0;
								}
								.portfolio_grid li .portfolio_item_bottom .technology span {
									font:13px/17px " . $font_family . ";	
									background:#f1f1f1;
									padding:3px;
									margin-right:4px;
									display:inline-block;
								}";
								
        		wp_add_inline_style( 'lumia-portfolio', $custom_css );
		
				wp_enqueue_style( 'lumia-portfolio' );
				if( isset( $_REQUEST['slug'] ) ){
					wp_register_style( 'sliderkit-core', plugins_url( 'css/sliderkit-core.css', __FILE__ ) );
					wp_enqueue_style( 'sliderkit-core' );
					wp_register_style( 'sliderkit-demos', plugins_url( 'css/sliderkit-demos.css', __FILE__ ) );
					wp_enqueue_style( 'sliderkit-demos' );
				}
			}
		}
		
		/**
		 * web_portfolio_list
		 * @since 2.2
		 */
		 
		public function web_portfolio_list(){
			$portFunctions		=	new Web_Portfolio_Functions;
			$portFunctions->web_portfolio_tags();
			$portFunctions->web_portfolio_all();
		}
		
		/**
		 * portfolio_scripts
		 * @since 2.2
		 */
		 
		public function portfolio_scripts(){
			if( !is_admin() ){
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'portfolio', WP_PLUGIN_URL . '/web-portfolio/js/portfolio.js', array( 'jquery' ), '1.2' );
				wp_enqueue_script( 'wlslider', WP_PLUGIN_URL . '/web-portfolio/js/jquery.wlslider.js', array( 'jquery' ), '1.1.0' );
			}
		}
		
		/**
		 * load_portfoilio_scripts
		 * @since 2.2
		 */
		 
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
		
		/**
		 * portfolio_page_template
		 * @since 2.2
		 */
		 
		public function portfolio_page_template( $page_template ) {
			
			if ( is_page( 'web_portfolio' ) ) {
				if( file_exists( dirname( __FILE__ ) . '/templates/web-portfolio-page-template.php' ) )
					$page_template = dirname( __FILE__ ) . '/templates/web-portfolio-page-template.php';
			}
			return $page_template;
		}
		
		/**
		 * portfolio_single_template
		 * @since 2.2
		 */
		 
		public function portfolio_single_template ( $page_template ) {
			
			global $wp_query, $post;
		
			/* Checks for single template by post type */
			if ( $post->post_type == "lumia_porfolio" ){
				if( file_exists( dirname( __FILE__ ) . '/templates/web-portfolio-single-template.php' ) )
					$page_template = dirname( __FILE__ ) . '/templates/web-portfolio-single-template.php';
			}
			return $page_template;
		}
	}
	
	/**
	 * Creation of portfolio post type
	 * @since 2.2
	 */
		 
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
		flush_rewrite_rules();
	}
	
	/**
	 * lumia_porfolio_meta_boxes
	 * @since 2.2
	 */
	 
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
	
	/**
	 * display_lumia_porfolio_meta_box
	 * @since 2.2
	 */
	 
	function display_lumia_porfolio_meta_box() {
		$post_id					=	get_the_ID();
		$porfolio_data				=	get_post_meta( $post_id, '_porfolio', true );
		$link						=	( empty( $porfolio_data['link'] ) )               ? '' : $porfolio_data['link'];
		$technology_used			=	( empty( $porfolio_data['technology_used'] ) )    ? '' : $porfolio_data['technology_used'];
	
		wp_nonce_field( 'lumia_porfolio', 'lumia_porfolio' );
		?>
		<table class="widefat">
			<tr>
				<td style="width:20%">link : </td>
				<td><input type="text" name="porfolio[link]" value="<?php echo $link; ?>" class="widefat" /></td>
			</tr>
			<tr>
				<td style="width:20%">Technologies Used: </td>
				<td><input type="text" name="porfolio[technology_used]" value="<?php echo $technology_used; ?>" class="widefat" /></td>
			</tr>
		</table>
		<?php
	} 
	
	/**
	 * action hook for lumia_porfolio_save_post
	 * @since 2.2
	 */
	 
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
	
	/**
	 * action hook for lumia_porfolio_edit_columns
	 * @since 2.2
	 */
	 
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
	
	/**
	 * action hook for lumia_porfolio_columns
	 * @since 2.2
	 */
	 
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
	
	/**
	 * Load MultipleThumbnails
	 * @since 2.2
	 */
	 
	require_once( 'includes/multiple-thumbnails.php' );	 
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
endif;

$webPortfolio = new Web_Portfolio;
?>