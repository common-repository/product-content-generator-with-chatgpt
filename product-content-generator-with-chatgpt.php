<?php
/**
 * Plugin Name: Product Content Generator with ChatGPT for WooCommerce
 * Plugin URI: https://emagicone.com/plugins/chatgpt-for-woocommerce-products/
 * Description: Product Content Generator with ChatGPT for WooCommerce allows you to connect your WooCommerce store to ChatGPT for content generation and product details translation.
 *              Also Store Manager desktop software and Store Manager Connector plugin is required.
 * Author: eMagicOne
 * Author URI: https://emagicone.com/
 * Version: 1.1.6
 * Text Domain: product-content-generator-with-chatgpt
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * @package Product Content Generator with ChatGPT for WooCommerce
 */

/*
-----------------------------------------------------------------------------+
| eMagicOne                                                                    |
| Copyright (c) 2024 eMagicOne.com <contact@emagicone.com>		               |
| All rights reserved                                                          |
+------------------------------------------------------------------------------+
|                                                                              |
| Product Content Generator with ChatGPT for WooCommerce                       |
|                                                                              |
| Developed by eMagicOne,                                                      |
| Copyright (c) 2024                                            	           |
+------------------------------------------------------------------------------+
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMO_CG_CRYPT_KEY', "EMO_content_generator\0\0" );
define( 'EMO_CG_DEFAULT_OPENAI_KEY', '' );
define( 'EMO_CG_DEFAULT_MODEL', 'gpt-3.5-turbo' );
define( 'EMO_CG_DEFAULT_MAX_TOKENS', 1024 );
define( 'EMO_CG_DEFAULT_TEMPERATURE', 0.1 );
/** Check if Store Manager Connector is activated */
function emo_cg_check_is_smconnector_activated() {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	$plugin_status = false;
	$separator     = substr( __DIR__, -39, 1 );
	$path          = str_replace( $separator . 'product-content-generator-with-chatgpt', '', __DIR__ );

	$results = scandir( $path, SCANDIR_SORT_ASCENDING );

	foreach ( $results as $result ) {
		if ( '.' === $result || '..' === $result ) {
			continue;
		}

		$smconnector_plugin_path = "$path/$result";
		if ( is_dir( $smconnector_plugin_path )
			&& (bool) preg_match( '/^store-manager-connector$/', $result ) !== false
			&& file_exists( "$smconnector_plugin_path/smconnector.php" )
			&& ( is_plugin_active( "$result/smconnector.php" )
				|| in_array(
					"$result/smconnector.php",
					apply_filters(
						'active_plugins',
						get_option( 'active_plugins' )
					),
					true
				)
			)
		) {
			$plugin_status = true;
			break;
		}
	}

	return $plugin_status;
}
/** Get the default content generator options */
function emo_cg_get_default_content_generator_options() {
	$option_values = array(
		'open_ai_api_key' => EMO_CG_DEFAULT_OPENAI_KEY,
		'model'           => EMO_CG_DEFAULT_MODEL,
		'max_tokens'      => EMO_CG_DEFAULT_MAX_TOKENS,
		'temperature'     => EMO_CG_DEFAULT_TEMPERATURE,
	);

	return $option_values;
}
/** Get the encrypted password
 *
 * @param string $data The decrypted password.
 */
function emo_cg_get_encrypted_password( $data ) {
	return call_user_func(
		'base64_encode',
		openssl_encrypt(
			$data,
			'aes-192-ecb',
			EMO_CG_CRYPT_KEY,
			OPENSSL_RAW_DATA
		)
	);
}
/** Get the decrypted password
 *
 * @param string $data The encrypted password.
 */
function emo_cg_get_decrypted_password( $data ) {
	return trim(
		preg_replace(
			'/(^\s+)|(\s+$)/us',
			'',
			openssl_decrypt(
				call_user_func(
					'base64_decode',
					$data
				),
				'aes-192-ecb',
				EMO_CG_CRYPT_KEY,
				OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
			)
		),
		"\x00..\x1F"
	);
}

/**
 * Check if Product Content Generator with ChatGPT is active
 */
if ( emo_cg_check_is_smconnector_activated() ) {
	if ( ! class_exists( 'Emo_CG_ContentGeneratorStart' ) ) {
		include_once plugin_dir_path( __FILE__ ) . 'classes/class-emo-cg-contentgeneratorsettingspage.php';
		include_once plugin_dir_path( __FILE__ ) . 'classes/class-emo-cg-contentgeneratorstart.php';
		$plugin_basename = plugin_basename( __FILE__ );
		$GLOBALS['EmoContentGeneratorStart'] = new Emo_CG_ContentGeneratorStart( $plugin_basename );
	}
} else {
	add_action( 'admin_notices', 'emo_cg_admin_notices' );
}
/** Add connector admin notice */
function emo_cg_admin_notices() {
	echo '<div id="notice" class="error"><p>';
	echo '<b> Product Content Generator with ChatGPT for WooCommerce </b> add-on requires <a href="https://wordpress.org/plugins/store-manager-connector/"> Store Manager Connector </a> plugin. Please install and activate it.';
	echo '</p></div>', "\n";
}
