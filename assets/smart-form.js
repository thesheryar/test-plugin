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
			console.log('Form not found');
			return;
		}

		console.log('Form loaded successfully');

		/**
		 * Handle form submission
		 */
		$form.on('submit', function(e) {
			e.preventDefault();
			console.log('Form submitted');

			// Clear previous messages
			$messageContainer.removeClass('success error').addClass('hidden').html('');

			// Get form data
			const name = $('#smart_form_name').val();
			const email = $('#smart_form_email').val();
			const message = $('#smart_form_message').val();

			console.log('Form Values:', { name, email, message });

			// Basic validation
			if (!name || !email || !message) {
				showError('All fields are required!');
				return false;
			}

			if (email.indexOf('@') === -1) {
				showError('Please enter a valid email address');
				return false;
			}

			const formData = {
				action: 'smrt_submit_form',
				name: name,
				email: email,
				message: message
			};

			console.log('Sending form data:', formData);

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
					console.log('AJAX Success Response:', response);
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
					console.error('AJAX Error:', { textStatus, errorThrown, response: jqXHR.responseText });
					setSubmitState(false);
					$loadingSpan.addClass('hidden');

					if (textStatus === 'timeout') {
						showError(smartFormObj.i18n.error + ' (Timeout)');
					} else {
						showError('AJAX Error: ' + textStatus);
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
			$submitBtn.text(loading ? smartFormObj.i18n.sending : 'Send Message');
		}

		/**
		 * Show success message
		 *
		 * @param {string} message Success message
		 */
		function showSuccess(message) {
			$messageContainer.removeClass('error hidden').addClass('success');
			$messageContainer.html('<p>' + escapeHtml(message) + '</p>');
			console.log('Success message shown:', message);

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
			console.log('Error shown:', message);

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
			return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
		}
	});

})(jQuery);
