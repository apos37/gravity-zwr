<?php
/**
 * Plugin Name:         Add-On for Zoom Registration and Gravity Forms
 * Plugin URI:          https://github.com/apos37/gravity-zwr
 * Description:         Register attendees in your Zoom Webinar or Zoom Meeting through a Gravity Form
 * Version:             1.5.0
 * Requires at least:   5.9
 * Tested up to:        6.8
 * Requires PHP:        8.0
 * Author:              PluginRx
 * Author URI:          https://pluginrx.com/
 * Discord URI:         https://discord.gg/3HnzNEJVnR
 * Text Domain:         gravity-zwr
 * License:             GPLv3 or later
 * License URI:         http://www.gnu.org/licenses/gpl-3.0.txt
 * Created on:          October 9, 2024
 */


/**
 * Exit if accessed directly.
 */
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * If the old version is active, let's deactivate it
 */
if ( is_plugin_active( 'gravity-forms-zoom-webinar-registration-master/gravity-forms-zoom-webinar-registration.php' ) ) {
    deactivate_plugins( 'gravity-forms-zoom-webinar-registration-master/gravity-forms-zoom-webinar-registration.php' );
}


/**
 * Defines
 */
$plugin_data = get_file_data( __FILE__, [
    'name'         => 'Plugin Name',
    'version'      => 'Version',
    'textdomain'   => 'Text Domain',
    'author_uri'   => 'Author URI',
    'discord_uri'  => 'Discord URI'
] );

defined( 'GRAVITYZWR_NAME' ) || define( 'GRAVITYZWR_NAME', $plugin_data[ 'name' ] );
defined( 'GRAVITYZWR_TEXTDOMAIN' ) || define( 'GRAVITYZWR_TEXTDOMAIN', $plugin_data[ 'textdomain' ] );
defined( 'GRAVITYZWR_VERSION' ) || define( 'GRAVITYZWR_VERSION', $plugin_data[ 'version' ] );
defined( 'GRAVITYZWR_ROOT' ) || define( 'GRAVITYZWR_ROOT', plugin_dir_path( __FILE__ ) );
defined( 'GRAVITYZWR_PLUGIN_DIR' ) || define( 'GRAVITYZWR_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
defined( 'GRAVITYZWR_URI' ) || define( 'GRAVITYZWR_URI', plugin_dir_url( __FILE__ ) );
defined( 'GRAVITYZWR_ZOOMAPIURL' ) || define( 'GRAVITYZWR_ZOOMAPIURL', 'https://api.zoom.us/v2' );
defined( 'GRAVITYZWR_AUTHOR_URL' ) || define( 'GRAVITYZWR_AUTHOR_URL', $plugin_data[ 'author_uri' ] );
defined( 'GRAVITYZWR_GUIDE_URL' ) || define( 'GRAVITYZWR_GUIDE_URL', GRAVITYZWR_AUTHOR_URL . 'guide/plugin/' . GRAVITYZWR_TEXTDOMAIN . '/' );
defined( 'GRAVITYZWR_DOCS_URL' ) || define( 'GRAVITYZWR_DOCS_URL', GRAVITYZWR_AUTHOR_URL . 'docs/plugin/' . GRAVITYZWR_TEXTDOMAIN . '/' );
defined( 'GRAVITYZWR_SUPPORT_URL' ) || define( 'GRAVITYZWR_SUPPORT_URL', GRAVITYZWR_AUTHOR_URL . 'support/plugin/' . GRAVITYZWR_TEXTDOMAIN . '/' );
defined( 'GRAVITYZWR_DISCORD_URL' ) || define( 'GRAVITYZWR_DISCORD_URL', $plugin_data[ 'discord_uri' ] );


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
add_filter( 'plugin_row_meta', 'gravityzwr_plugin_row_meta' , 10, 2 );


/**
 * Add links to our website and Discord support
 *
 * @param array $links
 * @return array
 */
function gravityzwr_plugin_row_meta( $links, $file ) {
    $text_domain = GRAVITYZWR_TEXTDOMAIN;
    if ( $text_domain . '/' . $text_domain . '.php' == $file ) {

        $guide_url = GRAVITYZWR_GUIDE_URL;
        $docs_url = GRAVITYZWR_DOCS_URL;
        $support_url = GRAVITYZWR_SUPPORT_URL;
        $plugin_name = GRAVITYZWR_NAME;

        $our_links = [
            'guide' => [
                // translators: Link label for the plugin's user-facing guide.
                'label' => __( 'How-To Guide', 'gravity-zwr' ),
                'url'   => $guide_url
            ],
            'docs' => [
                // translators: Link label for the plugin's developer documentation.
                'label' => __( 'Developer Docs', 'gravity-zwr' ),
                'url'   => $docs_url
            ],
            'support' => [
                // translators: Link label for the plugin's support page.
                'label' => __( 'Support', 'gravity-zwr' ),
                'url'   => $support_url
            ],
        ];

        $row_meta = [];
        foreach ( $our_links as $key => $link ) {
            // translators: %1$s is the link label, %2$s is the plugin name.
            $aria_label = sprintf( __( '%1$s for %2$s', 'gravity-zwr' ), $link[ 'label' ], $plugin_name );
            $row_meta[ $key ] = '<a href="' . esc_url( $link[ 'url' ] ) . '" target="_blank" aria-label="' . esc_attr( $aria_label ) . '">' . esc_html( $link[ 'label' ] ) . '</a>';
        }

        // Require Gravity Forms Notice
        if ( ! is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
            echo '<div class="gravity-forms-required-notice" style="margin: 5px 0 15px; border-left-color: #d63638 !important; background: #FCF9E8; border: 1px solid #c3c4c7; border-left-width: 4px; box-shadow: 0 1px 1px rgba(0, 0, 0, .04); padding: 10px 12px;">';
            /* translators: 1: Plugin name, 2: Gravity Forms link */
            printf( esc_html__( 'This plugin requires the %s plugin to be activated!', 'gravity-zwr' ),
                '<strong><a href="https://www.gravityforms.com/" target="_blank">Gravity Forms</a>'
            );
            echo '</div>';
        }
        
        // Merge the links
        return array_merge( $links, $row_meta );
    }

    // Only apply to this plugin
    if ( 'gravity-forms-zoom-webinar-registration-master/gravity-forms-zoom-webinar-registration.php' == $file ) {

        // Disabled notice
        echo '<div class="gravity-forms-required-notice" style="margin: 5px 0 15px; border-left-color: #d63638 !important; background: #FCF9E8; border: 1px solid #c3c4c7; border-left-width: 4px; box-shadow: 0 1px 1px rgba(0, 0, 0, .04); padding: 10px 12px;">';
        /* translators: %s - Link to Old Plugin */
        printf( esc_html__( 'This plugin has been deactivated and replaced with the %1$s plugin. All of your settings and webinar feeds should transfer over to the new plugin, but it\'s good to double-check your settings and make sure it carried over correctly before deleting %2$s. If you need to activate this plugin again, you must deactivate the other one first.', 'gravity-zwr' ),
            '<strong>' . esc_html( GRAVITYZWR_NAME ) . '</strong>',
            '<a href="https://github.com/michaelbourne/gravity-forms-zoom-webinar-registration" target="_blank">' . esc_html__( 'this one', 'gravity-zwr' ) . '</a>'
        );
        echo '</div>';
    }

    // Return the links
    return (array) $links;
} // End gravityzwr_plugin_row_meta()