<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\Admin\Settings_Screens;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\Facebook\Admin;
use SkyVerge\WooCommerce\Facebook\Products\FB_Feed_Generator;


/**
 * The Messenger settings screen object.
 */
class Feed_Status extends Admin\Abstract_Settings_Screen {


	/** @var string screen ID */
	const ID = 'feed_status';

	/**
	 * Connection constructor.
	 */
	public function __construct() {

		$this->id    = self::ID;
		$this->label = __( 'Feed Status', 'facebook-for-woocommerce' );
		$this->title = $this->label;

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}


	/**
	 * Enqueues the assets.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {

		if ( ! $this->is_current_screen_page() ) {
			return;
		}

		wp_enqueue_script( 'facebook-for-woocommerce-feed-status', plugins_url( '/facebook-for-woocommerce/assets/js/admin/facebook-for-woocommerce-settings-feed-status.js' ), [ 'jquery' ], \WC_Facebookcommerce::PLUGIN_VERSION );

		$settings = get_option( FB_Feed_Generator::RUNNING_FEED_SETTINGS, array() );

		wp_localize_script(
			'facebook-for-woocommerce-feed-status',
			'facebook_for_woocommerce_feed_status',
			array(
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'feed_generation_nonce'  => wp_create_nonce( FB_Feed_Generator::FEED_GENERATION_NONCE ),
				'generation_in_progress' => FB_Feed_Generator::is_generation_in_progress(),
				'generation_progress'    => $settings['total'] !== 0 ? intval( ( ( $settings['page'] * FB_Feed_Generator::FEED_GENERATION_LIMIT ) / $settings['total'] ) * 100 ) : 0,
				'i18n'                   => array(
					/* translators: Placeholders %s - html code for a spinner icon */
					'confirm_resync' => esc_html__( 'Your products will now be resynced to Facebook, this may take some time.', 'facebook-for-woocommerce' ),
				),
			)
		);
	}

	public function render() {
		$settings = get_option( FB_Feed_Generator::RUNNING_FEED_SETTINGS, array() );
		?>
		<h1><?php esc_html_e( 'Feed Status', 'woocommerce' ); ?></h1>
		<div class="facebook-for-woocommerce-feed-status-wrapper">
			<form class="facebook-for-woocommerce-feed-generator">
				<header>
					<span class="spinner is-active"></span>
					<p><?php esc_html_e( 'This pages shows the status and statistics of the feed file generation', 'facebook-for-woocommerce' ); ?></p>
				</header>
				<section>
					<p><?php echo sprintf( esc_html__( 'Total number of products: %s ', 'facebook-for-woocommerce' ), $settings['total'] ) ?></p>
					<p><?php echo sprintf( esc_html__( 'Current batch number: %s', 'facebook-for-woocommerce' ), $settings['page'] ) ?></p>
					<p><?php echo sprintf( esc_html__( 'Started timestamp: %s', 'facebook-for-woocommerce' ), $settings['start'] ) ?></p>
				</section>
				<section>
					<progress class="facebook-woocommerce-feed-generator-progress" max="100" value="0"></progress>
					<?php
					if ( $settings['done'] ) {
						esc_html_e( ' Done in: ' . ( ( $settings['end'] - $settings['start'] ) / 60 ) . ' minutes.', 'facebook-for-woocommerce' );
					}
					?>
				</section>
				<section>
					<p>
						<?php
						$next = as_next_scheduled_action( FB_Feed_Generator::FEED_SCHEDULE_ACTION );
						if ( $next ) {
							esc_html_e( 'Next feed generation scheduled at: ' . date( 'Y-m-d H:i:s', $next ), 'facebook-for-woocommerce' );
						}
						?>
					</p>
				</section>
				<div class="wc-actions">
					<button type="submit" class="facebook-woocommerce-feed-generator-button button button-primary" value="<?php esc_attr_e( 'Generate Feed', 'woocommerce' ); ?>"><?php esc_html_e( 'Generate Feed', 'woocommerce' ); ?></button>
				</div>
			</form>
		</div>
		<?php
		parent::render();
	}

	/**
	 * Gets the screen settings.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_settings() {
		return array();
	}
}