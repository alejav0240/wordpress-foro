<?php
/**
 * CMB2 field sanitization
 *
 * @since  0.0.4
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @method string _id()
 */
class CMB2_Sanitize {

	/**
	 * A CMB field object
	 *
	 * @var CMB2_Field object
	 */
	public $field;

	/**
	 * Field's value
	 *
	 * @var mixed
	 */
	public $value;

	/**
	 * Setup our class vars
	 *
	 * @since 1.1.0
	 * @param CMB2_Field $field A CMB2 field object.
	 * @param mixed      $value Field value.
	 */
	public function __construct( CMB2_Field $field, $value ) {
		$this->field = $field;
		$this->value = $value;
	}

	/**
	 * Catchall method if field's 'sanitization_cb' is NOT defined,
	 * or field type does not have a corresponding validation method.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $name      Non-existent method name.
	 * @param  array  $arguments All arguments passed to the method.
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		return $this->default_sanitization();
	}

	/**
	 * Default fallback sanitization method. Applies filters.
	 *
	 * @since  1.0.2
	 */
	public function default_sanitization() {
		$field_type = $this->field->type();

		/**
		 * This exists for back-compatibility, but validation
		 * is not what happens here.
		 *
		 * @deprecated See documentation for "cmb2_sanitize_{$field_type}".
		 */
		if ( function_exists( 'apply_filters_deprecated' ) ) {
			$override_value = apply_filters_deprecated( "cmb2_validate_{$field_type}", array( null, $this->value, $this->field->object_id, $this->field->args(), $this ), '2.0.0', "cmb2_sanitize_{$field_type}" );
		} else {
			$override_value = apply_filters( "cmb2_validate_{$field_type}", null, $this->value, $this->field->object_id, $this->field->args(), $this );
		}

		if ( null !== $override_value ) {
			return $override_value;
		}

		$sanitized_value = '';
		switch ( $field_type ) {
			case 'wysiwyg':
			case 'textarea_small':
			case 'oembed':
				$sanitized_value = $this->textarea();
				break;
			case 'taxonomy_select':
			case 'taxonomy_select_hierarchical':
			case 'taxonomy_radio':
			case 'taxonomy_radio_inline':
			case 'taxonomy_radio_hierarchical':
			case 'taxonomy_multicheck':
			case 'taxonomy_multicheck_hierarchical':
			case 'taxonomy_multicheck_inline':
				$sanitized_value = $this->taxonomy();
				break;
			case 'multicheck':
			case 'multicheck_inline':
			case 'file_list':
			case 'group':
				// no filtering
				$sanitized_value = $this->value;
				break;
			default:
				// Handle repeatable fields array
				// We'll fallback to 'sanitize_text_field'
				$sanitized_value = $this->_default_sanitization();
				break;
		}

		return $this->_is_empty_array( $sanitized_value ) ? '' : $sanitized_value;
	}

	/**
	 * Default sanitization method, sanitize_text_field. Checks if value is array.
	 *
	 * @since  2.2.4
	 * @return mixed  Sanitized value.
	 */
	protected function _default_sanitization() {
		// Handle repeatable fields array.
		return is_array( $this->value ) ? array_map( 'sanitize_text_field', $this->value ) : sanitize_text_field( $this->value );
	}

