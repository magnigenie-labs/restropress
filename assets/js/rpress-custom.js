jQuery(function($) {
	$( document ).on( "click", ".submit-fooditem-button", function() { 
    var Selected = $(this);  	   
		var Form = $(this).parents('.fancybox-slide').find('form#fooditem-details');
		var itemId = $(this).attr('data-item-id');
		var itemPrice = $(this).attr('data-item-price');
		var action = 'rpress_add_to_cart';
		var itemQty = $(this).attr('data-item-qty');
		var FormData = Form.serializeArray();
		var SpecialInstruction = $(this).parents('.fancybox-slide').find('textarea.special-instructions').val();
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
						$('ul.rpress-cart').find('li.cart_item.empty').remove();
            $('ul.rpress-cart').find('li.cart_item.rpress_subtotal').remove();
            $('ul.rpress-cart').find('li.cart_item.rpress_cart_tax').remove();
						$(response.cart_item).insertBefore('ul.rpress-cart li.cart_item.rpress_total');
						$('.rpress-cart-number-of-items').find('.rpress-cart-quantity').text(response.cart_quantity);
						$('.rpress-cart-number-of-items').css('display', 'block');
						$('.cart_item.rpress-cart-meta.rpress_total').find('.cart-total').text(response.total);
            $('.cart_item.rpress-cart-meta.rpress_subtotal').find('.subtotal').text(response.total);
						$('.cart_item.rpress-cart-meta.rpress_total').css('display', 'block');
            $('.cart_item.rpress-cart-meta.rpress_subtotal').css('display', 'block');
						$('.cart_item.rpress_checkout').css('display', 'block');
						$('.delivery-items-options').css('display', 'block');
            var TotalHtml = '<li class="cart_item rpress-cart-meta rpress_subtotal">'+RpressVars.total_text+'<span class="subtotal">'+response.subtotal+'</span></li>';
            if( response.tax ) {
              var TaxHtml = '<li class="cart_item rpress-cart-meta rpress_cart_tax">'+RpressVars.estimated_tax+'<span class="cart-tax">'+response.tax+'</span></li>';
              $(TaxHtml).insertBefore('ul.rpress-cart li.cart_item.rpress_total');
              $(TotalHtml).insertBefore('ul.rpress-cart li.cart_item.rpress_cart_tax');
            }
						$.fancybox.close();
					}
				}
			})
		}
	});

	jQuery(document).on('click', 'a.rpress-edit-from-cart', function() {
		var CartItemId = $(this).attr('data-remove-item');
		var FoodItemId = $(this).attr('data-item-id');
		var FoodItemName = $(this).attr('data-item-name');
		var FoodItemPrice = $(this).attr('data-item-price');
		var action = 'rpress_edit_food_item';

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
				$.fancybox.open({
					'content'  : response,
					'type' : 'html',
					headers  : { 'X-fancyBox': true },
  				'width' : 600,
  				'height': 600,
  				'openEffect'  : 'fade',
				});
			}
		});
		}
	});

	$( document ).on( "click", ".update-fooditem-button", function() {
    var Selected = $(this);
    var selectedList = $(this).parents('li.rpress-cart-item');     
    var Form      = $(this).parents('.fancybox-slide').find('form#fooditem-update-details');
    var itemId    = $(this).attr('data-item-id');
    var itemPrice = $(this).attr('data-item-price');
    var cartKey   = $(this).attr('data-cart-key');
    var itemQty   = $(this).attr('data-item-qty');
    var action    = 'rpress_update_cart_items';
    var FormData  = Form.serializeArray();
    var SpecialInstruction = $(this).parents('.fancybox-slide').find('textarea.special-instructions').val();
    var GetDefaultText = Selected.text();
    Selected.text(RpressVars.wait_text);

    var data   = {
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
            
            $('ul.rpress-cart').find('li.rpress-cart-item').each(function(index, element) {
              if( index == cartKey ) {
                $(this).remove();
              }
            });
            
            $('ul.rpress-cart').find('li.rpress_total .cart-total').text(response.total);
            $('ul.rpress-cart').find('li.cart_item.empty').remove();
            $(response.cart_item).insertBefore('ul.rpress-cart li.cart_item.rpress_total')

            $('ul.rpress-cart').find('li.rpress-cart-item').each(function(index, item) {
              $(item).attr('data-cart-key', index);
              $(item).find('.rpress-edit-from-cart').attr('data-cart-item', index);
              $(item).find('.rpress-edit-from-cart').attr('data-remove-item', index);
              $(item).find('.rpress-remove-from-cart').attr('data-cart-item', index);
            });
            
            $.fancybox.close();
          }
        }
      })
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
					$('ul.rpress-cart').append(response.response);
					$('.rpress-cart-number-of-items').css('display','none');
          $('.delivery-items-options').css('display', 'none');
          Selected.text(OldText);
				}
			}
		});
	});

	$(document).on('click', '.rpress-close-button', function(e) {
		e.preventDefault();
		$.fancybox.close();
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
    $(this).parents('div.fancybox-slide').find('a.submit-fooditem-button').attr('data-item-qty', liveQtyVal);
    $(this).parents('div.fancybox-slide').find('a.update-fooditem-button').attr('data-item-qty', liveQtyVal);

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
		$(this).parents('div.fancybox-slide').find('a.submit-fooditem-button').attr('data-item-qty', liveQtyVal);
		$(this).parents('div.fancybox-slide').find('a.update-fooditem-button').attr('data-item-qty', liveQtyVal);

	});

	$(document).on('click', 'a.special-instructions-link', function(e) {
		e.preventDefault();
		$(this).parent('div').find('.special-instructions').toggleClass('hide');
	});

	$('body').on('click', '.cart_item.rpress_checkout a', function(e) {
    e.preventDefault();
    var href = $(this).attr('data-url');
    var deliveryOption = $('div.delivery-opts input[name=delivery_opt]:checked').val();
    var deliveryHrs = $('#rpress-allowed-hours').val();

    var action = 'rpress_proceed_checkout';

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
      xhrFields: {
        withCredentials: true
      },
      success : function(response) {
        console.log(response);
        if( response.status == 'error' ) {
          $( "<p class='rpress-min-price-error'>"+response.minimum_price_error+"</p>" ).insertAfter( "ul.rpress-cart" );
        }
        else {
          window.location.replace(href);
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

  var HeaderHeight = $('#masthead').outerHeight();
  var LiveSearch = $(".rpress_fooditems_list").offset().top - HeaderHeight;

  $(window).scroll(function() {
    if( jQuery(window).scrollTop() > LiveSearch ) {
      $('.rpress_fooditems_list').addClass('sticky-live-search');
    }
    else {
      $('.rpress_fooditems_list').removeClass('sticky-live-search');
    }
  });

  if( RpressVars.enable_fooditem_popup == '1' ) {
    //Fancbox Show Images
    $(".rpress-fancybox").fancybox({
      autoSize: false,
      fitToView: false,
      maxWidth: 20
    });
  }

  

});