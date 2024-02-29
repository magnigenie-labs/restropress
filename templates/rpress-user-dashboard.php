<?php
$current_user = wp_get_current_user();
$username = esc_html( $current_user->user_login );
$useremail = esc_html( $current_user->user_email );
$userfname = esc_html( $current_user->user_firstname );
$userlname = esc_html( $current_user->user_lastname );
$userdname = esc_html( $current_user->display_name );
$userid = esc_html( $current_user->ID );
$useravatar = get_avatar( $current_user->ID, 96 ); // 96 is the size in pixels
$user_phone = get_user_meta( $current_user->ID, 'phone_number', true );
$paged = '';

$args = array(  
    'post_type'         => 'rpress_payment',
    'post_status'       => 'any',
    'posts_per_page'    => 10, 
    'paged'             => $paged, 
    'order'             => 'DSC', 
    'author'            => get_current_user_id()
);

if ( ! is_user_logged_in() ) {
    // Set the redirect URL
    $redirect_url = 'http://example.com/redirect-url';

    ?>
   
    <div class="user-dashboard-wrapper user-profile">
        <div class="row">
            <div style="text-align: center; padding-bottom:20px;"><?php echo __( 'You are not logged in, please Log In', 'restropress' ); ?></div>
            <form action="<?php echo wp_login_url(); ?>" method="post" style="text-align: center; padding-bottom:20px;">
                <input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_url ); ?>">
                <input type="submit" value="Log In" />
            </form>
        </div>
    </div>
    <?php
} else {
    ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
    <script src="https://unpkg.com/micromodal/dist/micromodal.min.js
"></script>
  
    <div class="user-dashboard-wrapper user-profile">
        <div class="row">
            <div class="col-lg-3 col-12">
                <div class="box-bg height-100">
                    <ul class="sidebar-menu">   
                        <li class="active user-profile">
                            <div>
                                <img src="<?php echo RP_PLUGIN_URL . 'assets/images/user/profile.png'; ?>" />
                                <span><?php echo __( 'Profile', 'restropress' ); ?></span>
                            </div>
                        </li>
                        <li class="user-my-orders">
                            <div>
                                <img src="<?php echo RP_PLUGIN_URL . 'assets/images/user/my-order.png'; ?>" />
                                <span><?php echo __( 'My Orders', 'restropress' ); ?></span>
                            </div>
                        </li>
                        <li class="user-my-address">
                            <div>
                                <img src="<?php echo RP_PLUGIN_URL . 'assets/images/user/address.png'; ?>" />
                                <span><?php echo __( 'Saved Address', 'restropress' ); ?></span>
                            </div>
                        </li>
                        <li class="logout">
                            <a href="<?php echo wp_logout_url(home_url()); ?>">
                                <img src="<?php echo RP_PLUGIN_URL . 'assets/images/user/logout.png'; ?>" />
                                <span><?php echo __( 'Logout', 'restropress' ); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9 col-12" id="user-profile">        
                <div class="box-bg pd-2">
                    <div class="avtar-header border-solid-bg pd-2">
                        <div class="flex align-items">
                            <a class="avtar-image me-15" href="#">
                                <?php if($useravatar) {
                                    echo $useravatar ;
                                } else {
                                    ?><img src="http://rpress-user-admin.local/wp-content/uploads/2024/02/avatar-1.png" class="rounded-circle"><?php
                                } ?>
                            </a>
                            <div class="avtar-details-wrap">
                                <?php if( !empty($userfname) && !empty($userlname) ) {
                                    ?><h2 class="avtar-name"><?php echo $userfname." ".$userlname; ?></h2><?php
                                } else {
                                    ?><h2 class="avtar-name"><?php echo $username; ?></h2><?php
                                } ?>
                                <?php if( !empty($useremail) ) {
                                    ?><p class="avtar-email"><?php echo $useremail; ?></p><?php
                                } ?>
                            </div>                
                        </div>             
                    </div>
                    <div class="border-solid-bg pd-2">
                        <div class="box-header">
                            <h4 class="box-title"><?php echo __( 'Personal Information', 'restropress' ); ?></h4>  
                        </div>
                        <div class="box-body">
                            <form method="POST" class="profile-form-wrap">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="input-wrap mb-2">
                                            <input type="text" class="form-control" name="first_name" value="<?php echo $userfname; ?>">
                                            <label class="form-label"><?php echo __( 'First name', 'restropress' ); ?></label>                                        
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-wrap mb-2">
                                            <input type="text" class="form-control" name="last_name" value="<?php echo $userlname; ?>">
                                            <label class="form-label"><?php echo __( 'Last name', 'restropress' ); ?></label>                    
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="input-wrap mb-2">
                                            <input type="email" class="form-control" name="email" value="<?php echo $useremail; ?>">
                                            <label class="form-label"><?php echo __( 'Email address', 'restropress' ); ?></label>                                        
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-wrap mb-2">
                                            <input type="text" class="form-control" name="phone" value="<?php echo $user_phone; ?>">
                                            <label class="form-label"><?php echo __( 'Phone', 'restropress' ); ?></label>                    
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <input type="submit" name="submit_profile_form" value="Save Changes">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>  
            </div>
            <div class="col-lg-9 col-12" id="user-my-orders" style="display:none;">        
                <div class="box-bg pd-2">
                    <div class="light-gray-bg">
                        <div class="box-body">
                            <div class="table-responsive">
                                <div class="flex justify-content-space-between align-items-center pd-4"> 
                                    <h4 class="box-title"><?php echo __( 'Orders', 'restropress' ); ?></h4>    
                                </div>
                                <table class="table" id="user-orders">
                                <thead>
                                    <tr>
                                        <th class="text-align-left"><?php echo __( 'ORDER ID', 'restropress' ); ?></th>
                                        <th class="text-', 'restropress' ); ?>align-right"><?php echo __( 'DATE', 'restropress' ); ?></th>
                                        <th class="text-align-right"><?php echo __( 'PRICE', 'restropress' ); ?></th>
                                        <th class="text-align-right"><?php echo __( 'STATUS', 'restropress' ); ?></th>
                                        <th class="text-align-right"><?php echo __( 'ACTION', 'restropress' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $loop = new WP_Query( $args );
                                    if ( $loop->have_posts() ) :
                                        while ( $loop->have_posts() ) : $loop->the_post(); 
                                            $payment = new RPRESS_Payment( get_the_ID() );
                                            $billing_name = array();
                                            if ( ! empty( $payment->user_info['first_name'] ) ) {
                                                $billing_name[] = $payment->user_info['first_name'];
                                            }
                                    
                                            if ( ! empty( $payment->user_info['last_name'] ) ) {
                                                $billing_name[] = $payment->user_info['last_name'];
                                            }
                                            $billing_name = implode( ' ', array_values( $billing_name ) );
                                    
                                            $address_info   = get_post_meta( $payment->ID, '_rpress_delivery_address', true );
                                            $address        = !empty( $address_info['address'] ) ? $address_info['address'] . ', ' : '';
                                            $address        .= !empty( $address_info['flat'] ) ? $address_info['flat'] . ', ' : '';
                                            $address        .= !empty( $address_info['city'] ) ? $address_info['city'] . ', ' : '';
                                            $address        .= !empty( $address_info['postcode'] ) ? $address_info['postcode']  : '';
                                    
                                            $service_type = get_post_meta( $payment->ID, '_rpress_delivery_type', true );
                                            $order_status = get_post_meta( $payment->ID, '_order_status', true );
                                    
                                            $order_items = array();
                                            foreach ( $payment->fooditems as $cart_item ) {
                                                $fooditem = new RPRESS_Fooditem( $cart_item['id'] );
                                                $name     = $fooditem->get_name();
                                    
                                                if ( $fooditem->has_variable_prices() && isset( $cart_item['options']['price_id'] ) ) {
                                                    $variation_name = rpress_get_price_option_name( $fooditem->ID, $cart_item['options']['price_id'] );
                                                    if ( ! empty( $variation_name ) ) {
                                                        $name .= ' - ' . $variation_name;
                                                    }
                                                }
                                    
                                                $order_items[] = $name . ' &times; ' . $cart_item['quantity'];
                                            }
                                    
                                            $items_purchased = implode( ', ', $order_items );
                                            ?>
                                                <tr>
                                                    <td>#<?php echo esc_html( $payment->number ); ?></td>        
                                                    <td><?php echo date_i18n( get_option('date_format'), strtotime( $payment->date ) ); ?></td>   
                                                    <td><?php echo wp_kses_post( rpress_currency_filter( rpress_format_amount( $payment->total ) ) ) ; ?></td>                                   
                                                    <td>
                                                        <?php 
                                                        if( esc_html( $order_status ) == "completed" ) {
                                                            ?>
                                                            <i class="fa-solid fa-circle order-status order-status-completed"></i>
                                                            Completed
                                                            <?php
                                                        } else {
                                                            ?>
                                                            <i class="fa-solid fa-circle order-status order-status-pending"></i>
                                                            Pending
                                                            <?php
                                                        }
                                                        ?>
                                                        
                                                    </td>
                                                    <td>
                                                        <div class="viewbg">
                                                            <a href="#" class="rpress-view-order-btn"data-order-id="<?php echo $payment->ID; ?>">
                                                            <span class="rp-ajax-toggle-text"></span>											                                              
                                                                <svg width="15" height="9" viewBox="0 0 15 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M7.04315 0.0860729C4.56496 0.229609 2.22737 1.55658 0.364333 3.87659C-0.121932 4.48296 -0.121932 4.59134 0.367262 5.20357C2.22737 7.52651 4.57374 8.85055 7.06365 8.98823C9.87872 9.14056 12.5415 7.81065 14.6359 5.20064C15.1222 4.59427 15.1222 4.48589 14.633 3.87366C13.2738 2.1776 11.6568 1.01173 9.86993 0.440518C9.31336 0.261831 8.64255 0.132941 8.11528 0.100719C7.54113 0.0684967 7.4181 0.065568 7.04315 0.0860729ZM8.12113 1.45113C8.96184 1.60931 9.77033 2.18346 10.2097 2.93043C10.737 3.83265 10.7927 4.92236 10.3591 5.84509C10.1892 6.20539 10.0252 6.43974 9.7469 6.72681C8.17972 8.34086 5.51112 7.84873 4.60304 5.77771C4.27496 5.03074 4.2691 4.1168 4.58839 3.3376C4.89597 2.57891 5.56678 1.91689 6.33133 1.61517C6.91133 1.38668 7.49133 1.33396 8.12113 1.45113Z" fill="#9F9F9F"></path>
                                                                    <path d="M7.1928 2.86892C6.71532 2.97144 6.2935 3.2673 6.0533 3.67155C5.87461 3.96741 5.81602 4.1871 5.81895 4.53862C5.81895 4.87842 5.85117 5.0161 5.98592 5.30024C6.11188 5.56095 6.47512 5.92418 6.74168 6.05307C7.68199 6.50711 8.77462 6.04428 9.11442 5.05125C9.21109 4.76711 9.21402 4.32185 9.12028 4.02599C8.90644 3.34932 8.28543 2.8777 7.57361 2.85134C7.43007 2.84548 7.25724 2.85427 7.1928 2.86892Z" fill="#9F9F9F"></path>
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php 
                                        endwhile;
                                    endif;
                                    $found_post = count( $loop->posts );
                                    wp_reset_postdata(); 
                                    ?>
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>  
            </div>  
            <div class="col-lg-9 col-12" id="user-my-address" style="display:none;">        
                <div class="box-bg pd-2">
                    <div class="box-header pd-2">     
                        <h4 class="box-title"><?php echo __( 'Saved Address', 'restropress' ); ?></h4>
                        <p class="box-title-description"><?php echo __( '
                            We will delivery your order here', 'restropress' ); ?>
                        </p>  
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php
                            // Get the current user's ID
                            $user_id = get_current_user_id();

                            // Get user meta data for addresses
                            $user_addresses = get_user_meta($user_id, 'user_addresses', true);

                            if (!empty($user_addresses)) {
                                foreach ($user_addresses as $index => $address) {
                                    ?>
                                    <div class="col-lg-4 col-xs-12">
                                        <div class="address-wrap <?php if($index==0) echo 'default';?>">
                                            <div class="flex justify-content-space-between items-center">
                                                <div class="type-of-address"><?php echo esc_html($address['address_type']); ?></div>
                                                <div class="rp-order-dropdown">
                                                    <input type="checkbox" id="dropdown">
                                                    <label class="dropdown__face" for="dropdown">
                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                    </label>
                                                    <ul class="dropdown__items">
                                                        <li><a href="#">
                                                        <svg width="18" height="17" viewBox="0 0 18 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M8.40027 0.208473C7.98814 0.298342 7.58306 0.539186 7.31183 0.859114C7.22377 0.96336 6.91027 1.56727 6.39247 2.63849C5.54003 4.40348 5.49776 4.46459 5.16665 4.55446C5.08563 4.57603 4.2966 4.69825 3.41599 4.82766C2.25358 4.99661 1.74634 5.08647 1.57374 5.15118C0.432468 5.56816 -0.0324963 6.98807 0.626203 8.03412C0.686085 8.13118 1.30252 8.77463 1.99292 9.46481C3.30327 10.7733 3.37724 10.8667 3.38076 11.1543C3.38076 11.2334 3.25396 12.053 3.09897 12.9732C2.94398 13.8971 2.81717 14.7382 2.81717 14.8425C2.82069 15.6729 3.3702 16.4134 4.19093 16.6937C4.47273 16.7872 5.01871 16.7836 5.30403 16.6794C5.42379 16.6362 6.07545 16.3055 6.75528 15.9389C8.60105 14.9431 8.54469 14.9719 8.77365 14.9719C8.99556 14.9719 8.91102 14.9288 10.7674 15.9245C12.1693 16.6794 12.2186 16.7009 12.5849 16.7513C13.684 16.9022 14.7196 15.9748 14.7231 14.8389C14.7231 14.731 14.5963 13.8899 14.4413 12.9696C14.2863 12.0494 14.1595 11.2334 14.1595 11.1543C14.163 10.8667 14.237 10.7769 15.5332 9.48279C16.8366 8.1851 16.981 8.00896 17.1254 7.58479C17.2381 7.24329 17.2381 6.68971 17.1219 6.3554C16.8964 5.70836 16.3786 5.23026 15.7375 5.08288C15.6143 5.05412 14.8428 4.9319 14.0186 4.81328C13.1943 4.69106 12.4546 4.57603 12.3736 4.55446C12.0425 4.46459 12.0037 4.40348 11.1478 2.63849C10.3059 0.902249 10.239 0.790814 9.85152 0.517618C9.56268 0.312719 9.23157 0.20488 8.85819 0.1905C8.68559 0.183311 8.47776 0.1905 8.40027 0.208473ZM9.1893 1.45583C9.34781 1.55648 9.3619 1.57805 10.1615 3.19926C10.6088 4.10153 11.028 4.91752 11.0949 5.00739C11.2535 5.22307 11.5176 5.42797 11.8135 5.56457C12.0707 5.68319 12.1024 5.69038 14.318 6.0175C14.9203 6.10377 15.4734 6.19364 15.5509 6.21521C15.7481 6.26913 16.0053 6.54233 16.0616 6.75082C16.1109 6.94134 16.0863 7.1678 15.9982 7.34754C15.963 7.41943 15.3853 8.01975 14.7196 8.68117C14.0468 9.34978 13.4409 9.97886 13.3599 10.1011C13.1697 10.3779 13.0675 10.687 13.0429 11.0501C13.0253 11.3053 13.057 11.5497 13.3141 13.0703C13.6382 14.9899 13.6382 15.015 13.4409 15.281C13.2753 15.5039 13.064 15.6117 12.8033 15.6081C12.5955 15.6081 12.5532 15.5866 11.0421 14.7778C10.1897 14.3177 9.39712 13.9186 9.28088 13.8827C9.00261 13.8 8.53764 13.8 8.25937 13.8827C8.14313 13.9186 7.35057 14.3212 6.49814 14.7778C4.98701 15.5866 4.94474 15.6081 4.73691 15.6081C4.47625 15.6117 4.2649 15.5039 4.09935 15.281C3.90209 15.015 3.90209 14.9863 4.22615 13.0667C4.5467 11.1615 4.55727 10.9926 4.4058 10.5432C4.26842 10.1478 4.06764 9.91056 2.81365 8.67398C2.12677 7.99458 1.58431 7.43022 1.54556 7.35473C1.45398 7.1714 1.42932 6.94493 1.47864 6.75082C1.535 6.53873 1.79214 6.26913 1.98939 6.2188C2.06689 6.19724 2.8524 6.07142 3.73301 5.94201C4.61363 5.8126 5.39913 5.68679 5.47663 5.66881C5.86762 5.56457 6.36781 5.18353 6.56507 4.83844C6.61086 4.75217 6.97015 4.02963 7.36114 3.22802C7.75213 2.4264 8.10438 1.72184 8.13961 1.65714C8.21358 1.53851 8.38618 1.4091 8.55173 1.35518C8.72081 1.30126 9.01318 1.3444 9.1893 1.45583Z" fill="#959595"/>
                                                        </svg><?php echo __( 'Default', 'restropress' ); ?></a></li>
                                                        <li><a onclick="editaddress(event)">
                                                        <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M12.7766 0.679296C12.0579 0.873714 12.4735 0.484879 6.04375 7.05976C2.81371 10.3693 0.129226 13.1486 0.0815978 13.2414C-0.00499847 13.4005 -0.00499847 13.4712 0.00366116 15.9633C0.0166506 18.473 0.0166506 18.526 0.107577 18.6453C0.155205 18.7116 0.25046 18.8088 0.315408 18.8574C0.432312 18.9502 0.48427 18.9502 2.9436 18.9635C5.38562 18.9723 5.45489 18.9723 5.61077 18.884C5.70169 18.8353 8.42514 16.0958 11.6682 12.7995C17.4052 6.95814 17.5654 6.79023 17.7212 6.46767C18.0027 5.87558 18.072 5.28348 17.9248 4.69139C17.7429 3.97116 17.5654 3.73255 16.0932 2.24348C14.9761 1.1079 14.6861 0.878134 14.2011 0.719063C13.7854 0.582087 13.1793 0.564413 12.7766 0.679296ZM13.9153 2.1286C14.0149 2.18162 14.6038 2.7472 15.2836 3.44976C16.3833 4.57651 16.4873 4.69581 16.5565 4.91674C16.6475 5.20837 16.6258 5.53535 16.4916 5.8093C16.3963 6.00372 14.4393 8.05837 14.3483 8.05837C14.292 8.05837 10.6896 4.38209 10.6896 4.32465C10.6896 4.24511 12.6943 2.23907 12.8545 2.15953C12.9368 2.11534 13.0537 2.06232 13.1143 2.04465C13.2919 1.98279 13.7292 2.03139 13.9153 2.1286ZM11.5339 7.21883L13.3525 9.07465L9.19585 13.3165L5.03923 17.5584H3.21205H1.38054V15.6937V13.8246L5.52417 9.59604C7.80598 7.26744 9.68079 5.36302 9.69378 5.36302C9.70677 5.36302 10.5338 6.19814 11.5339 7.21883Z" fill="#959595"/>
                                                        </svg><?php echo __( 'Edit', 'restropress' ); ?></a></li>
                                                        <li><a class="btn btn-primary delete-address" data-index="<?php echo $index; ?>">
                                                        <svg width="19" height="20" viewBox="0 0 19 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M7.80363 0.754601C6.79659 0.963877 5.91406 1.55433 5.3611 2.38396C5.15603 2.69413 4.91068 3.23227 4.81913 3.57981C4.72759 3.91241 4.67266 4.45428 4.66899 4.98495V5.36613L2.61098 5.37734L0.549293 5.38855L0.37352 5.49319C0.278309 5.54924 0.153802 5.66883 0.0988728 5.75104C0.0109859 5.88558 0 5.94163 0 6.19575C0 6.45735 0.0109859 6.49845 0.109859 6.64794C0.172112 6.73389 0.296618 6.84974 0.384505 6.90206C0.549293 6.99548 0.552955 6.99548 1.64056 7.00669L2.72816 7.01791V11.6593C2.72816 14.5256 2.74281 16.3979 2.76478 16.5586C2.94788 17.8404 3.94759 18.8644 5.20364 19.0475C5.40871 19.0774 6.60617 19.0849 9.33799 19.0774C12.9487 19.0662 13.1977 19.0587 13.4211 18.9952C14.2706 18.7485 14.9444 18.1768 15.3033 17.4032C15.5816 16.7978 15.5597 17.2313 15.5743 11.8424L15.5816 7.01417H16.6143C17.7275 7.01417 17.8374 6.99922 18.0534 6.81984C18.5441 6.40503 18.372 5.56793 17.7568 5.41097C17.6543 5.38481 16.9256 5.36986 15.6256 5.36986H13.6518L13.6298 4.68598C13.6152 4.12169 13.5968 3.94605 13.5199 3.64708C13.2123 2.45122 12.3701 1.45343 11.2788 0.990036C10.6856 0.739653 10.5904 0.724705 9.26475 0.713493C8.33461 0.70602 7.99771 0.713493 7.80363 0.754601ZM10.4732 2.4288C11.0701 2.6306 11.5645 3.07531 11.8208 3.65082C11.9929 4.032 12.0295 4.24127 12.0295 4.84668V5.36986H9.15123H6.26927L6.28758 4.76072C6.30589 4.21138 6.31687 4.12542 6.41209 3.85636C6.60983 3.30327 6.99067 2.85483 7.48138 2.59323C7.97574 2.33164 8.13687 2.30922 9.28306 2.32043C10.14 2.33164 10.1949 2.33537 10.4732 2.4288ZM13.963 11.7677L13.9521 16.525L13.8495 16.723C13.714 16.9846 13.4943 17.2014 13.2416 17.3247L13.0366 17.4219H9.15489H5.27322L5.10111 17.3397C4.85942 17.2275 4.57745 16.9473 4.46026 16.6969L4.35773 16.4876L4.34674 11.749L4.33942 7.01417H9.15489H13.9704L13.963 11.7677Z" fill="#959595"/>
                                                            <path d="M7.54736 8.67716C7.47046 8.70332 7.34229 8.78927 7.26539 8.86775C7.00539 9.12934 7.01272 9.01723 7.01272 12.1676C7.01272 14.0959 7.02736 15.0414 7.053 15.1423C7.21046 15.7252 7.89525 15.9345 8.34201 15.5309C8.63496 15.2693 8.62398 15.4151 8.62398 12.1937C8.62398 10.228 8.60933 9.30499 8.5837 9.20035C8.55806 9.10692 8.4775 8.9836 8.3713 8.87896C8.13327 8.63232 7.85497 8.56505 7.54736 8.67716Z" fill="#959595"/>
                                                            <path d="M10.2424 8.66969C10.063 8.7407 9.87988 8.90513 9.78466 9.08077L9.7041 9.23773V12.19C9.7041 15.0003 9.70776 15.1497 9.77368 15.2731C10.041 15.7888 10.6709 15.9046 11.048 15.5085C11.3117 15.232 11.2971 15.4113 11.2971 12.1751V9.26388L11.1945 9.06582C11.0187 8.72201 10.5793 8.5389 10.2424 8.66969Z" fill="#959595"/>
                                                        </svg><?php echo __( 'Delete', 'restropress' ); ?></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="user-name pt-1"><?php echo esc_html($address['full_name']); ?></div>
                                            <div class="user-address pt-1"><?php echo esc_html($address['address']); ?></div>
                                            <div class="pt-1 user-contact"><?php echo esc_html($address['phone']); ?></div>
                                            <div class="pt-1 user-pin" style="display:none;"><?php echo esc_html($address['pincode']); ?></div>
                                            <div class="pt-1 user-address-index" style="display:none;"><?php echo esc_html($index); ?></div>
                                            <div class="pt-2">
                                                <button type="submit" class="btn btn-primary"><?php echo __( 'DELIVER HERE', 'restropress' ); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                // Display a message if no addresses are found
                                ?>
                                <div class="address-not-found-wrap">
                                    <img src="<?php echo RP_PLUGIN_URL . 'assets/images/user/address-not-found.png'; ?>"/>
                                    <h2>Address Not Found</h2>
                                    <p>
                                        Please add your address information in your profile by clicking this button below.
                                    </p>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <!----add-address-wrap---->
                        <div class="row">
                            <div class="add-address-wrap">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary mapimg add-new-address-btn" onclick="addaddress()" id="scrollmap">
                                        <i class="fa fa-plus"></i> 
                                        <?php echo __( 'Add New Address','restropress' ); ?>
                                    </button>
                                </div>
                                <div id="add-address-bg">
                                    <div class="box-header">
                                        <h4 class="box-title"><?php echo __( 'Add Delivery Address', 'restropress' ); ?></h4>  
                                    </div>
                                    <div class="box-body">
                                        <form method="POST" class="profile-form-wrap">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="input-wrap mb-2">
                                                        <input type="text" class="form-control" id="fullNameInput" name="full_name" value="">
                                                        <label class="form-label"><?php echo __( 'Full name', 'restropress' ); ?></label>                                        
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="input-wrap mb-2">
                                                        <input type="text" class="form-control" id="phoneInput" name="phone" value="">
                                                        <label class="form-label"><?php echo __( 'Phone', 'restropress' ); ?></label>                    
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="input-wrap mb-2">
                                                        <input type="text" class="form-control" id="addressInput" name="address" value="">
                                                        <label class="form-label"><?php echo __( 'Address', 'restropress' ); ?></label>                                        
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="input-wrap mb-2">
                                                        <input type="text" class="form-control" id="pincodeInput" name="pincode" value="">
                                                        <label class="form-label"><?php echo __( 'Pincode', 'restropress' ); ?></label>                    
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <h6 class="address-type"><?php echo __( 'Address Type', 'restropress' ); ?></h6>
                                                <div class="flex align-items-center">
                                                    <div>
                                                        <input id="radio-1" class="radio-custom" name="address_type" type="radio" checked value="Home">
                                                        <label for="radio-1" class="radio-custom-label">
                                                        <svg width="20" height="16" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M19.66 8.24873L18 6.73873L17.15 5.99873L10.66 0.248734C10.4775 0.0884151 10.2429 0 9.99999 0C9.75709 0 9.52248 0.0884151 9.33999 0.248734L2.84999 5.99873L1.99999 6.73873L0.339993 8.24873C0.15416 8.42723 0.0443991 8.67057 0.0335951 8.92801C0.0227911 9.18545 0.111778 9.43713 0.282002 9.63057C0.452226 9.824 0.690547 9.94427 0.947275 9.96628C1.204 9.9883 1.45932 9.91037 1.65999 9.74873L1.99999 9.44873V17.9987C1.99999 18.7944 2.31606 19.5574 2.87867 20.1201C3.44128 20.6827 4.20434 20.9987 4.99999 20.9987H7.99999C8.26521 20.9987 8.51956 20.8934 8.7071 20.7058C8.89464 20.5183 8.99999 20.264 8.99999 19.9987V14.9987C8.99999 14.7335 9.10535 14.4792 9.29289 14.2916C9.48042 14.1041 9.73478 13.9987 9.99999 13.9987C10.133 14.0006 10.2643 14.029 10.3862 14.0822C10.5081 14.1354 10.6182 14.2124 10.71 14.3087C10.8035 14.3974 10.8775 14.5045 10.9275 14.6233C10.9774 14.7421 11.0021 14.8699 11 14.9987V19.9987C11 20.264 11.1054 20.5183 11.2929 20.7058C11.4804 20.8934 11.7348 20.9987 12 20.9987H15C15.7956 20.9987 16.5587 20.6827 17.1213 20.1201C17.6839 19.5574 18 18.7944 18 17.9987V9.44873L18.34 9.74873C18.539 9.92354 18.7993 10.0122 19.0636 9.99536C19.328 9.97849 19.5748 9.85742 19.75 9.65873C19.9248 9.45971 20.0135 9.19947 19.9966 8.93512C19.9797 8.67077 19.8587 8.42391 19.66 8.24873ZM16 17.9987C16 18.264 15.8946 18.5183 15.7071 18.7058C15.5196 18.8934 15.2652 18.9987 15 18.9987H13V14.9987C13 14.2031 12.6839 13.44 12.1213 12.8774C11.5587 12.3148 10.7956 11.9987 9.99999 11.9987C9.20434 11.9987 8.44128 12.3148 7.87867 12.8774C7.31606 13.44 6.99999 14.2031 6.99999 14.9987V18.9987H4.99999C4.73478 18.9987 4.48042 18.8934 4.29289 18.7058C4.10535 18.5183 3.99999 18.264 3.99999 17.9987V7.66873L9.99999 2.33873L16 7.66873V17.9987Z" fill="#9C9A9A"/>
                                                        </svg> 
                                                        <?php echo __( 'Home', 'restropress' ); ?></label>
                                                    </div>
                                                    <div>
                                                        <input id="radio-2" class="radio-custom" name="address_type" type="radio" value="Office">
                                                        <label for="radio-2" class="radio-custom-label">
                                                        <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M7.63798 0.175747C7.0704 0.301006 6.47933 0.751157 6.22881 1.25611C6.03701 1.63189 5.98221 1.90589 5.98221 2.4539V2.93537H4.12289C2.92901 2.93537 2.19311 2.95103 2.06002 2.97843C1.56681 3.0802 1.05012 3.42858 0.760457 3.84741C0.462966 4.28191 0.325964 4.91212 0.419908 5.42098C0.482538 5.75762 0.701742 6.19211 0.928774 6.43089L1.12841 6.63835L1.13624 10.357L1.14798 14.0717L1.24192 14.3418C1.45721 14.9446 1.98565 15.4652 2.58063 15.6609L2.87029 15.7549H9.13326H15.3962L15.6781 15.6531C16.2026 15.4613 16.6449 15.062 16.8876 14.5649C17.099 14.1187 17.099 14.1735 17.099 10.2591V6.64618L17.2869 6.44263C18.2107 5.42881 17.9288 3.82393 16.7075 3.17414C16.2887 2.95103 16.1556 2.93537 14.2728 2.93537H12.5583V2.44216C12.5583 2.11727 12.5388 1.86675 12.4957 1.70626C12.3 0.946875 11.6619 0.34015 10.8869 0.175747C10.5189 0.0974598 7.9981 0.0974598 7.63798 0.175747ZM10.8791 1.49488C11.2157 1.69452 11.3057 1.90198 11.3057 2.48913V2.93537H9.27027H7.2348V2.49305C7.2348 1.96461 7.30134 1.78455 7.57535 1.5536C7.77498 1.38137 7.8885 1.36963 9.32898 1.38137L10.699 1.3892L10.8791 1.49488ZM16.1243 4.29756C16.6371 4.56374 16.7428 5.23701 16.3357 5.64019C16.2613 5.71456 14.6721 6.71664 12.8049 7.86746L9.40727 9.96164H9.11369H8.82011L5.59077 7.96923C1.5629 5.48361 1.86431 5.68325 1.73905 5.43273C1.61379 5.18612 1.60987 4.93952 1.71947 4.69683C1.82516 4.46197 1.89562 4.38759 2.12657 4.28191L2.32228 4.18796H9.11761H15.9168L16.1243 4.29756ZM5.33634 9.28445C6.82379 10.2004 8.11162 10.9872 8.20165 11.0342C8.72617 11.3003 9.49338 11.3003 10.0257 11.0342C10.1158 10.9872 10.6129 10.6897 11.1296 10.3726C11.6463 10.0517 12.9145 9.27271 13.9479 8.63467L15.8268 7.47602L15.8386 10.6349C15.8503 14.1578 15.862 13.9856 15.5802 14.2596C15.2905 14.5414 15.7564 14.5219 9.11761 14.5219C2.40449 14.5219 2.91335 14.5453 2.61978 14.2283C2.53366 14.1383 2.44754 13.9934 2.42406 13.9112C2.39666 13.8094 2.381 12.7291 2.381 10.6153V7.46819L2.51017 7.54256C2.57672 7.58562 3.8528 8.36849 5.33634 9.28445Z" fill="#9C9A9A"/>
                                                        </svg>    
                                                        <?php echo __( 'Office', 'restropress' ); ?></label>
                                                    </div>
                                                    <div>
                                                        <input id="radio-3" class="radio-custom" name="address_type" type="radio" value="Other">
                                                        <label for="radio-3" class="radio-custom-label">
                                                        <svg width="15" height="19" viewBox="0 0 15 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M7.06645 0.00944138C6.21649 0.105944 5.73027 0.209869 5.18095 0.414007C3.726 0.959614 2.50859 1.95804 1.6809 3.29051C0.90146 4.54874 0.541433 6.17814 0.719591 7.63309C1.0091 9.97512 2.67932 13.2042 5.44818 16.7822C6.10143 17.6285 6.94396 18.6417 7.13697 18.8162C7.39307 19.0463 7.70484 19.0463 7.96095 18.8162C8.15395 18.6417 8.99648 17.6285 9.64973 16.7822C12.1402 13.5642 13.7399 10.6395 14.2447 8.36799C14.3746 7.78527 14.4154 7.40668 14.4154 6.79798C14.4117 5.01641 13.6991 3.31278 12.4112 2.02856C11.3496 0.963326 10.0988 0.321217 8.62532 0.0799618C8.27643 0.0242882 7.31884 -0.0202503 7.06645 0.00944138ZM8.32839 1.28995C9.64231 1.46439 10.882 2.13248 11.7691 3.14204C12.9234 4.45224 13.4059 6.19299 13.083 7.87435C12.7452 9.63736 11.6614 11.8272 9.86871 14.3845C9.0967 15.4869 7.6269 17.3946 7.54896 17.3946C7.47101 17.3946 6.00121 15.4869 5.2292 14.3845C3.43649 11.8272 2.3527 9.63736 2.01494 7.87435C1.56213 5.5249 2.70159 3.12348 4.83577 1.92463C5.84533 1.36047 7.12583 1.12664 8.32839 1.28995Z" fill="#9C9A9A"/>
                                                            <path d="M7.01122 3.45753C6.6215 3.52062 6.36911 3.60228 6.00537 3.78044C5.17026 4.185 4.46876 5.03496 4.20895 5.96286C4.10874 6.31547 4.07162 7.02438 4.13101 7.41039C4.30545 8.50903 5.02551 9.47405 6.0462 9.9714C6.64748 10.2646 7.26361 10.376 7.89458 10.3054C8.3808 10.2498 8.62948 10.1793 9.05261 9.9714C10.0733 9.47405 10.7934 8.50903 10.9678 7.41039C11.0272 7.02438 10.9901 6.31547 10.8899 5.96286C10.6338 5.04238 9.92483 4.185 9.09343 3.78044C8.4105 3.44639 7.72385 3.34247 7.01122 3.45753ZM8.26946 4.76772C8.81506 4.96444 9.27159 5.36529 9.51656 5.86265C9.76152 6.36001 9.82833 6.81653 9.7207 7.31018C9.58708 7.95229 9.16024 8.52388 8.58865 8.83565C8.33255 8.97669 7.88345 9.08062 7.5494 9.08062C7.21536 9.08062 6.76625 8.97669 6.51015 8.83565C5.80866 8.45336 5.32244 7.65165 5.32244 6.87221C5.32244 6.42682 5.52286 5.86636 5.81237 5.49149C6.0796 5.1426 6.56954 4.82711 7.01122 4.71205C7.30072 4.63782 7.99851 4.67122 8.26946 4.76772Z" fill="#9C9A9A"/>
                                                        </svg>                                                    
                                                        <?php echo __( 'Other', 'restropress' ); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row pt-1">
                                                <div class="col-sm-12">
                                                    <div class="default-address-checkbox pt-2">
                                                        <input type="checkbox" value="1" id="default-address-checkboxInput6" name="default_address" class="mm">
                                                        <label for="default-address-checkboxInput6"></label>
                                                        <span class="default-add-text"><?php echo __( 'Make this my default address', 'restropress' ); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="edit_user_address_index" id="edit_user_address_index" value="">
                                            <div class="text-end">
                                                <input type="submit" name="submit_user_address" id="form_submit_button" value="Save Address">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>  
            </div> 
        </div>
    </div>


<div class="modal micromodal-slide" id="rpressModal" aria-hidden="true">
  <div class="modal__overlay" tabindex="-1" data-micromodal-close>
    <div class="modal__container modal-content" role="dialog" aria-modal="true">
      <header class="modal__header modal-header">
        <h2 class="modal__title modal-title"></h2>
        <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
      </header>
      <main class="modal__content modal-body">
      </main>
      <footer class="modal__footer modal-footer">
        <div class="rpress-popup-actions edit-rpress-popup-actions rp-col-md-12">
          <div class="rp-col-md-4 rp-col-xs-4 btn-count">
            <div class="rp-col-md-4 rp-col-sm-4 rp-col-xs-4">
              <input type="button" value="&#8722;" class="qtyminus qtyminus-style qtyminus-style-edit">
            </div>
            <div class="rp-col-md-4 rp-col-sm-4 rp-col-xs-4">
              <input type="text" name="quantity" value="1" class="qty qty-style" readonly>
            </div>
            <div class="rp-col-md-4 rp-col-sm-4 rp-col-xs-4">
              <input type="button" value="&#43;" class="qtyplus qtyplus-style qtyplus-style-edit">
            </div>
          </div>
          <div class="rp-col-md-8 rp-col-xs-8">
            <a href="javascript:void(0);" data-title="" data-item-qty="1" data-cart-key="" data-item-id="" data-variable-id="" data-item-price="" data-cart-action="" class="center submit-fooditem-button text-center inline rp-col-md-6">
              <span class="cart-action-text rp-ajax-toggle-text"></span>
              <span class="cart-item-price"></span>
            </a>
          </div>
        </div>
      </footer>
    </div>
  </div>
</div>


    <?php
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['submit_profile_form'] == "Submit") {
    // Get submitted form data
    $new_user_fname = $_POST['first_name'];
    $new_user_lname = $_POST['last_name'];
    $new_user_email = $_POST['email'];
    $new_user_phone = $_POST['phone'];

    // Update user data
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    // Update first name
    if (!empty($new_user_fname)) {
        wp_update_user(array('ID' => $user_id, 'first_name' => $new_user_fname));
    }

    // Update last name
    if (!empty($new_user_lname)) {
        wp_update_user(array('ID' => $user_id, 'last_name' => $new_user_lname));
    }

    // Update email
    if (!empty($new_user_email) && is_email($new_user_email)) {
        wp_update_user(array('ID' => $user_id, 'user_email' => $new_user_email));
    }

    // Update phone
    if (!empty($new_user_phone)) {
        update_user_meta($user_id, 'phone_number', $new_user_phone);
    }
    
    // Output JavaScript to reload the page
    echo '<script>window.location.href = window.location.href;</script>';
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_user_address'])) {
    // Get current user ID
    $user_id = get_current_user_id();

    // Verify user ID
    if (!$user_id) {
        echo "Error: Unable to get user ID.";
        exit; // Stop execution if user ID is not valid
    }

    // Get existing addresses
    $existing_addresses = get_user_meta($user_id, 'user_addresses', true);
    if (empty($existing_addresses)) {
        $existing_addresses = array(); // Initialize as empty array if no addresses exist
    }

    // Get form data
    $fullName = sanitize_text_field($_POST['full_name']);
    $phone = sanitize_text_field($_POST['phone']);
    $address = sanitize_text_field($_POST['address']);
    $pincode = sanitize_text_field($_POST['pincode']);
    $addressType = sanitize_text_field($_POST['address_type']);
    $isDefault = isset($_POST['default_address']) ? 1 : 0;

    // Debug: Output the value of $_POST['edit_user_address_index']
    echo "Edit Index: " . $_POST['edit_user_address_index'];

    // If edit index is provided, update the address, otherwise add a new one
    if (!empty($_POST['edit_user_address_index'])) {
        $edit_index = intval($_POST['edit_user_address_index']);
        if (isset($existing_addresses[$edit_index])) {
            // Update the existing address
            $existing_addresses[$edit_index] = array(
                'full_name' => $fullName,
                'phone' => $phone,
                'address' => $address,
                'pincode' => $pincode,
                'address_type' => $addressType,
                'default_address' => $isDefault
            );
        }
    } else {
        // Create an array to hold the new address data
        $new_address = array(
            'full_name' => $fullName,
            'phone' => $phone,
            'address' => $address,
            'pincode' => $pincode,
            'address_type' => $addressType,
            'default_address' => $isDefault
        );

        // Add the new address to the existing addresses
        $existing_addresses[] = $new_address;
    }

    // Debug: Output the existing addresses array
    echo "Existing Addresses: ";

    // Update user meta with serialized address data
    update_user_meta($user_id, 'user_addresses', $existing_addresses);

    // Output JavaScript to reload the page
    echo '<script>window.location.href = window.location.href;</script>';
    exit;
}
?>