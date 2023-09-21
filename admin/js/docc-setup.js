(function ($) {
	'use strict';

	const pluginUrlPath = localized_vars["pluginUrlPath"];
	const setupStatus = localized_vars["setupStatus"];
	const autoSetup = localized_vars["autoSetup"];
	const setupComplete = localized_vars["setupComplete"];
	const adminAjax = localized_vars["adminAjax"];
	const etThemeBuilder = localized_vars["etThemeBuilder"];
	const etThemeOptions = localized_vars["etThemeOptions"];
	const etCustomizerOptionSetTheme = localized_vars["etCustomizerOptionSetTheme"];
	
	$(document).ready(function () {
		// function calls
		gs_buttons();

		$('a#gs-toggle-advanced-info').click(() => 
			$('.gs-plugins th:not(.always-show), .gs-plugins td:not(.always-show), .gs-themes  th:not(.always-show), .gs-themes  td:not(.always-show)')
			.toggle());

		$('a#gs-toggle-support-info').click(() => $('.asc-support-info').toggle());

		// ajax calls
		$("#gs-header-start").click(function () {
			$("#gs-setup-progress").append("<p>Beginning install process...</p>");

			var looper = $.Deferred().resolve();

			var counter = 0;
			let total = 34;

			looper = looper.then(function () {
				return $.ajax({
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'install_theme'
					},
				}).done(function (response) {
					var $progress = $("#gs-setup-progress");
					if ($progress.children().length > 2) $progress.children().first().remove();
					$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") Installing Divi</p>");
				});
			});

			looper = looper.then(function () {
				return $.ajax({
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'activate_theme'
					},
				}).done(function (response) {
					var $progress = $("#gs-setup-progress");
					if ($progress.children().length > 2) $progress.children().first().remove();
					$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") Activating Divi</p>");
				});
			});

			let plugins = [
				'gravityforms/gravityforms.php',
				'gravityformspartialentries/partialentries.php',
				'gravityformsuserregistration/userregistration.php',
				'gravityformswebhooks/webhooks.php',
				'styles-and-layouts-for-gravity-forms/styles-layouts-gravity-forms.php',
				'importexport-add-on-feeds-for-gravity-forms/import-export-feeds-for-gravity-forms.php',
				'GFChart/gfchart.php',
				'members/members.php',
				'advanced-custom-fields-font-awesome/acf-font-awesome.php',
				'advanced-custom-fields-pro/acf.php',
				'frontend-reset-password/som-frontend-reset-password.php',
				'shortcode-in-menus/shortcode-in-menus.php',
				'wp-post-modal/wp-post-modal.php',
				'nav-menu-roles/nav-menu-roles.php',
				'wordpress-importer/wordpress-importer.php'
			];

			$.each(plugins, function (i, plugin_name) {
				looper = looper.then(function () {
					return $.ajax({
						url: ajaxurl,
						method: 'POST',
						data: {
							action: 'install_plugin',
							plugin: plugin_name
						},
					}).done(function (response) {
						var $progress = $("#gs-setup-progress");
						if ($progress.children().length > 2) $progress.children().first().remove();
						$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") Installing " + plugin_name + "</p>");
					});
				});
				looper = looper.then(function () {
					return $.ajax({
						url: ajaxurl,
						method: 'POST',
						data: {
							action: 'activate_plugin',
							plugin: plugin_name
						},
					}).done(function (response) {
						var $progress = $("#gs-setup-progress");
						if ($progress.children().length > 2) $progress.children().first().remove();
						$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") Activating " + plugin_name + "</p>");
					});
				});
			});
			looper.then(function () {
				$("#gs-setup-progress").append("<p>Install complete.</p>");

				let status = '(1/3) Guided Setup';
				setup_status(status);
				location.reload();
			});
		});
		function setup_status(status) {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				async: false,
				data: {
					action: 'setup_status',
					status: status
				},
				success: function (data) {
					console.log("DOCC Status Updated.");
				}
			})
		}
		$("#gs-theme-install").click(function () {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'install_theme',
				},
				success: function (data) {
					console.log("Installing theme...");
					location.reload();
				}
			});
		});
		$("#gs-theme-activate").click(function () {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'activate_theme',
				},
				success: function (data) {
					console.log("Activating theme...");
					location.reload();
				}
			});
		});
		$(".gs-plugin-install").click(function () {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'install_plugin',
					plugin: $(this).attr('id'),
				},
				success: function (data) {
					console.log("Installing plugin...");
					location.reload();
				}
			});
		});
		$(".gs-plugin-activate").click(function () {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'activate_plugin',
					plugin: $(this).attr('id'),
				},
				success: function (data) {
					console.log("Activating plugin...");
					location.reload();
				}
			});
		});
		let asStarted = false;
		$("#as-header-start").click(function () {
			if (asStarted) return;
			asStarted = true;
			$( this ).prop('disabled', true);
			$( this ).text("Starting...");
			start_auto_setup();
		});
		$("#asc-rerun").click(function () {
			// Reset Divi Theme Builder
			$.ajax({
				url: etThemeBuilder,
				type: 'POST',
				data: {},
				success: function (data) {
					let et_config_string = $(data).find('#et-theme-builder-js-extra')[0].innerHTML.slice(31).slice(0, -2);
					let et_config = JSON.parse(et_config_string)['config'];
					let nonces = et_config['nonces'];
					let theme_builder_nonce = nonces['et_theme_builder_api_reset'];

					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'et_theme_builder_api_reset',
							nonce: theme_builder_nonce
						},
						success: function (data) {
							console.log('Divi Theme Builder reset.');
						}
					})
				}
			});
			// Reset Divi Theme Options
			$.ajax({
				url: etThemeOptions,
				type: 'POST',
				data: {},
				success: function (data) {
					let _wpnonce_reset = $(data).find('#_wpnonce_reset').val();
					$.ajax({
						url: etThemeOptions,
						type: 'POST',
						data: {
							action: 'reset',
							reset: 'Yes',
							_wpnonce_reset: _wpnonce_reset
						},
						success: function (data) {
							console.log('Divi Options reset.');
						}
					});
				}
			});
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'rerun_automatic_setup',
				},
				success: function (data) {
					console.log("Initializing settings...");
					window.location.replace(autoSetup);
				}
			});

		});
		$("#asc-support-email").click(function () {
			if (!element_exists($("#asc-email"))) return;
			if (!validateEmail($("#asc-email").val())) {
				if (element_exists($("#te-error-message"))) $("#te-error-message").show();
				return;
			}
			if (!element_exists($("#asc-msg"))) return;
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'setup_support_email',
					sender: $("#asc-email").val(),
					msg: $("#asc-msg").val(),
				},
				success: function (data) {
					console.log("Sending support email...");
					$("#asc-email").val('');
					$("#asc-msg").val('');
				}
			});
		});
		$("#te-send").click(function () {
			if (!element_exists($("#te-email"))) return;
			if (!validateEmail($("#te-email").val())) {
				if (element_exists($("#te-error-message"))) $("#te-error-message").show();
				return;
			}
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'test_email',
					email: $("#te-email").val(),
				},
				success: function (data) {
					if (element_exists($("#te-error-message"))) $("#te-error-message").hide();
					if (element_exists($("#te-email-sent-message"))) $("#te-email-sent-message").show();
					console.log("Sending test email...");
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'setup_complete'
						},
						success: function (data) {
							console.log("DOCC Status Updated.");
							window.location.replace(setupComplete);
						}
					})
				}
			});
		});
	});
	// dependancy functions
	function gs_buttons() {
		let install_selector = "td[data-label='Install Button']";
		let activate_selector = "td[data-label='Activate Button']";

		if (element_exists($(install_selector)) || element_exists($(activate_selector))) {
			if (element_exists($("#gs-header-start"))) $("#gs-header-start").show();
			if (element_exists($("#gs-do-not-leave-page"))) $("#gs-do-not-leave-page").show();
			return;
		} else {
			if (element_exists($("#gs-header-continue"))) $("#gs-header-continue").show();
			if (element_exists($("#gs-header-instructions"))) $("#gs-header-instructions").show();
		}
	}
	function element_exists($element) {
		if ($element.length) return true;
		else return false;
	}
	function validateEmail(email) {
		const regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return regex.test(email);
	}
	function bool_to_int_template_object(object) {
		for (let i = 0; i < object.length; i++) {
			object[i]['autogenerated_title'] = (object[i]['autogenerated_title'] ? 1 : 0);
			object[i]['default'] = (object[i]['default'] ? 1 : 0)
			object[i]['enabled'] = (object[i]['enabled'] ? 1 : 0)
		}
		return object;
	}
	async function start_auto_setup() {
		let $add_user_roles = $.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'add_user_roles',
			}
		});
		let $import_pages = $.ajax({
			url: ajaxurl,
			type: 'POST',
			async: false,
			data: {
				action: 'import_pages',
			}
		});
		let $divi_options = $.ajax({
			url: etThemeOptions,
			type: 'POST',
			data: {}
		}).then(async function (data) {

			let etCorePortability = JSON.parse($(data).filter('#et-core-portability-js-extra')[0].innerHTML.slice(25).slice(0, -2));
			let nonces = etCorePortability['nonces'];
			let import_nonce = nonces['import'];

			const res = await fetch(pluginUrlPath + 'admin/dependencies/files/docc.DiviThemeOptions.json');
			const blob = await res.blob();
			const file = new File([blob], 'docc.DiviThemeOptions.json', { type: blob.type });
			var form = new FormData();
			form.append("action", "et_core_portability_import");
			form.append("nonce", import_nonce);
			form.append("file", file, "docc.DiviThemeOptions.json");
			form.append("context", "epanel");
			form.append("incoming_layout_duplicate_decision", "");
			form.append("include_global_presets", "false");
			let settings = {
				"url": adminAjax,
				"method": "POST",
				"timeout": 0,
				"processData": false,
				"mimeType": "multipart/form-data",
				"contentType": false,
				"data": form
			};
			return $.ajax(settings).done(function (response) {
				console.log(response);
			});
		});
		let $divi_templates = $.ajax({
			url: etThemeBuilder,
			type: 'POST',
			data: {}
		}).then(async function (data) {

			let et_config_string = $(data).find('#et-theme-builder-js-extra')[0].innerHTML.slice(31).slice(0, -2);
			let et_config = JSON.parse(et_config_string)['config'];
			let nonces = et_config['nonces'];
			let nonce = nonces['et_theme_builder_api_save'];

			const save_divi_templates = async () => {
				const file_json = await fetch(pluginUrlPath + 'admin/dependencies/files/docc.DiviThemeBuilderTemplates.json')
					.then(response => response.json())
					.then(data => data);

				let templates = bool_to_int_template_object(file_json['templates']); // export file has true/false but action requires 1/0 to enable templates

				return $.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'et_theme_builder_api_save',
						nonce: nonce,
						live: 1,
						templates,
					}
				}).then(function (data) {
					console.log(data);
				});
			}
			await save_divi_templates();
		});
		let $divi_customizations = $.ajax({
			url: etCustomizerOptionSetTheme,
			type: 'POST',
			data: {}
		}).then(async function (data) {
			let et_core_portability_js_extra = $(data).filter('#et-core-portability-js-extra')[0].innerHTML;
			// For some reason 'var etCorePortability' is included twice on this page so the resulting string needs to be cut in half
			let etCorePortabilityString = et_core_portability_js_extra.slice(0, et_core_portability_js_extra.length / 2);
			let etCorePortability = JSON.parse(etCorePortabilityString.slice(25).slice(0, -1));
			let nonces = etCorePortability['nonces'];
			let import_nonce = nonces['import'];

			const res = await fetch(pluginUrlPath + 'admin/dependencies/files/docc.DiviCustomizerSettings.json');
			const blob = await res.blob();
			const file = new File([blob], 'docc.DiviCustomizerSettings.json', { type: blob.type });
			var form = new FormData();
			form.append("action", "et_core_portability_import");
			form.append("nonce", import_nonce);
			form.append("file", file, "docc.DiviCustomizerSettings.json");
			form.append("context", "et_divi_mods");
			form.append("include_global_presets", "false");
			let settings = {
				"url": adminAjax,
				"method": "POST",
				"timeout": 0,
				"processData": false,
				"mimeType": "multipart/form-data",
				"contentType": false,
				"data": form
			};
			return $.ajax(settings).done(function (response) {
				console.log(response);
			});
		});
		let $gf_forms = $.ajax({
			url: ajaxurl,
			type: 'POST',
			async: false,
			data: {
				action: 'import_forms',
			}
		});
		let $gf_feeds = $.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'import_feeds',
			}
		});
		let $enable_partial_entries = $.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'gf_id',
				title: 'New Observation'
			}
		}).then(function (id) {
			return $.ajax({
				url: 'admin.php?page=gf_edit_forms&view=settings&subview=gravityformspartialentries&id=' + id,
				type: 'POST',
				data: {
					_gaddon_setting_enable: 1,
					enable: 'on',
					gf_feed_id: 31
				}
			}).then(function (data) {
				let nonce = $(data).find('input#_gravityformspartialentries_save_settings_nonce').val();
				return $.ajax({
					url: 'admin.php?page=gf_edit_forms&view=settings&subview=gravityformspartialentries&id=' + id,
					type: 'POST',
					data: {
						_gravityformspartialentries_save_settings_nonce: nonce,
						_gaddon_setting_enable: 1,
						enable: 'on',
						_gaddon_setting_warning_message: 'Please note that your information is saved on our server as you enter it.',
						_gaddon_setting_feed_condition_conditional_logic: 0,
						_gaddon_setting_feed_condition_conditional_logic_object: {},
						'gform-settings-save': 'Update Settings',
						gf_feed_id: 31
					}
				});
			});
		});
		let $misc_settings = $.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'misc_settings',
			}
		});

		var counter = 0;
		let total = 8;
		var errors = 0;
		var error_log = {};

		$.Deferred().resolve()
		.then(data => $add_user_roles)
		.catch(error => {
			errors++;
			error_log['User Roles'] = "Could not import user roles.";
		})
		.done(data => {
			if (element_exists($("#as-roles-status"))) $("#as-roles-status").show();
			var $progress = $("#as-setup-progress");
			if ($progress.children().length > 2) $progress.children().first().remove();
			$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") User Roles Imported</p>");
		})
		.then(data => $import_pages)
		.catch(error => {
			errors++;
			error_log['Pages'] = "Could not import pages.";
		})
		.done(data => {
			if (element_exists($("#as-pages-status"))) $("#as-pages-status").show();
			var $progress = $("#as-setup-progress");
			if ($progress.children().length > 2) $progress.children().first().remove();
			$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") Pages Imported</p>");
		})
		.then(data => $divi_options)
		.catch(error => {
			errors++;
			error_log['Divi Option'] = "Could not update Divi theme options.";
		})
		.done(data => {
			if (element_exists($("#as-options-status"))) $("#as-options-status").show();
			var $progress = $("#as-setup-progress");
			if ($progress.children().length > 2) $progress.children().first().remove();
			$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") Divi Options Updated</p>");
		})
		.then(data => $divi_templates)
		.catch(error => {
			errors++;
			error_log['Divi Templates'] = "Could not import Divi theme builder templates.";
		})
		.done(data => {
			if (element_exists($("#as-templates-status"))) $("#as-templates-status").show();
			var $progress = $("#as-setup-progress");
			if ($progress.children().length > 2) $progress.children().first().remove();
			$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") Divi Templates Imported</p>");
		})
		.then(data => $divi_customizations)
		.catch(error => {
			errors++;
			error_log['Divi Customizations'] = "Could not import Divi theme customizations.";
		})
		.done(data => {
			if (element_exists($("#as-customizations-status"))) $("#as-customizations-status").show();
			var $progress = $("#as-setup-progress");
			if ($progress.children().length > 2) $progress.children().first().remove();
			$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") Divi Customizations Imported</p>");
		})
		.then(data => $gf_forms)
		.catch(error => {
			errors++;
			error_log['Forms'] = "Could not import Gravity Forms form objects.";
		})
		.done(data => {
			if (element_exists($("#as-forms-status"))) $("#as-forms-status").show();
			var $progress = $("#as-setup-progress");
			if ($progress.children().length > 2) $progress.children().first().remove();
			$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") Gravity Forms Created</p>");
		})
		.then(data => $gf_feeds)
		.catch(error => {
			errors++;
			error_log['Feeds'] = "Could not import custom Gravity Forms feeds.";
		})
		.done(data => {
			if (element_exists($("#as-feeds-status"))) $("#as-feeds-status").show();
			var $progress = $("#as-setup-progress");
			if ($progress.children().length > 2) $progress.children().first().remove();
			$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") Custom Form Feeds Created</p>");
		})
		.then(data => $enable_partial_entries).catch(error => {
			errors++;
			error_log['Partial Entries'] = "Could not enable partial entries.";
		})
		.done(data => data)
		.then(data => $misc_settings)
		.catch(error => {
			errors++;
			error_log['Misc Settings'] = "Could not configure miscellaneous settings.";
		})
		.done(data => {
			if (element_exists($("#as-settings-status"))) $("#as-settings-status").show();
			var $progress = $("#as-setup-progress");
			if ($progress.children().length > 2) $progress.children().first().remove();
			$progress.append("<p>(" + (++counter).toString() + " / " + total.toString() + ") Settings Updated</p>");
			$progress = $("#as-setup-progress");
			if ($progress.children().length > 2) $progress.children().first().remove();
			$progress.append("<p>Setup complete. Errors encountered: " + errors.toString() + "</p>");

			if (element_exists($("#as-header-start"))) $("#as-header-start").hide();
			if (element_exists($("#as-header-continue"))) $("#as-header-continue").show();
		})
		.then(data => {
			return $.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'as_error_log',
					error_log: error_log
				}
			});
		})
		.then(data => {
			return $.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'dismiss_admin_notice_remote',
				}
			});
		})
		.then(data => {
			return $.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'dismiss_admin_notice_installed',
				}
			});
		});
	}

})(jQuery);