<?php

/**
 * Contains a base class to provide reusable functionality for theme objects.
 *
 * PHP Version 5.5+
 *
 * @category Themes
 * @package  Theme_Base
 * @author   Fingerpaint Developers <devs@fingerpaintmarketing.com>
 * @license  GPLv3
 * @link     http://fingerpaintmarketing.com
 * @version  1.1.0
 */

/**
 * A base class to provide reusable functionality for theme objects.
 *
 * @category Themes
 * @package  Theme_Base
 * @author   Fingerpaint Developers <devs@fingerpaintmarketing.com>
 * @license  GPLv3
 * @link     http://fingerpaintmarketing.com
 */
class Theme_Base {

	/**
	 * A variable to store cached results of computations for this session.
	 *
	 * @access protected
	 * @var array
	 */
	protected $cache = array();

	/**
	 * A function to get a generic select field's options.
	 *
	 * @param string $field_id The field ID to look up.
	 *
	 * @access protected
	 * @return array
	 */
	protected function get_acf_select_field( $field_id ) {
		if ( empty( $this->cache['acf_fields'][ $field_id ] ) ) {
			$field                                  = get_field_object( $field_id );
			$this->cache['acf_fields'][ $field_id ] = ( isset( $field['choices'] ) ) ? $field['choices'] : array();
		}

		return $this->cache['acf_fields'][ $field_id ];
	}

	/**
	 * A function to get an option element, including selecting the active option.
	 *
	 * @param string $value   The value to use in the option element.
	 * @param string $text    The display text to use.
	 * @param string $current The comparision value to use to determine selected.
	 *
	 * @access public
	 * @return string The HTML for the option element.
	 */
	public function get_option( $value, $text, $current ) {
		$selected = ( $value == $current ) ? ' selected="selected"' : '';

		return <<<HTML
<option value="{$value}"{$selected}>{$text}</option>
HTML;
	}

	/**
	 * A function to get unified user data based on a meta query.
	 *
	 * @param array  $fields  The fields to get.
	 * @param string $key     The meta key to look up.
	 * @param string $compare The comparison operator to use.
	 * @param string $value   The value to compare against.
	 * @param string $orderby The field to order by.
	 * @param string $order   The order to use (ASC | DESC)
	 *
	 * @access protected
	 * @return array
	 */
	protected function get_userdata( $fields, $key, $compare, $value, $orderby = 'ID', $order = 'ASC' ) {
		global $wpdb;

		/* Filter for comparison operator. */
		if ( ! in_array( $compare, array( '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE' ) ) ) {
			return false;
		}

		/* Set up user fields filter for elements to exclude from meta query. */
		$user_fields = array(
			'ID',
			'user_login',
			'user_nicename',
			'user_email',
			'user_url',
			'user_registered',
			'user_status',
			'display_name'
		);

		/* If key column is not included in list, add. */
		if ( ! in_array( $key, $fields ) ) {
			$fields[] = $key;
		}

		/* Handle order by RAND. */
		if ( $orderby === 'RAND' ) {
			$order_by = 'RAND()';
		} else {

			/* If order by column is not included in list, add. */
			if ( ! in_array( $orderby, $fields ) ) {
				$fields[] = $orderby;
			}

			/* Construct order by clause. */
			$order_by = esc_sql( $orderby ) . ' ' . esc_sql( $order );
		}

		/* Construct query to get user information. */
		$select = 'SELECT DISTINCT(ID) AS ID';
		$from   = ' FROM ' . $wpdb->users . ' ';
		$where  = ' WHERE ' . esc_sql( $key ) . ' ' . $compare . ' \'' . esc_sql( $value ) . '\''
		          . ' ORDER BY ' . $order_by;
		foreach ( $fields as $field ) {

			/* Sanitize, just in case. */
			$field = esc_sql( $field );

			/* Add the element to the SELECT statement. */
			$select .= ', ' . $field;

			/* Add the JOIN for the meta query. */
			if ( ! in_array( $field, $user_fields ) ) {
				$from .= <<<SQL
INNER JOIN (
	SELECT user_id,
		meta_value AS {$field}
	FROM {$wpdb->usermeta}
	WHERE meta_key = '{$field}'
) AS meta_{$field} ON {$wpdb->users}.ID = meta_{$field}.user_id

SQL;
			}
		}

		/* Run query and compile results. */
		$users   = array();
		$results = $wpdb->get_results( $select . $from . $where );
		foreach ( $results as $result ) {
			$users[] = (array) $result;
		}

		return $users;
	}

	/**
	 * A function to override the automatic display of Jetpack sharing links.
	 *
	 * @access public
	 * @return void
	 */
	protected function jetpack_sharing_links_override() {
		add_filter( 'the_content', array( $this, 'filter_the_content' ), 10, 1 );
		add_filter( 'the_excerpt', array( $this, 'filter_the_excerpt' ), 10, 1 );
	}

