<?php
/**
 * Plugin Name: Notice
 * Plugin URI: https://jmr.codes/
 * Description: A site-wide notification bar plugin with scheduling.
 * Version: 0.1.1
 * Author: James Robinson
 * Author URI: https://jmr.codes/
 * License: GPL2
 */

/*  Copyright 2017 James Robinson (email : support@jmr.codes)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !defined( 'ABSPATH' ) ) exit;

add_action('admin_init', 'jmr_notice_init' );
add_action('admin_menu', 'jmr_notice_add_page');
add_action('wp_footer', 'jmr_notice_add', 999);
add_action('wp_head', 'jmr_notice_add_styles', 999);

// Init plugin options
function jmr_notice_init(){
	register_setting( 'jmr_notice_options', 'jmr_notice', 'jmr_notice_validate' );
}

// Add menu page
function jmr_notice_add_page() {
	add_options_page('Site Notice', 'Site Notice', 'manage_options', 'jmr_notice', 'jmr_notice_do_page');
}

function jmr_notice_enabled() {

	$config = get_option('jmr_notice');

	// Check whether the notice is enabled
	$enable = ( isset( $config['enable'] ) && '1' === $config['enable'] ) ? true : false;

	if ( false === $enable ) {
		return false;
	}

	// Check whether the we are on schedule
	$wptimezone = get_option('timezone_string');
	if ( $wptimezone && false === empty( $wptimezone ) ) {
		date_default_timezone_set($wptimezone);
	}

	$start_date = false;
	$end_date = false;

	if ( isset( $config['start_date'] ) && false === empty( $config['start_date'] ) ) {
		$start_date = strtotime( $config['start_date'] );
	}

	if ( isset( $config['end_date'] ) && false === empty( $config['end_date'] ) ) {
		$end_date = strtotime( $config['end_date'] );
	}

	if ( $start_date && time() < $start_date ) {
		return false;
	}

	if ( $end_date && time() > $end_date ) {
		return false;
	}

	return true;

}

// Add custom styles
function jmr_notice_add_styles() {

	$enabled = jmr_notice_enabled();

	if ( $enabled ) {

		$config = get_option('jmr_notice');

		if ( isset( $config['css'] ) && false === empty( $config['css'] ) ) {
			$css = '<style>';
			$css .=  $config['css'];
			$css .= '</style>';
		} else {
			$css = '<style>.notice{background-color:#c00;color:#fff;display:none;font-size:1.25rem;padding:2em 2vw;position:relative;text-align:center;width:100%;z-index:100}.notice .notice-content{display:block;margin:0 auto}.notice h1{font-size:1.2rem;font-weight:700;margin:0 0 0.25em}.notice p{font-size:1rem;margin:0}.notice a{color:#fff;text-decoration:underline}.notice a:hover{color:#00c}
</style>';
		}

		echo $css;
	}

}

// Add notice
function jmr_notice_add() {

	$enabled = jmr_notice_enabled();

	if ( $enabled ) {

		$config = get_option('jmr_notice');

		$heading = false;
		$message = false;
		$link = false;
		$link_label = __('Read more', 'jmr_notice');

		if ( isset( $config['heading'] ) && false === empty( $config['heading'] ) ) {
			$heading = $config['heading'];
		}

		if ( isset( $config['message'] ) && false === empty( $config['message'] ) ) {
			$message = $config['message'];
		}

		if ( isset( $config['link'] ) && false === empty( $config['link'] ) ) {
			$link = $config['link'];
		}

		if ( isset( $config['link'] ) && false === empty( $config['link'] ) ) {
			$link_label = $config['link'];
		}

		if ( $heading || $message ) :

			$html = '<section id="jmrNotice" class="notice"><div class="notice-content">';

			if ( $heading ) {
				$html .= '<h1>' . $heading . '</h1>';
			}

			if ( $message || $link ) {
				$html .= '<p class="notice-message">';

				if ( $message ) {
					$html .= $config['message'];
				}

				if ( $link ) {
					$html .= ' <a href="' . $link . '">' . $link_label . '</a>';
				}

				$html .= '</p>';
			}

			$html .= '</div></section>';

			$html .= '<script>window.addEventListener("load",function e(){var d=document.getElementById("jmrNotice");if(void 0!==d){var t=document.body.firstChild;t.parentNode.insertBefore(d,t),d.style.display="block"}},!1);</script>';

			echo $html;

		endif;
	}
}

// Draw the menu page
function jmr_notice_do_page() {
?>
	<div class="wrap">

		<h2><?php esc_html_e('Site Notice', 'jmr_notice'); ?></h2>

		<form method="post" action="options.php">
<?php
		settings_fields('jmr_notice_options');
		$options = get_option('jmr_notice');
?>

			<p><?php esc_html_e('This allows you to create a notification that will appear on all pages of your site.', 'jmr_notice'); ?></p>
			<hr>

			<h3 class="title"><?php esc_html_e('Enable', 'jmr_notice'); ?></h3>
			<p><?php esc_html_e('Enable or disable the notification. If this is unchecked the notice will not appear even if scheduled.', 'jmr_notice'); ?></p>
			<table class="form-table">
				<tr>
					<th scope="row"></th>
					<td>
						<label><input type="checkbox" name="jmr_notice[enable]" value="1" <?php if ( isset( $options['enable'] ) ) checked( $options['enable'], '1' ); ?>><?php esc_html_e('Show site notice', 'jmr_notice'); ?></label>
						<p class="description"><?php esc_html_e('The notice may not appear if you have set a schedule.', 'jmr_notice'); ?></p>
					</td>
				</tr>
			</table>
			<hr>

			<h3 class="title"><?php esc_html_e('Schedule', 'jmr_notice'); ?></h3>
			<p><?php esc_html_e('Show the site notice from a specific date or during a date range. The date will be based on the WordPress timezone setting, if available, or the server timezone setting.', 'jmr_notice'); ?></p>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="jmr_notice[start_date]"><?php esc_html_e('Start', 'jmr_notice'); ?></label></th>
					<td>
						<input type="datetime-local" name="jmr_notice[start_date]" value="<?php if ( isset( $options['start_date'] ) ) echo $options['start_date']; ?>">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="jmr_notice[end_date]"><?php esc_html_e('End', 'jmr_notice'); ?></label></th>
					<td>
						<input type="datetime-local" name="jmr_notice[end_date]" value="<?php if ( isset( $options['end_date'] ) ) echo $options['end_date']; ?>">
					</td>
				</tr>
			</table>
			<hr>

			<h3 class="title"><?php esc_html_e('Notice Details', 'jmr_notice'); ?></h3>
			<p><?php esc_html_e('A heading or message is required. Other fields are optional.', 'jmr_notice'); ?></p>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="jmr_notice[heading]"><?php esc_html_e('Heading', 'jmr_notice'); ?></label></th>
					<td>
						<input type="text" class="regular-text" name="jmr_notice[heading]" value="<?php if ( isset( $options['heading'] ) ) echo $options['heading']; ?>">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="jmr_notice[message]"><?php esc_html_e('Message', 'jmr_notice'); ?></label></th>
					<td>
						<textarea rows="4" cols="46" class="regular-text" name="jmr_notice[message]"><?php if ( isset( $options['message'] ) ) echo $options['message']; ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="jmr_notice[link]"><?php esc_html_e('Link', 'jmr_notice'); ?></label></th>
					<td>
						<input type="url" class="regular-text code" name="jmr_notice[link]" value="<?php if ( isset( $options['link'] ) ) echo $options['link']; ?>" placeholder="http://example.com/">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="jmr_notice[link_label]"><?php esc_html_e('Link Label', 'jmr_notice'); ?></label></th>
					<td>
						<input type="text" class="regular-text" name="jmr_notice[link_label]" value="<?php if ( isset( $options['link_label'] ) ) echo $options['link_label']; ?>">
					</td>
				</tr>
			</table>
			<hr>

			<h3 class="title"><?php esc_html_e('Advanced', 'jmr_notice'); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="jmr_notice[heading]"><?php esc_html_e('Custom CSS', 'jmr_notice'); ?></label></th>
					<td>
						<textarea class="code" name="jmr_notice[css]" cols="80" rows="10"><?php if ( isset( $options['css'] ) ) echo $options['css']; ?></textarea>
					</td>
				</tr>
			</table>

			<p class="submit"><input type="submit" class="button-primary" value="<?php esc_html_e('Save Changes') ?>"></p>

		</form>

	</div>

<?php
}

// Sanitize and validate input
function jmr_notice_validate($input) {

	$input['enable'] = ( isset( $input['enable'] ) ) ? filter_var( $input['enable'], FILTER_SANITIZE_NUMBER_INT ) : false;

	// Notice Details
	$input['heading'] = sanitize_text_field( $input['heading'] );
	$input['message'] = sanitize_text_field( $input['message'] );
	$input['link'] = esc_url_raw( $input['link'], array('http', 'https') );
	$input['link_label'] = sanitize_text_field( $input['link_label'] );

	// Custom CSS
	$input['css'] = ( isset( $input['css'] ) && false === empty( $input['css'] ) ) ? wp_kses( $input['css'], array() ) : null;

	return $input;

}
