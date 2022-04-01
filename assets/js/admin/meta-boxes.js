jQuery( function ( $ ) {

	// Run tipTip
	function runTipTip() {
		// Remove any lingering tooltips
		$( '#tiptip_holder' ).removeAttr( 'style' );
		$( '#tiptip_arrow' ).removeAttr( 'style' );
		$( '.tips' ).tipTip({
			'attribute': 'data-tip',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200
		});
	}

	runTipTip();

	$( '.rp-metaboxes-wrapper' ).on( 'click', '.rp-metabox > h3', function() {
		$( this ).parent( '.rp-metabox' ).toggleClass( 'closed' ).toggleClass( 'open' );
	});

	$( '.rp-select2' ).select2();

	// Tabbed Panels
	$( document.body ).on( 'rp-init-tabbed-panels', function() {
		$( 'ul.rp-tabs' ).show();
		$( 'ul.rp-tabs a' ).click( function( e ) {
			e.preventDefault();
			var panel_wrap = $( this ).closest( 'div.panel-wrap' );
			$( 'ul.rp-tabs li', panel_wrap ).removeClass( 'active' );
			$( this ).parent().addClass( 'active' );
			$( 'div.panel', panel_wrap ).hide();
			$( $( this ).attr( 'href' ) ).show();
		});
		$( 'div.panel-wrap' ).each( function() {
			$( this ).find( 'ul.rp-tabs li' ).eq( 0 ).find( 'a' ).click();
		});
	}).trigger( 'rp-init-tabbed-panels' );

	// Date Picker
	$( document.body ).on( 'rp-init-datepickers', function() {
		$( '.date-picker-field, .date-picker' ).datepicker({
			dateFormat: 'yy-mm-dd',
			numberOfMonths: 1,
			showButtonPanel: true
		});
	}).trigger( 'rp-init-datepickers' );

	// Meta-Boxes - Open/close
	$( '.rp-metaboxes-wrapper' ).on( 'click', '.rp-metabox h3', function( event ) {
		// If the user clicks on some form input inside the h3, like a select list (for variations), the box should not be toggled
		if ( $( event.target ).filter( ':input, option, .sort' ).length ) {
			return;
		}

		$( this ).next( '.rp-metabox-content' ).stop().slideToggle();
	})
	.on( 'click', '.expand_all', function() {
		$( this ).closest( '.rp-metaboxes-wrapper' ).find( '.rp-metabox > .rp-metabox-content' ).show();
		return false;
	})
	.on( 'click', '.close_all', function() {
		$( this ).closest( '.rp-metaboxes-wrapper' ).find( '.rp-metabox > .rp-metabox-content' ).hide();
		return false;
	});
	$( '.rp-metabox.closed' ).each( function() {
		$( this ).find( '.rp-metabox-content' ).hide();
	});
});