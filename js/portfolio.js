jQuery( document ).ready(function( $ ) {
	$( document ).on( 'click', ".web_portfolio li a", function() {
		$( 'spinner' ).show();
		var href			=	$( this ).attr( "href" );
		href				=	href.split( '#' );
		catslug				=	href[1];
		$( ".web_portfolio li a.active" ).removeClass( 'active' );
		$( this ).addClass( 'active' );
		
		if( catslug == 'all' ) {
			$( 'ul.portfolio_grid li.hide' ).fadeIn( 'normal' ).removeClass( 'hide' );
		} else {
			$( 'ul.portfolio_grid li' ).each(function() {
				if( !$( this ).hasClass( catslug ) ) {
					$( this ).fadeOut( 'normal' ).addClass( 'hide' );
    
				} else {
					$( this ).fadeIn( 'normal' ).removeClass( 'hide' );
				}
			});
		}
		setTimeout( function(){ $( '.spinner' ).hide(); }, 500 );
		return false;
	});
});