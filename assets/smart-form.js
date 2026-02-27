/**
 * Smart Contact Form - Frontend JavaScript
 *
 * @package Smart_Contact_Form
 * @since 1.0.0
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		const $form = $('#smart-contact-form');
		const $submitBtn = $('#smart_form_submit');
		const $loadingSpan = $('#smart_form_loading');
		const $messageContainer = $('#smart_form_message_container');

		if ($form.length === 0) {
			return;
		}

		/**
		 * Handle form submission
		 */
		$form.on('submit', function(e) {
			e.preventDefault();

			// Clear previous messages
			$messageContainer.removeClass('success error').addClass('hidden').html('');

			// Validate form
			if (!$form[0].checkValidity()) {
				showError(smartFormObj.i18n.requiredFields);
				return false;
			}

			// Get form data
			const formData = {
				action: 'smrt_submit_form',
				nonce: $('input[name="smart_form_nonce"]').val(),
				name: $('#smart_form_name').val(),
				email: $('#smart_form_email').val(),
				message: $('#smart_form_message').val()
			};

			// Show loading state
			setSubmitState(true);
			$loadingSpan.removeClass('hidden');

			// Send AJAX request
			$.ajax({
				type: 'POST',
				url: smartFormObj.ajaxUrl,
				data: formData,
				dataType: 'json',
				timeout: 10000,
				success: function(response) {
					setSubmitState(false);
					$loadingSpan.addClass('hidden');

					if (response.success) {
						showSuccess(response.data || smartFormObj.i18n.success);
						$form[0].reset();
					} else {
						showError(response.data || smartFormObj.i18n.error);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					setSubmitState(false);
					$loadingSpan.addClass('hidden');

					if (textStatus === 'timeout') {
						showError(smartFormObj.i18n.error + ' (Timeout)');
					} else {
						showError(smartFormObj.i18n.error);
					}
				}
			});

			return false;
		});

		/**
		 * Set submit button state
		 *
		 * @param {boolean} loading Whether the form is being submitted
		 */
		function setSubmitState(loading) {
			$submitBtn.prop('disabled', loading);
			$submitBtn.text(loading ? smartFormObj.i18n.sending : '<?php esc_html_e( "Send Message", "smart-contact-form" ); ?>');
		}

		/**
		 * Show success message
		 *
		 * @param {string} message Success message
		 */
		function showSuccess(message) {
			$messageContainer.removeClass('error hidden').addClass('success');
			$messageContainer.html('<p>' + escapeHtml(message) + '</p>');

			// Auto-hide after 5 seconds
			setTimeout(function() {
				$messageContainer.addClass('hidden');
			}, 5000);
		}

		/**
		 * Show error message
		 *
		 * @param {string|array} message Error message or array of errors
		 */
		function showError(message) {
			$messageContainer.removeClass('success hidden').addClass('error');

			if (typeof message === 'object' && message !== null) {
				let errorHtml = '<ul>';
				$.each(message, function(key, value) {
					errorHtml += '<li>' + escapeHtml(value) + '</li>';
				});
				errorHtml += '</ul>';
				$messageContainer.html(errorHtml);
			} else {
				$messageContainer.html('<p>' + escapeHtml(message) + '</p>');
			}
		}

		/**
		 * Escape HTML special characters
		 *
		 * @param {string} text Text to escape
		 * @returns {string} Escaped text
		 */
		function escapeHtml(text) {
			const map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return text.replace(/[&<>"']/g, function(m) { return map[m]; });
		}
	});

})(jQuery);
