<?php
/**
 * Activity & Error Logger for ADStn Auto Poster.
 *
 * @package    ADStn_Auto_Poster
 * @subpackage ADStn_Auto_Poster/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ADStn_Logger {

	/**
	 * Log table name with WP prefix.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'adstn_logs';
	}

	/**
	 * Insert a new log entry.
	 *
	 * @param array $args
	 * @return int|false
	 */
	public static function log( $args ) {
		global $wpdb;

		$defaults = array(
			'post_id'         => 0,
			'post_title'      => '',
			'status'          => 'pending',
			'request_payload' => '',
			'response_data'   => '',
			'error_message'   => '',
			'created_at'      => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $args, $defaults );

		if ( is_array( $data['request_payload'] ) || is_object( $data['request_payload'] ) ) {
			$data['request_payload'] = wp_json_encode( $data['request_payload'] );
		}

		if ( is_array( $data['response_data'] ) || is_object( $data['response_data'] ) ) {
			$data['response_data'] = wp_json_encode( $data['response_data'] );
		}

		$result = $wpdb->insert(
			self::get_table_name(),
			$data,
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Update an existing log entry.
	 *
	 * @param int   $log_id
	 * @param array $args
	 * @return bool
	 */
	public static function update_log( $log_id, $args ) {
		global $wpdb;

		if ( isset( $args['request_payload'] ) && ( is_array( $args['request_payload'] ) || is_object( $args['request_payload'] ) ) ) {
			$args['request_payload'] = wp_json_encode( $args['request_payload'] );
		}

		if ( isset( $args['response_data'] ) && ( is_array( $args['response_data'] ) || is_object( $args['response_data'] ) ) ) {
			$args['response_data'] = wp_json_encode( $args['response_data'] );
		}

		$updated = $wpdb->update(
			self::get_table_name(),
			$args,
			array( 'id' => absint( $log_id ) )
		);

		return false !== $updated;
	}

	/**
	 * Get logs with pagination and filters.
	 *
	 * @param array $args
	 * @return array
	 */
	public static function get_logs( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'per_page' => 20,
			'page'     => 1,
			'status'   => '',
			'search'   => '',
			'orderby'  => 'created_at',
			'order'    => 'DESC',
		);

		$r = wp_parse_args( $args, $defaults );
		$table = self::get_table_name();

		$where = array( '1=1' );
		$params = array();

		if ( ! empty( $r['status'] ) && in_array( $r['status'], array( 'success', 'failed', 'pending' ), true ) ) {
			$where[]  = 'status = %s';
			$params[] = $r['status'];
		}

		if ( ! empty( $r['search'] ) ) {
			$where[]  = '(post_title LIKE %s OR error_message LIKE %s)';
			$search_term = '%' . $wpdb->esc_like( $r['search'] ) . '%';
			$params[] = $search_term;
			$params[] = $search_term;
		}

		$where_clause = implode( ' AND ', $where );
		$orderby      = in_array( $r['orderby'], array( 'id', 'created_at', 'status', 'post_id' ), true ) ? $r['orderby'] : 'created_at';
		$order        = 'ASC' === strtoupper( $r['order'] ) ? 'ASC' : 'DESC';

		$per_page = max( 1, absint( $r['per_page'] ) );
		$page     = max( 1, absint( $r['page'] ) );
		$offset   = ( $page - 1 ) * $per_page;

		// Get total count
		$count_query = "SELECT COUNT(*) FROM {$table} WHERE {$where_clause}";
		if ( ! empty( $params ) ) {
			$total_items = (int) $wpdb->get_var( $wpdb->prepare( $count_query, $params ) );
		} else {
			$total_items = (int) $wpdb->get_var( $count_query );
		}

		// Get items
		$query = "SELECT * FROM {$table} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$query_params = array_merge( $params, array( $per_page, $offset ) );
		$items = $wpdb->get_results( $wpdb->prepare( $query, $query_params ), ARRAY_A );

		return array(
			'items'        => $items ? $items : array(),
			'total_items'  => $total_items,
			'total_pages'  => ceil( $total_items / $per_page ),
			'current_page' => $page,
		);
	}

	/**
	 * Get log statistics.
	 *
	 * @return array
	 */
	public static function get_stats() {
		global $wpdb;
		$table = self::get_table_name();

		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $table_exists !== $table ) {
			return array(
				'total'      => 0,
				'success'    => 0,
				'failed'     => 0,
				'last_share' => '—',
			);
		}

		$total   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		$success = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'success'" );
		$failed  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'failed'" );
		$last    = $wpdb->get_var( "SELECT created_at FROM {$table} ORDER BY created_at DESC LIMIT 1" );

		return array(
			'total'      => $total,
			'success'    => $success,
			'failed'     => $failed,
			'last_share' => $last ? $last : '—',
		);
	}

	/**
	 * Clear all logs.
	 *
	 * @return bool
	 */
	public static function clear_logs() {
		global $wpdb;
		$table = self::get_table_name();
		return false !== $wpdb->query( "TRUNCATE TABLE {$table}" );
	}

	/**
	 * Get a single log by ID.
	 *
	 * @param int $id
	 * @return array|null
	 */
	public static function get_log( $id ) {
		global $wpdb;
		$table = self::get_table_name();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", absint( $id ) ), ARRAY_A );
	}
}
