<?php
require_once( 'multiple-thumbnails.php' );
class Web_Portfolio_Functions {
	
	/**
	 * web_portfolio_tags
	 * @since 2.1.9
	 */
	 
	public function web_portfolio_tags() {
		
		$html =	'';
		$porfolio_category = get_categories( array( 'taxonomy' => 'porfolio_category', 'hide_empty' => '1' ) );
		
		if( !empty( $porfolio_category ) ){
			
			$html =	'<ul class="web_portfolio"><li><a href="#all" class="active">Show All</a></li>';
			foreach( $porfolio_category as $category ){
				$html .= '<li><a href="#' . $category->slug . '">' . $category->name . '</a></li>';
			}
			$html .= '<div class="clear"></div></ul>';
		} else {
			$html =	'No portfolio listed.';
		}
		
		echo $html;
	}
	
	/**
	 * web_portfolio_all
	 * @since 2.1.9
	 */
	 
	public function web_portfolio_all() {
		
		$options = get_option( 'porfolio_settings' );
		$display_mode = isset( $options['display_mode'] ) ? esc_attr( $options['display_mode'] ) : '';
		
		$html =	$porfolio_data = '';
		$portfolio = query_posts( array( 'post_type' => 'lumia_porfolio', 'taxonomy' => 'porfolio_category', 'term' => '' ) );
		$structure = ( get_option( 'permalink_structure' ) == '' ) ? '&' : '?';
		
		$html =	'<div class="portfolio_grid_block"><div class="spinner"></div><ul class="portfolio_grid">';
		$k = 0;
		
		foreach( $portfolio as $portfolioObj ){
						
			if( $k > 0 && $k % $display_mode == 0 ) {
				$k = 1;
				$class = 'box' . $k . '_color';
			} else {
				$k++;
				$class = 'box' . $k . '_color';
			}
			
			$porfolio_data =	get_post_meta( $portfolioObj->ID, '_porfolio', true );
			$technology_used =	!empty( $porfolio_data['technology_used'] ) ? $porfolio_data['technology_used'] : '';
			$html .=	'<li class="' . $this->get_category_slug( $portfolioObj->ID ) . '">
							<div class="portfolio_item_top">
								<a href="' . get_permalink( $portfolioObj->ID ) . $structure . 'color_scheme=' . $class . '">
									' . get_the_post_thumbnail( $portfolioObj->ID, 'large' ) . '
								</a>
							</div>
							<div class="portfolio_item_bottom ' . $class . '">
								<h3>
									<a href="' . get_permalink( $portfolioObj->ID ) . $structure . 'color_scheme=' . $class . '">
										' . $portfolioObj->post_title . '
									</a>
								</h3>
								<div class="technology">
									<span>' . str_replace( array( ",", ", " ) , array( "</span><span>", "</span><span>" ), $technology_used ) . '</span>
								</div>
							</div>
							<div class="clear"></div>
						</li>';
		}
		wp_reset_query();
		
		$html .= '<div class="clear"></div></ul></div>';
		
		echo $html;
	}
	
	/**
	 * get_category_slug
	 * @since 2.1.9
	 */
	 
	public function get_category_slug( $portfolio ){
		
		$catnames =	'';
		$categories = get_the_terms( $portfolio, 'porfolio_category' );
		$count = count( $categories );
		$i = 1;
		
		if( $categories ){
			
			foreach( $categories as $category ) {
				
				 $catnames		.=	$category->slug;
				 if ( $i < $count ) 
					$catnames	.=	' ';
				 $i++;
			}
		}
		return $catnames;
	}
	
	/**
	 * web_portfolio_single
	 * @since 2.1.9
	 */
	 
	public function web_portfolio_single(){
		
		global $wpdb, $wp_query;
		
		$html =	$porfolio_data = '';
		$color_scheme = isset( $_GET['color_scheme'] ) ? $_GET['color_scheme'] : '';
		$slug =	$wp_query->query_vars['name'];	
		$portfolio = $wpdb->get_row( "SELECT * FROM $wpdb->posts WHERE post_name = '{$slug}' AND post_status = 'publish' AND post_type = 'lumia_porfolio'" );		
		$porfolio_data = get_post_meta( $portfolio->ID, '_porfolio', true );
		$link =	!empty( $porfolio_data['link'] ) ? $porfolio_data['link'] : '';
		$technology_used =	!empty( $porfolio_data['technology_used'] ) ? $porfolio_data['technology_used'] : '';
			
		$html =	'<div class="portfolio_outer">
					<div class="portfolio_lft">
						<h3 class="' . $color_scheme . '">' . $portfolio->post_title . '</h3>
						' . apply_filters( 'the_content', $portfolio->post_content ) . '
						<h4 class="' . $color_scheme . '">Technologies used</h4>
						<div class="technology ' . $color_scheme . '">
							<span>' . str_replace( array( ",", ", " ) , array( "</span><span>", "</span><span>" ), $technology_used ) . '</span>
						</div>
						<a class="' . $color_scheme . '" href="' . $link . '" target="_blank">View Website</a>
						<div class="clear"></div>
					</div>
					<div class="portfolio_rgt ' . $color_scheme . '">
						<ul class="portfolios">' . $this->set_portfolio_images( $portfolio->ID ) . '</ul>
						<div id="portfolio-pager">
							' . $this->set_portfolio_thumb_images( $portfolio->ID ) . '    
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>';

		echo $html;
	}
	
	/**
	 * set_portfolio_thumb_images
	 * @since 2.1.9
	 */
	 
	public function set_portfolio_thumb_images( $portid ){
		
		$html =	'';
		for( $k = 1; $k <= 3; $k++ ){
			
			$image_name = 'portfolio-image' . $k;
			$image_id =	MultipleThumbnails::get_post_thumbnail_id( 'lumia_porfolio', $image_name, $portid ); 
			$image_thumb_url = wp_get_attachment_image_src( $image_id, 'thumbnail' ); 
			
			if( $image_thumb_url ){
				$html .= '<a href="javascript:;" data-slide-index="' . ( $k - 1 ) . '"><img src="' . $image_thumb_url[0] . '" alt="' . get_the_title( $portid ) . '" width="75" height="50"/></a>';
			}
		}
		
		return $html;
	}
	
	/**
	 * set_portfolio_images
	 * @since 2.1.9
	 */
	 
	public function set_portfolio_images( $portid ){
		
		$html =	'';
		for( $k = 1; $k <= 3; $k++ ){
			
			$image_name = 'portfolio-image' . $k;
			$image_id =	MultipleThumbnails::get_post_thumbnail_id( 'lumia_porfolio', $image_name, $portid ); 
			$image_feature_url = wp_get_attachment_image_src( $image_id, 'web_portfolio' ); 
			
			if( $image_feature_url ){
				$html .= '<li><img src="' . $image_feature_url[0] . '" alt="' . get_the_title( $portid ) . '" /></li>';
			}
		}
		
		return $html;
	}
}
?>