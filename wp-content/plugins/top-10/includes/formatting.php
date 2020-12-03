<?php
/**
 * Language functions
 *
 * @package Top_Ten
 */

/**
 * Function to create an excerpt for the post.
 *
 * @since   1.6
 * @param   int        $id             Post ID.
 * @param   int|string $excerpt_length Length of the excerpt in words.
 * @param   bool       $use_excerpt Use Excerpt.
 * @return  string     Excerpt
 */
function tptn_excerpt( $id, $excerpt_length = 0, $use_excerpt = true ) {
	$content = '';

	if ( $use_excerpt ) {
		$content = get_post( $id )->post_excerpt;
	}

	if ( '' === $content ) {
		$content = get_post( $id )->post_content;
	}

	$output = strip_tags( strip_shortcodes( $content ) );

	if ( $excerpt_length > 0 ) {
		$output = wp_trim_words( $output, $excerpt_length );
	}

	/**
	 * Filters excerpt generated by tptn.
	 *
	 * @since   1.9.10.1
	 *
	 * @param   array   $output         Formatted excerpt
	 * @param   int     $id             Post ID
	 * @param   int     $excerpt_length Length of the excerpt
	 * @param   boolean $use_excerpt    Use the excerpt?
	 */
	return apply_filters( 'tptn_excerpt', $output, $id, $excerpt_length, $use_excerpt );
}


/**
 * Truncate a string to a certain length.
 *
 * @since 2.5.4
 *
 * @param  string $string String to truncate.
 * @param  int    $count Maximum number of characters to take.
 * @param  string $more What to append if $string needs to be trimmed.
 * @param  bool   $break_words Optionally choose to break words.
 * @return string Truncated string.
 */
function tptn_trim_char( $string, $count = 60, $more = '&hellip;', $break_words = false ) {
	$string = wp_strip_all_tags( $string, true );
	if ( 0 === $count ) {
		return '';
	}
	if ( mb_strlen( $string ) > $count && $count > 0 ) {
		$count -= min( $count, mb_strlen( $more ) );
		if ( ! $break_words ) {
			$string = preg_replace( '/\s+?(\S+)?$/u', '', mb_substr( $string, 0, $count + 1 ) );
		}
		$string = mb_substr( $string, 0, $count ) . $more;
	}
	/**
	 * Filters truncated string.
	 *
	 * @since 2.4.0
	 *
	 * @param string $string String to truncate.
	 * @param int $count Maximum number of characters to take.
	 * @param string $more What to append if $string needs to be trimmed.
	 * @param bool $break_words Optionally choose to break words.
	 */
	return apply_filters( 'tptn_trim_char', $string, $count, $more, $break_words );
}
