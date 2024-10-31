<?php
/**
 *   This file is part of Product Content Generator with ChatGPT Connector.
 *
 *   Product Content Generator with ChatGPT Connector is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Product Content Generator with ChatGPT Connector is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Mobile Assistant Connector.
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 *   author    eMagicOne <contact@emagicone.com>
 *   2024 eMagicOne
 *   http://www.gnu.org/licenses   GNU General Public License
 *
 * @package Product Content Generator with ChatGPT Connector
 */

/**
 * Start the plugin
 */
class Emo_CG_ContentGeneratorStart {
	/** Main plugin method
	 *
	 * @param string $plugin_basename The plugin basename.
	 */
	public function __construct( $plugin_basename ) {
		add_action( 'admin_enqueue_scripts', array( &$this, 'register_option_styles' ) );
		register_activation_hook( __FILE__, array( &$this, 'emo_content_generator_activation' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'emo_content_generator_deactivation' ) );

		$plugin = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_$plugin_basename", array( &$this, 'setting_link' ) );

	}
			/** Plugin deactivation method */
	public function emo_content_generator_deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );
	}
			/** Plugin activation method */
	public function emo_content_generator_activation() {
		global $wpdb;

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';
		check_admin_referer( "activate-plugin_{$plugin}" );

		if ( ! ( get_option( 'CONTENT_GENERATOR_OPTIONS' ) ) ) {
			$wpdb->replace(
				$wpdb->options,
				array(
					'option_name'  => 'CONTENT_GENERATOR_OPTIONS',
					'option_value' => serialize( emo_cg_get_default_content_generator_options() ),
				)
			);
		}
	}
			/** Register styles */
	public function register_option_styles() {
		global $hook_suffix;

		if ( 'toplevel_page_product-content-generator-with-chatgpt' === $hook_suffix ) {
			wp_register_style( 'ema_style', plugins_url( '../assets/css/style.css', __FILE__ ), array(), '1.0' );
			wp_enqueue_style( 'ema_style' );
		}
	}
			/** Add settings link on plugins page
			 *
			 * @param string $links Settings link.
			 */
	public function setting_link( $links ) {
		$settings_link = '<a href="admin.php?page=product-content-generator-with-chatgpt">Settings</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}
}

