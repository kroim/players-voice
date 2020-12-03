<?php
/**
 * @package WPSEO_News
 */

/**
 * Represents the javascript strings.
 */
class WPSEO_News_Javascript_Strings {

	/** @var null|array */
	private static $strings = null;

	/**
	 * Fills the strings with values.
	 */
	private static function fill() {
		self::$strings = array(
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'choose_image' => __( 'Choose image.', 'wordpress-seo-news' ),
		);
	}

	/**
	 * Returns the array with strings.
	 *
	 * @return array
	 */
	public static function strings() {
		if ( self::$strings === null ) {
			self::fill();
		}

		return self::$strings;
	}
}
