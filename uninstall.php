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
 * @package Product Content Generator with ChatGPT for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$wpdb->delete( $wpdb->options, array( 'option_name' => 'CONTENT_GENERATOR_OPTIONS' ) );
