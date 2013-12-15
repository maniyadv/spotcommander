/*

Copyright 2013 Ole Jon Bjørkum

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
	project_name = global_variables[0];
	project_version = parseFloat(global_variables[1]);
	project_serial = parseInt(global_variables[2]);
	project_website = global_variables[3];
	project_developer = global_variables[4];
	project_android_app_minimum_version = parseFloat(global_variables[5]);

	// Error code
	error_code = parseInt(global_variables[6]);

	// User agent
	ua = navigator.userAgent;

	// Feature detection
	ua_supports_csstransitions = (Modernizr.csstransitions && !shc(ua, 'DISABLE_CSSTRANSITIONS'));
	ua_supports_csstransforms3d = (Modernizr.csstransforms3d && !shc(ua, 'DISABLE_CSSTRANSFORMS3D'));
	ua_supports_devicemotion = (Modernizr.devicemotion);
	ua_supports_inputtype_range = (Modernizr.inputtypes.range);
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
	ua_is_android = (shc(ua, 'Android'));
	ua_is_ios = (shc(ua, 'iPhone') || shc(ua, 'iPod') || shc(ua, 'iPad'));
	ua_is_standalone = (ua_is_android && shc(ua, project_name) || ua_is_ios && window.navigator && ('standalone' in window.navigator) && window.navigator.standalone);
	ua_is_android_app = (ua_is_android && ua_is_standalone);
	ua_is_webkit = (shc(ua, 'AppleWebKit'));
	ua_is_ubuntu_unity = (window.external && ('getUnityObject' in window.external));
	ua_is_pinnable_msie = (window.external && ('msIsSiteMode' in window.external));

	if(shc(ua, 'Windows Phone') || shc(ua, 'IEMobile') || shc(ua, 'ZuneWP7') || shc(ua, 'Bada') || shc(ua, 'BlackBerry') || shc(ua, 'webOS') || shc(ua, 'NokiaBrowser') || shc(ua, 'Symbian') || shc(ua, 'SymbOS') || shc(ua, 'Series60') || shc(ua, 'Series40') || shc(ua, 'Fennec') || shc(ua, 'Opera Mobi') || shc(ua, 'Opera Tablet') || shc(ua, 'Firefox') && shc(ua, 'Mobile'))
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
	else if(shc(ua, 'Chrome') || shc(ua, 'Firefox') || shc(ua, 'Safari') && shc(ua, 'Macintosh'))
	{
		ua_is_supported = true;
	}

	// Desktop integration
	integrated_in_ubuntu_unity = false;
	integrated_in_msie = false;

	// Scrolling
	scrolling = false;

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

	// Settings
	var settings = [
		{ setting: 'settings_check_for_updates' , value: 'true' },
		{ setting: 'settings_region' , value: 'ALL' },
		{ setting: 'settings_nowplaying_refresh_interval' , value: '5' },
		{ setting: 'settings_volume_control' , value: 'spotify' },
		{ setting: 'settings_start_track_radio_simulation' , value: '5' },
		{ setting: 'settings_motion_gestures' , value: 'false' },
		{ setting: 'settings_motion_sensitivity' , value: '30' },
		{ setting: 'settings_keyboard_shortcuts' , value: (ua_supports_touch) ? 'false' : 'true' },
		{ setting: 'settings_keep_screen_on' , value: 'false' },
		{ setting: 'settings_pause_on_incoming_call' , value: 'false' },
		{ setting: 'settings_flip_to_pause' , value: 'false' },
		{ setting: 'settings_persistent_notification' , value: 'false' },
		{ setting: 'settings_sort_playlists' , value: 'default' },
		{ setting: 'settings_sort_playlist_tracks' , value: 'default' },
		{ setting: 'settings_sort_starred_tracks' , value: 'default' },
		{ setting: 'settings_sort_starred_albums' , value: 'default' },
		{ setting: 'settings_sort_search_tracks' , value: 'default' },
		{ setting: 'settings_sort_search_albums' , value: 'default' },
		{ setting: 'settings_sort_album_tracks' , value: 'default' },
		{ setting: 'settings_sort_artist_tracks' , value: 'default' },
		{ setting: 'settings_sort_artist_albums' , value: 'default' },
		{ setting: 'settings_sort_artist_appears_on_albums' , value: 'default' }
	];

	for(var i = 0; i < settings.length; i++)
	{
		var cookie = { id: settings[i].setting, value: settings[i].value, expires: 36500 };
		if(!isCookie(cookie.id)) $.cookie(cookie.id, cookie.value, { expires: cookie.expires });
	}

	settings_check_for_updates = stringToBoolean($.cookie('settings_check_for_updates'));
	settings_region = $.cookie('settings_region');
	settings_nowplaying_refresh_interval = parseInt($.cookie('settings_nowplaying_refresh_interval'));
	settings_volume_control = $.cookie('settings_volume_control');
	settings_start_track_radio_simulation = $.cookie('settings_start_track_radio_simulation');
	settings_motion_gestures = stringToBoolean($.cookie('settings_motion_gestures'));
	settings_motion_sensitivity = parseInt($.cookie('settings_motion_sensitivity'));
	settings_keyboard_shortcuts = stringToBoolean($.cookie('settings_keyboard_shortcuts'));
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
		var code = checkForErrors();

		if(code != 0)
		{
			window.location.replace('error.php?code='+code);
			return;
		}

		// Resize
		$(window).on('resize', function()
		{
			hideActivityOverflowActions();
			hideMenu();
			hideNowplayingOverflowActions();
			hideNowplaying();

			clearTimeout(timeout_window_resize);

			timeout_window_resize = setTimeout(function()
			{
				setCss();
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

				if(pointer_start_x < pointer_edge || isDisplayed('div#transparent_cover_div') || isDisplayed('div#black_cover_div') || isVisible('div#nowplaying_div'))
				{
					if($(event.target).attr('id') != 'nowplaying_volume_slider') event.preventDefault();
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

				if(ua_is_ios && ua_is_standalone)
				{
					var activity_height = $('div#activity_div').outerHeight();

					if(window_height > activity_height && !isVisible('div#nowplaying_div')) event.preventDefault();

					if(pointer_start_x > window_width - pointer_edge)
					{
						event.preventDefault();

						var gesture_trigger = - window_width / 8;
						var gesture_block = 50;

						if(!pointer_gesture_done && pointer_moved_x < gesture_trigger && Math.abs(pointer_moved_y) < gesture_block)
						{
							goBack();
							pointer_gesture_done = true;
						}
					}
				}
			});

			$(document).on(pointer_cancel_event, function(event)
			{
				pointer_moved = true;
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
			$(document).on(pointer_move_event, 'div#top_actionbar_div, div#bottom_actionbar_div', function(event)
			{
				event.preventDefault();
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

		// Motion
		if(settings_motion_gestures && ua_supports_devicemotion)
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

				if(motion_ready && motion_change > settings_motion_sensitivity)
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
		$(document).on(pointer_event, 'div.actions_div, span.actions_span', function()
		{
			if(pointer_moved || scrolling) return;

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
				else if(action == 'scroll_to_top')
				{
					scrollToTop(true);
				}
				else if(action == 'show_menu')
				{
					showMenu();
				}
				else if(action == 'show_actions_dialog')
				{
					showActionsDialog(data.dialogactions);					
				}
				else if(action == 'show_details_dialog')
				{
					showDetailsDialog(data.dialogdetails);					
				}
				else if(action == 'hide_dialog')
				{
					hideDialog();					
				}
				else if(action == 'hide_transparent_cover_div')
				{
					hideMenu();
					hideActivityOverflowActions();
					hideNowplayingOverflowActions();
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
				else if(action == 'play_uri_randomly')
				{
					playUriRandomly(data.uri);
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
				else if(action == 'add_playlist')
				{
					addPlaylists(data.uri);				
				}
				else if(action == 'remove_playlist')
				{
					removePlaylist(data.id);				
				}
				else if(action == 'confirm_add_spotify_playlists')
				{
					confirmAddSpotifyPlaylists();
				}
				else if(action == 'add_spotify_playlists')
				{
					addSpotifyPlaylists();
				}
				else if(action == 'star_uri')
				{
					if($('div.img_div', element).length)
					{
						if($('div.img_div', element).hasClass('star_24_img_div'))
						{
							starUri(data.type, data.artist, data.title, data.uri, false);
							$('div.img_div', element).removeClass('star_24_img_div').addClass('unstar_24_img_div');
						}
						else if($('div.img_div', element).hasClass('unstar_24_img_div'))
						{
							unstarUri(data.uri, false);
							$('div.img_div', element).removeClass('unstar_24_img_div').addClass('star_24_img_div');
						}
					}
					else
					{
						var html = $(element).html();

						if(html.indexOf('Star') != -1)
						{
							starUri(data.type, data.artist, data.title, data.uri, true);
							$(element).html(html.replace('Star', 'Unstar'));
						}
						else
						{
							unstarUri(data.uri, true);
							$(element).html(html.replace('Unstar', 'Star'));
						}	
					}						
				}
				else if(action == 'unstar_uri')
				{
					unstarUri(data.uri, false);					
				}
				else if(action == 'confirm_import_starred_spotify_tracks')
				{
					confirmImportStarredSpotifyTracks();
				}
				else if(action == 'import_starred_spotify_tracks')
				{
					importStarredSpotifyTracks();
				}
				else if(action == 'search_spotify')
				{
					searchSpotify(data.string);					
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
				else if(action == 'resize_cover_art')
				{
					if(ua_supports_csstransitions && !ua_is_android && !$(element).hasClass('show_hide_cover_art_animation')) $(element).addClass('show_hide_cover_art_animation');

					var width = $(element).width();
					var height = $(element).height();
					var max_height = 640;

					if(width > max_height)
					{
						if(height < max_height)
						{
							$(element).css('height', max_height+'px');
						}
						else
						{
							$(element).css('height', '');
						}
					}
					else
					{
						if(height < width)
						{
							$(element).css('height', width+'px');
						}
						else
						{
							$(element).css('height', '');
						}
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

			if(id == 'add_playlists_form')
			{
				addPlaylists($('input:text#add_playlists_uris_input').val());
			}
			else if(id == 'import_starred_tracks_form')
			{
				importStarredTracks($('input:text#import_starred_tracks_uris_input').val());
			}
			else if(id == 'search_form')
			{
				searchSpotify(encodeURIComponent($('input:text#search_input').val()));
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
				$(element).val('').css('color', '#000');
			}
			else if(event.type == 'focusout' && value == '')
			{
				$(element).val(hint).css('color', '');
			}
		});

		// Sliders, drop-down lists & checkboxes
		$(document).on('change', 'input#nowplaying_volume_slider, select, input:checkbox', function()
		{
			var element = this;
			var id = $(element).attr('id');

			if(id == 'nowplaying_volume_slider')
			{
				var value = $(element).val();

				$('span#nowplaying_volume_level_span').html(value);

				autoRefreshNowplaying('reset');

				clearTimeout(timeout_nowplaying_volume_slider);

				timeout_nowplaying_volume_slider = setTimeout(function()
				{
					adjustVolume(value);
				}, 250);
			}
			else if($(element).hasClass('setting_select'))
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
			}
		});

		// Hash change
		$(window).bind('hashchange', function()
		{
			showActivity();
		});

		// Load activity
		var cookie = { id: 'installed', value: getCurrentTime(), expires: 36500 };

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

		if(ua_is_ubuntu_unity)
		{
			integrateInUbuntuUnity();
		}
		else if(ua_is_pinnable_msie)
		{
			integrateInMSIE();
		}

		setTimeout(function()
		{
			nativeAppLoad(false);

			startRefreshNowplaying();
			refreshNowplaying('manual');

			autoRefreshNowplaying('start');
		}, 1000);
	});
});
