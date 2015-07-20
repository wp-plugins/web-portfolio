<?php 
header( "Content-type: text/css" );
include( '../../../../wp-load.php' );
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

?>
/**************************************************/
/*                  G E N E R A L                 */
/**************************************************/
.clear {
	clear:both;
}
.web_portfolio{
    margin-bottom: 20px;
    margin-left: 0;
	width:100%;
}
.web_portfolio > li, .web_portfolio > li {
    float: left;
	list-style: none outside none;
	margin:0 !important;
}
.web_portfolio li a {
	background: <?php echo $nav_bg_color; ?>;
    color: <?php echo $nav_font_color; ?>;
    padding: 5px 11px;
	text-decoration:none;
	font: <?php echo $nav_font_size; ?> <?php echo $font_family; ?>;
	border-radius:2px;
    margin-right: 5px;
}
.web_portfolio li a.active , .web_portfolio li a:hover{
    background: <?php echo $nav_hover_color; ?>;
    color: <?php echo $nav_font_hover_color; ?>;
}
.portfolio_grid_block{
	width:100%;
	position:relative;
}
.portfolio_grid li{
    -moz-box-sizing: border-box;
    display: block;
    padding: 0;
    width: <?php echo $width; ?>;
	float:left;
}
.portfolio_grid li .portfolio_item_top {
	display:block;
}
.portfolio_grid li .portfolio_item_top img {
    margin: 0;
    padding: 0;
	display:block;
	height: auto;
    max-width: 100%;
	box-shadow:none;
	border-radius:0;
}
.portfolio_wrapper{
	width:100%;
}
.portfolio_outer {
	padding:30px;
    background: #f1f1f1;
}
.portfolio_lft{
	float:left;
	width:35%;
}
.portfolio_lft h3{
    margin:0 0 10px 0;
	font:<?php echo $font_size; ?>/1.2em <?php echo $font_family; ?>;
    color: <?php echo $font_color; ?>;
    padding: 15px;
    display: block;
}
.portfolio_lft p{
	font: <?php echo $content_font_size; ?> <?php echo $font_family; ?>;
	margin-bottom:10px;
    color: <?php echo $content_font_color; ?>;
}
.portfolio_lft h4{
    font:1.5em/35px <?php echo $font_family; ?>;
	margin:10px 0;
	display:block;
	color:#19528A;
    padding-left: 10px;
    background: #fff;
}
.portfolio_lft span{
	display:block;
	font:<?php echo $content_font_size; ?> <?php echo $font_family; ?>;
    color: <?php echo $content_font_color; ?>;
}
.portfolio_lft a{
	border-radius:3px;
	padding:5px 20px;
	text-align:center;
	display:block;
	font:1.1em <?php echo $font_family; ?>;
	color:#fff;
	text-decoration:none;
	margin:10px 0;
    background:#801638;
    text-transform: uppercase;
}
.portfolio_lft a:hover{
	color:#f1f1f1;
}
.portfolio_rgt{
	width:61%;
	margin-left:25px;
	float:left;
	background:#f1f1f1;
	padding:5px;
}
.portfolio_wrapper .portfolio_rgt ul.portfolios{
	margin:0;
	padding:0;
}
.portfolio_wrapper .portfolio_rgt ul.portfolios li{
	margin:0;
}
.portfolio_wrapper .portfolio_rgt #portfolio-pager a{
	width:75px;
	margin-right:10px;
	float:left;
}
.portfolio_grid li .portfolio_item_bottom {
	padding:15px 10px;
}
<?php if( isset( $box1_color ) ) { ?>
.box1_color {
	background: <?php echo $box1_color;?>;
}
.portfolio_lft h4.box1_color {
	border-left: 5px solid <?php echo $box1_color;?>;
    color: <?php echo $box1_color;?>;
}
.technology.box1_color {
	background: none;
}
.technology.box1_color span {
    background: <?php echo $box1_color;?>;
    display: inline-block;
    font: 13px/17px Open Sans;
    margin-right: 4px;
    padding: 3px;
    color: #fff;
}
<?php } ?>
<?php if( isset( $box2_color ) ) { ?>
.box2_color {
	background: <?php echo $box2_color;?>;
}
.portfolio_lft h4.box2_color {
	border-left: 5px solid <?php echo $box2_color;?>;
    color: <?php echo $box2_color;?>;
}
.technology.box2_color {
	background: none;
}
.technology.box2_color span {
    background: <?php echo $box2_color;?>;
    display: inline-block;
    font: 13px/17px Open Sans;
    margin-right: 4px;
    padding: 3px;
    color: #fff;
}
<?php } ?>
<?php if( isset( $box3_color ) ) { ?>
.box3_color {
	background: <?php echo $box3_color;?>;
}
.portfolio_lft h4.box3_color {
	border-left: 5px solid <?php echo $box3_color;?>;
    color: <?php echo $box3_color;?>;
}
.technology.box3_color {
	background: none;
}
.technology.box3_color span {
    background: <?php echo $box3_color;?>;
    display: inline-block;
    font: 13px/17px Open Sans;
    margin-right: 4px;
    padding: 3px;
    color: #fff;
}
<?php } ?>
<?php if( isset( $box4_color ) ) { ?>
.box4_color {
	background: <?php echo $box4_color;?>;
}
.portfolio_lft h4.box4_color {
	border-left: 5px solid <?php echo $box4_color;?>;
    color: <?php echo $box4_color;?>;
}
.technology.box4_color {
	background: none;
}
.technology.box4_color span {
    background: <?php echo $box4_color;?>;
    display: inline-block;
    font: 13px/17px Open Sans;
    margin-right: 4px;
    padding: 3px;
    color: #fff;
}
<?php } ?>
.portfolio_grid li .portfolio_item_bottom h3 {
	font:<?php echo $font_size; ?>/23px <?php echo $font_family; ?>;	
	margin:0 0 10px 0;
}
.portfolio_grid li .portfolio_item_bottom h3 a {
	text-decoration:none;
	color: <?php echo $font_color; ?>;
	margin:0;
}
.portfolio_grid li .portfolio_item_bottom .technology span {
	font:13px/17px <?php echo $font_family; ?>;	
	background:#f1f1f1;
	padding:3px;
	margin-right:4px;
	display:inline-block;
}
.spinner {
	width:100%;
	height:100%;
	position:absolute;
	background: #fff url( ../images/spinner.gif ) no-repeat center center;
	display:none;
	opacity: 0.8;
}
@media screen and (max-width: 767px) {
	img{
    	max-width: 100%;
    }
	.portfolio_grid li,
    .portfolio_lft {  
    	width: 100%;
    }
    .portfolio_rgt {
    	width: 95%;
        margin-left: 0;    
    }
}
@media (min-width: 768px) and (max-width: 1023px){
	.portfolio_grid li, {
    	width: 50%;
    }
    .portfolio_lft {  
    	width: 100%;
    }
    .portfolio_rgt {
    	width: 98.4%;
        margin-left: 0;    
    }
}
@media ( max-device-width: 480px ) and (orientation: landscape) {
	.web_portfolio li {
    	width: 50%;
     }
    .web_portfolio li a {
    	width: 87%;
        display: block;
        margin-bottom: 5px;
     }
}
@media ( max-device-width: 480px ) and (orientation: portrait) {
	.web_portfolio li {
    	float: none; 
     }
    .web_portfolio li a {
    	width: 92%;
        display: block;
        margin-right: 0;
        margin-bottom: 10px;
     }
}