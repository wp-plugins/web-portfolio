<?php get_header(); ?>
<div class="portfolio_wrapper">
	<?php 
	$portFunctions = new Web_Portfolio_Functions;
	$portFunctions->web_portfolio_single();
	?>
</div>
<?php get_footer(); ?>