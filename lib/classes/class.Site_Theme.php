<?php

/**
 * Contains a theme class to provide core functionality for the theme.
 *
 * PHP Version 5.5+
 *
 * @category Themes
 * @package  Theme_Base
 * @author   Fingerpaint Developers <devs@fingerpaintmarketing.com>
 * @license  Copyright 2014 Fingerpaint. All rights reserved.
 * @link     http://fingerpaintmarketing.com
 */

/* Load dependencies. */
require_once 'class.Theme_Base.php';
require_once 'class.Social_Link_Widget.php';

/**
 * A theme class to provide core functionality for the theme.
 *
 * PHP Version 5.5+
 *
 * @category Themes
 * @package  Theme_Base
 * @author   Fingerpaint Developers <devs@fingerpaintmarketing.com>
 * @license  Copyright 2014 Fingerpaint. All rights reserved.
 * @link     http://fingerpaintmarketing.com
 */
class Site_Theme extends Theme_Base {

	/**
	 * Constructor function. Registers action and filter hooks.
	 *
	 * @access public
	 * @return Site_Theme
	 */
	public function __construct() {

		/* Register action hooks. */
		add_action( 'widgets_init', array( $this, 'action_widgets_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ), 1000 );

		/* Register filter hooks. */
		add_filter( 'gform_confirmation', array( $this, 'filter_gform_confirmation' ), 10, 4 );
		add_filter( 'gform_tabindex', array( $this, 'filter_gform_tabindex' ), 10, 2 );
	}

	/**
	 * A function to return the result of any get function in the instance of this class.
	 *
	 * @param string $func The function part to use when getting the result to return.
	 *
	 * @access public
	 * @return mixed
	 */
	public static function get( $func ) {
		global $theme;

		return call_user_func( array( $theme, 'get_' . $func ) );
	}

	/**
	 * A function to echo the result of any get function in the instance of this class.
	 *
	 * @param string $func THe function part to use when getting the result to echo.
	 *
	 * @access public
	 * @return void
	 */
	public static function the( $func ) {
		echo Site_Theme::get( $func );
	}

	/**
	 * An action hook to register widgets.
	 *
	 * @access public
	 * @return void
	 */
	public function action_widgets_init() {
		register_widget( 'Social_Link_Widget' );
	}

	/**
	 * Action hook to register custom scripts and styles.
	 *
	 * @see    https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/
	 *
	 * @access public
	 * @return void
	 */
	public function action_wp_enqueue_scripts() {

		/* Store the theme path for use in Roots scripts. */
		wp_localize_script( 'roots_js', 'themes_path', substr( get_template_directory(), strpos( get_template_directory(), 'wp-content/' ) ) );

		/* Add the Wistia Embed Shepherd script to help with Google Analytics integration. */
		// IF USING WISTIA, UNCOMMENT THIS SECTION.
		//wp_enqueue_script( 'wistia-embed-shepherd', '//fast.wistia.com/assets/external/embed_shepherd-v1.js' );
	}

	/**
	 * A filter for the confirmation to add custom JavaScript to fire Google Analytics events.
	 *
	 * @param mixed $confirmation The confirmation message or array to be filtered.
	 * @param array $form The current form.
	 * @param array $lead The current entry array.
	 * @param bool $is_ajax Whether the form is configured to be submitted via AJAX or not.
	 *
	 * @access public
	 * @return mixed
	 */
	public function filter_gform_confirmation( $confirmation, $form, $lead, $is_ajax ) {

		/* If the confirmation is not configured to be text, bail out. */
		if ( is_array( $confirmation ) ) {
			return $confirmation;
		}

		/* Set default form values for Google Analytics events. */
		$events = array(
			array(
				'action' => 'Submitted',
				'label'  => $form['title'],
			),
		);

		/* Override or augment default event values in specific cases, if needed. */
		switch ( $form['id'] ) {
		}

		/* Add Google Analytics tracking snippet to confirmation message dynamically. */
		$ga_function_calls = '';
		foreach ( $events as $event ) {
			$ga_function_calls .= <<<JAVASCRIPT
ga('send', 'event', 'Forms', '{$event['action']}', '{$event['label']}');

JAVASCRIPT;
		}

		return $confirmation . <<<HTML
<script type="text/javascript">
	if (typeof ga === 'function') {
		{$ga_function_calls}
	}
</script>
HTML;
	}

	/**
	 * A filter function for the Gravity Forms tabindex.
	 *
	 * @param int $tabindex What Gravity Forms wants to use as the tab index.
	 * @param RGFormsModel $form The form object.
	 *
	 * @return bool False to disable tabindex completely.
	 */
	public function filter_gform_tabindex( $tabindex, $form ) {
		return false;
	}
}
