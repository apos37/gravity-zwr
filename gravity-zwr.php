<?php
/**
 * Plugin Name:         Add-On for Zoom Registration and Gravity Forms
 * Plugin URI:          https://apos37.com/wordpress-addon-for-zoom-gravity-forms/
 * Description:         Register attendees in your Zoom Webinar or Zoom Meeting through a Gravity Form
 * Version:             1.3.1
 * Requires at least:   5.9.0
 * Tested up to:        6.6.2
 * Requires PHP:        8.0
 * Author:              Apos37
 * Author URI:          https://apos37.com/
 * Text Domain:         gravity-zwr
 * License:             GPLv3 or later
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Defines
 */
defined( 'GRAVITYZWR_NAME' ) || define( 'GRAVITYZWR_NAME', 'Add-On for Zoom Registration and Gravity Forms' );
defined( 'GRAVITYZWR_TEXTDOMAIN' ) || define( 'GRAVITYZWR_TEXTDOMAIN', 'gravity-zwr' );
defined( 'GRAVITYZWR_VERSION' ) || define( 'GRAVITYZWR_VERSION', '1.3.1' );
defined( 'GRAVITYZWR_ROOT' ) || define( 'GRAVITYZWR_ROOT', plugin_dir_path( __FILE__ ) );
defined( 'GRAVITYZWR_URI' ) || define( 'GRAVITYZWR_URI', plugin_dir_url( __FILE__ ) );
defined( 'GRAVITYZWR_ZOOMAPIURL' ) || define( 'GRAVITYZWR_ZOOMAPIURL', 'https://api.zoom.us/v2' );


/**
 * Load the Bootstrap
 */
add_action( 'gform_loaded', array( 'GravityZWR_Bootstrap', 'load' ), 5 );


/**
 * GravityZWR_Bootstrap Class
 */
class GravityZWR_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
            return;
        }

        // Load API Helper classes.
        require_once GRAVITYZWR_ROOT . 'includes/class-gravityzwr-wordpressremote.php';
        require_once GRAVITYZWR_ROOT . 'includes/class-gravityzwr-zoomapi.php';

        // Load main plugin class.
        require_once GRAVITYZWR_ROOT . 'includes/class-gravityzwr.php';

        // Register the addon
        GFAddOn::register( 'GravityZWR' );
    }
}


/**
 * Filter plugin action links
 */
add_filter( 'plugin_row_meta', 'gfzoom_plugin_row_meta' , 10, 2 );


/**
 * Add links to our website and Discord support
 *
 * @param array $links
 * @return array
 */
function gfzoom_plugin_row_meta( $links, $file ) {
    // Only apply to this plugin
    if ( GRAVITYZWR_TEXTDOMAIN.'/'.GRAVITYZWR_TEXTDOMAIN.'.php' == $file ) {

        // Add the link
        $row_meta = [
            'docs'    => '<a href="'.esc_url( 'https://apos37.com/wordpress-addon-for-zoom-gravity-forms/' ).'" target="_blank" aria-label="'.esc_attr__( 'Plugin Website Link', 'gravity-zwr' ).'">'.esc_html__( 'Website', 'gravity-zwr' ).'</a>',
            'discord' => '<a href="'.esc_url( 'https://discord.gg/3HnzNEJVnR' ).'" target="_blank" aria-label="'.esc_attr__( 'Plugin Support on Discord', 'gravity-zwr' ).'">'.esc_html__( 'Discord Support', 'gravity-zwr' ).'</a>'
        ];
        return array_merge( $links, $row_meta );
    }

    // Return the links
    return (array) $links;
} // End gfzoom_plugin_row_meta()