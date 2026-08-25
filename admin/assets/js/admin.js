/**
 * ADStn Auto Poster - Admin JS
 */

(function($) {
	'use strict';

	$(document).ready(function() {

		var i18n = (typeof adstnAdmin !== 'undefined' && adstnAdmin.i18n) ? adstnAdmin.i18n : {};

		// =========================================================================
		// Toast Notifications Helper
		// =========================================================================
		function showToast(message, type) {
			type = type || 'success';
			var icon = type === 'success' ? '✓' : '⚠️';
			var $container = $('#adstn-toast-container');
			
			if (!$container.length) {
				$container = $('<div id="adstn-toast-container" class="adstn-toast-container"></div>').appendTo('body');
			}

			var $toast = $('<div class="adstn-toast ' + type + '"><span>' + icon + '</span><span>' + message + '</span></div>').appendTo($container);

			setTimeout(function() {
				$toast.addClass('is-show');
			}, 10);

			setTimeout(function() {
				$toast.removeClass('is-show');
				setTimeout(function() {
					$toast.remove();
				}, 300);
			}, 4000);
		}

		// =========================================================================
		// Copy to Clipboard
		// =========================================================================
		$(document).on('click', '.js-adstn-copy', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var targetSelector = $btn.data('target');
			var $input = $(targetSelector);

			if ($input.length) {
				$input.select();
				navigator.clipboard.writeText($input.val()).then(function() {
					var origHtml = $btn.html();
					$btn.html('<span class="dashicons dashicons-yes"></span> ' + (i18n.copied || 'Copied!'));
					showToast(i18n.copied || 'Copied to clipboard!', 'success');
					setTimeout(function() {
						$btn.html(origHtml);
					}, 2000);
				}).catch(function() {
					document.execCommand('copy');
					showToast(i18n.copied || 'Copied!', 'success');
				});
			}
		});

		// =========================================================================
		// Password Visibility Toggle
		// =========================================================================
		$(document).on('click', '.adstn-btn-toggle-pwd', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var $input = $btn.siblings('input');
			var isPassword = $input.attr('type') === 'password';

			$input.attr('type', isPassword ? 'text' : 'password');
			$btn.find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
		});

		// =========================================================================
		// Segmented Auth Method Switcher
		// =========================================================================
		$('input[name="auth_method"]').on('change', function() {
			var selected = $(this).val();
			$('.adstn-segment-label').removeClass('is-active');
			$(this).closest('.adstn-segment-label').addClass('is-active');

			if (selected === 'oauth') {
				$('#adstn-oauth-section').slideDown(200);
				$('#adstn-manual-token-section').slideUp(200);
			} else {
				$('#adstn-oauth-section').slideUp(200);
				$('#adstn-manual-token-section').slideDown(200);
			}
		});

		// =========================================================================
		// Privacy Cards Radio Selection
		// =========================================================================
		$('.adstn-privacy-card input[type="radio"]').on('change', function() {
			$('.adstn-privacy-card').removeClass('is-active');
			$(this).closest('.adstn-privacy-card').addClass('is-active');
		});

		// =========================================================================
		// Excerpt Range Slider Sync
		// =========================================================================
		var $rangeInput = $('#adstn-excerpt-range');
		var $numInput = $('#adstn-excerpt-length');

		$rangeInput.on('input', function() {
			$numInput.val($(this).val());
			updateTemplateLivePreview();
		});

		$numInput.on('change', function() {
			$rangeInput.val($(this).val());
			updateTemplateLivePreview();
		});

		// =========================================================================
		// Template Variable Chip Inserter
		// =========================================================================
		$(document).on('click', '.js-insert-tag', function(e) {
			e.preventDefault();
			var tag = $(this).data('tag');
			var $textarea = $('#adstn-message-template');

			if ($textarea.length) {
				var el = $textarea[0];
				var startPos = el.selectionStart;
				var endPos = el.selectionEnd;
				var text = $textarea.val();

				$textarea.val(text.substring(0, startPos) + tag + text.substring(endPos, text.length));
				el.focus();
				el.selectionStart = startPos + tag.length;
				el.selectionEnd = startPos + tag.length;

				updateTemplateLivePreview();
			}
		});

		// =========================================================================
		// Live Template Preview & Character Counter
		// =========================================================================
		function updateTemplateLivePreview() {
			var $textarea = $('#adstn-message-template');
			if (!$textarea.length) {
				return;
			}

			var val = $textarea.val();
			$('#adstn-char-count').text(val.length);

			// Call AJAX preview
			$.ajax({
				url: adstnAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'adstn_preview_template',
					nonce: adstnAdmin.nonce,
					template: val
				},
				success: function(res) {
					if (res.success && res.data && res.data.preview) {
						var formatted = res.data.preview.replace(/\n/g, '<br>');
						$('#adstn-sim-content').html(formatted);
					}
				}
			});
		}

		$('#adstn-message-template').on('input keyup', function() {
			updateTemplateLivePreview();
		});

		// Initial preview render if on template page
		if ($('#adstn-message-template').length) {
			updateTemplateLivePreview();
		}

		// =========================================================================
		// AJAX Form Submissions (Connection, Rules, Template)
		// =========================================================================
		$('#adstn-connection-form, #adstn-rules-form, #adstn-template-form').on('submit', function(e) {
			e.preventDefault();
			var $form = $(this);
			var $btn = $form.find('button[type="submit"]');
			var origHtml = $btn.html();

			$btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation:spin 1s infinite linear;"></span> ' + (i18n.saving || 'Saving...'));

			var formData = $form.serializeArray();
			formData.push({ name: 'action', value: 'adstn_save_settings' });
			formData.push({ name: 'nonce', value: adstnAdmin.nonce });

			$.ajax({
				url: adstnAdmin.ajaxUrl,
				type: 'POST',
				data: $.param(formData),
				success: function(res) {
					$btn.prop('disabled', false).html(origHtml);
					if (res.success) {
						showToast(res.data.message || (i18n.saved || 'Saved!'), 'success');
					} else {
						showToast(res.data.message || (i18n.failed || 'Failed!'), 'error');
					}
				},
				error: function() {
					$btn.prop('disabled', false).html(origHtml);
					showToast('Server connection error.', 'error');
				}
			});
		});

		// =========================================================================
		// Test Connection Action
		// =========================================================================
		$('#adstn-test-connection-btn, #adstn-quick-test-btn').on('click', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var origHtml = $btn.html();

			$btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation:spin 1s infinite linear;"></span> ' + (i18n.testing || 'Testing...'));

			$.ajax({
				url: adstnAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'adstn_test_connection',
					nonce: adstnAdmin.nonce
				},
				success: function(res) {
					$btn.prop('disabled', false).html(origHtml);
					if (res.success) {
						showToast(res.data.message || (i18n.connected || 'Connected!'), 'success');
					} else {
						showToast(res.data.message || (i18n.failed || 'Failed!'), 'error');
					}
				},
				error: function() {
					$btn.prop('disabled', false).html(origHtml);
					showToast('Connection request failed.', 'error');
				}
			});
		});

		// =========================================================================
		// Refresh User Profile
		// =========================================================================
		$('#adstn-refresh-profile-btn').on('click', function(e) {
			e.preventDefault();
			var $btn = $(this);
			$btn.find('.dashicons').css('animation', 'spin 1s infinite linear');

			$.ajax({
				url: adstnAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'adstn_refresh_profile',
					nonce: adstnAdmin.nonce
				},
				success: function(res) {
					$btn.find('.dashicons').css('animation', 'none');
					if (res.success) {
						showToast(res.data.message, 'success');
						setTimeout(function() { location.reload(); }, 800);
					} else {
						showToast(res.data.message, 'error');
					}
				},
				error: function() {
					$btn.find('.dashicons').css('animation', 'none');
					showToast('Profile refresh failed.', 'error');
				}
			});
		});

		// =========================================================================
		// Disconnect Account Action
		// =========================================================================
		$('#adstn-disconnect-btn, #adstn-disconnect-btn-2').on('click', function(e) {
			e.preventDefault();
			if (!confirm(i18n.confirmDisconnect || 'Are you sure you want to disconnect?')) {
				return;
			}

			$.ajax({
				url: adstnAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'adstn_disconnect_account',
					nonce: adstnAdmin.nonce
				},
				success: function(res) {
					if (res.success) {
						showToast(res.data.message, 'success');
						setTimeout(function() {
							window.location.href = 'admin.php?page=adstn-auto-poster&tab=connection';
						}, 800);
					} else {
						showToast(res.data.message, 'error');
					}
				}
			});
		});

		// =========================================================================
		// Clear Logs Action
		// =========================================================================
		$('#adstn-clear-logs-btn').on('click', function(e) {
			e.preventDefault();
			if (!confirm(i18n.confirmClearLogs || 'Are you sure you want to clear logs?')) {
				return;
			}

			$.ajax({
				url: adstnAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'adstn_clear_logs',
					nonce: adstnAdmin.nonce
				},
				success: function(res) {
					if (res.success) {
						showToast(res.data.message, 'success');
						setTimeout(function() { location.reload(); }, 600);
					}
				}
			});
		});

		// =========================================================================
		// View Log Details Modal
		// =========================================================================
		$(document).on('click', '.js-view-log-details', function(e) {
			e.preventDefault();
			var logId = $(this).data('log-id');
			var $modal = $('#adstn-log-modal');
			var $body = $('#adstn-modal-body');

			$body.html('<div style="text-align:center;padding:30px;"><span class="dashicons dashicons-update" style="animation:spin 1s infinite linear;font-size:32px;"></span></div>');
			$modal.fadeIn(200);

			$.ajax({
				url: adstnAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'adstn_get_log_details',
					log_id: logId,
					nonce: adstnAdmin.nonce
				},
				success: function(res) {
					if (res.success && res.data) {
						var log = res.data;
						var html = '<div class="adstn-log-details-wrap">';
						html += '<p><strong>' + (i18n.article || 'Article') + ':</strong> ' + (log.post_title || 'N/A') + ' (ID: #' + log.post_id + ')</p>';
						html += '<p><strong>' + (i18n.date || 'Date') + ':</strong> ' + log.created_at + '</p>';
						html += '<p><strong>' + (i18n.status || 'Status') + ':</strong> ' + log.status + '</p>';

						if (log.error_message) {
							html += '<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:10px;border-radius:6px;margin:12px 0;"><strong>' + (i18n.error || 'Error') + ':</strong> ' + log.error_message + '</div>';
						}

						if (log.request_payload) {
							html += '<h4 style="margin:16px 0 6px;">' + (i18n.requestPayload || 'Request Payload') + ':</h4>';
							html += '<pre style="background:#1e293b;color:#f8fafc;padding:12px;border-radius:8px;font-size:12px;max-height:160px;overflow:auto;">' + formatJson(log.request_payload) + '</pre>';
						}

						if (log.response_data) {
							html += '<h4 style="margin:16px 0 6px;">' + (i18n.apiResponse || 'Server API Response') + ':</h4>';
							html += '<pre style="background:#1e293b;color:#f8fafc;padding:12px;border-radius:8px;font-size:12px;max-height:160px;overflow:auto;">' + formatJson(log.response_data) + '</pre>';
						}

						html += '</div>';
						$body.html(html);
					} else {
						$body.html('<p style="color:red;">' + (i18n.loadFailed || 'Failed to load log details.') + '</p>');
					}
				}
			});
		});

		$('.adstn-modal-close, .adstn-modal-overlay').on('click', function() {
			$('#adstn-log-modal').fadeOut(200);
		});

		function formatJson(val) {
			if (typeof val === 'string') {
				try {
					var parsed = JSON.parse(val);
					return JSON.stringify(parsed, null, 2);
				} catch (e) {
					return val;
				}
			}
			return JSON.stringify(val, null, 2);
		}

		// =========================================================================
		// Retry Failed Log
		// =========================================================================
		$(document).on('click', '.js-retry-log', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var logId = $btn.data('log-id');
			$btn.find('.dashicons').css('animation', 'spin 1s infinite linear');

			$.ajax({
				url: adstnAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'adstn_retry_log',
					log_id: logId,
					nonce: adstnAdmin.nonce
				},
				success: function(res) {
					$btn.find('.dashicons').css('animation', 'none');
					if (res.success) {
						showToast(res.data.message, 'success');
						setTimeout(function() { location.reload(); }, 1000);
					} else {
						showToast(res.data.message, 'error');
					}
				},
				error: function() {
					$btn.find('.dashicons').css('animation', 'none');
					showToast('Retry request failed.', 'error');
				}
			});
		});

		// =========================================================================
		// Metabox Instant Share (Post Edit Screen)
		// =========================================================================
		$('#adstn-instant-share-btn').on('click', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var postId = $btn.data('post-id');
			var $notice = $('#adstn-instant-share-notice');
			var origHtml = $btn.html();

			$btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation:spin 1s infinite linear;"></span> ' + (i18n.sharing || 'Sharing...'));
			$notice.hide().empty();

			$.ajax({
				url: adstnAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'adstn_instant_share',
					post_id: postId,
					nonce: adstnAdmin.nonce
				},
				success: function(res) {
					$btn.prop('disabled', false).html(origHtml);
					if (res.success) {
						$notice.css('color', '#15803d').html('✓ ' + (res.data.message || (i18n.shared || 'Shared!'))).fadeIn();
					} else {
						$notice.css('color', '#b91c1c').html('⚠️ ' + (res.data.message || (i18n.failed || 'Failed!'))).fadeIn();
					}
				},
				error: function() {
					$btn.prop('disabled', false).html(origHtml);
					$notice.css('color', '#b91c1c').html('⚠️ Server error').fadeIn();
				}
			});
		});

		// =========================================================================
		// Direct Web Share Test Tool
		// =========================================================================
		$('#adstn-open-share-btn').on('click', function(e) {
			e.preventDefault();
			var text = $('#adstn-quick-share-input').val();
			if (text) {
				var url = 'https://www.adstn.ovh/share?text=' + encodeURIComponent(text);
				window.open(url, '_blank', 'width=650,height=550');
			}
		});

	});

})(jQuery);
