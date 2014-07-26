/*

Copyright 2014 Ole Jon Bjørkum

This file is part of SpotCommander.

SpotCommander is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

SpotCommander is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with SpotCommander.  If not, see <http://www.gnu.org/licenses/>.

*/

// Global variables

function setGlobalVariables(global_variables)
{
	global_variables = $.parseJSON(global_variables);

	// Project
	project_name = global_variables.project_name;
	project_version = parseFloat(global_variables.project_version);
	project_serial = parseInt(global_variables.project_serial);
	project_website = global_variables.project_website;
	project_developer = global_variables.project_developer;
	project_android_app_minimum_version = parseFloat(global_variables.project_android_app_minimum_version);

	// Error code
	project_error_code = parseInt(global_variables.project_error_code);

	// User agent
	ua = navigator.userAgent;

	// Feature detection
	ua_supports_csstransitions = (Modernizr.csstransitions && !shc(ua, 'DISABLE_CSSTRANSITIONS'));
	ua_supports_csstransforms3d = (Modernizr.csstransforms3d && !shc(ua, 'DISABLE_CSSTRANSFORMS3D'));
	ua_supports_devicemotion = (Modernizr.devicemotion);
	ua_supports_inputtype_range = (Modernizr.inputtypes.range);
	ua_supports_notifications = (Modernizr.notification);
	ua_supports_touch = (Modernizr.touch && !shc(ua, 'CrOS'));

	if(ua_supports_touch)
	{
		pointer_event = 'touchend';
		pointer_down_event = 'touchstart';
		pointer_move_event = 'touchmove';
		pointer_cancel_event = 'touchcancel';
		pointer_up_event = 'touchend';
	}
	else
	{
		pointer_event = 'mouseup';
		pointer_down_event = 'mousedown';
		pointer_move_event = 'mousemove';
		pointer_cancel_event = 'mousecancel';
		pointer_up_event = 'mouseup';
	}

	// Device & browser
	ua_is_supported = false;
	ua_is_webkit = (shc(ua, 'AppleWebKit'));
	ua_is_android = (shc(ua, 'Android'));
	ua_is_ios = (shc(ua, 'iPhone') || shc(ua, 'iPod') || shc(ua, 'iPad'));
	ua_is_os_x = (shc(ua, 'Macintosh; Intel Mac OS X'));
	ua_is_standalone = (ua_is_android && shc(ua, project_name) || ua_is_ios && window.navigator && ('standalone' in window.navigator) && window.navigator.standalone || ua_is_os_x && shc(ua, 'FluidApp'));
	ua_is_android_app = (ua_is_android && ua_is_standalone);
	ua_is_msie = (getMSIEVersion() >= 11);
	ua_is_pinnable_msie = (window.external && ('msIsSiteMode' in window.external));

	if(shc(ua, 'Windows Phone') || shc(ua, 'IEMobile') || shc(ua, 'ZuneWP7') || shc(ua, 'Tizen') || shc(ua, 'Bada') || shc(ua, 'BlackBerry') || shc(ua, 'webOS') || shc(ua, 'NokiaBrowser') || shc(ua, 'Symbian') || shc(ua, 'SymbOS') || shc(ua, 'Series60') || shc(ua, 'Series40') || shc(ua, 'Fennec') || shc(ua, 'Opera Mobi') || shc(ua, 'Opera Tablet') || shc(ua, 'Firefox') && shc(ua, 'Mobile') || shc(ua, 'Ubuntu') && shc(ua, 'Mobile'))
	{
		ua_is_supported = false;
	}
	else if(ua_is_android)
	{
		if(shc(ua, 'Android 2.3') || shc(ua, 'Android 4'))
		{
			if(ua_is_webkit) ua_is_supported = true;
		}
	}
	else if(ua_is_ios)
	{
		if(shc(ua, 'OS 5_') || shc(ua, 'OS 6_') || shc(ua, 'OS 7_'))
		{
			if(ua_is_webkit) ua_is_supported = true;
		}
	}
	else if(shc(ua, 'Chrome') || shc(ua, 'Firefox') || shc(ua, 'Safari') && shc(ua, 'Macintosh') || ua_is_msie)
	{
		ua_is_supported = true;
	}

	// Scrolling
	scrolling = false;
	scrolling_black_cover_div = false;

	// Pointer
	pointer_is_down = false;
	pointer_moved = false;
	pointer_moved_sensitivity = 10;

	// XHRs
	xhr_activity = new XMLHttpRequest();
	xhr_remote_control = new XMLHttpRequest();
	xhr_adjust_volume = new XMLHttpRequest();
	xhr_nowplaying = new XMLHttpRequest();
	xhr_cover_art = new XMLHttpRequest();

	// Intervals
	interval_motion = null;
	interval_nowplaying_auto_refresh = null;

	// Timeouts
	timeout_window_resize = null;
	timeout_scrolling = null;
	timeout_activity_loading = null;
	timeout_activity_error = null;
	timeout_activity_loaded_input_text = null;
	timeout_remote_control = null;
	timeout_nowplaying_error = null;
	timeout_nowplaying_volume_slider = null;
	timeout_nowplaying_auto_refresh = null;
	timeout_show_toast = null;
	timeout_hide_toast_first = null;
	timeout_hide_toast_second = null;
	timeout_notification = null;

	// Events
	var prefix = Modernizr.prefixed('transition');

	if(prefix == 'WebkitTransition')
	{
		event_transitionend = 'webkitTransitionEnd';
	}
	else if(prefix == 'msTransition')
	{
		event_transitionend = 'MSTransitionEnd';
	}
	else
	{
		event_transitionend = 'transitionend';
	}

	// Now playing
	nowplaying_refreshing = false;
	nowplaying_last_data = '';
	nowplaying_last_uri = '';
	nowplaying_cover_art_moving = false;

	// Desktop integration
	integrated_in_msie = false;

	// Notifications
	notification = null;

	// Settings
	var settings = [
		{ setting: 'settings_check_for_updates' , value: 'true' },
		{ setting: 'settings_nowplaying_refresh_interval' , value: '5' },
		{ setting: 'settings_volume_control' , value: 'spotify' },
		{ setting: 'settings_start_track_radio_simulation' , value: '5' },
		{ setting: 'settings_shake_to_skip' , value: 'false' },
		{ setting: 'settings_shake_sensitivity' , value: '30' },
		{ setting: 'settings_keyboard_shortcuts' , value: (ua_supports_touch) ? 'false' : 'true' },
		{ setting: 'settings_notifications' , value: 'false' },
		{ setting: 'settings_keep_screen_on' , value: 'false' },
		{ setting: 'settings_pause_on_incoming_call' , value: 'false' },
		{ setting: 'settings_flip_to_pause' , value: 'false' },
		{ setting: 'settings_persistent_notification' , value: 'false' },
		{ setting: 'settings_sort_playlists' , value: 'default' },
		{ setting: 'settings_sort_playlist_tracks' , value: 'default' },
		{ setting: 'settings_sort_library_tracks' , value: 'default' },
		{ setting: 'settings_sort_library_albums' , value: 'default' },
		{ setting: 'settings_sort_library_artists' , value: 'default' },
		{ setting: 'settings_sort_search_tracks' , value: 'default' }
	];

	for(var i = 0; i < settings.length; i++)
	{
		var cookie = { id: settings[i].setting, value: settings[i].value, expires: 3650 };
		if(!isCookie(cookie.id)) $.cookie(cookie.id, cookie.value, { expires: cookie.expires });
	}

	settings_check_for_updates = stringToBoolean($.cookie('settings_check_for_updates'));
	settings_nowplaying_refresh_interval = parseInt($.cookie('settings_nowplaying_refresh_interval'));
	settings_volume_control = $.cookie('settings_volume_control');
	settings_start_track_radio_simulation = $.cookie('settings_start_track_radio_simulation');
	settings_shake_to_skip = stringToBoolean($.cookie('settings_shake_to_skip'));
	settings_shake_sensitivity = parseInt($.cookie('settings_shake_sensitivity'));
	settings_keyboard_shortcuts = stringToBoolean($.cookie('settings_keyboard_shortcuts'));
	settings_notifications = stringToBoolean($.cookie('settings_notifications'));
	settings_keep_screen_on = stringToBoolean($.cookie('settings_keep_screen_on'));
	settings_pause_on_incoming_call = stringToBoolean($.cookie('settings_pause_on_incoming_call'));
	settings_flip_to_pause = stringToBoolean($.cookie('settings_flip_to_pause'));
	settings_persistent_notification = stringToBoolean($.cookie('settings_persistent_notification'));
}

