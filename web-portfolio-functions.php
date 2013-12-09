<?php
require_once( 'multiple-thumbnails.php' );
class Web_Portfolio_Functions {
	
	public function web_portfolio_tags() {
		
		$html				=	'';
		$porfolio_category	=	get_categories( array( 'taxonomy' => 'porfolio_category', 'hide_empty' => '1' ) );
		
		if( !empty( $porfolio_category ) ){
			
			$html			=	'<ul class="web_portfolio"><li><a href="#all" class="active">Show All</a></li>';
			foreach( $porfolio_category as $category ){
				$html		.=	'<li><a href="#' . $category->slug . '">' . $category->name . '</a></li>';
			}
			$html			.=	'</ul>';
		} else {
			$html			=	'No portfolio listed.';
		}
		
		echo $html;
	}
	
	public function web_portfolio_all() {
		
		global $wpdb;
		
		$html				=	'';
		$portfolio			=	query_posts( array( 'post_type' => 'lumia_porfolio', 'taxonomy' => 'porfolio_category', 'term' => '' ) );
		
		$html				=	'<div class="portfolio_grid_block"><ul class="portfolio_grid">';
		$pageArray			=	$wpdb->get_row( "SELECT ID FROM $wpdb->posts WHERE post_title = 'Portfolio' AND post_status = 'publish' AND post_type = 'page'" );
		$structure			=	( get_option( 'permalink_structure' ) == '' ) ? '&' : '?';
		
		foreach( $portfolio as $portfolioObj ){
			$porfolio_data				=	get_post_meta( $portfolioObj->ID, '_porfolio', true );
			$link						=	$porfolio_data['link'];
			$technology_used			=	$porfolio_data['technology_used'];
			$html			.=	'<li class="' . $this->get_category_slug( $portfolioObj->ID ) . '">
									<div class="caption top">
										<h3><a href="' . get_permalink( $pageArray->ID ) . $structure . 'slug=' . $portfolioObj->post_name . '">' . $portfolioObj->post_title . '</a></h3>
									</div>
									<div class="portfolio_item">
										<div class="thumbnail">
											<a href="' . get_permalink( $pageArray->ID ) . $structure . 'slug=' . $portfolioObj->post_name . '">' . get_the_post_thumbnail( $portfolioObj->ID, 'large' ) . '</a>
										</div>
									</div>
									<div class="caption bottom">
										<span>' . $technology_used . '</span><a href="' . $link . '" class="viewmore" title="Visit Website" target="_blank"></a>
									</div>
								</li>';
		}
		wp_reset_query();
		
		$html				.=	'</ul></div>';
		
		echo $html;
	}
	
	public function get_category_slug( $portfolio ){
		
		$catnames				=	'';
		$categories				=	get_the_terms( $portfolio, 'porfolio_category' );
		$count					=	count( $categories );
		$i						=	1;
		
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
	
	public function web_portfolio_single(){
		
		global $wpdb;
		
		$html					=	'';
		$slug					=	$_REQUEST['slug'];
		$portfolio				=	$wpdb->get_row( "SELECT * FROM $wpdb->posts WHERE post_name = '{$slug}' AND post_status = 'publish' AND post_type = 'lumia_porfolio'" );
		
		$porfolio_data			=	get_post_meta( $portfolio->ID, '_porfolio', true );
		$link					=	$porfolio_data['link'];
		$technology_used		=	$porfolio_data['technology_used'];
			
		$html					=	'<div class="portfolio_wrapper">
										<div class="portfolio_lft">
											<h3><span>' . $portfolio->post_title . '</span></h3>
											' . apply_filters( 'the_content', $portfolio->post_content ) . '
											<h4>Technologies used</h4>
											<span>' . $technology_used . '</span>
											<a href="' . $link . '" target="_blank">View Website</a>
										</div>
										<div class="portfolio_rgt">
											<ul class="portfolios">' . $this->set_portfolio_images( $portfolio->ID ) . '</ul>
											<div id="portfolio-pager">
												' . $this->set_portfolio_thumb_images( $portfolio->ID ) . '    
											</div>
										</div>
									</li>';
	
		echo $html;
	}
	
	public function set_portfolio_thumb_images( $portid ){
		
		$html						=	'';
		for( $k = 1; $k <= 3; $k++ ){
			
			$image_name 			=	'portfolio-image' . $k;
			$image_id 				=	MultipleThumbnails::get_post_thumbnail_id( 'lumia_porfolio', $image_name, $portid ); 
			$image_thumb_url 		=	wp_get_attachment_image_src( $image_id, 'thumbnail' ); 
			
			if( $image_thumb_url ){
				$html				.=	'<a href="javascript:;" data-slide-index="' . ( $k - 1 ) . '"><img src="' . $image_thumb_url[0] . '" alt="' . get_the_title( $portid ) . '" width="75" height="50"/></a>';
			}
		}
		
		return $html;
	}
	
	public function set_portfolio_images( $portid ){
		
		$html						=	'';
		for( $k = 1; $k <= 3; $k++ ){
			
			$image_name 			=	'portfolio-image' . $k;
			$image_id 				=	MultipleThumbnails::get_post_thumbnail_id( 'lumia_porfolio', $image_name, $portid ); 
			$image_feature_url 		=	wp_get_attachment_image_src( $image_id, 'full' ); 
			
			if( $image_feature_url ){
				$html				.=	'<li><img src="' . $image_feature_url[0] . '" alt="' . get_the_title( $portid ) . '" /></li>';
			}
		}
		
		return $html;
	}
}
?>