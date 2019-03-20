jQuery(function($) {
  // Get Cookie
  function rpress_getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') c = c.substring(1);
      if (c.indexOf(name) != -1) return c.substring(name.length,c.length);
    }
    return "";
  }

	$( document ).on( "click", ".submit-fooditem-button", function() { 

    if( $(this).attr('data-cart-action') == 'add-cart' ) {
    
      var Selected = $(this);  	   
  		var Form = $(this).parents('.rpress-food-options').find('form#fooditem-details');
  		var itemId = $(this).attr('data-item-id');
  		var itemPrice = $(this).attr('data-item-price');
  		var action = 'rpress_add_to_cart';
  		var itemQty = $(this).attr('data-item-qty');
  		var FormData = Form.serializeArray();
  		var SpecialInstruction = $(this).parents('.rpress-food-options').find('textarea.special-instructions').val();
      var GetDefaultText = Selected.text();
      Selected.text(RpressVars.wait_text);

  		var data   = {
  			action: action,
  			fooditem_id: itemId,
  			fooditem_price: itemPrice,
  			fooditem_qty: itemQty,
  			special_instruction: SpecialInstruction,
  			post_data: Form.serializeArray()
  		};
		
  		if( itemId !== '' ) {
  			$.ajax({
  				type: "POST",
  				data: data,
  				dataType: "json",
  				url: rpress_scripts.ajaxurl,
  				xhrFields: {
  					withCredentials: true
  				},
  				success: function(response) {
  				  if( response ) {
              Selected.text(RpressVars.added_into_cart);
              
              var DeliveryMethod = rpress_getCookie('deliveryMethod');
              var DeliveryTime = rpress_getCookie('deliveryTime');
              var DeliveryFee = rpress_getCookie('rpress_delivery_price');

  						$('ul.rpress-cart').find('li.cart_item.empty').remove();
              $('ul.rpress-cart').find('li.cart_item.rpress_subtotal').remove();
              $('ul.rpress-cart').find('li.cart_item.cart-sub-total').remove();
              $('ul.rpress-cart').find('li.cart_item.rpress_cart_tax').remove();
              $('ul.rpress-cart').find('li.cart_item.rpress-cart-meta.rpress-delivery-fee').remove();
              $('ul.rpress-cart').find('li.cart_item.rpress-cart-meta.rpress_subtotal').remove();
              
              if( response.delivery_fee !== undefined ) {
                $( "<li class='cart_item rpress-cart-meta rpress-delivery-fee'>Delivery Fee <span class='cart-delivery-fee'>"+response.delivery_fee+"</span></li>" ).insertBefore( "ul.rpress-cart li.cart_item.rpress_total" );
                $( "<li class='cart_item rpress-cart-meta rpress_subtotal'>Subtotal <span class='cart-sub-total'>"+response.subtotal+"</span></li>" ).insertBefore( "ul.rpress-cart li.cart_item.rpress-delivery-fee" );
                $(response.cart_item).insertBefore('ul.rpress-cart li.cart_item.rpress-cart-meta.rpress_subtotal');
              }
              else {
                $(response.cart_item).insertBefore('ul.rpress-cart li.cart_item.rpress_total');
              }
  						
  						$('.rpress-cart-number-of-items').find('.rpress-cart-quantity').text(response.cart_quantity);
  						$('.rpress-cart-number-of-items').css('display', 'block');
  						$('.cart_item.rpress-cart-meta.rpress_total').find('.cart-total').text(response.total);
              $('.cart_item.rpress-cart-meta.rpress_subtotal').find('.subtotal').text(response.total);
  						$('.cart_item.rpress-cart-meta.rpress_total').css('display', 'block');
              $('.cart_item.rpress-cart-meta.rpress_subtotal').css('display', 'block');
              $('.cart_item.rpress_checkout').addClass(rpress_scripts.button_color);
  						$('.cart_item.rpress_checkout').css('display', 'block');


              
              if( DeliveryMethod !== '' &&  DeliveryTime !== '' ) {
                $('.delivery-items-options').find('.delivery-opts').html('<span class="delMethod">'+ DeliveryMethod +'</span><span class="delTime"> at '+ DeliveryTime + '</span>' );

                if( $('.delivery-wrap .delivery-change').length == 0 ) {
                  $( "<span class='delivery-change'>Change?</span>" ).insertBefore( ".delivery-opts" );
                }
                
              }

  						$('.delivery-items-options').css('display', 'block');

              var TotalHtml = '<li class="cart_item rpress-cart-meta rpress_subtotal">'+RpressVars.total_text+'<span class="subtotal">'+response.subtotal+'</span></li>';
              if( response.tax ) {
                var TaxHtml = '<li class="cart_item rpress-cart-meta rpress_cart_tax">'+RpressVars.estimated_tax+'<span class="cart-tax">'+response.tax+'</span></li>';
                $(TaxHtml).insertBefore('ul.rpress-cart li.cart_item.rpress_total');
                $(TotalHtml).insertBefore('ul.rpress-cart li.cart_item.rpress_cart_tax');
              }
  						$('#rpressModal').modal('hide');
  					}
  				}
  			})
  		}
    }
	});

	jQuery(document).on('click', 'a.rpress-edit-from-cart', function() {
		var CartItemId = $(this).attr('data-remove-item');
		var FoodItemId = $(this).attr('data-item-id');
		var FoodItemName = $(this).attr('data-item-name');
		var FoodItemPrice = $(this).attr('data-item-price');
		var action = 'rpress_edit_food_item';
    $('#rpressModal').removeClass('rpress-delivery-options');

		var data   = {
			action: action,
			cartitem_id : CartItemId,
			fooditem_id : FoodItemId,
			fooditem_name : FoodItemName,
			fooditem_price : FoodItemPrice
		};

		if( CartItemId !== '' ) {
      $.fancybox.open({
        type     : 'html',
        afterShow : function(instance, current) {
          instance.showLoading( current );
        }
      });

			$.ajax({
				type: "POST",
				data: data,
				dataType: "json",
				url: rpress_scripts.ajaxurl,
				xhrFields: {
					withCredentials: true
			},
			success: function(response) {
        $.fancybox.close();
				$('#rpressModal .modal-title').html(FoodItemName);
        $('#rpressModal .modal-body').html(response);

        $('#rpressModal').find('.submit-fooditem-button').attr('data-item-id', FoodItemId); //setter
        $('#rpressModal').find('.submit-fooditem-button').attr('data-item-price', FoodItemPrice);
        $('#rpressModal').find('.submit-fooditem-button').attr('data-cart-key', CartItemId);
        $('#rpressModal').find('.submit-fooditem-button').attr('data-cart-action', 'update-cart');
        $('#rpressModal').find('.submit-fooditem-button').text(rpress_scripts.update_cart);
        
        $('#rpressModal').modal();
			}
		});
		}
	});

  //Update Food Item
	$( document ).on( "click", ".submit-fooditem-button", function() {
    if( $(this).attr('data-cart-action') == 'update-cart' ) {
      var Selected = $(this);
      var selectedList = $(this).parents('li.rpress-cart-item');     
      var Form      = $(this).parents('#rpressModal').find('form#fooditem-update-details');
      var itemId    = $(this).attr('data-item-id');
      var itemPrice = $(this).attr('data-item-price');
      var cartKey   = $(this).attr('data-cart-key');
      var itemQty   = $(this).attr('data-item-qty');
      var action    = 'rpress_update_cart_items';
      var FormData  = Form.serializeArray();
      var SpecialInstruction = $(this).parents('#rpressModal').find('textarea.special-instructions').val();
      var GetDefaultText = Selected.text();
      Selected.text(RpressVars.wait_text);

      var data = {
        action            : action,
        fooditem_id       : itemId,
        fooditem_price    : itemPrice,
        fooditem_cartkey  : cartKey,
        fooditem_Qty      : itemQty,
        special_instruction: SpecialInstruction,
        post_data         : Form.serializeArray()
      };

      if( itemId !== '' ) {
        $.ajax({
          type     : "POST",
          data     : data,
          dataType : "json",
          url      : rpress_scripts.ajaxurl,
          xhrFields: {
            withCredentials: true
          },
          success: function(response) {
            if( response ) {
              Selected.text(RpressVars.added_into_cart);
            
              $('ul.rpress-cart').find('li.rpress-cart-item').each(function(index, element) {
                if( index == cartKey ) {
                  $(this).remove();
                }
              });

              $('ul.rpress-cart').find('li.cart_item.rpress-cart-meta.rpress-delivery-fee').remove();
              $('ul.rpress-cart').find('li.cart_item.rpress-cart-meta.rpress_subtotal').remove();
            
              $('ul.rpress-cart').find('li.rpress_total .cart-total').text(response.total);
              $('ul.rpress-cart').find('li.cart_item.empty').remove();

              if( typeof response.delivery_fee !== "undefined" ) {
                $( "<li class='cart_item rpress-cart-meta rpress-delivery-fee'>Delivery Fee <span class='cart-delivery-fee'>"+response.delivery_fee+"</span></li>" ).insertBefore( "ul.rpress-cart li.cart_item.rpress_total" );
                $( "<li class='cart_item rpress-cart-meta rpress_subtotal'>Subtotal <span class='cart-sub-total'>"+response.subtotal+"</span></li>" ).insertBefore( "ul.rpress-cart li.cart_item.rpress-delivery-fee" );
                $(response.cart_item).insertBefore('ul.rpress-cart li.cart_item.rpress-cart-meta.rpress_subtotal');
              }
              else {
                $(response.cart_item).insertBefore('ul.rpress-cart li.cart_item.rpress_total');
              }

              $('ul.rpress-cart').find('li.rpress-cart-item').each(function(index, item) {
                $(item).attr('data-cart-key', index);
                $(item).find('.rpress-edit-from-cart').attr('data-cart-item', index);
                $(item).find('.rpress-edit-from-cart').attr('data-remove-item', index);
                $(item).find('.rpress-remove-from-cart').attr('data-cart-item', index);
              });
            
              $('#rpressModal').modal('hide');
            }
          }
        })
      }
    }
  });

	//ajax clear cart
	$( document ).on('click', 'a.rpress-clear-cart', function(e) {
		e.preventDefault();
    var Selected = $(this);
    var OldText = $(this).text();
		var action = 'rpress_clear_cart';
		var data = {
			action: action
		}
    $(this).text(RpressVars.wait_text);
		
		$.ajax({
			type: "POST",
			data: data,
			dataType: "json",
			url: rpress_scripts.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success : function(response) {
        if( response.status == 'success' ) {
          $('ul.rpress-cart').find('li.cart_item.rpress_total').css('display','none');
					$('ul.rpress-cart').find('li.cart_item.rpress_checkout').css('display','none');
					$('ul.rpress-cart').find('li.rpress-cart-item').remove();
					$('ul.rpress-cart').find('li.cart_item.empty').remove();
          $('ul.rpress-cart').find('li.rpress_subtotal').remove();
          $('ul.rpress-cart').find('li.rpress_cart_tax').remove();
          $('ul.rpress-cart').find('li.rpress-delivery-fee').remove();
					$('ul.rpress-cart').append(response.response);
					$('.rpress-cart-number-of-items').css('display','none');
          $('.delivery-items-options').css('display', 'none');
          Selected.text(OldText);
				}
			}
		});
	});


	//quantity Minus
	var liveQtyVal; 

	$(document).on('click', '.qtyminus', function(e) {
		// Stop acting like a button
    e.preventDefault();
    
    // Get the field name
    fieldName = $(this).attr('field');
    
    // Get its current value
    var currentVal = parseInt($('input[name='+fieldName+']').val());
       
    // If it isn't undefined or its greater than 0
    if (!isNaN(currentVal) && currentVal > 1) {
            
    // Decrement one only if value is > 1
    	$('input[name='+fieldName+']').val(currentVal - 1);
      $('.qtyplus').val("+").removeAttr('style');
      liveQtyVal = currentVal - 1;
    } 
    else {
    	// Otherwise put a 0 there
      $('input[name='+fieldName+']').val(1);
      $('.qtyminus').val("-").css('color','#aaa');
      $('.qtyminus').val("-").css('cursor','not-allowed');
      liveQtyVal = 1;
    }
    $(this).parents('div.modal-footer').find('a.submit-fooditem-button').attr('data-item-qty', liveQtyVal);
    $(this).parents('div.modal-footer').find('a.submit-fooditem-button').attr('data-item-qty', liveQtyVal);

	});

  //Show Image on Modal
  $('.rpress-pop').on('click', function() {
      $('.imagepreview').attr('src', $(this).find('img').attr('src'));
      $('#rpressImageModal').modal('show');   
    }); 


	$(document).on('click', '.qtyplus', function(e) {
		// Stop acting like a button
    e.preventDefault();
    
    // Get the field name
    fieldName = $(this).attr('field');
    // Get its current value
    var currentVal = parseInt($('input[name='+fieldName+']').val());
    // If is not undefined
    if (!isNaN(currentVal)) {
    	$('input[name='+fieldName+']').val(currentVal + 1);
      $('.qtyminus').val("-").removeAttr('style');
      liveQtyVal = currentVal + 1;
    } else {
    	// Otherwise put a 0 there
      $('input[name='+fieldName+']').val(1);
      liveQtyVal = 1;
		}
		$(this).parents('div.modal-footer').find('a.submit-fooditem-button').attr('data-item-qty', liveQtyVal);
		$(this).parents('div.modal-footer').find('a.submit-fooditem-button').attr('data-item-qty', liveQtyVal);

	});

  //Close minimum order error modal
  $('body').on('click', '#rpress-err-close-button', function() {
    $.fancybox.close();
  });

	$(document).on('click', 'a.special-instructions-link', function(e) {
		e.preventDefault();
		$(this).parent('div').find('.special-instructions').toggleClass('hide');
	});

  //Disable Checkout and check for errors
	$('body').on('click', '.cart_item.rpress_checkout a', function(e) {
    e.preventDefault();
    var CheckoutUrl = rpress_scripts.checkout_page;

    var deliveryOption = $('div.delivery-opts input[name=delivery_opt]:checked').val();
    var deliveryHrs = $('#rpress-allowed-hours').val();
    var ErrorHtml;

    var action = 'rpress_proceed_checkout';
    var prevText = $(this).text() ;
    var $this = $(this);
    var data = {
      action       : action,
      deliveryOpt  : deliveryOption,
      deliveryTime : deliveryHrs
    }

    $.ajax({
      type: "POST",
      data: data,
      dataType: "json",
      url: rpress_scripts.ajaxurl,
      beforeSend : function(){
       $this.text('Please Wait ..')
      },
      xhrFields: {
        withCredentials: true
      },
      success : function(response) {
        $this.text(prevText)
        if( response.status == 'error' ) {
          if( response.minimum_price_error ) {
            ErrorString = response.minimum_price_error;
          }

          ErrorHtml = '<a id="RPressError" href="#RPressMinOrder"></a>';
          ErrorHtml += '<div class="RPressMinOrderWrap">';
          ErrorHtml += '<p id="RPressMinOrder">'+ ErrorString +'';
          ErrorHtml += '<a href="javascript:void(0)" title="Close" id="rpress-err-close-button">&times;</a>';
          ErrorHtml += '</p></div>'; 
             
          document.body.insertAdjacentHTML('beforeend' , ErrorHtml );
          $("#RPressError").fancybox().trigger('click');
        }
        else {
          $this.attr('disabled');
          window.location.href = CheckoutUrl;
        }
      }
    });
  });

  if ($(window).width() > 991) {      
    var TotalHeight = 120;

    if (jQuery(".sticky-sidebar").length != '') {
      $('.sticky-sidebar').rpressStickySidebar({
        additionalMarginTop: TotalHeight
      });
    }
  }
  else {
    var TotalHeight = 70;
  }


	//RestroPress category link click
  $('body').on('click', '.rpress-category-link', function() {
    var this_id = $(this).attr('data-id');
    var gotom = setInterval(function () {
        rpress_go_to_navtab(this_id);
        clearInterval(gotom);
    }, 400);
  });

  function rpress_go_to_navtab(id) {
    var scrolling_div = $('#menu-category-' + id);
    $('html, body').animate({
        scrollTop: scrolling_div.offset().top - TotalHeight
    }, 500);
  }

  
  //jQuery live search
  $('.rpress_fooditems_list').find('.rpress-title-holder a').each(function(){
    $(this).attr('data-search-term', $(this).text().toLowerCase());
  });

  $('#rpress-food-search').on('keyup', function(){
    var searchTerm = $(this).val().toLowerCase();
    var DataId;
    var SelectedTermId;
    $('.rpress_fooditems_list').find('.rpress-element-title').each(function(index, elem) {
      $(this).removeClass('not-matched');
      $(this).removeClass('matched');
    });
    $('.rpress_fooditems_list').find('.rpress-title-holder a').each(function(){
      DataId = $(this).parents('.rpress_fooditem').attr('data-term-id');
      if ($(this).filter('[data-search-term *= ' + searchTerm + ']').length > 0 || searchTerm.length < 1) {
        $(this).parents('.rpress_fooditem').show();
        $('.rpress_fooditems_list').find('.rpress-element-title').each(function(index, elem) {
          if( $(this).attr('data-term-id') == DataId ) {
            $(this).addClass('matched');
          }
          else {
            $(this).addClass('not-matched');
          }
        });
      } 
      else {
        $(this).parents('.rpress_fooditem').hide();
        $('.rpress_fooditems_list').find('.rpress-element-title').each(function(index, elem) {
          $(this).addClass('not-matched');
        });
      }
    });
  });

  $('body').on('click', '.rpress-filter-toggle', function() {
    $('div.rpress-filter-wrapper').toggleClass('active');
  });


  //Init Google map
  if( RpressVars.enable_google_autocomplete == '1'
    && RpressVars.is_checkout_page == '1' 
    && RpressVars.google_api !== '' ) {
    setTimeout(function(){initAutocomplete()},'3000');
    
    jQuery('input.rpress-gateway').on('change', function() {
      setTimeout(function(){initAutocomplete()},'3000');
    });

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

     // if( typeof google !== 'undefined' ) {
        // Create the autocomplete object, restricting the search to geographical
        // location types.
        autocomplete = new google.maps.places.Autocomplete(

        /** @type {!HTMLInputElement} */(document.getElementsByClassName("autocomplete")[0]),

        {types: ['geocode']});


        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        autocomplete.addListener('place_changed', fillInAddress);
      //}
      
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
          document.getElementById(addressType).value = val;
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
  }

});
