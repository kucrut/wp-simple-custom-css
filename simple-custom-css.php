<?php
/**
 * Plugin Name: Simple Custom CSS
 * Plugin URI: http://johnregan3.github.io/simple-custom-css
 * Description: The simple, solid way to add custom CSS to your WordPress website. Simple Custom CSS allows you to add your own styles or override the default CSS of a plugin or theme.</p>
 * Author: John Regan
 * Author URI: http://johnregan3.me
 * Version: 2.5
 * Text Domain: sccss
 *
 * Copyright 2014  John Regan  (email : john@johnregan3.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package SCCSS
 * @author John Regan
 * @version 2.5
 */


/**
 * Print direct link to Custom CSS admin page
 *
 * Fetches array of links generated by WP Plugin admin page ( Deactivate | Edit )
 * and inserts a link to the Custom CSS admin page
 *
 * @since  1.0
 * @param  array $links Array of links generated by WP in Plugin Admin page.
 * @return array        Array of links to be output on Plugin Admin page.
 */
function sccss_settings_link( $links ) {
	$settings_page = '<a href="' . admin_url( 'themes.php?page=simple-custom-css.php' ) .'">Settings</a>';
	array_unshift( $links, $settings_page );
	return $links;
}

$plugin = plugin_basename( __FILE__ );

add_filter( "plugin_action_links_$plugin", 'sccss_settings_link' );

/**
 * Register text domain
 *
 * @since 1.0
 */
function sccss_textdomain() {
	load_plugin_textdomain( 'sccss' );
}

add_action( 'init', 'sccss_textdomain' );


/**
 * Delete Options on Uninstall
 *
 * @since 1.1
 */
function sccss_uninstall() {
	delete_option( 'sccss_settings' );
}

register_uninstall_hook( __FILE__, 'sccss_uninstall' );



/**
 * Enqueue link to add CSS through PHP
 *
 * This is a typical WP Enqueue statement,
 * except that the URL of the stylesheet is simply a query var.
 * This query var is passed to the URL, and when it is detected by sccss_add_trigger(),
 * It fires sccss_trigger_check, which writes its PHP/CSS to the browser.
 *
 * Credit for this technique: @Otto http://ottopress.com/2010/dont-include-wp-load-please/
 *
 * @since 1.1
 */
function sccss_register_style() {
	wp_register_style( 'sccss_style', home_url( '/?sccss=1' ) );
	wp_enqueue_style( 'sccss_style' );
}

add_action( 'wp_enqueue_scripts', 'sccss_register_style', 99 );

/**
 * Add Query Var Stylesheet trigger
 *
 * Adds a query var to our stylesheet, so it can trigger our psuedo-stylesheet
 *
 * @since 1.1
 * @param string $vars
 * @return array $vars
 */
function sccss_add_trigger( $vars ) {
	$vars[] = 'sccss';
	return $vars;
}

add_filter( 'query_vars','sccss_add_trigger' );

/**
 * If trigger (query var) is tripped, load our pseudo-stylesheet
 *
 * I'd prefer to esc $content at the very last moment, but we need to allow the > character.
 *
 * @since 1.1
 */
function sccss_trigger_check() {
	if ( intval( get_query_var( 'sccss' ) ) == 1 ) {
		ob_start();
			header( 'Content-type: text/css' );
			$options = get_option( 'sccss_settings' );
			if ( isset( $options['sccss-quotes'] ) ) {
				$content = $options['sccss-content'];
			} else {
				$raw_content = isset( $options['sccss-content'] ) ? $options['sccss-content'] : '';
				$esc_content = esc_html( $raw_content );
				$content     = str_replace( '&gt;', '>', $esc_content );
			}
			if ( isset( $options['sccss-credit'] ) ) {
echo "/*
 * Created by the Simple Custom CSS Plugin
 * http://wordpress.org/plugins/simple-custom-css/
 */\n\n";
				}
				echo $content;
			exit;
		ob_clean();
	}
}

add_action( 'template_redirect', 'sccss_trigger_check' );



/**
 * Register "Custom CSS" submenu in "Appearance" Admin Menu
 *
 * @since 1.0
 */
