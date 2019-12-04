<?php
/**
 * Upgrade Screen
 *
 * @package     RPRESS
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0.0.1
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Render Upgrades Screen
 *
 * @since  1.0.0
 * @return void
*/
function rpress_upgrades_screen() {
  $action = isset( $_GET['rpress-upgrade'] ) ? sanitize_text_field( $_GET['rpress-upgrade'] ) : '';
  ?>

  <div class="wrap">
  <h2><?php _e( 'RestroPress - Upgrades', 'restropress' ); ?></h2>
  <?php
  if ( is_callable( 'rpress_upgrade_render_' . $action ) ) {

    // Until we have fully migrated all upgrade scripts to this new system, we will selectively enqueue the necessary scripts.
    add_filter( 'rpress_load_admin_scripts', '__return_true' );
    rpress_load_admin_scripts( '' );

    // This is the new method to register an upgrade routine, so we can use an ajax and progress bar to display any needed upgrades.
    call_user_func( 'rpress_upgrade_render_' . $action );

  } else {

    // This is the legacy upgrade method, which requires a page refresh at each step.
    $step   = isset( $_GET['step'] )        ? absint( $_GET['step'] )                     : 1;
    $total  = isset( $_GET['total'] )       ? absint( $_GET['total'] )                    : false;
    $custom = isset( $_GET['custom'] )      ? absint( $_GET['custom'] )                   : 0;
    $number = isset( $_GET['number'] )      ? absint( $_GET['number'] )                   : 100;
    $steps  = round( ( $total / $number ), 0 );
    if ( ( $steps * $number ) < $total ) {
      $steps++;
    }

    $doing_upgrade_args = array(
      'page'        => 'rpress-upgrades',
      'rpress-upgrade' => $action,
      'step'        => $step,
      'total'       => $total,
      'custom'      => $custom,
      'steps'       => $steps
    );
    update_option( 'rpress_doing_upgrade', $doing_upgrade_args );
    if ( $step > $steps ) {
      // Prevent a weird case where the estimate was off. Usually only a couple.
      $steps = $step;
    }
    ?>

      <?php if( ! empty( $action ) ) : ?>

        <div id="rpress-upgrade-status">
          <p><?php _e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'restropress' ); ?></p>

          <?php if( ! empty( $total ) ) : ?>
            <p><strong><?php printf( __( 'Step %d of approximately %d running', 'restropress' ), $step, $steps ); ?></strong></p>
          <?php endif; ?>
        </div>
        <script type="text/javascript">
          setTimeout(function() { document.location.href = "index.php?rpress_action=<?php echo $action; ?>&step=<?php echo $step; ?>&total=<?php echo $total; ?>&custom=<?php echo $custom; ?>"; }, 250);
        </script>

      <?php else : ?>

        <div id="rpress-upgrade-status">
          <p>
            <?php _e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'restropress' ); ?>
            <img src="<?php echo RP_PLUGIN_URL . '/assets/images/loading.gif'; ?>" id="rpress-upgrade-loader"/>
          </p>
        </div>
        <script type="text/javascript">
          jQuery( document ).ready( function() {
            // Trigger upgrades on page load
            var data = { action: 'rpress_trigger_upgrades' };
            jQuery.post( ajaxurl, data, function (response) {
              if( response == 'complete' ) {
                jQuery('#rpress-upgrade-loader').hide();
                document.location.href = 'index.php'; // Redirect to the dashboard
              }
            });
          });
        </script>

      <?php endif; ?>

    <?php
  }
  ?>
  </div>
  <?php
}
