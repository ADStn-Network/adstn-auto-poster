<?php
/**
 * Activity & Error Logger for ADStn Auto Poster.
 * Uses native WordPress options storage (rolling 200 logs) to avoid custom DB tables.
 *
 * @package    ADStn_Auto_Poster
 * @subpackage ADStn_Auto_Poster/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ADStn_Logger {

	/**
	 * Option key.
	 */
	const OPTION_KEY = 'adstn_activity_logs';

	/**
	 * Max log entries to keep.
	 */
	const MAX_LOGS = 200;

	/**
	 * Insert a new log entry.
	 *
	 * @param array $args
	 * @return int|false Log ID.
	 */
	public static function log( $args ) {
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
		$logs = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $logs ) ) {
			$logs = array();
		}

		$log_id = time() . wp_rand( 100, 999 );
		$data['id'] = (int) $log_id;

		// Add to beginning of array
		array_unshift( $logs, $data );

		// Cap max logs
		if ( count( $logs ) > self::MAX_LOGS ) {
			$logs = array_slice( $logs, 0, self::MAX_LOGS );
		}

		update_option( self::OPTION_KEY, $logs, false );

		return $data['id'];
	}

	/**
	 * Update an existing log entry.
	 *
	 * @param int   $log_id
	 * @param array $args
	 * @return bool
	 */
	public static function update_log( $log_id, $args ) {
		$logs = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $logs ) ) {
			return false;
		}

		$updated = false;
		foreach ( $logs as $idx => $entry ) {
			if ( isset( $entry['id'] ) && (int) $entry['id'] === (int) $log_id ) {
				$logs[ $idx ] = wp_parse_args( $args, $entry );
				$updated      = true;
				break;
			}
		}

		if ( $updated ) {
			update_option( self::OPTION_KEY, $logs, false );
		}

		return $updated;
	}

	/**
	 * Get logs with pagination and filters.
	 *
	 * @param array $args
	 * @return array
	 */
	public static function get_logs( $args = array() ) {
		$defaults = array(
			'per_page' => 20,
			'page'     => 1,
			'status'   => '',
			'search'   => '',
		);

		$r    = wp_parse_args( $args, $defaults );
		$logs = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $logs ) ) {
			$logs = array();
		}

		// Filter by status
		if ( ! empty( $r['status'] ) ) {
			$logs = array_filter( $logs, function( $item ) use ( $r ) {
				return isset( $item['status'] ) && $item['status'] === $r['status'];
			} );
		}

		// Filter by search query
		if ( ! empty( $r['search'] ) ) {
			$search_q = mb_strtolower( trim( $r['search'] ) );
			$logs = array_filter( $logs, function( $item ) use ( $search_q ) {
				$title = isset( $item['post_title'] ) ? mb_strtolower( $item['post_title'] ) : '';
				$err   = isset( $item['error_message'] ) ? mb_strtolower( $item['error_message'] ) : '';
				return ( strpos( $title, $search_q ) !== false || strpos( $err, $search_q ) !== false );
			} );
		}

		$total_items  = count( $logs );
		$per_page     = max( 1, absint( $r['per_page'] ) );
		$current_page = max( 1, absint( $r['page'] ) );
		$total_pages  = max( 1, ceil( $total_items / $per_page ) );
		$offset       = ( $current_page - 1 ) * $per_page;

		$paged_items = array_slice( array_values( $logs ), $offset, $per_page );

		return array(
			'items'        => $paged_items,
			'total_items'  => $total_items,
			'total_pages'  => $total_pages,
			'current_page' => $current_page,
		);
	}

	/**
	 * Get log statistics.
	 *
	 * @return array
	 */
	public static function get_stats() {
		$logs = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $logs ) || empty( $logs ) ) {
			return array(
				'total'      => 0,
				'success'    => 0,
				'failed'     => 0,
				'last_share' => '—',
			);
		}

		$total   = count( $logs );
		$success = 0;
		$failed  = 0;
		$last    = isset( $logs[0]['created_at'] ) ? $logs[0]['created_at'] : '—';

		foreach ( $logs as $item ) {
			if ( isset( $item['status'] ) && 'success' === $item['status'] ) {
				$success++;
			} elseif ( isset( $item['status'] ) && 'failed' === $item['status'] ) {
				$failed++;
			}
		}

		return array(
			'total'      => $total,
			'success'    => $success,
			'failed'     => $failed,
			'last_share' => $last,
		);
	}

	/**
	 * Clear all logs.
	 *
	 * @return bool
	 */
	public static function clear_logs() {
		return update_option( self::OPTION_KEY, array(), false );
	}

	/**
	 * Get a single log by ID.
	 *
	 * @param int $id
	 * @return array|null
	 */
	public static function get_log( $id ) {
		$logs = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $logs ) ) {
			return null;
		}

		foreach ( $logs as $item ) {
			if ( isset( $item['id'] ) && (int) $item['id'] === (int) $id ) {
				return $item;
			}
		}

		return null;
	}
}