	/**
	 * Sets the object terms to the object (if not options-page) and optionally returns the sanitized term values.
	 *
	 * @since  2.2.4
	 * @return mixed  Blank value, or sanitized term values if "cmb2_return_taxonomy_values_{$cmb_id}" is true.
	 */
	public function taxonomy() {
		$sanitized_value = '';

		if ( ! $this->field->args( 'taxonomy' ) ) {
			CMB2_Utils::log_if_debug( __METHOD__, __LINE__, "{$this->field->type()} {$this->field->_id( '', false )} is missing the 'taxonomy' parameter." );
		} else {

			if ( in_array( $this->field->object_type, array( 'options-page', 'term' ), true ) ) {
				$return_values = true;
			} else {
				wp_set_object_terms( $this->field->object_id, $this->value, $this->field->args( 'taxonomy' ) );
				$return_values = false;
			}

			$cmb_id = $this->field->cmb_id;

			/**
			 * Filter whether 'taxonomy_*' fields should return their value when being sanitized.
			 *
			 * By default, these fields do not return a value as we do not want them stored to meta
			 * (as they are stored as terms). This allows overriding that and is used by CMB2::get_sanitized_values().
			 *
			 * The dynamic portion of the hook, $cmb_id, refers to the this field's CMB2 box id.
			 *
			 * @since 2.2.4
			 *
			 * @param bool          $return_values By default, this is only true for 'options-page' boxes. To enable:
			 *                                     `add_filter( "cmb2_return_taxonomy_values_{$cmb_id}", '__return_true' );`
			 * @param CMB2_Sanitize $sanitizer This object.
			 */
			if ( apply_filters( "cmb2_return_taxonomy_values_{$cmb_id}", $return_values, $this ) ) {
				$sanitized_value = $this->_default_sanitization();
			}
		}

		return $sanitized_value;
	}

	/**
	 * Simple checkbox validation
	 *
	 * @since  1.0.1
	 * @return string|false 'on' or false
	 */
	public function checkbox() {
		return $this->value === 'on' ? 'on' : false;
	}

	/**
	 * Validate url in a meta value.
	 *
	 * @since  1.0.1
	 * @return string        Empty string or escaped url
	 */
	public function text_url() {
		$protocols = $this->field->args( 'protocols' );
		$default   = $this->field->get_default();

		// for repeatable.
		if ( is_array( $this->value ) ) {
			foreach ( $this->value as $key => $val ) {
				$this->value[ $key ] = self::sanitize_and_secure_url( $val, $protocols, $default );
			}
		} else {
			$this->value = self::sanitize_and_secure_url( $this->value, $protocols, $default );
		}

		return $this->value;
	}

	public function colorpicker() {
		// for repeatable.
		if ( is_array( $this->value ) ) {
			$check = $this->value;
			$this->value = array();
			foreach ( $check as $key => $val ) {
				if ( $val && '#' != $val ) {
					$this->value[ $key ] = esc_attr( $val );
				}
			}
		} else {
			$this->value = ! $this->value || '#' == $this->value ? '' : esc_attr( $this->value );
		}
		return $this->value;
	}

	/**
	 * Validate email in a meta value
	 *
	 * @since  1.0.1
	 * @return string       Empty string or sanitized email
	 */
	public function text_email() {
		// for repeatable.
		if ( is_array( $this->value ) ) {
			foreach ( $this->value as $key => $val ) {
				$val = trim( $val );
				$this->value[ $key ] = is_email( $val ) ? $val : '';
			}
		} else {
			$this->value = trim( $this->value );
			$this->value = is_email( $this->value ) ? $this->value : '';
		}

		return $this->value;
	}

	/**
	 * Validate money in a meta value
	 *
	 * @since  1.0.1
	 * @return string Empty string or sanitized money value
	 */
	public function text_money() {
		if ( ! $this->value ) {
			return '';
		}

		global $wp_locale;

		$search = array( $wp_locale->number_format['thousands_sep'], $wp_locale->number_format['decimal_point'] );
		$replace = array( '', '.' );

		// Strip slashes. Example: 2\'180.00.
		// See https://github.com/CMB2/CMB2/issues/1014.
		$this->value = wp_unslash( $this->value );

		// for repeatable.
		if ( is_array( $this->value ) ) {
			foreach ( $this->value as $key => $val ) {
				if ( $val ) {
					$this->value[ $key ] = number_format_i18n( (float) str_ireplace( $search, $replace, $val ), 2 );
				}
			}
		} else {
			$this->value = number_format_i18n( (float) str_ireplace( $search, $replace, $this->value ), 2 );
		}

		return $this->value;
	}

