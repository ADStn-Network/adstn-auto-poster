<?php
/**
 * Auto Publisher & Content Formatter for ADStn.
 *
 * @package    ADStn_Auto_Poster
 * @subpackage ADStn_Auto_Poster/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ADStn_Publisher {

	/**
	 * Settings array.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * API Client instance.
	 *
	 * @var ADStn_API_Client
	 */
	private $api_client;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->settings   = get_option( 'adstn_settings', array() );
		$this->api_client = new ADStn_API_Client( $this->settings );

		// Hook into post status transitions
		add_action( 'transition_post_status', array( $this, 'handle_post_transition' ), 10, 3 );
	}

	/**
	 * Handle transition of post status.
	 *
	 * @param string  $new_status
	 * @param string  $old_status
	 * @param WP_Post $post
	 */
	public function handle_post_transition( $new_status, $old_status, $post ) {
		// Only run when post transitions to 'publish'
		if ( 'publish' !== $new_status ) {
			return;
		}

		// Avoid auto-draft, revisions, imports, or bulk edits if not valid
		if ( wp_is_post_revision( $post->ID ) || wp_is_post_autosave( $post->ID ) ) {
			return;
		}

		// Check if global auto-publish is enabled
		if ( empty( $this->settings['enabled'] ) || '1' !== (string) $this->settings['enabled'] ) {
			return;
		}

		// Check if API client is connected
		if ( ! $this->api_client->is_connected() ) {
			return;
		}

		// Check if this post type is enabled
		$allowed_post_types = isset( $this->settings['post_types'] ) && is_array( $this->settings['post_types'] ) ? $this->settings['post_types'] : array( 'post' );
		if ( ! in_array( $post->post_type, $allowed_post_types, true ) ) {
			return;
		}

		// Check post event settings (whether updates to already published posts trigger auto-publish)
		$allowed_events = isset( $this->settings['post_events'] ) && is_array( $this->settings['post_events'] ) ? $this->settings['post_events'] : array( 'publish' );
		$is_update      = ( 'publish' === $old_status );

		if ( $is_update && ! in_array( 'update', $allowed_events, true ) ) {
			return;
		}

		// Check if already published on ADStn (prevent duplicates unless explicitly re-shared)
		$already_published = get_post_meta( $post->ID, '_adstn_published', true );
		if ( ! empty( $already_published ) && ! $is_update ) {
			return;
		}

		// Check post-level metabox override
		$post_override = get_post_meta( $post->ID, '_adstn_auto_publish', true );
		if ( '0' === $post_override ) {
			// User specifically disabled auto-publish for this post
			return;
		}

		// Check category filters for standard posts
		if ( 'post' === $post->post_type ) {
			$post_categories = wp_get_post_categories( $post->ID );

			$include_cats = isset( $this->settings['include_categories'] ) && is_array( $this->settings['include_categories'] ) ? array_map( 'intval', $this->settings['include_categories'] ) : array();
			$exclude_cats = isset( $this->settings['exclude_categories'] ) && is_array( $this->settings['exclude_categories'] ) ? array_map( 'intval', $this->settings['exclude_categories'] ) : array();

			// If specific categories are required to include
			if ( ! empty( $include_cats ) ) {
				$has_included = false;
				foreach ( $post_categories as $cat_id ) {
					if ( in_array( $cat_id, $include_cats, true ) ) {
						$has_included = true;
						break;
					}
				}
				if ( ! $has_included ) {
					return;
				}
			}

			// If specific categories are excluded
			if ( ! empty( $exclude_cats ) ) {
				foreach ( $post_categories as $cat_id ) {
					if ( in_array( $cat_id, $exclude_cats, true ) ) {
						return; // Skip post
					}
				}
			}
		}

		// Publish the post
		$this->publish_post_to_adstn( $post->ID );
	}

	/**
	 * Prepare content and publish post to ADStn.
	 *
	 * @param int         $post_id
	 * @param string|null $custom_message Optional custom message overriding template.
	 * @return array|WP_Error
	 */
	public function publish_post_to_adstn( $post_id, $custom_message = null ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error( 'invalid_post', __( 'Invalid Post ID.', 'adstn-auto-poster' ) );
		}

		// Check for custom message in post meta if none passed
		if ( null === $custom_message ) {
			$custom_message = get_post_meta( $post_id, '_adstn_custom_message', true );
		}

		if ( ! empty( trim( $custom_message ) ) ) {
			$final_content = $this->parse_template_tags( $custom_message, $post );
		} else {
			$template      = ! empty( $this->settings['message_template'] ) ? $this->settings['message_template'] : "{title}\n\n{excerpt}\n\n{url}";
			$final_content = $this->parse_template_tags( $template, $post );
		}

		$privacy = ! empty( $this->settings['privacy'] ) ? $this->settings['privacy'] : 'public';

		// Log initiation
		$log_id = ADStn_Logger::log( array(
			'post_id'         => $post_id,
			'post_title'      => get_the_title( $post_id ),
			'status'          => 'pending',
			'request_payload' => array(
				'content' => $final_content,
				'privacy' => $privacy,
			),
		) );

		// Execute API call
		$result = $this->api_client->publish_content( $final_content, $privacy );

		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_message();

			// Update post meta with error
			update_post_meta( $post_id, '_adstn_last_error', $error_message );
			update_post_meta( $post_id, '_adstn_error_time', current_time( 'mysql' ) );

			// Update Log
			if ( $log_id ) {
				ADStn_Logger::update_log( $log_id, array(
					'status'        => 'failed',
					'error_message' => $error_message,
					'response_data' => $result->get_error_data(),
				) );
			}

			return $result;
		}

		// Successful publication
		update_post_meta( $post_id, '_adstn_published', 1 );
		update_post_meta( $post_id, '_adstn_published_at', current_time( 'mysql' ) );
		delete_post_meta( $post_id, '_adstn_last_error' );

		if ( isset( $result['data']['id'] ) ) {
			update_post_meta( $post_id, '_adstn_adstn_post_id', $result['data']['id'] );
		}

		// Update Log
		if ( $log_id ) {
			ADStn_Logger::update_log( $log_id, array(
				'status'        => 'success',
				'response_data' => $result,
			) );
		}

		return $result;
	}

	/**
	 * Parse template placeholders for a given post.
	 *
	 * @param string  $template
	 * @param WP_Post $post
	 * @return string
	 */
	public function parse_template_tags( $template, $post ) {
		$title     = get_the_title( $post );
		$url       = get_permalink( $post );
		$site_name = get_bloginfo( 'name' );

		// Author Name
		$author_id   = $post->post_author;
		$author_name = get_the_author_meta( 'display_name', $author_id );

		// Excerpt
		$excerpt_len = isset( $this->settings['excerpt_length'] ) ? (int) $this->settings['excerpt_length'] : 160;
		$excerpt     = '';

		if ( ! empty( $post->post_excerpt ) ) {
			$excerpt = $post->post_excerpt;
		} else {
			$excerpt = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		}

		$excerpt = wp_trim_words( $excerpt, 40, '...' );
		if ( mb_strlen( $excerpt ) > $excerpt_len ) {
			$excerpt = mb_substr( $excerpt, 0, $excerpt_len ) . '...';
		}

		// Categories & Hashtags
		$categories_list = array();
		$cat_hashtags    = array();
		$post_cats       = get_the_category( $post->ID );
		if ( ! empty( $post_cats ) && ! is_wp_error( $post_cats ) ) {
			foreach ( $post_cats as $cat ) {
				$categories_list[] = $cat->name;
				$clean_cat_tag     = preg_replace( '/\s+/', '_', trim( $cat->name ) );
				$clean_cat_tag     = preg_replace( '/[^\p{L}\p{N}_]+/u', '', $clean_cat_tag );
				if ( ! empty( $clean_cat_tag ) ) {
					$cat_hashtags[] = '#' . $clean_cat_tag;
				}
			}
		}

		// Tags & Hashtags
		$tags_list    = array();
		$tag_hashtags = array();
		$post_tags    = get_the_tags( $post->ID );
		if ( ! empty( $post_tags ) && ! is_wp_error( $post_tags ) ) {
			foreach ( $post_tags as $tag ) {
				$tags_list[]  = $tag->name;
				$clean_tag    = preg_replace( '/\s+/', '_', trim( $tag->name ) );
				$clean_tag    = preg_replace( '/[^\p{L}\p{N}_]+/u', '', $clean_tag );
				if ( ! empty( $clean_tag ) ) {
					$tag_hashtags[] = '#' . $clean_tag;
				}
			}
		}

		// Build Combined Hashtags string
		$max_tags      = isset( $this->settings['max_hashtags'] ) ? (int) $this->settings['max_hashtags'] : 5;
		$hashtags_mode = isset( $this->settings['hashtags_mode'] ) ? $this->settings['hashtags_mode'] : 'tags';
		$all_hashtags  = array();

		if ( 'both' === $hashtags_mode ) {
			$all_hashtags = array_unique( array_merge( $cat_hashtags, $tag_hashtags ) );
		} elseif ( 'categories' === $hashtags_mode ) {
			$all_hashtags = $cat_hashtags;
		} elseif ( 'tags' === $hashtags_mode ) {
			$all_hashtags = $tag_hashtags;
		}

		if ( $max_tags > 0 && count( $all_hashtags ) > $max_tags ) {
			$all_hashtags = array_slice( $all_hashtags, 0, $max_tags );
		}

		$hashtags_string = implode( ' ', $all_hashtags );

		// Placeholders mapping
		$tags_map = array(
			'{title}'      => $title,
			'{url}'        => $url,
			'{excerpt}'    => $excerpt,
			'{author}'     => $author_name,
			'{site_name}'  => $site_name,
			'{categories}' => implode( ', ', $categories_list ),
			'{tags}'       => implode( ', ', $tags_list ),
			'{hashtags}'   => $hashtags_string,
		);

		$parsed = str_replace( array_keys( $tags_map ), array_values( $tags_map ), $template );

		// Clean up multiple excessive line breaks
		$parsed = preg_replace( "/\n{3,}/", "\n\n", trim( $parsed ) );

		return $parsed;
	}

	/**
	 * Generate sample preview text for a dummy or real post.
	 *
	 * @param string $template
	 * @return string
	 */
	public static function generate_sample_preview( $template ) {
		$dummy_map = array(
			'{title}'      => __( 'Sample Article Title for Auto-Publishing', 'adstn-auto-poster' ),
			'{url}'        => home_url( '/sample-article' ),
			'{excerpt}'    => __( 'This is a sample excerpt showing how your post content will appear on the ADStn platform after auto-publishing.', 'adstn-auto-poster' ),
			'{author}'     => __( 'Author Name', 'adstn-auto-poster' ),
			'{site_name}'  => get_bloginfo( 'name' ),
			'{categories}' => __( 'News, Technology', 'adstn-auto-poster' ),
			'{tags}'       => __( 'WordPress, Marketing', 'adstn-auto-poster' ),
			'{hashtags}'   => '#Technology #WordPress #News',
		);

		return str_replace( array_keys( $dummy_map ), array_values( $dummy_map ), $template );
	}
}
