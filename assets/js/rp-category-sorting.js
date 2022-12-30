( function( $ ){

	$( document ).ready( function() {

		const base_index = parseInt( rp_pro_sorting_data.paged ) > 0 ? ( parseInt( rp_pro_sorting_data.paged ) - 1 ) * parseInt( $( '#' + rp_pro_sorting_data.per_page_id ).val() ) : 0;
		const tax_table  = $( '#the-list' );

		$.ajax({
			type: 'POST',
			url: window.ajaxurl,
			data: {
				'action': 'rp_get_category_order',
				'term_order_nonce': rp_pro_sorting_data.term_order_nonce
			},
			dataType: 'JSON',
			success: function( response ) {
				console.log( response );
				if(  response['success'] ){
					keysSorted = Object.keys(response.data).sort(function(a,b){return response.data[a]-response.data[b]});
					console.log(keysSorted);
					$.each( keysSorted, function( index, value){
						var $target = tax_table.find(`#tag-${value}`);
						$target.appendTo(tax_table); // or prependTo
					} )
				}
			}
		});

		// If the tax table contains items.
		if ( ! tax_table.find( 'tr:first-child' ).hasClass( 'no-items' ) ) {

			tax_table.sortable({
				placeholder: "rp-drag-drop-tax-placeholder",
				axis: "y",

				// On start, set a height for the placeholder to prevent table jumps.
				start: function( event, ui ) {
					const item  = $( ui.item[0] );
					const index = item.index();
					const colspan = item.children( 'th,td' ).filter( ':visible' ).length;
					$( '.rp-drag-drop-tax-placeholder' )
					.css( 'height', item.css( 'height' ) )
					.css( 'display', 'flex' )
					.css( 'width', '0' );
				},

				// Update callback.
				update: function( event, ui ) {
					const item = $( ui.item[0] );

					// Hide checkbox, append a preloader.
					item.find( 'input[type="checkbox"]' ).hide().after( '<img src="' + rp_pro_sorting_data.preloader_url + '" class="rp-drag-drop-preloader" />' );

					const taxonomy_ordering_data = [];

					tax_table.find( 'tr.ui-sortable-handle' ).each( function() {
						const ele = $( this );
						const term_data = {
							term_id: ele.attr( 'id' ).replace( 'tag-', '' ),
							order: parseInt( ele.index() ) + 1
						}
						taxonomy_ordering_data.push( term_data );
					});

					// AJAX Data.
					const data = {
						'action': 'rp_update_category_order',
						'taxonomy_ordering_data': taxonomy_ordering_data,
						'base_index': base_index,
						'term_order_nonce': rp_pro_sorting_data.term_order_nonce
					};

					// Run the ajax request.
					$.ajax({
						type: 'POST',
						url: window.ajaxurl,
						data: data,
						dataType: 'JSON',
						success: function( response ) {
							console.log( response );
							$( '.rp-drag-drop-preloader' ).remove();
							item.find( 'input[type="checkbox"]' ).show();
						}
					});
				}
			});
		}
	});

 })( jQuery );