	/**
	 * Converts text date to timestamp
	 *
	 * @since  1.0.2
	 * @return string Timestring
	 */
	public function text_date_timestamp() {
		// date_create_from_format if there is a slash in the value.
		$this->value = wp_unslash( $this->value );

		return is_array( $this->value )
			? array_map( array( $this->field, 'get_timestamp_from_value' ), $this->value )
			: $this->field->get_timestamp_from_value( $this->value );
	}

	/**
	 * Datetime to timestamp
	 *
	 * @since  1.0.1
	 *
	 * @param bool $repeat Whether or not to repeat.
	 * @return string|array Timestring
	 */
	public function text_datetime_timestamp( $repeat = false ) {
		// date_create_from_format if there is a slash in the value.
		$this->value = wp_unslash( $this->value );

		if ( $this->is_empty_value() ) {
			return '';
		}

		$repeat_value = $this->_check_repeat( __FUNCTION__, $repeat );
		if ( false !== $repeat_value ) {
			return $repeat_value;
		}

		// Account for timestamp values passed through REST API.
		if ( $this->is_valid_date_value() ) {

			$this->value = CMB2_Utils::make_valid_time_stamp( $this->value );

		} elseif ( isset( $this->value['date'], $this->value['time'] ) ) {
			$this->value = $this->field->get_timestamp_from_value( $this->value['date'] . ' ' . $this->value['time'] );
		}

		if ( $tz_offset = $this->field->field_timezone_offset() ) {
			$this->value += (int) $tz_offset;
		}

		return $this->value;
	}

	/**
	 * Datetime to timestamp with timezone
	 *
	 * @since  1.0.1
	 *
	 * @param bool $repeat Whether or not to repeat.
	 * @return string       Timestring
	 */
	public function text_datetime_timestamp_timezone( $repeat = false ) {
		static $utc_values = array();

		if ( $this->is_empty_value() ) {
			return '';
		}

		// date_create_from_format if there is a slash in the value.
		$this->value = wp_unslash( $this->value );

		$utc_key = $this->field->_id( '', false ) . '_utc';

		$repeat_value = $this->_check_repeat( __FUNCTION__, $repeat );
		if ( false !== $repeat_value ) {
			if ( ! empty( $utc_values[ $utc_key ] ) ) {
				$this->_save_utc_value( $utc_key, $utc_values[ $utc_key ] );
				unset( $utc_values[ $utc_key ] );
			}

			return $repeat_value;
		}

		$tzstring = null;

		if ( is_array( $this->value ) && array_key_exists( 'timezone', $this->value ) ) {
			$tzstring = $this->value['timezone'];
		}

		if ( empty( $tzstring ) ) {
			$tzstring = CMB2_Utils::timezone_string();
		}

		$offset = CMB2_Utils::timezone_offset( $tzstring );

		if ( 'UTC' === substr( $tzstring, 0, 3 ) ) {
			$tzstring = timezone_name_from_abbr( '', $offset, 0 );
			/**
			 * The timezone_name_from_abbr() returns false if not found based on offset.
			 * Since there are currently some invalid timezones in wp_timezone_dropdown(),
			 * fallback to an offset of 0 (UTC+0)
			 * https://core.trac.wordpress.org/ticket/29205
			 */
			$tzstring = false !== $tzstring ? $tzstring : timezone_name_from_abbr( '', 0, 0 );
		}

		$full_format = $this->field->args['date_format'] . ' ' . $this->field->args['time_format'];

		try {
			$datetime = null;

			if ( is_array( $this->value ) ) {

				$full_date = $this->value['date'] . ' ' . $this->value['time'];
				$datetime = date_create_from_format( $full_format, $full_date );

			} elseif ( $this->is_valid_date_value() ) {

				$timestamp = CMB2_Utils::make_valid_time_stamp( $this->value );
				if ( $timestamp ) {
					$datetime = new DateTime();
					$datetime->setTimestamp( $timestamp );
				}
			}

			if ( ! is_object( $datetime ) ) {
				$this->value = $utc_stamp = '';
			} else {
				$datetime->setTimezone( new DateTimeZone( $tzstring ) );
				$utc_stamp   = date_timestamp_get( $datetime ) - $offset;
				$this->value = serialize( $datetime );
			}

			if ( $this->field->group ) {
				$this->value = array(
					'supporting_field_value' => $utc_stamp,
					'supporting_field_id'    => $utc_key,
					'value'                  => $this->value,
				);
			} else {
				// Save the utc timestamp supporting field.
				if ( $repeat ) {
					$utc_values[ $utc_key ][] = $utc_stamp;
				} else {
					$this->_save_utc_value( $utc_key, $utc_stamp );
				}
			}
		} catch ( Exception $e ) {
			$this->value = '';
			CMB2_Utils::log_if_debug( __METHOD__, __LINE__, $e->getMessage() );
		}

		return $this->value;
	}

