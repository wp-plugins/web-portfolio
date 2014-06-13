jQuery( document ).ready(function() {
	jQuery( ".web_portfolio li a" ).click(function() {
		var href			=	jQuery( this ).attr( "href" );
		href				=	href.split( '#' );
		catslug				=	href[1];
		jQuery( ".web_portfolio li a.active" ).removeClass( 'active' );
		jQuery( this ).addClass( 'active' );
		
		if( catslug == 'all' ) {
			jQuery( 'ul.portfolio_grid li.hide' ).fadeIn( 'normal' ).removeClass( 'hide' );
		} else {
			jQuery( 'ul.portfolio_grid li' ).each(function() {
				if( !jQuery( this ).hasClass( catslug ) ) {
					jQuery( this ).fadeOut( 'normal' ).addClass( 'hide' );
    
				} else {
					jQuery( this ).fadeIn( 'normal' ).removeClass( 'hide' );
				}
			});
		}
		return false;
	});
});