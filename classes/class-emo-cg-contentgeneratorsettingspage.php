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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create plugin settings page
 */
class Emo_CG_ContentGeneratorSettingsPage {
	/** Options array
	 *
	 * @var array $options The array of settings in admin panel.
	 */
	private $options;

	/** Build plugin configuration page */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );

		if ( is_multisite() ) {
			$curr_site = get_current_blog_id();
			$sites     = get_sites();
			$option    = get_blog_option( $curr_site, 'CONTENT_GENERATOR_OPTIONS' );
			foreach ( $sites as $site ) {
				if ( $curr_site != $site->blog_id ) {
					switch_to_blog( $site->blog_id );
					update_blog_option( $site->blog_id, 'CONTENT_GENERATOR_OPTIONS', $option );
					restore_current_blog();
				}
			}
		}
	}

	/** Add options page */
	public function add_plugin_page() {
		// This page will be under "Settings".
		add_menu_page(
			'Product Content Generator with ChatGPT',
			'Product Content Generator with ChatGPT',
			'manage_options',
			'product-content-generator-with-chatgpt',
			array( $this, 'create_admin_page' ),
			plugins_url( '../images/emagicone.png', __FILE__ )
		);
	}

	/** Options page callback */
	public function create_admin_page() {
		global $wpdb;

		$this->options = get_option( 'CONTENT_GENERATOR_OPTIONS' );

		if ( ! $this->options ) {
			$wpdb->replace(
				$wpdb->options,
				array(
					'option_name'  => 'CONTENT_GENERATOR_OPTIONS',
					'option_value' => serialize( emo_cg_get_default_content_generator_options() ),
				)
			);
		}
		?>
		<div class="wrap">
			<h2>ChatGPT Product Content Generator</h2>
            <?php settings_errors(); ?>
			<form method="post" action="options.php">
			<?php settings_fields( 'content_generator_group' ); ?>
			<div class="section_wrap">
			<?php do_settings_sections( 'content-generator-access' ); ?>
			</div>
			<div class="button_toolbar_cg tablenav bottom">
				<?php submit_button( __( 'Save Settings', 'product-content-generator-with-chatgpt' ), 'primary', 'submit-form', false ); ?>
			</div>
			<div class="section_wrap">
			<?php do_settings_sections( 'content-generator-note' ); ?>
			</div>
			</form>
		</div>
		<?php
	}

	/** Register and add settings */
	public function page_init() {
		register_setting(
			'content_generator_group', // Option group.
			'CONTENT_GENERATOR_OPTIONS', // Option name.
			array( $this, 'sanitize' ) // Sanitize.
		);

		// Add Access Settings.
		add_settings_section(
			'setting_section_id', // ID.
			__( 'Access Settings', 'product-content-generator-with-chatgpt' ), // Title.
			array( $this, 'print_section_access' ),
			'content-generator-access' // Page.
		);

		// Add Note Section.
		add_settings_section(
			'setting_section_id', // ID.
			__( 'Important! Check pricing section to estimate the cost of the service offered by ChatGPT: <a href="https://openai.com/pricing">https://openai.com/pricing</a>', 'product-content-generator-with-chatgpt' ), // Title.
			array( $this, 'print_note_section' ),
			'content-generator-note' // Page.
		);

		// Add field 'open_ai_api_key'.
		add_settings_field(
			'emo_cg_open_ai_api_key', // ID.
			__( 'OpenAI API Key', 'product-content-generator-with-chatgpt' ), // Title.
			array( $this, 'openai_api_key_callback' ), // Callback.
			'content-generator-access', // Page.
			'setting_section_id' // Section.
		);

		// Add field 'model'.
		add_settings_field(
			'emo_cg_model', // ID.
			__( 'Model', 'product-content-generator-with-chatgpt' ), // Title.
			array( $this, 'model_callback' ), // Callback.
			'content-generator-access', // Page.
			'setting_section_id' // Section.
		);

		// Add field 'max_tokens'.
		add_settings_field(
			'emo_cg_max_tokens', // ID.
			__( 'Max Tokens', 'product-content-generator-with-chatgpt' ), // Title.
			array( $this, 'max_tokens_callback' ), // Callback.
			'content-generator-access', // Page.
			'setting_section_id' // Section.
		);

		// Add field 'temperature'.
		add_settings_field(
			'emo_cg_temperature', // ID.
			__( 'Temperature', 'product-content-generator-with-chatgpt' ), // Title.
			array( $this, 'temperature_callback' ), // Callback.
			'content-generator-access', // Page.
			'setting_section_id' // Section.
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys.
	 * @return array
	 */
	public function sanitize( $input ) {
		$new_options             = array();
		$options_prev            = get_option( 'CONTENT_GENERATOR_OPTIONS' );
		$cg_open_ai_api_key_prev = isset( $options_prev['open_ai_api_key'] )
			? $options_prev['open_ai_api_key']
			: EMO_CG_DEFAULT_OPENAI_KEY;

		$new_options['open_ai_api_key'] = ! empty( $input['emo_cg_open_ai_api_key'] ) && $input['emo_cg_open_ai_api_key'] != $cg_open_ai_api_key_prev
			? sanitize_text_field( $input['emo_cg_open_ai_api_key'] )
			: EMO_CG_DEFAULT_OPENAI_KEY;
		$new_options['model']           = isset( $input['emo_cg_model'] )
			? sanitize_text_field( $input['emo_cg_model'] )
			: EMO_CG_DEFAULT_MODEL;
		$new_options['max_tokens']      = isset( $input['emo_cg_max_tokens'] )
			? (int) sanitize_text_field( $input['emo_cg_max_tokens'] )
			: EMO_CG_DEFAULT_MAX_TOKENS;
		$new_options['temperature']     = isset( $input['emo_cg_temperature'] )
			? (float) number_format( sanitize_text_field( $input['emo_cg_temperature'] ), 1 )
			: EMO_CG_DEFAULT_TEMPERATURE;

		if ( empty( $input['emo_cg_open_ai_api_key'] ) ) {
			$new_options['open_ai_api_key'] = '';
		} elseif ( $input['emo_cg_open_ai_api_key'] !== $cg_open_ai_api_key_prev ) {
			$new_options['open_ai_api_key'] = emo_cg_get_encrypted_password( $input['emo_cg_open_ai_api_key'] );
		} else {
			$new_options['open_ai_api_key'] = $cg_open_ai_api_key_prev;
		}

		return $new_options;
	}

	/** Print the Access Section text */
	public function print_section_access() {
		echo esc_html__( 'Enter OpenAI options below:', 'product-content-generator-with-chatgpt' );
	}

	/** Print the Store Manager Connector Options Section text */
	public function print_note_section() {
		?>
		<p>Also make sure to check your current balance once you run the AI. eMagicOne does not charge any fees
			and does not guarantee any results that you can get when using third-party AI services.
		You should choose your plan an have at least $5 on your balance to be able to use ChatGPT API.
		Please refer to ChatGPT pricing for details.</p>
		<p>How to get your ChatGPT API Key:
		<ol>
			<li>Go to the official OpenAI website at <a href="https://openai.com">https://openai.com</a></li>
			<li>Create an account or login with your existing OpenAI account.</li>
			<li>Navigate to API Section. API Section may provide details about the available plans, including free
				and paid options.</li>
			<li>Generate a new API key by selecting [Create new secret key button]. This key is essential for authentication
			your requests to the ChatGPT API. Keep it secure and do not share it publicly.</li>
			<li>Specify the API key above in the OpenAI API Key.</li>
	</ol></p>
		<p><b>Please note</b>, Product Content Generator with ChatGPT is not a stand-alone product. It is used to set OpenAI
			credentials in Store Manager for WooCommerce.
			If you still do not have Store Manager installed, download free trial by this
			<a href="https://emagicone.com/products/store-manager-for-woocommerce">link</a>
		</p>
		<?php
	}
	/** Generate openAI api key field on the plugin settings page */
	public function openai_api_key_callback() {
		$validate_api_key = false;

		if ( ! isset( $this->options['open_ai_api_key'] ) ) {
			$this->options['open_ai_api_key'] = EMO_CG_DEFAULT_OPENAI_KEY;
		} else {
			$this->options['open_ai_api_key'] = emo_cg_get_decrypted_password( $this->options['open_ai_api_key'] );
		}

		if ( preg_match( '/^sk-/', $this->options['open_ai_api_key'] ) ) {
			$validate_api_key = true;
		}

		printf(
			'<input type="password" id="emo_cg_open_ai_api_key" name="CONTENT_GENERATOR_OPTIONS[emo_cg_open_ai_api_key]" 
                    pattern="^sk-.*$" value="%s" autocomplete="off"/> %s',
			esc_attr( $this->options['open_ai_api_key'] ),
			'' === $this->options['open_ai_api_key'] ? '' : esc_textarea(
				! $validate_api_key
				? '<span class="warning"><br />' . esc_html__( 'API key is not valid.', 'product-content-generator-with-chatgpt' ) . '</span>'
				: ''
			)
		);
	}
	/** Generate model field on the plugin settings page */
	public function model_callback() {
		if ( ! isset( $this->options['model'] ) ) {
			$this->options['model'] = 'gpt-3.5-turbo';
		}

		?>
		<select id="emo_cg_model" name="CONTENT_GENERATOR_OPTIONS[emo_cg_model]">
            <option value="gpt-4o" <?php selected( $this->options['model'], 'gpt-4o' ); ?>>ChatGPT -4o</option>
            <option value="gpt-4o-mini" <?php selected( $this->options['model'], 'gpt-4o-mini' ); ?>>ChatGPT -4o mini</option>
            <option value="gpt-4-turbo" <?php selected( $this->options['model'], 'gpt-4-turbo' ); ?>>ChatGPT -4 Turbo</option>
            <option value="gpt-4" <?php selected( $this->options['model'], 'gpt-4' ); ?>>ChatGPT -4</option>
            <option value="gpt-3.5-turbo" <?php selected( $this->options['model'], 'gpt-3.5-turbo' ); ?>>ChatGPT -3.5 Turbo</option>
		</select>
		<?php
	}
	/** Generate max tokens field on the plugin settings page */
	public function max_tokens_callback() {
		if ( ! isset( $this->options['max_tokens'] ) ) {
			$this->options['max_tokens'] = 1024;
		}

		?>
		<input type="number" id="emo_cg_max_tokens" name="CONTENT_GENERATOR_OPTIONS[emo_cg_max_tokens]"
			   min="1" max="99999" required
			   value="<?php echo esc_attr( $this->options['max_tokens'] ); ?>"/>
		<?php
	}

	/** Generate temperature field on the plugin settings page */
	public function temperature_callback() {
		if ( ! isset( $this->options['temperature'] ) ) {
			$this->options['temperature'] = 0.1;
		}

		?>
		<input type="number" id="emo_cg_temperature" name="CONTENT_GENERATOR_OPTIONS[emo_cg_temperature]"
			   min="0.1" max="1.9" step="0.1" required
			   value="<?php echo esc_attr( $this->options['temperature'] ); ?>" />
		<?php
	}
}

if ( is_admin() ) {
	$GLOBALS['ContentGeneratorSettingsPage'] = new Emo_CG_ContentGeneratorSettingsPage();
}