	/**
	 * Sanitize textareas and wysiwyg fields
	 *
	 * @since  1.0.1
	 * @return string       Sanitized data
	 */
	public function textarea() {
		if ( $this->value === null )
			$this->value = '';
		
		return is_array( $this->value ) ? array_map( 'wp_kses_post', $this->value ) : wp_kses_post( $this->value );
	}

	/**
	 * Sanitize code textareas
	 *
	 * @since  1.0.2
	 *
	 * @param bool $repeat Whether or not to repeat.
	 * @return string       Sanitized data
	 */
	public function textarea_code( $repeat = false ) {
		$repeat_value = $this->_check_repeat( __FUNCTION__, $repeat );
		if ( false !== $repeat_value ) {
			return $repeat_value;
		}

		return htmlspecialchars_decode( stripslashes( $this->value ) );
	}

	/**
	 * Handles saving of attachment post ID and sanitizing file url
	 *
	 * @since  1.1.0
	 * @return string        Sanitized url
	 */
	public function file() {
		$file_id_key = $this->field->_id( '', false ) . '_id';

		if ( $this->field->group ) {
			// Return an array with url/id if saving a group field.
			$this->value = $this->_get_group_file_value_array( $file_id_key );
		} else {
			$this->_save_file_id_value( $file_id_key );
			$this->text_url();
		}

		return $this->value;
	}

	/**
	 * Gets the values for the `file` field type from the data being saved.
	 *
	 * @since  2.2.0
	 *
	 * @param mixed $id_key ID key to use.
	 * @return array
	 */
	public function _get_group_file_value_array( $id_key ) {
		$alldata = $this->field->group->data_to_save;
		$base_id = $this->field->group->_id( '', false );
		$i       = $this->field->group->index;

		// Check group $alldata data.
		$id_val  = isset( $alldata[ $base_id ][ $i ][ $id_key ] )
			? absint( $alldata[ $base_id ][ $i ][ $id_key ] )
			: '';

		// We don't want to save 0 to the DB for file fields.
		if ( 0 === $id_val ) {
			$id_val = '';
		}

		return array(
			'value' => $this->text_url(),
			'supporting_field_value' => $id_val,
			'supporting_field_id'    => $id_key,
		);
	}

	/**
	 * Peforms saving of `file` attachement's ID
	 *
	 * @since  1.1.0
	 *
	 * @param mixed $file_id_key ID key to use.
	 * @return mixed
	 */
	public function _save_file_id_value( $file_id_key ) {
		$id_field = $this->_new_supporting_field( $file_id_key );

		// Check standard data_to_save data.
		$id_val = isset( $this->field->data_to_save[ $file_id_key ] )
			? $this->field->data_to_save[ $file_id_key ]
			: null;

		// If there is no ID saved yet, try to get it from the url.
		if ( $this->value && ! $id_val ) {
			$id_val = CMB2_Utils::image_id_from_url( $this->value );

		// If there is an ID but user emptied the input value, remove the ID.
		} elseif ( ! $this->value && $id_val ) {
			$id_val = null;
		}

		return $id_field->save_field( $id_val );
	}

