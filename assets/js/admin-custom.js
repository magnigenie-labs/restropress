jQuery(function($){
	$('select.addon-items-list').chosen();

	$('select.addon-items-list').on('change', function(event, params) {
		if( event.type == 'change' ) {
			$('.rpress-order-payment-recalc-totals').show();
		}
	});

	$('input.rpress_timings').timepicker({
		dropdown: true,
    	scrollbar: true
	});

	//Validate License
	$('body').on('click', '.rpress-validate-license', function(e) {
		e.preventDefault();
		var ButtonText = $(this).text();
		var Button = $(this);
		var Selected = $(this).parent('.rpress-license-wrapper').find('.rpress-license-field')
		var ItemId = Selected.attr('data-item-id');
		var ProductName = Selected.attr('data-item-name');
		var License = Selected.val();
		var LicenseString = $(this).parent('.rpress-license-wrapper').find('.rpress_license_string').val();
		var action = $(this).attr('data-action');
		
		if( License.length ) {
			Button.addClass('disabled');
			Button.text('Please Wait');

			data = {
				action       : action,
				item_id      : ItemId,
				product_name : ProductName,
				license 		 : License,
				license_key  : LicenseString,
			};

			$.ajax({
				type: "POST",
				data : data,
				dataType: "json",
				url: rpress_admin_vars.ajaxurl,
				xhrFields: {
					withCredentials: true
				},
				success: function (response) {
					if( response.status !== 'error' ){

						$.toast({
    					heading: 'Success',
    					text: 'Congrats, your license successfully activated!',
    					showHideTransition: 'slide',
    					icon: 'success',
    					position: { top: '36px', right: '0px' },
    					stack: false
						});

						Button.parent('.rpress-license-wrapper').addClass('rpress-updated');
						Button.parents('.rpress-purchased-wrap').find('.rpress-license-deactivate-wrapper').removeClass('hide').addClass('show');
					}
					else {
						$.toast({
    					heading: 'Error',
    					text: 'Invalid License Key',
    					showHideTransition: 'slide',
    					icon: 'error',
    					position: { top: '36px', right: '0px' },
    					stack: false
						})
					}
					Button.text('Activate License');
					Button.removeClass('disabled');
				}
			})
		}
	});

	//Deactivate License
	$('body').on('click', '.rpress-deactivate-license', function(e) {
		e.preventDefault();
		var Selected = $(this);
		var action = $(this).attr('data-action');
		var Licensestring = $(this).parents('.rpress-purchased-wrap').find('.rpress_license_string').val();
		var ProductName = $(this).parents('.rpress-purchased-wrap').find('.rpress-license-field').attr('data-item-name');

		Selected.addClass('disabled');
		Selected.text('Please Wait');

		if( Licensestring.length ) {
			data = {
				action       : action,
				product_name : ProductName,
				license_key  : Licensestring,
			};

			$.ajax({
				type: "POST",
				data : data,
				dataType: "json",
				url: rpress_admin_vars.ajaxurl,
				xhrFields: {
					withCredentials: true
				},
				success: function (response) {
					$.toast({
						heading: 'Information',
						text: 'Your license has been deactivated',
						showHideTransition: 'slide',
						icon: 'info',
						position: { top: '36px', right: '0px' },
						stack: false
					});

					if( response.status !== 'error' ){
						Selected.parents('.rpress-purchased-wrap').find('.rpress-license-wrapper').removeClass('rpress-updated');
						Selected.parents('.rpress-purchased-wrap').find('.rpress-license-deactivate-wrapper').removeClass('show').addClass('hide');
					}
					Selected.text('Deactivate License');
					Selected.removeClass('disabled');
				}
			})
		}
	});

	//Show latitude longitude field for store location
	$('body').on('click', '.store_lat_lng_position', function(e) {
		e.preventDefault();
		$(this).parents('table').find('.store_lat_lng').show();
	});

	//Init Google map
  
  setTimeout(function(){initAutocomplete()},'3000');
    
    
    var placeSearch, autocomplete;
    var componentForm = {
      street_number: 'short_name',
      route: 'long_name',
      locality: 'long_name',
      administrative_area_level_1: 'short_name',
      country: 'short_name',
      postal_code: 'short_name'
    };

    function initAutocomplete() {
      // Create the autocomplete object, restricting the search to geographical
      // location types.
      autocomplete = new google.maps.places.Autocomplete(

      /** @type {!HTMLInputElement} */(document.getElementsByClassName("autocomplete")[0]),

      {types: ['geocode']});


      // When the user selects an address from the dropdown, populate the address
      // fields in the form.
      autocomplete.addListener('place_changed', fillInAddress);
    }

    function fillInAddress() {
      // Get the place details from the autocomplete object.
      var place = autocomplete.getPlace();
      var LatLng = place.geometry.location.lat() + '-' + place.geometry.location.lng();
      jQuery('#rpress_geo_address').val(LatLng);

      // Get each component of the address from the place details
      // and fill the corresponding field on the form.
      for ( var i = 0; i < place.address_components.length; i++ ) {
        var addressType = place.address_components[i].types[0];
        
        if ( componentForm[addressType] ) {
          var val = place.address_components[i][componentForm[addressType]];
          //document.getElementById(addressType).value = val;
        }
      }
    }

    // Bias the autocomplete object to the user's geographical location,
    // as supplied by the browser's 'navigator.geolocation' object.
    function geolocate() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
          var geolocation = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
          };

          var circle = new google.maps.Circle({
            center: geolocation,
            radius: position.coords.accuracy
          });
          autocomplete.setBounds(circle.getBounds());
        });
      }
    }

    //Show Custom Lat Lng values by set
    if( rpress_admin_vars.custom_address == '1' ) {
    	$('tr.store_lat_lng').show();
    }
  

});