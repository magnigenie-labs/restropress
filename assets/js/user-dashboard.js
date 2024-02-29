var $ = jQuery;
jQuery('document').ready(function($) {
    new DataTable('#user-orders', {
        responsive: true,
        dom: 'frtip',
        initComplete: function () {
            $('#user-orders_filter input').attr('placeholder','Search orders');
        }
    });

    $('body .user-dashboard-wrapper.user-profile ul li').click(function(){
        $('body .user-dashboard-wrapper.user-profile ul li').removeClass('active');
        $(this).addClass('active');
    });

    $('body .user-dashboard-wrapper.user-profile ul li.user-profile').click(function(){
        $('body .user-dashboard-wrapper.user-profile #user-profile').show();
        $('body .user-dashboard-wrapper.user-profile #user-my-address').hide();
        $('body .user-dashboard-wrapper.user-profile #user-my-orders').hide();
    });

    $('body .user-dashboard-wrapper.user-profile ul li.user-my-address').click(function(){
        $('body .user-dashboard-wrapper.user-profile #user-profile').hide();
        $('body .user-dashboard-wrapper.user-profile #user-my-address').show();
        $('body .user-dashboard-wrapper.user-profile #user-my-orders').hide();
    });

    $('body .user-dashboard-wrapper.user-profile ul li.user-my-orders').click(function(){
        $('body .user-dashboard-wrapper.user-profile #user-profile').hide();
        $('body .user-dashboard-wrapper.user-profile #user-my-address').hide();
        $('body .user-dashboard-wrapper.user-profile #user-my-orders').show();
    });

    $('body .user-profile .delete-address').click(function(e){
        e.preventDefault();
        var index = $(this).data('index');
        
        $.ajax({
            url: users.ajaxurl,
            type: 'POST',
            data: {
                index: index,
                action: 'rpress_delete_user_address'
            },
            success: function(response){
                alert('Address Deleted.');
                location.reload();
            },
            error: function(){
                alert('Error occurred while processing your request.');
            }
        });
    });
});

function addaddress() {
    var div = document.getElementById("add-address-bg");
    if (div.className === "") {
        div.className = "active";
    } else {
        div.className = "";
    }
}

function editaddress(event) {
    var div = document.getElementById("add-address-bg");
    if (div.className === "") {
        div.className = "active";
    } else {
        div.className = "";
    }

    // Find the nearest parent element with class 'address-wrap'
    var addressWrap = $(event.target).closest('.address-wrap');
    console.log(addressWrap);
    
    // Get the values within the nearest 'address-wrap' element
    var addressType = addressWrap.find('.type-of-address').text();
    var fullName = addressWrap.find('.user-name').text();
    var address = addressWrap.find('.user-address').text();
    var phone = addressWrap.find('.user-contact').text();
    var pincode = addressWrap.find('.user-pin').text();
    var addressIndex = addressWrap.find('.user-address-index').text();
    
    // Example: Update a form with the retrieved values
    $('#add-address-bg .box-header .box-title').text("Edit Delivery Address");
    $('#form_submit_button').val('Save Changes');
    $('#addressTypeInput').val(addressType);
    $('#fullNameInput').val(fullName);
    $('#addressInput').val(address);
    $('#phoneInput').val(phone);
    $('#pincodeInput').val(pincode);
    $('#edit_user_address_index').val(addressIndex);
}