// Window load

$(window).load(function()
{
	// Ajax settings
	$.ajaxSetup({ cache: false, timeout: 30000 });

	// Load
	$.get('main.php?global_variables', function(xhr_data)
	{
		setGlobalVariables(xhr_data);
		setCss();

		// Check for errors
		var error_code = checkForErrors();

		if(error_code != 0)
		{
			window.location.replace('error.php?code='+error_code);
			return;
		}

		// Resize
		$(window).on('resize', function()
		{
			if($(window).width() >= 1024)
			{
				$('div#menu_div').removeClass('show_menu_animation hide_menu_animation').css('visibility', '').css('left', '');
				$('div#top_actionbar_inner_left_div > div > div > div').removeClass('show_menu_img_animation hide_menu_img_animation');
				$('div#black_cover_activity_div').hide().removeClass('show_black_cover_activity_div_animation hide_black_cover_activity_div_animation').css('opacity', '');

			}

			hideNowplayingOverflowActions();
			hideNowplaying();

			clearTimeout(timeout_window_resize);

			timeout_window_resize = setTimeout(function()
			{
				setCss();
				setCoverArtSize();
				setCardVerticalSize();
			}, 500);
		});

		// Scrolling
		$(window).on('scroll', function()
		{
			scrolling = true;

			clearTimeout(timeout_scrolling);

			timeout_scrolling = setTimeout(function()
			{
				scrolling = false;
			}, 250);
		});

		// Pointer
		if(ua_supports_touch)
		{
			$(document).on(pointer_down_event, function(event)
			{
				pointer_points = event.originalEvent.touches.length;
				pointer_is_down = true;
				pointer_moved = (pointer_points != 1);
				pointer_gesture_done = false;

				pointer_start_x = event.originalEvent.touches[0].pageX;
				pointer_start_y = event.originalEvent.touches[0].pageY;

				pointer_edge = 25;
			});

			$(document).on(pointer_move_event, function(event)
			{
				if(typeof pointer_start_x == 'undefined' || typeof pointer_start_y == 'undefined') return;

				pointer_end_x = event.originalEvent.touches[0].pageX;
				pointer_end_y = event.originalEvent.touches[0].pageY;

				pointer_moved_x = pointer_end_x - pointer_start_x;
				pointer_moved_y = pointer_end_y - pointer_start_y;

				pointer_moved = (Math.abs(pointer_moved_x) > pointer_moved_sensitivity || Math.abs(pointer_moved_y) > pointer_moved_sensitivity);

				if(pointer_start_x < pointer_edge || isDisplayed('div#transparent_cover_div') || isDisplayed('div#black_cover_div') && !scrolling_black_cover_div || isDisplayed('div#black_cover_activity_div'))
				{
					event.preventDefault();
				}

				if(pointer_start_x < pointer_edge)
				{
					var gesture_trigger = window_width / 8;
					var gesture_block = 50;

					if(!pointer_gesture_done && pointer_moved_x > gesture_trigger && Math.abs(pointer_moved_y) < gesture_block)
					{
						showMenu();
						pointer_gesture_done = true;
					}
				}

				var gesture_trigger = - window_width / 8;
				var gesture_block = 50;

				if(!pointer_gesture_done && isDisplayed('div#black_cover_activity_div') && pointer_moved_x < gesture_trigger && Math.abs(pointer_moved_y) < gesture_block)
				{
					hideMenu();
					pointer_gesture_done = true;
				}

				if(ua_is_ios && ua_is_standalone)
				{
					if(pointer_start_x > window_width - pointer_edge)
					{
						event.preventDefault();

						var gesture_trigger = - window_width / 8;
						var gesture_block = 50;

						if(!pointer_gesture_done && !isDisplayed('div#black_cover_activity_div') && pointer_moved_x < gesture_trigger && Math.abs(pointer_moved_y) < gesture_block)
						{
							goBack();
							pointer_gesture_done = true;
						}
					}
				}
			});

			$(document).on(pointer_up_event, function()
			{
				pointer_is_down = false;
			});
		}
		else
		{
			$(document).on(pointer_down_event, function(event)
			{
				pointer_is_down = true;
				pointer_moved = false;

				pointer_start_x = event.pageX;
				pointer_start_y = event.pageY;
			});

			$(document).on(pointer_move_event, function(event)
			{
				if(typeof pointer_start_x == 'undefined' || typeof pointer_start_y == 'undefined') return;

				pointer_end_x = event.pageX;
				pointer_end_y = event.pageY;

				pointer_moved_x = pointer_end_x - pointer_start_x;
				pointer_moved_y = pointer_end_y - pointer_start_y;

				pointer_moved = (Math.abs(pointer_moved_x) > pointer_moved_sensitivity || Math.abs(pointer_moved_y) > pointer_moved_sensitivity);
			});

			$(document).on(pointer_up_event, function()
			{
				pointer_is_down = false;
			});
		}

		if(ua_supports_touch)
		{
			$(document).on(pointer_move_event, 'div#top_actionbar_div, div#bottom_actionbar_div, div#menu_div, div#black_cover_div', function(event)
			{
				event.preventDefault();
			});

			$(document).on(pointer_move_event, 'div#nowplaying_div', function(event)
			{
				if($(event.target).attr('id') != 'nowplaying_volume_slider')  event.preventDefault();
			});

			$(document).on(pointer_down_event, 'div#bottom_actionbar_div', function(event)
			{
				pointer_bottombar_start_x = event.originalEvent.touches[0].pageX;
				pointer_bottombar_start_y = event.originalEvent.touches[0].pageY;
			});

			$(document).on(pointer_move_event, 'div#bottom_actionbar_div', function(event)
			{
				pointer_bottombar_end_x = event.originalEvent.touches[0].pageX;
				pointer_bottombar_end_y = event.originalEvent.touches[0].pageY;

				pointer_bottombar_moved_x = pointer_bottombar_end_x - pointer_bottombar_start_x;
				pointer_bottombar_moved_y = pointer_bottombar_end_y - pointer_bottombar_start_y;

				var gesture_trigger = - window_height / 8;
				var gesture_block = 50;

				if(!pointer_gesture_done && pointer_bottombar_moved_y < gesture_trigger && Math.abs(pointer_bottombar_moved_x) < gesture_block)
				{
					showNowplaying();
					pointer_gesture_done = true;
				}
			});

			$(document).on(pointer_down_event, 'div#nowplaying_div', function(event)
			{
				pointer_nowplaying_start_x = event.originalEvent.touches[0].pageX;
				pointer_nowplaying_start_y = event.originalEvent.touches[0].pageY;
			});

			$(document).on(pointer_move_event, 'div#nowplaying_div', function(event)
			{
				pointer_nowplaying_end_x = event.originalEvent.touches[0].pageX;
				pointer_nowplaying_end_y = event.originalEvent.touches[0].pageY;

				pointer_nowplaying_moved_x = pointer_nowplaying_end_x - pointer_nowplaying_start_x;
				pointer_nowplaying_moved_y = pointer_nowplaying_end_y - pointer_nowplaying_start_y;

				var gesture_trigger = window_height / 8;
				var gesture_block = 50;

				if(!pointer_gesture_done && !nowplaying_cover_art_moving && $(event.target).attr('id') != 'nowplaying_volume_slider' && pointer_nowplaying_moved_y > gesture_trigger && Math.abs(pointer_nowplaying_moved_x) < gesture_block)
				{	
					hideNowplaying();
					pointer_gesture_done = true;
				}
			});

			$(document).on(pointer_down_event, 'div#nowplaying_cover_art_div', function(event)
			{
				pointer_cover_art_start_x = event.originalEvent.touches[0].pageX;
				pointer_cover_art_start_y = event.originalEvent.touches[0].pageY;

				pointer_cover_art_moved_x = 0;
				pointer_cover_art_moved_y = 0;

				$(this).css('transition', '').css('transform', '').css('-webkit-transition', '').css('-webkit-transform', '').css('-moz-transition', '').css('-moz-transform', '').css('-ms-transition', '').css('-ms-transform', '').css('left', '');
			});

			$(document).on(pointer_move_event, 'div#nowplaying_cover_art_div', function(event)
			{
				pointer_cover_art_end_x = event.originalEvent.touches[0].pageX;
				pointer_cover_art_end_y = event.originalEvent.touches[0].pageY;

				pointer_cover_art_moved_x = pointer_cover_art_end_x - pointer_cover_art_start_x;
				pointer_cover_art_moved_y = pointer_cover_art_end_y - pointer_cover_art_start_y;

				var cover_art_move_treshold = 25;
				var cover_art_move = pointer_cover_art_moved_x;

				if(Math.abs(cover_art_move) > cover_art_move_treshold || nowplaying_cover_art_moving)
				{
					nowplaying_cover_art_moving = true;

					if(ua_supports_csstransitions && ua_supports_csstransforms3d)
					{
						var scale_variable = Math.abs(cover_art_move);
						var scale_constant = 0.5 / window_width;
						var scale = 1 - (scale_variable * scale_constant);

						$(this).css('transform', 'translate3d('+cover_art_move+'px, 0, 0) scale3d('+scale+', '+scale+', 1)').css('-webkit-transform', 'translate3d('+cover_art_move+'px, 0, 0) scale3d('+scale+', '+scale+', 1)').css('-moz-transform', 'translate3d('+cover_art_move+'px, 0, 0) scale3d('+scale+', '+scale+', 1)').css('-ms-transform', 'translate3d('+cover_art_move+'px, 0, 0) scale3d('+scale+', '+scale+', 1)');
					}
					else
					{
						$(this).css('left', ''+cover_art_move+'px');
					}
				}
			});

			$(document).on(pointer_up_event, 'div#nowplaying_cover_art_div', function(event)
			{
				nowplaying_cover_art_moving = false;

				var gesture_trigger = - window_width / 2;

				if(pointer_cover_art_moved_x < gesture_trigger)
				{
					remoteControl('next');
				}
				else
				{
					if(ua_supports_csstransitions && ua_supports_csstransforms3d)
					{
						$(this).css('transition', 'transform 0.25s cubic-bezier(0.190, 1.000, 0.220, 1.000)').css('transform', 'translate3d(0, 0, 0) scale3d(1, 1, 1)').css('-webkit-transition', '-webkit-transform 0.25s cubic-bezier(0.190, 1.000, 0.220, 1.000)').css('-webkit-transform', 'translate3d(0, 0, 0) scale3d(1, 1, 1)').css('-moz-transition', '-moz-transform 0.25s cubic-bezier(0.190, 1.000, 0.220, 1.000)').css('-moz-transform', 'translate3d(0, 0, 0) scale3d(1, 1, 1)').css('-ms-transition', '-ms-transform 0.25s cubic-bezier(0.190, 1.000, 0.220, 1.000)').css('-ms-transform', 'translate3d(0, 0, 0) scale3d(1, 1, 1)');
					}
					else
					{
						$(this).stop().animate({ left: '0' }, 250, 'easeOutExpo');
					}
				}
			});
		}
		else
		{
			document.addEventListener('contextmenu', function(event)
			{
				event.preventDefault();
			}, false);

			$(document).on(pointer_event, 'div.list_item_div', function(event)
			{
				if(pointer_moved || scrolling || typeof event.which != 'undefined' && event.which !== 3) return;

				var element = this;
				var data = $('div.show_actions_dialog_div', element).data();

				if(typeof data != 'undefined') showActionsDialog($.parseJSON($.base64.decode(data.dialogactions)));
			});
		}

		// Motion
		if(settings_shake_to_skip && ua_supports_devicemotion)
		{
			var motion_ready = true;
			var motion_x1 = 0, motion_y1 = 0, motion_z1 = 0, motion_x2 = 0, motion_y2 = 0, motion_z2 = 0;

			window.addEventListener('devicemotion', function(event)
			{
				motion_x1 = event.accelerationIncludingGravity.x, motion_y1 = event.accelerationIncludingGravity.y, motion_z1 = event.accelerationIncludingGravity.z;
			}, false);

			interval_motion = setInterval(function()
			{
				motion_change = Math.abs(motion_x1 - motion_x2 + motion_y1 - motion_y2 + motion_z1 - motion_z2);

				if(motion_ready && motion_change > settings_shake_sensitivity)
				{
					motion_ready = false;

					showToast('Shake detected, playing next track', 2);

					remoteControl('next');

					setTimeout(function()
					{
						motion_ready = true;
					}, 500);
				}

				motion_x2 = motion_x1, motion_y2 = motion_y1, motion_z2 = motion_z1;
			}, 250);
		}

		// Highlight
		$(document).on(pointer_down_event, 'div.actions_div, span.actions_span', function()
		{
			var element = this;
			var data = $(element).data();

			if($(element).attr('data-highlightclass')) $(element).addClass(data.highlightclass);

			if($(element).attr('data-highlightotherelement'))
			{
				var parent = $(element).parents(data.highlightotherelementparent);
				$(data.highlightotherelement, parent).addClass(data.highlightotherelementclass);
			}
		});

		$(document).on(pointer_move_event+' '+pointer_cancel_event, 'div.actions_div, span.actions_span', function(event)
		{
			if(event.type == pointer_move_event && !pointer_moved && !scrolling) return;

			var element = this;
			var data = $(element).data();

			if($(element).attr('data-highlightclass')) $(element).removeClass(data.highlightclass);

			if($(element).attr('data-highlightotherelement'))
			{
				var parent = $(element).parents(data.highlightotherelementparent);
				$(data.highlightotherelement, parent).removeClass(data.highlightotherelementclass);
			}
		});

		$(document).on(pointer_up_event+' mouseout', 'div.actions_div, span.actions_span', function()
		{
			var element = this;
			var data = $(element).data();

			if($(element).attr('data-highlightclass')) $(element).removeClass(data.highlightclass);

			if($(element).attr('data-highlightotherelement'))
			{
				var parent = $(element).parents(data.highlightotherelementparent);
				$(data.highlightotherelement, parent).removeClass(data.highlightotherelementclass);
			}
		});

		// Actions
		$(document).on(pointer_event, 'div.actions_div, span.actions_span', function(event)
		{
			if(pointer_moved || scrolling || !ua_supports_touch && typeof(event.which) != 'undefined' && event.which !== 1) return;

			var element = this;
			var actions = $(element).data('actions').split(' ');
			var data = $(element).data();

			for(var i = 0; i < actions.length; i++)
			{
				var action = actions[i];

				if(action == 'change_activity')
				{
					changeActivity(data.activity, data.subactivity, data.args);
				}
				else if(action == 'replace_activity')
				{
					replaceActivity(data.activity, data.subactivity, data.args);
				}
				else if(action == 'refresh_activity')
				{
					refreshActivity();
				}
				else if(action == 'reload_activity')
				{
					reloadActivity();
				}
				else if(action == 'show_activity_overflow_actions')
				{
					showActivityOverflowActions();
				}
				else if(action == 'hide_activity_overflow_actions')
				{
					hideActivityOverflowActions();
				}
				else if(action == 'open_external_activity')
				{
					openExternalActivity(data.uri);
				}
				else if(action == 'change_activity_if_is_authorized_with_spotify')
				{
					changeActivityIfIsAuthorizedWithSpotify(data.activity, data.subactivity, data.args, data.isauthorizedwithspotify);
				}
				else if(action == 'scroll_to_top')
				{
					scrollToTop(true);
				}
				else if(action == 'toggle_menu')
				{
					toggleMenu();
				}
				else if(action == 'show_actions_dialog')
				{
					showActionsDialog($.parseJSON($.base64.decode(data.dialogactions)));
				}
				else if(action == 'show_details_dialog')
				{
					showDetailsDialog($.parseJSON($.base64.decode(data.dialogdetails)));
				}
				else if(action == 'hide_dialog')
				{
					hideDialog();
				}
				else if(action == 'hide_transparent_cover_div')
				{
					hideActivityOverflowActions();
					hideNowplayingOverflowActions();
				}
				else if(action == 'hide_black_cover_activity_div')
				{
					hideMenu();
				}
				else if(action == 'toggle_list_item_actions')
				{
					var list_item_div = $(element).parent();
					var list_item_main_div = $(element);
					var list_item_main_actions_arrow_div = $('div.list_item_main_actions_arrow_div', list_item_main_div);
					var list_item_main_corner_arrow_div = $('div.list_item_main_corner_arrow_div', list_item_main_div);
					var list_item_actions_div = $('div.list_item_actions_div', list_item_div);

					var is_hidden = $(list_item_actions_div).is(':hidden');

					$('div.list_item_div').css('border-bottom-width', '');
					$('div.list_item_main_corner_arrow_div').show();
					$('div.list_item_main_actions_arrow_div').hide();
					$('div.list_item_actions_div').hide();

					if(is_hidden)
					{
						$(list_item_div).css('border-bottom-width', '0');

						$(list_item_main_corner_arrow_div).hide();
						$(list_item_main_actions_arrow_div).show();

						hideDiv(list_item_actions_div);
						$(list_item_actions_div).show();
						fadeInDiv(list_item_actions_div);
					}
				}
				else if(action == 'show_all_list_items')
				{
					$('div.'+data.items).show();
					$(element).hide();
				}
				else if(action == 'submit_form')
				{
					submitForm(data.form);
				}
				else if(action == 'confirm_authorize_with_spotify')
				{
					confirmAuthorizeWithSpotify();
				}
				else if(action == 'authorize_with_spotify')
				{
					authorizeWithSpotify();
				}
				else if(action == 'confirm_deauthorize_from_spotify')
				{
					confirmDeauthorizeFromSpotify();
				}
				else if(action == 'deauthorize_from_spotify')
				{
					deauthorizeFromSpotify();
				}
				else if(action == 'apply_settings')
				{
					applySettings();
				}
				else if(action == 'check_for_updates')
				{
					checkForUpdates('manual');
				}
				else if(action == 'change_native_app_computer')
				{
					changeNativeAppComputer()
				}
				else if(action == 'confirm_change_native_app_computer')
				{
					confirmChangeNativeAppComputer()
				}
				else if(action == 'confirm_remove_all_playlists')
				{
					confirmRemoveAllPlaylists();
				}
				else if(action == 'remove_all_playlists')
				{
					removeAllPlaylists();
				}
				else if(action == 'confirm_remove_all_saved_items')
				{
					confirmRemoveAllSavedItems();
				}
				else if(action == 'remove_all_saved_items')
				{
					removeAllSavedItems();
				}
				else if(action == 'confirm_clear_cache')
				{
					confirmClearCache();
				}
				else if(action == 'clear_cache')
				{
					clearCache();
				}
				else if(action == 'confirm_restore_to_default')
				{
					confirmRestoreToDefault();
				}
				else if(action == 'restore_to_default')
				{
					restoreToDefault();
				}
				else if(action == 'confirm_suspend_computer')
				{
					confirmSuspendComputer();
				}
				else if(action == 'suspend_computer')
				{
					suspendComputer();
				}
				else if(action == 'confirm_shut_down_computer')
				{
					confirmShutDownComputer();
				}
				else if(action == 'shut_down_computer')
				{
					shutDownComputer();
				}
				else if(action == 'toggle_nowplaying')
				{
					toggleNowplaying();
				}
				else if(action == 'refresh_nowplaying')
				{
					startRefreshNowplaying();
					refreshNowplaying('manual');
				}
				else if(action == 'show_nowplaying_overflow_actions')
				{
					showNowplayingOverflowActions();
				}
				else if(action == 'hide_nowplaying_overflow_actions')
				{
					hideNowplayingOverflowActions();
				}
				else if(action == 'remote_control')
				{
					remoteControl(data.remotecontrol);
				}
				else if(action == 'adjust_volume')
				{
					adjustVolume(data.volume);
				}
				else if(action == 'adjust_volume_control')
				{
					adjustVolumeControl(data.volumecontrol);
				}
				else if(action == 'toggle_shuffle_repeat')
				{
					toggleShuffleRepeat(data.remotecontrol);
				}
				else if(action == 'play_uri')
				{
					playUri(data.uri);
				}
				else if(action == 'shuffle_play_uri')
				{
					shufflePlayUri(data.uri);
				}
				else if(action == 'start_track_radio')
				{
					startTrackRadio(data.uri, data.playfirst);
				}
				else if(action == 'clear_recently_played')
				{
					clearRecentlyPlayed();
				}
				else if(action == 'queue_uri')
				{
					queueUri(data.artist, data.title, data.uri);
				}
				else if(action == 'queue_uris')
				{
					queueUris(data.uris, data.randomly);
				}
				else if(action == 'move_queued_uri')
				{
					moveQueuedUri(data.id, data.sortorder, data.direction);
				}
				else if(action == 'remove_from_queue')
				{
					removeFromQueue(data.id, data.sortorder);
				}
				else if(action == 'clear_queue')
				{
					clearQueue();
				}
				else if(action == 'add_to_playlist')
				{
					addToPlaylist(data.title, data.uri, data.isauthorizedwithspotify);
				}
				else if(action == 'add_uris_to_playlist')
				{
					addUrisToPlaylist(data.uri, data.uris);
				}
				else if(action == 'browse_playlist')
				{
					browsePlaylist(data.uri, data.isauthorizedwithspotify);
				}
				else if(action == 'confirm_import_spotify_playlists')
				{
					confirmImportSpotifyPlaylists(data.isauthorizedwithspotify);
				}
				else if(action == 'import_spotify_playlists')
				{
					importSpotifyPlaylists();
				}
				else if(action == 'import_playlist')
				{
					importPlaylists(data.uri);
				}
				else if(action == 'remove_playlist')
				{
					removePlaylist(data.id);
				}
				else if(action == 'save')
				{
					save(data.artist, data.title, data.uri, data.isauthorizedwithspotify, element);
				}
				else if(action == 'remove')
				{
					remove(data.uri, data.isauthorizedwithspotify);
				}
				else if(action == 'confirm_import_saved_spotify_tracks')
				{
					confirmImportSavedSpotifyTracks(data.isauthorizedwithspotify);
				}
				else if(action == 'import_saved_spotify_tracks')
				{
					importSavedSpotifyTracks();
				}
				else if(action == 'get_search')
				{
					getSearch(data.string);
				}
				else if(action == 'clear_search_history')
				{
					clearSearchHistory();
				}
				else if(action == 'browse_album')
				{
					browseAlbum(data.uri);
				}
				else if(action == 'browse_artist')
				{
					browseArtist(data.uri);
				}
				else if(action == 'get_artist_biography')
				{
					getArtistBiography(data.artist);
				}
				else if(action == 'resize_cover_art')
				{
					if(ua_supports_csstransitions) $(element).addClass('show_hide_cover_art_animation');

					var container_width = $('div#cover_art_div').outerWidth();
					var width = $(element).data('width');
					var height = $(element).data('height');
					var minimum_height = $(element).height();
					var resized = $(element).data('resized');

					if(resized)
					{
						$(element).height('').data('resized', false);
					}
					else
					{
						if(width > container_width)
						{
							var ratio = container_width / width;
							var height = Math.floor(height * ratio);

							if(height > minimum_height) $(element).height(height);
						}
						else
						{
							$(element).height(height);
						}

						$(element).data('resized', true);
					}
				}
				else if(action == 'share_uri')
				{
					shareUri(data.title, data.uri);
				}
				else if(action == 'set_cookie')
				{
					$.cookie(data.cookieid, data.cookievalue, { expires: parseInt(data.cookieexpires) });
				}
			}	
		});

		// Forms
		$(document).on('submit', 'form', function(event)
		{
			event.preventDefault();

			var element = this;
			var id = $(element).attr('id');

			if(id == 'import_playlists_form')
			{
				importPlaylists($('input:text#import_playlists_uris_input').val());
			}
			else if(id == 'create_playlist_form')
			{
				var name = $('input:text#create_playlist_name_input').val();
				var make_public = ($('input:checkbox#create_playlist_make_public_input').prop('checked')) ? 'true' : 'false';

				createPlaylist(name, make_public);
			}
			else if(id == 'search_form')
			{
				getSearch(encodeURIComponent($('input:text#search_input').val()));
			}
		});

		// Text fields
		$(document).on('focus blur', 'input:text', function(event)
		{
			var element = this;
			var value = $(element).val();
			var hint = $(element).data('hint');

			if(event.type == 'focusin' && value == hint)
			{
				$(element).val('').addClass('focused_text_input');
			}
			else if(event.type == 'focusout' && value == '')
			{
				$(element).val(hint).removeClass('focused_text_input');
			}
		});

		// Volume slider
		$(document).on('input change', 'input#nowplaying_volume_slider', function(event)
		{
			if(event.type == 'input' && ua_is_msie || event.type == 'change' && !ua_is_msie) return;

			var element = this;
			var value = $(element).val();

			$('span#nowplaying_volume_level_span').html(value);

			autoRefreshNowplaying('reset');

			clearTimeout(timeout_nowplaying_volume_slider);

			timeout_nowplaying_volume_slider = setTimeout(function()
			{
				$(element).blur();

				adjustVolume(value);
			}, 250);
		});

		// Drop-down lists and checkboxes
		$(document).on('change', 'select, input:checkbox', function()
		{
			var element = this;

			if($(element).hasClass('setting_select'))
			{
				var setting = $(element).attr('name');
				var value = $(element).val();

				saveSetting(setting, value);
			}
			else if($(element).hasClass('setting_checkbox'))
			{
				var setting = $(element).attr('name');
				var value = ($(element).prop('checked')) ? 'true' : 'false';

				saveSetting(setting, value);

				if(setting == 'settings_notifications' && value == 'true') requestNotificationsPermission();
			}
		});

		// Hash change
		$(window).bind('hashchange', function()
		{
			showActivity();
		});

		// Load activity
		var cookie = { id: 'installed_'+project_version, value: getCurrentTime(), expires: 3650 };

		if(!isCookie(cookie.id)) $.cookie(cookie.id, cookie.value, { expires: cookie.expires });

		if(!ua_supports_inputtype_range || ua_is_ios && shc(ua, 'OS 5_'))
		{
			$('div#nowplaying_volume_buttons_div').show();
		}
		else
		{
			$('div#nowplaying_volume_slider_div').show();
		}

		var cookie = { id: 'current_activity_'+project_version };

		if(ua_is_ios && ua_is_standalone && isCookie(cookie.id))
		{
			var activity = $.parseJSON($.cookie(cookie.id));

			activityLoading();
			changeActivity(activity.activity, activity.subactivity, activity.args);
		}
		else
		{
			showActivity();
		}

		if(settings_keyboard_shortcuts) enableKeyboardShortcuts();

		if(ua_is_pinnable_msie) integrateInMSIE();

		setTimeout(function()
		{
			nativeAppLoad(false);

			startRefreshNowplaying();
			refreshNowplaying('manual');

			autoRefreshNowplaying('start');
		}, 1000);
	});
});
