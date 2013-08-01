<?php
/*
Plugin Name: Simple Analytics
Description: A simple plugin to include your Google Analtyics tracking.
Version: 1.0.0
Author: Theme Blvd
Author URI: http://themeblvd.com
License: GPL2

    Copyright 2013  Theme Blvd

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

define( 'TB_SIMPLE_ANALYTICS_PLUGIN_VERSION', '1.0.0' );
define( 'TB_SIMPLE_ANALYTICS_TWEEPLE_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'TB_SIMPLE_ANALYTICS_PLUGIN_URI', plugins_url( '' , __FILE__ ) );
define( 'TB_SIMPLE_ANALYTICS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Setup plugin.
 */
class Theme_Blvd_Simple_Analytics {

    /**
     * Only instance of object.
     */
    private static $instance = null;

    /**
     * Creates or returns an instance of this class.
     *
     * @since 1.0.0
     *
     * @return  Theme_Blvd_Simple_Analytics A single instance of this class.
     */
    public static function get_instance() {
        if( self::$instance == null ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Initiate plugin.
     *
     * @since 1.0.0
     */
    private function __construct() {

        // Output Analytics
        if ( ! is_admin() && ! current_user_can( 'edit_theme_options' ) ) {

            $analytics = get_option( 'themeblvd_analytics' );

            if ( $analytics && isset( $analytics['placement'] ) ) {

                if ( $analytics['placement'] == 'foot' ) {
                    add_action( 'wp_footer', array( $this, 'output' ), 1000 );
                } else {
                    add_action( 'wp_head', array( $this, 'output' ), 2 );
                }

            }

        }

        // Settings page
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );

    }

    /**
     * Output analytics
     *
     * @since 1.0.0
     */
    public function output() {

        $analytics = get_option( 'themeblvd_analytics' );

        if ( ! empty( $analytics['google_id'] ) ) :
?>
<!-- Simple Analytics by Theme Blvd -->
<script type="text/javascript">

    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '<?php echo esc_attr( $analytics['google_id'] ); ?>']);
    _gaq.push(['_trackPageview']);

    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();

</script>
<?php
        endif; // end if $analytics['google_id'] )

    }

    /**
     * Add settings page
     *
     * @since 1.0.0
     */
    public function admin_menu() {
        add_options_page( __('Analytics', 'themeblvd_sai'), __('Analytics', 'themeblvd_sai'), 'edit_theme_options', 'simple-analytics', array( $this, 'settings_page' ) );
    }

    /**
     * Register settings
     *
     * @since 1.0.0
     */
    public function admin_init() {
        register_setting( 'themeblvd_analytics', 'themeblvd_analytics', array( $this, 'sanitize' ) );
    }

    /**
     * Sanitization callback for saving settings
     *
     * @since 1.0.0
     */
    public function sanitize( $input ) {

        global $allowedposttags;
        $allowed_tags = array_merge( $allowedposttags, array( 'script' => array( 'type' => true, 'src' => true ) ) );

        $output = array();

        foreach ( $input as $key => $value ) {

            switch ( $key ) {

                case 'google_id' :
                    $output[$key] = esc_attr( $value );
                    break;

                case 'placement' :
                    $choices = array( 'head', 'foot' );
                    if ( in_array( $value, $choices ) ) {
                        $output[$key] = $value;
                    } else {
                        $output[$key] = $choices[0];
                    }
                    break;

            }

        }

        return $output;

    }

    /**
     * Display settings page
     *
     * @since 1.0.0
     */
    public function settings_page() {

        // Setup current settings
        $settings = get_option( 'themeblvd_analytics' );

        $code = '';
        if ( isset( $settings['google_id'] ) ) {
            $code = $settings['google_id'];
        }

        $placement = 'head';
        if ( isset( $settings['placement'] ) ) {
            $placement = $settings['placement'];
        }

        ?>
        <div class="wrap">

            <?php settings_errors( 'themeblvd_analytics' ); ?>

            <div id="icon-options-general" class="icon32"><br></div>
            <h2><?php _e('Analytics', 'themeblvd_sai'); ?></h2>

            <form method="POST" action="options.php">

                <?php settings_fields( 'themeblvd_analytics' ); ?>

                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="themeblvd_analytics[code]"><?php _e('Google Tracking ID', 'themeblvd_sai'); ?></label>
                            </th>
                            <td>
                                <input name="themeblvd_analytics[google_id]" type="text" class="regular-text" value="<?php echo $code; ?>" />
                                <p class="description"><?php _e('Input your Google Analytics "Tracking ID"<br />Example: UA-12345678-9', 'themeblvd_sai'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="themeblvd_analytics[placement]"><?php _e('Analytics Placement', 'themeblvd_sai'); ?></label>
                            </th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="radio" name="themeblvd_analytics[placement]" value="head" <?php checked( 'head', $placement ); ?>> <span><?php _e('Include within <code>&lt;head&gt;</code> tag.', 'themeblvd_sai'); ?></span>
                                    </label><br>
                                    <label>
                                        <input type="radio" name="themeblvd_analytics[placement]" value="foot" <?php checked( 'foot', $placement ); ?>> <span><?php _e('Include before closing <code>&lt;/body&gt;</code> tag.', 'themeblvd_sai'); ?></span>
                                    </label><br>
                                </fieldset>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(); ?>

            </form>

        </div><!-- .wrap (end) -->
        <?php

    }
}

/**
 * Initiate plugin.
 *
 * @since 1.0.0
 */
function themeblvd_simple_analytics_init() {
    Theme_Blvd_Simple_Analytics::get_instance();
}
add_action( 'plugins_loaded', 'themeblvd_simple_analytics_init' );