function sccss_register_submenu_page() {
	add_theme_page( __( 'Simple Custom CSS', 'sccss' ), __( 'Custom CSS', 'sccss' ), 'manage_options', basename( __FILE__ ), 'sccss_render_submenu_page' );
}

add_action( 'admin_menu', 'sccss_register_submenu_page' );


/**
 * Register settings
 *
 * @since 1.0
 */
function sccss_register_settings() {
	register_setting( 'sccss_settings_group', 'sccss_settings' );
}

add_action( 'admin_init', 'sccss_register_settings' );


/**
 * Render Admin Menu page
 *
 * @since 1.0
 */
function sccss_render_submenu_page() {

	$options = get_option( 'sccss_settings' );
	$quotes  = isset( $options['sccss-quotes'] ) ? $options['sccss-quotes'] : '';
	$credit  = isset( $options['sccss-credit'] ) ? 1 : 0 ;
	$content = isset( $options['sccss-content'] ) ? $options['sccss-content'] : '';

	if ( isset( $_GET['settings-updated'] ) ) : ?>
		<div id="message" class="updated"><p><?php _e( 'Custom CSS updated successfully.' ); ?></p></div>
	<?php endif; ?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e( 'Simple Custom CSS', 'sccss' ); ?></h2>
		<p><?php _e( 'Simple Custom CSS allows you to add your own styles or override the default CSS of a plugin or theme.', 'sccss' ) ?></p>
		<form name="sccss-form" action="options.php" method="post" enctype="multipart/form-data">
			<?php settings_fields( 'sccss_settings_group' ); ?>
			<div id="templateside">
				<?php do_action( 'sccss-sidebar-top' ); ?>
				<h3><?php _e( 'Instructions', 'sccss' ) ?></h3>
				<ol>
					<li><?php _e( 'Enter your custom CSS in the the texarea to the right.', 'sccss' ) ?></li>
					<li><?php _e( 'Click "Update Custom CSS."', 'sccss' ) ?></li>
					<li><?php _e( 'Enjoy your new CSS styles!', 'sccss' ) ?></li>
				</ol>
				<p>&nbsp;</p>
				<h3>Allow Double Quotes</h3>
				<p>
					<input type="checkbox" name="sccss_settings[sccss-quotes]" value="1" <?php checked( 1, $quotes ); ?> />&nbsp;&nbsp;<?php _e( 'Allow Double Quotes', 'sccss' ) ?><br />
					<span class="description"><?php _e( 'Some CSS selectors use quotation marks (").  In order to allow these, it requres a small adjustment to the way the CSS is output. It is recommended that you only enable this if it is necessary.', 'sccss' ); ?></span>
				</p>
				<p>&nbsp;</p>
				<h3><?php _e( 'Help', 'sccss' ) ?></h3>
				<p><a href="<?php echo esc_url( 'https://github.com/johnregan3/simple-custom-css/wiki' ); ?>" ><?php _e( 'Simple Custom CSS Wiki', 'sccss' ); ?></a></p>
				<p>&nbsp;</p>
				<h3>Attribution</h3>
				<p>
					<input type="checkbox" name="sccss_settings[sccss-credit]" value="1" <?php checked( 1, $credit ); ?> />&nbsp;&nbsp;<?php _e( 'This Plugin is Really Helpful!', 'sccss' ) ?><br />
					<span class="description"><?php _e( 'Print credit to the author within the CSS file. No text will appear on your website.', 'sccss' ); ?></span>
				</p>
				<?php do_action( 'sccss-sidebar-bottom' ); ?>
			</div>
			<div id="template">
				<?php do_action( 'sccss-form-top' ); ?>
				<div>
					<textarea cols="70" rows="30" name="sccss_settings[sccss-content]" id="sccss_settings[sccss-content]" ><?php echo esc_html( $content ); ?></textarea>
				</div>
				<?php do_action( 'sccss-textarea-bottom' ); ?>
				<div>
					<?php submit_button( __( 'Update Custom CSS', 'sccss' ), 'primary', 'submit', true ); ?>
				</div>
				<?php do_action( 'sccss-form-bottom' ); ?>
			</div>
		</form>
	</div>
	<?php
}
