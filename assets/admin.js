( function () {
	'use strict';

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-fbm-toggle]' );
		if ( ! button ) {
			return;
		}
		event.preventDefault();
		var checked = 'on' === button.getAttribute( 'data-fbm-toggle' );
		document.querySelectorAll( '.fbm__cb' ).forEach( function ( cb ) {
			cb.checked = checked;
		} );
	} );
} )();