	/**
	 * Peforms saving of `text_datetime_timestamp_timezone` utc timestamp
	 *
	 * @since  2.2.0
	 *
	 * @param mixed $utc_key   UTC key.
	 * @param mixed $utc_stamp UTC timestamp.
	 * @return mixed
	 */
	public function _save_utc_value( $utc_key, $utc_stamp ) {
		return $this->_new_supporting_field( $utc_key )->save_field( $utc_stamp );
	}

	/**
	 * Returns a new, supporting, CMB2_Field object based on a new field id.
	 *
	 * @since  2.2.0
	 *
	 * @param mixed $new_field_id New field ID.
	 * @return CMB2_Field
	 */
	public function _new_supporting_field( $new_field_id ) {
		return $this->field->get_field_clone( array(
			'id' => $new_field_id,
			'sanitization_cb' => false,
		) );
	}

	/**
	 * If repeating, loop through and re-apply sanitization method
	 *
	 * @since  1.1.0
	 * @param  string $method Class method.
	 * @param  bool   $repeat Whether repeating or not.
	 * @return mixed          Sanitized value
	 */
	public function _check_repeat( $method, $repeat ) {
		if ( $repeat || ! $this->field->args( 'repeatable' ) ) {
			return false;
		}

		$values_array = $this->value;

		$new_value = array();
		foreach ( $values_array as $iterator => $this->value ) {
			if ( $this->value ) {
				$val = $this->$method( true );
				if ( ! empty( $val ) ) {
					$new_value[] = $val;
				}
			}
		}

		$this->value = $new_value;

		return empty( $this->value ) ? null : $this->value;
	}

	/**
	 * Determine if passed value is an empty array
	 *
	 * @since  2.0.6
	 * @param  mixed $to_check Value to check.
	 * @return boolean         Whether value is an array that's empty
	 */
	public function _is_empty_array( $to_check ) {
		if ( is_array( $to_check ) ) {
			$cleaned_up = array_filter( $to_check );
			return empty( $cleaned_up );
		}
		return false;
	}

	/**
	 * Sanitize a URL. Make the default scheme HTTPS.
	 *
	 * @since  2.10.0
	 * @param  string  $value     Unescaped URL.
	 * @param  array   $protocols Allowed protocols for URL.
	 * @param  string  $default   Default value if no URL found.
	 * @return string             escaped URL.
	 */
	public static function sanitize_and_secure_url( $url, $protocols = null, $default = null ) {
		if ( empty( $url ) ) {
			return $default;
		}

		$orig_scheme = parse_url( $url, PHP_URL_SCHEME );
		$url         = esc_url_raw( $url, $protocols );

		// If original url has no scheme...
		if ( null === $orig_scheme ) {

			// Let's make sure the added scheme is https.
			$url = set_url_scheme( $url, 'https' );
		}

		return $url;
	}

	/**
	 * Check if the current field's value is empty.
	 *
	 * @since  2.9.1
	 *
	 * @return boolean Wether value is empty.
	 */
	public function is_empty_value() {
		if ( empty( $this->value ) ) {
			return true;
		}

		if ( is_array( $this->value ) ) {
			$test = array_filter( $this->value );
			if ( empty( $test ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the current field's value is a valid date value.
	 *
	 * @since  2.9.1
	 *
	 * @return boolean Wether value is a valid date value.
	 */
	public function is_valid_date_value() {
		return is_scalar( $this->value ) && CMB2_Utils::is_valid_date( $this->value );
	}

}