	/**
	 * Filter function to remove JetPack automatic sharing links from the content.
	 *
	 * @param string $content The content to filter.
	 *
	 * @access public
	 * @return string The modified content.
	 */
	public function filter_the_content( $content ) {
		remove_filter( 'the_content', 'sharing_display', 19 );

		return $content;
	}

	/**
	 * Filter function to remove JetPack automatic sharing links from the excerpt.
	 *
	 * @param string $excerpt The excerpt to filter.
	 *
	 * @access public
	 * @return string The modified excerpt.
	 */
	public function filter_the_excerpt( $excerpt ) {
		remove_filter( 'the_excerpt', 'sharing_display', 19 );

		return $excerpt;
	}

	/**
	 * A function to return the appropriate FontAwesome class based on a file path.
	 *
	 * @param string $filepath The filepath to extract the extension from.
	 *
	 * @return string The FontAwesome class to use.
	 */
	public function get_fa_file_icon( $filepath ) {
		switch ( strtolower( pathinfo( $filepath, PATHINFO_EXTENSION ) ) ) {
			case 'csv':
			case 'xlr':
			case 'xls':
			case 'xlsx':
				return 'fa-file-excel-o';
			case 'pdf':
				return 'fa-file-pdf-o';
			case 'doc':
			case 'docx':
			case 'odt':
			case 'pages':
			case 'rtf':
			case 'wpd':
			case 'wps':
				return 'fa-file-word-o';
			case '7z':
			case '7zip':
			case 'cbr':
			case 'deb':
			case 'gz':
			case 'pkg':
			case 'rar':
			case 'rpm':
			case 'sitx':
			case 'tar':
			case 'zip':
			case 'zipx':
				return 'fa-file-archive-o';
			case '3dm':
			case '3ds':
			case 'ai':
			case 'bmp':
			case 'dds':
			case 'eps':
			case 'gif':
			case 'indd':
			case 'jpeg':
			case 'jpg':
			case 'max':
			case 'obj':
			case 'pct':
			case 'png':
			case 'ps':
			case 'psd':
			case 'svg':
			case 'tga':
			case 'thm':
			case 'tif':
			case 'tiff':
			case 'yuv':
				return 'fa-file-image-o';
			case 'aac':
			case 'aif':
			case 'iff':
			case 'm3u':
			case 'm4a':
			case 'mid':
			case 'mp3':
			case 'mpa':
			case 'ra':
			case 'wav':
			case 'wma':
				return 'fa-file-audio-o';
			case 'txt':
				return 'fa-file-text-o';
			case 'key':
			case 'odp':
			case 'pps':
			case 'ppt':
			case 'pptx':
				return 'fa-file-powerpoint-o';
			case '3g2':
			case '3gp':
			case 'asf':
			case 'asx':
			case 'avi':
			case 'flv':
			case 'm4v':
			case 'mov':
			case 'mp4':
			case 'mpg':
			case 'srt':
			case 'swf':
			case 'vob':
			case 'wmv':
				return 'fa-file-video-o';
			default:
				return 'fa-file-o';
		}
	}

	/**
	 * A function to print an option element, including selecting the active option.
	 *
	 * @param string $value   The value to use in the option element.
	 * @param string $text    The display text to use.
	 * @param string $current The comparision value to use to determine selected.
	 *
	 * @access public
	 * @return void
	 */
	public function print_option( $value, $text, $current ) {
		echo $this->get_option( $value, $text, $current );
	}

	/**
	 * A function to return either an array of segments or a specific segment.
	 *
	 * @param int $num The segment number to look up.
	 *
	 * @access public
	 * @return mixed  Segment value if $num provided, false if not found, array if not specified.
	 */
	public function segments( $num = '' ) {
		/* Make sure that the segments are computed and cached. */
		if ( ! isset( $this->cache['segments'] ) ) {
			$this->cache['segments'] = array_filter( explode( '/', strtok( $_SERVER['REQUEST_URI'], '?' ) ) );
			if ( ! is_array( $this->cache['segments'] ) ) {
				$this->cache['segments'] = array();
			}
		}

		/* Determine if we are looking for a specific number or not. */
		if ( empty( $num ) ) {
			return $this->cache['segments'];
		} elseif ( array_key_exists( $num, $this->cache['segments'] ) ) {
			return $this->cache['segments'][ $num ];
		} else {
			return false;
		}
	}

	/**
	 * A function to determine whether the ajax flag is set or not.
	 *
	 * @access public
	 * @return bool
	 */
	public function use_wrapper() {
		return ( ! isset( $_GET['ajax'] ) || $_GET['ajax'] !== 'true' );
	}
}
