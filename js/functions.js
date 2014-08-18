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

// Activities

function showActivity()
{
	var activity = getActivity();

	activityLoading();

	xhr_activity = $.get(activity.activity+'.php?'+activity.subactivity+'&'+activity.args, function(xhr_data)
	{
		clearTimeout(timeout_activity_loading);

		hideDiv('div#activity_div');
		setActivityContent(xhr_data);
		fadeInDiv('div#activity_div');

		activityLoaded();
	}).fail(function()
	{
		timeout_activity_error = setTimeout(function()
		{
			hideDiv('div#activity_div');

			setActivityTitle('Error');
			setActivityActions('<div title="Retry" class="actions_div" data-actions="reload_activity" data-highlightclass="darker_grey_highlight" onclick="void(0)"><div class="img_div img_32_div reload_32_img_div"></div></div>');
			setActivityActionsVisibility('visible');

			var append = (ua_is_android_app) ? ' Long-press the back button on your device to go back to the list of computers.' : '';

			setActivityContent('<div id="activity_inner_div"><div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Request failed. Make sure you are connected. Tap the top right icon to retry.'+append+'</div></div></div>');

			fadeInDiv('div#activity_div');
		}, 1000);
	});

	if(ua_is_ios && ua_is_standalone)
	{
		var cookie = { id: 'current_activity_'+project_version, value: JSON.stringify({ activity: activity.activity, subactivity: activity.subactivity, args: activity.args }), expires: 1 };

		if(isActivity('playlists', ''))
		{
			$.removeCookie(cookie.id);
		}
		else
		{
			$.cookie(cookie.id, cookie.value, { expires: cookie.expires });
		}
	}
}

function activityLoading()
{
	xhr_activity.abort();

	hideActivityOverflowActions();
	hideMenu();
	hideNowplayingOverflowActions();
	hideNowplaying();

	hideDiv('div#activity_div');

	scrollToTop(false);

	clearTimeout(timeout_activity_loading);

	timeout_activity_loading = setTimeout(function()
	{
		setActivityActionsVisibility('visible');
		setActivityActions('<div><div class="img_div img_32_div loading_32_img_div"></div></div>');
		setActivityTitle('Wait...');

		$('div#activity_div').empty();
	}, 1000);
}

function activityLoaded()
{
	// All
	clearTimeout(timeout_activity_error);

	checkForDialogs();
	checkForUpdates('auto');

	var data = getActivityData();
	var title = (typeof data.title == 'undefined') ? 'Unknown' : data.title;

	setActivityTitle(title);

	if(typeof data.actions == 'undefined')
	{
		setActivityActionsVisibility('hidden');
	}
	else
	{
		if(data.actions.length == 1)
		{
			setActivityActions('<div title="'+data.actions[0].action[0]+'" class="actions_div" data-highlightclass="darker_grey_highlight" onclick="void(0)"><div class="img_div img_32_div '+data.actions[0].action[1]+'"></div></div>');

			for(var i = 0; i < data.actions[0].keys.length; i++)
			{
				$('div.actions_div', 'div#top_actionbar_inner_right_div > div').data(data.actions[0].keys[i], data.actions[0].values[i]);
			}
		}
		else
		{
			setActivityActions('<div title="More" class="actions_div" data-actions="show_activity_overflow_actions" data-highlightclass="darker_grey_highlight" onclick="void(0)"><div class="img_div img_32_div overflow_32_img_div"></div></div>');

			$('div#top_actionbar_overflow_actions_inner_div').empty();

			for(var i = 0; i < data.actions.length; i++)
			{
				var highlight_arrow = (i == 0) ? 'data-highlightotherelement="div#top_actionbar_overflow_actions_arrow_div" data-highlightotherelementparent="div#top_actionbar_overflow_actions_div" data-highlightotherelementclass="up_arrow_dark_grey_highlight"' : '';

				$('div#top_actionbar_overflow_actions_inner_div').append('<div class="actions_div" data-highlightclass="dark_grey_highlight" '+highlight_arrow+' onclick="void(0)">'+data.actions[i].action[0]+'</div>');

				for(var f = 0; f < data.actions[i].keys.length; f++)
				{
					$('div.actions_div', 'div#top_actionbar_overflow_actions_inner_div').last().data(data.actions[i].keys[f], data.actions[i].values[f]);
				}
			}
		}

		setActivityActionsVisibility('visible');
	}

	showMenuIndicator();

	// Cover art
	if(typeof data.cover_art_uri != 'undefined')
	{
		setCoverArtSize();

		if(data.cover_art_uri != '') getCoverArt(data.cover_art_uri);
	}

	// Cards
	if($('div.cards_vertical_div').length) setCardVerticalSize();

	// Activities
	if(isActivity('discover', ''))
	{
		if(ua_supports_csstransitions && ua_supports_csstransforms3d)
		{
			setTimeout(function()
			{
				$('div.card_div').addClass('prepare_discover_card_animation').each(function(index)
				{
					var element = $(this);
					var timeout = index * 100;

					setTimeout(function()
					{
						$(element).addClass('show_discover_card_animation');
					}, timeout);
				});
			}, 250);
		}
		else
		{
			showDiv('div.card_div');
		}
	}
	else if(isActivity('settings', ''))
	{
		if(ua_is_android_app) $('div#settings_android_app_div').show();
		if(!ua_supports_notifications) disableSetting('div.setting_notifications_div');
		if(!ua_supports_devicemotion) disableSetting('div.setting_shake_to_skip_div');
	}

	// Text fields
	if(!ua_supports_touch)
	{
		if($('input:text#create_playlist_name_input').length)
		{
			focusTextInput('input:text#create_playlist_name_input')
		}
		else if($('input:text#search_input').length)
		{
			focusTextInput('input:text#search_input');
		}
	}
}

function changeActivity(activity, subactivity, args)
{
	var args = args.replace(/&amp;/g, '&').replace(/%2F/g, '%252F').replace(/%5C/g, '%255C');
	var hash  = '#'+activity+'/'+subactivity+'/'+args+'/'+getCurrentTime();

	window.location.href=hash;
}

function replaceActivity(activity, subactivity, args)
{
	var args = args.replace(/&amp;/g, '&').replace(/%2F/g, '%252F').replace(/%5C/g, '%255C');
	var hash  = '#'+activity+'/'+subactivity+'/'+args+'/'+getCurrentTime();

	window.location.replace(hash);
}

function reloadActivity()
{
	var a = getActivity();
	replaceActivity(a.activity, a.subactivity, a.args);
}

function refreshActivity()
{
	var a = getActivity();

	$.get(a.activity+'.php?'+a.subactivity+'&'+a.args, function(xhr_data)
	{
		if(isActivityWithArgs(a.activity, a.subactivity, a.args))
		{
			setActivityContent(xhr_data);
			setCoverArtSize();
			setCardVerticalSize();
		}
	});
}

function getActivity()
{
	var hash = window.location.hash.slice(1);

	if(hash == '')
	{
		var a = getDefaultActivity();
		var activity = [a.activity, a.subactivity, a.args];
	}
	else
	{
		var activity = hash.split('/');
	}

	return { activity: activity[0], subactivity: activity[1], args: activity[2] };
}

function getActivityData()
{
	return ($('div#activity_inner_div').length && $('div#activity_inner_div').attr('data-activitydata')) ? $.parseJSON($.base64.decode($('div#activity_inner_div').data('activitydata'))) : '';
}

function getDefaultActivity()
{
	var cookie = { id: 'hide_first_time_activity_'+project_version, value: 'true', expires: 3650 };

	if(!isCookie(cookie.id))
	{
		var activity = { activity: 'first-time', subactivity: '', args: '' };
		$.cookie(cookie.id, cookie.value, { expires: cookie.expires });
	}
	else
	{
		var activity = { activity: 'playlists', subactivity: '', args: '' };
	}

	return activity;
}

function setActivityTitle(title)
{
	$('div#top_actionbar_inner_center_div > div').attr('title', title).html(title);
}

function setActivityActions(actions)
{
	$('div#top_actionbar_inner_right_div > div').html(actions);
}

function setActivityActionsVisibility(visibility)
{
	$('div#top_actionbar_inner_right_div > div').css('visibility', visibility);
}

function setActivityContent(content)
{
	$('div#activity_div').html(content);
}

function isActivityWithArgs(activity, subactivity, args)
{
	var a = getActivity();
	return (a.activity == activity && a.subactivity == subactivity && a.args == args);
}

function isActivity(activity, subactivity)
{
	var a = getActivity();
	return (a.activity == activity && a.subactivity == subactivity);
}

function goBack()
{
	if(ua_is_ios && ua_is_standalone)
	{
		if(!isDisplayed('div#transparent_cover_div') && !isDisplayed('div#black_cover_div') && !isDisplayed('div#black_cover_activity_div') && !isVisible('div#nowplaying_div') && !textInputHasFocus()) history.back();
	}
	else
	{
		if(isDisplayed('div#dialog_div'))
		{
			closeDialog();
		}
		else if(isDisplayed('div#top_actionbar_overflow_actions_div'))
		{
			hideActivityOverflowActions();
		}
		else if(isDisplayed('div#nowplaying_actionbar_overflow_actions_div'))
		{
			hideNowplayingOverflowActions();
		}
		else if(isVisible('div#menu_div'))
		{
			hideMenu();
		}
		else if(isVisible('div#nowplaying_div'))
		{
			hideNowplaying();
		}
		else if(ua_is_android_app)
		{
			history.back();
		}
	}
}

function openExternalActivity(uri)
{
	if(ua_is_android_app)
	{
		if(shc(uri, 'http://www.youtube.com/results?search_query='))
		{
			var query = decodeURIComponent(uri.replace('http://www.youtube.com/results?search_query=', ''));
			var query = Android.JSsearchApp('com.google.android.youtube', query);

			if(query == 0) showToast('App not installed', 2);
		}
		else
		{
			Android.JSopenUri(uri);
		}
	}
	else
	{
		if(ua_is_android && shc(ua, 'Android 2') || ua_is_ios && ua_is_standalone)
		{
			var a = document.createElement('a');
			a.setAttribute('href', uri);
			a.setAttribute('target', '_blank');
			var dispatch = document.createEvent('HTMLEvents');
			dispatch.initEvent('click', true, true);
			a.dispatchEvent(dispatch);
		}
		else
		{
			window.open(uri);
		}
	}
}

function changeActivityIfIsAuthorizedWithSpotify(activity, subactivity, args, is_authorized_with_spotify)
{
	if(is_authorized_with_spotify)
	{
		changeActivity(activity, subactivity, args);
	}
	else
	{
		changeActivity('profile', '', '');
	}
}

// Menus

function toggleMenu()
{
	if(isVisible('div#menu_div'))
	{
		hideMenu();
	}
	else
	{
		showMenu();
	}
}

function showMenu()
{
	if(isVisible('div#menu_div') || isDisplayed('div#transparent_cover_div') || isDisplayed('div#black_cover_div') || isDisplayed('div#black_cover_activity_div') || isVisible('div#nowplaying_div') || textInputHasFocus()) return;

	$('div#menu_div').css('visibility', 'visible');
	$('div#black_cover_activity_div').show();

	setTimeout(function()
	{
		if(ua_supports_csstransitions && ua_supports_csstransforms3d)
		{
			$('div#menu_div').addClass('show_menu_animation');
			$('div#top_actionbar_inner_left_div > div > div > div').addClass('show_menu_img_animation');
			$('div#black_cover_activity_div').addClass('show_black_cover_activity_div_animation');
		}
		else
		{
			$('div#menu_div').stop().animate({ left: '0' }, 250, 'easeOutExpo');
			$('div#black_cover_activity_div').stop().animate({ 'opacity': '0.5' }, 250, 'easeOutExpo');
		}
	}, 25);

	nativeAppCanCloseCover();
}

function hideMenu()
{
	if(window_width >= 1024 || !isVisible('div#menu_div') || isDisplayed('div#dialog_div')) return;

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#menu_div').addClass('hide_menu_animation').one(event_transitionend, function()
		{
			$('div#menu_div').css('visibility', '').removeClass('show_menu_animation hide_menu_animation');
			nativeAppCanCloseCover();
		});

		$('div#top_actionbar_inner_left_div > div > div > div').addClass('hide_menu_img_animation').one(event_transitionend, function()
		{
			$('div#top_actionbar_inner_left_div > div > div > div').removeClass('show_menu_img_animation hide_menu_img_animation');
		});

		$('div#black_cover_activity_div').addClass('hide_black_cover_activity_div_animation').one(event_transitionend, function()
		{
			$('div#black_cover_activity_div').hide().removeClass('show_black_cover_activity_div_animation hide_black_cover_activity_div_animation');
		});
	}
	else
	{
		$('div#menu_div').stop().animate({ left: $('div#menu_div').data('cssleft') }, 250, 'easeOutExpo', function()
		{
			$('div#menu_div').css('visibility', '').css('left', '');
			nativeAppCanCloseCover();
		});

		$('div#black_cover_activity_div').stop().animate({ 'opacity': '0' }, 250, 'easeOutExpo', function()
		{
			$('div#black_cover_activity_div').hide().css('opacity', '');
		});
	}
}

function showMenuIndicator()
{
	$('div.menu_big_item_indicator_div').removeClass('menu_big_item_indicator_active_div');
	$('div.menu_big_item_text_div').css('font-weight', '');

	var a = getActivity();
	var current_activity = a.activity;

	$('div.menu_big_item_div').each(function()
	{
		var element = $(this);
		var activity = $(element).data('activity');

		if(activity == current_activity)
		{
			$('div.menu_big_item_indicator_div', element).addClass('menu_big_item_indicator_active_div');
			$('div.menu_big_item_text_div', element).css('font-weight', 'bold');
		}
	});
}

function showActivityOverflowActions()
{
	if(isDisplayed('div#top_actionbar_overflow_actions_div')) return;

	hideMenu();

	$('div#transparent_cover_div').show();
	$('div#top_actionbar_overflow_actions_div').show();

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#top_actionbar_overflow_actions_div').addClass('prepare_overflow_actions_animation');

		setTimeout(function()
		{
			$('div#top_actionbar_overflow_actions_div').addClass('show_overflow_actions_animation');
		}, 25);
	}
	else
	{
		fadeInDiv('div#top_actionbar_overflow_actions_div');
	}

	nativeAppCanCloseCover();
}

function hideActivityOverflowActions()
{
	if(!isDisplayed('div#top_actionbar_overflow_actions_div')) return;

	$('div#top_actionbar_overflow_actions_div').hide();

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#top_actionbar_overflow_actions_div').removeClass('prepare_overflow_actions_animation show_overflow_actions_animation');
	}
	else
	{
		hideDiv('div#top_actionbar_overflow_actions_div');
	}

	$('div#transparent_cover_div').hide();

	nativeAppCanCloseCover();
}

// Remote control

function remoteControl(action)
{
	xhr_remote_control.abort();

	clearTimeout(timeout_remote_control);

	if(action == 'launch_quit' || action == 'next' || action == 'previous') startRefreshNowplaying();

	xhr_remote_control = $.post('main.php?'+getCurrentTime(), { action: action }, function(xhr_data)
	{
		if(action == 'launch_quit')
		{
			refreshNowplaying('manual');
		}
		else if(action == 'play_pause' || action == 'pause')
		{
			refreshNowplaying('silent');
		}
		else if(action == 'next' || action == 'previous')
		{
			var timeout = (xhr_data == 'queue_is_empty') ? 500 : 1000;

			timeout_remote_control = setTimeout(function()
			{
				refreshNowplaying('manual');
			}, timeout);
		}
	});
}

function adjustVolume(volume)
{
	xhr_adjust_volume.abort();

	autoRefreshNowplaying('reset');

	var cookie = { id: 'settings_volume_control' };
	var control = $.cookie(cookie.id);
	var action = (control == 'spotify') ? 'adjust_spotify_volume' : 'adjust_system_volume';

	xhr_adjust_volume = $.post('main.php?'+getCurrentTime(), { action: action, data: volume }, function(xhr_data)
	{
		$('input#nowplaying_volume_slider').val(xhr_data);
		$('span#nowplaying_volume_level_span').html(xhr_data);

		if(!isVisible('#nowplaying_div'))
		{
			if(xhr_data == 0)
			{
				showToast('Volume muted', 1);
			}
			else
			{
				showToast('Volume: '+xhr_data+' %', 1);
			}
		}
	});
}

function adjustVolumeControl(control)
{
	var cookie = { id: 'hide_adjust_volume_control_dialog_'+project_version, value: 'true', expires: 3650 };

	if(!isCookie(cookie.id))
	{
		showDialog({ title: 'Adjust volume', body_class: 'dialog_message_div', body_text: 'With this action you can toggle between adjusting Spotify\'s volume and the system volume.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires', 'volumecontrol'], values: ['hide_dialog set_cookie adjust_volume_control', cookie.id, cookie.value, cookie.expires, control] } });
	}
	else
	{
		var cookie = { id: 'settings_volume_control', value: control, expires: 3650 };
		$.cookie(cookie.id, cookie.value, { expires: cookie.expires });

		if(control == 'spotify')
		{
			showToast('Controlling Spotify\'s volume', 2);
		}
		else
		{
			showToast('Controlling the system volume', 2);
		}

		refreshNowplaying('silent');
	}
}

function toggleShuffleRepeat(action)
{
	var cookie = { id: 'hide_shuffle_repeat_dialog_'+project_version, value: 'true', expires: 3650 };

	if(!isCookie(cookie.id))
	{
		showDialog({ title: 'Shuffle & repeat', body_class: 'dialog_message_div', body_text: 'Shuffle and repeat can be toggled, but it is not possible to get the current status. Toggling requires the Spotify window to have focus. It will try to focus automatically. Advertisements and active webviews may prevent toggling from working.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires', 'remotecontrol'], values: ['hide_dialog set_cookie toggle_shuffle_repeat', cookie.id, cookie.value, cookie.expires, action] } });
	}
	else
	{
		$.post('main.php?'+getCurrentTime(), { action: action }, function(xhr_data)
		{
			if(xhr_data == 'spotify_is_not_running')
			{
				showToast('Spotify is not running', 2);
			}
			else if(action == 'toggle_shuffle')
			{
				showToast('Shuffle toggled', 2);
			}
			else if(action == 'toggle_repeat')
			{
				showToast('Repeat toggled', 2);
			}
		});
	}
}

function playUri(uri)
{
	startRefreshNowplaying();

	$.post('main.php?'+getCurrentTime(), { action: 'play_uri', data: uri }, function()
	{
		refreshNowplaying('manual');
	});
}

function shufflePlayUri(uri)
{
	var cookie = { id: 'hide_shuffle_play_uri_dialog_'+project_version, value: 'true', expires: 3650 };

	if(!isCookie(cookie.id))
	{
		showDialog({ title: 'Shuffle play', body_class: 'dialog_message_div', body_text: 'This action plays the media, toggles shuffle off/on and skips one track to ensure random playback. Shuffle must already be enabled. Advertisements may prevent this from working.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires', 'uri'], values: ['hide_dialog set_cookie shuffle_play_uri', cookie.id, cookie.value, cookie.expires, uri] } });
	}
	else
	{
		startRefreshNowplaying();

		$.post('main.php?'+getCurrentTime(), { action: 'shuffle_play_uri', data: uri }, function()
		{
			refreshNowplaying('manual');
		});
	}
}

function startTrackRadio(uri, play_first)
{
	var cookie = { id: 'hide_start_track_radio_dialog_'+project_version, value: 'true', expires: 3650 };

	if(getUriType(uri) == 'local')
	{
		showToast('Not possible for local files', 4);
	}
	else if(!isCookie(cookie.id))
	{
		showDialog({ title: 'Start track radio', body_class: 'dialog_message_div', body_text: 'For this to work you must leave the mouse cursor over the now playing cover art (avoid the resize button) on the computer running Spotify, or it will fail badly. If the up keyboard key is simulated wrong number of times, change it in settings. It may not work on all tracks.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys: ['actions', 'cookieid', 'cookievalue', 'cookieexpires', 'uri', 'playfirst'], values: ['hide_dialog set_cookie start_track_radio', cookie.id, cookie.value, cookie.expires, uri, play_first] } });
	}
	else
	{
		showToast('Starting track radio', 2);

		var data = JSON.stringify([uri, play_first, settings_start_track_radio_simulation]);

		$.post('main.php?'+getCurrentTime(), { action: 'start_track_radio', data: data }, function()
		{
			startRefreshNowplaying();

			setTimeout(function()
			{
				refreshNowplaying('manual');
			}, 2000);
		});
	}
}

function confirmSuspendComputer()
{
	showDialog({ title: 'Suspend computer', body_class: 'dialog_message_div', body_text: 'This will suspend the computer running Spotify, and you will lose connection to '+project_name+'.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog suspend_computer'] } });
}

function suspendComputer()
{
	remoteControl('suspend_computer');
}

function confirmShutDownComputer()
{
	showDialog({ title: 'Shut down computer', body_class: 'dialog_message_div', body_text: 'This will shut down the computer running Spotify, and you will lose connection to '+project_name+'. ConsoleKit must be installed for this to work.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog shut_down_computer'] } });
}

function shutDownComputer()
{
	remoteControl('shut_down_computer');
}

// Now playing

function toggleNowplaying()
{
	if(isVisible('div#nowplaying_div'))
	{
		hideNowplayingOverflowActions();
		hideNowplaying();
	}
	else
	{
		hideActivityOverflowActions();
		showNowplaying();
	}
}

function showNowplaying()
{
	if(isVisible('div#nowplaying_div') || textInputHasFocus()) return;

	hideMenu();

	$('div#nowplaying_div').css('visibility', 'visible');

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#nowplaying_div').addClass('show_nowplaying_animation');
	}
	else
	{
		$('div#nowplaying_div').stop().animate({ bottom: '0' }, 500, 'easeOutExpo');
	}

	nativeAppCanCloseCover();
}

function hideNowplaying()
{
	if(!isVisible('div#nowplaying_div') || isDisplayed('div#transparent_cover_div')) return;

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#nowplaying_div').addClass('hide_nowplaying_animation').one(event_transitionend, function()
		{
			$('div#nowplaying_div').css('visibility', '').removeClass('show_nowplaying_animation hide_nowplaying_animation');
			nativeAppCanCloseCover();
		});
	}
	else
	{
		$('div#nowplaying_div').stop().animate({ bottom: $('div#nowplaying_div').data('cssbottom') }, 500, 'easeOutExpo', function()
		{
			$('div#nowplaying_div').css('visibility', '');
			nativeAppCanCloseCover();
		});
	}
}

function showNowplayingOverflowActions()
{
	if(isDisplayed('div#nowplaying_actionbar_overflow_actions_div')) return;

	$('div#transparent_cover_div').show();
	$('div#nowplaying_actionbar_overflow_actions_div').show();

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#nowplaying_actionbar_overflow_actions_div').addClass('prepare_overflow_actions_animation');

		setTimeout(function()
		{
			$('div#nowplaying_actionbar_overflow_actions_div').addClass('show_overflow_actions_animation');
		}, 25);
	}
	else
	{
		fadeInDiv('div#nowplaying_actionbar_overflow_actions_div');
	}

	$('input#nowplaying_volume_slider').attr('disabled', 'disabled');

	nativeAppCanCloseCover();
}

function hideNowplayingOverflowActions()
{
	if(!isDisplayed('div#nowplaying_actionbar_overflow_actions_div')) return;

	$('div#nowplaying_actionbar_overflow_actions_div').hide();

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#nowplaying_actionbar_overflow_actions_div').removeClass('prepare_overflow_actions_animation show_overflow_actions_animation');
	}
	else
	{
		hideDiv('div#nowplaying_actionbar_overflow_actions_div');
	}

	$('div#transparent_cover_div').hide();

	setTimeout(function()
	{
		$('input#nowplaying_volume_slider').removeAttr('disabled');
	}, 250);

	nativeAppCanCloseCover();
}

function startRefreshNowplaying()
{
	nowplaying_refreshing = true;

	xhr_nowplaying.abort();

	clearTimeout(timeout_nowplaying_error);

	$('div#bottom_actionbar_inner_center_div > div').html('Refreshing...');

	if(!isVisible('div#nowplaying_div')) return;

	hideNowplayingOverflowActions();

	$('div#nowplaying_actionbar_right_div > div').removeClass('actions_div').css('opacity', '0.5');

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#nowplaying_cover_art_div').css('transition', '').css('transform', '').css('-webkit-transition', '').css('-webkit-transform', '').css('-moz-transition', '').css('-moz-transform', '').css('-ms-transition', '').css('-ms-transform', '');
		$('div#nowplaying_cover_art_div').off(event_transitionend).removeClass('prepare_nowplaying_cover_art_animation show_nowplaying_cover_art_animation hide_nowplaying_cover_art_animation').addClass('hide_nowplaying_cover_art_animation');
	}
	else
	{
		$('div#nowplaying_cover_art_div').stop().animate({ left: '-'+window_width+'px' }, 500, 'easeOutExpo');
	}
}

function refreshNowplaying(type)
{
	nowplaying_refreshing = true;

	xhr_nowplaying.abort();

	autoRefreshNowplaying('reset');

	clearTimeout(timeout_nowplaying_error);

	xhr_nowplaying = $.get('nowplaying.php', function(xhr_data)
	{
		nowplaying_refreshing = false;

		clearTimeout(timeout_nowplaying_error);

		if(type == 'manual' || nowplaying_last_data != xhr_data)
		{
			nowplaying_last_data = xhr_data;

			var nowplaying = $.parseJSON(xhr_data);

			var cookie = { id: 'nowplaying_uri', value: nowplaying.uri, expires: 3650 };
			$.cookie(cookie.id, cookie.value, { expires: cookie.expires });

			$('div#nowplaying_actionbar_right_div > div').addClass('actions_div').data('actions', 'show_nowplaying_overflow_actions').css('opacity', '');

			$('div#nowplaying_actionbar_overflow_actions_inner_div').empty();

			for(var i = 0; i < nowplaying.actions.length; i++)
			{
				var highlight_arrow = (i == 0) ? 'data-highlightotherelement="div#nowplaying_actionbar_overflow_actions_arrow_div" data-highlightotherelementparent="div#nowplaying_actionbar_overflow_actions_div" data-highlightotherelementclass="up_arrow_dark_grey_highlight"' : '';

				$('div#nowplaying_actionbar_overflow_actions_inner_div').append('<div class="actions_div" data-highlightclass="dark_grey_highlight" '+highlight_arrow+' onclick="void(0)">'+nowplaying.actions[i].action[0]+'</div>');

				for(var f = 0; f < nowplaying.actions[i].keys.length; f++)
				{
					$('div.actions_div', 'div#nowplaying_actionbar_overflow_actions_inner_div').last().data(nowplaying.actions[i].keys[f], nowplaying.actions[i].values[f]);
				}
			}

			$('div#nowplaying_artist_div').attr('title', nowplaying.artist).html(hsc(nowplaying.artist));
			$('div#nowplaying_title_div').attr('title', nowplaying.title+' ('+nowplaying.tracklength+')').html(hsc(nowplaying.title));

			// Change lyrics title and body
			$("#lyrics_div").html("<i>fetching lyrics ..</i>");
			$("#top_actionbar_inner_center_div").html(nowplaying.title);

			// Change lyrics automatically
			var lyricsFor = "artist=" + encodeURIComponent(nowplaying.artist) + "&title=" + encodeURIComponent(nowplaying.title);
			$.get("lyrics.php", lyricsFor, function() {})
					.done(function(data) {$("#lyrics_div").html(data);})
					.fail(function() {console.log("Unable to fetch lyrics");});

			$('input#nowplaying_volume_slider').val(nowplaying.current_volume);
			$('span#nowplaying_volume_level_span').html(nowplaying.current_volume);

			$('div#nowplaying_play_pause_div').removeClass('play_64_img_div pause_64_img_div').addClass(nowplaying.play_pause+'_64_img_div');

			$('div#bottom_actionbar_inner_left_div > div > div > div').removeClass('play_32_img_div pause_32_img_div').addClass(nowplaying.play_pause+'_32_img_div');

			if(type == 'manual' || nowplaying_last_uri != nowplaying.uri)
			{
				nowplaying_last_uri = nowplaying.uri;

				$('div#nowplaying_cover_art_div').data('uri', nowplaying.uri).attr('title', nowplaying.album+' ('+nowplaying.released+')');

				if(getUriType(nowplaying.uri) == 'local' && nowplaying.artist != 'Unknown' && nowplaying.album != 'Unknown')
				{
					$.getJSON(project_website+'api/1/cover-art/?type=album&artist='+encodeURIComponent(nowplaying.artist)+'&album='+encodeURIComponent(nowplaying.album)+'&callback=?', function(data)
					{
						var lastfm_cover_art = (typeof data.mega == 'undefined' || data.mega == '') ? 'img/no-cover-art-640.png?'+project_serial : data.mega;

						$('img#nowplaying_cover_art_preload_img').attr('src', 'img/album-24.png?'+project_serial).attr('src', lastfm_cover_art).on('load error', function(event)
						{
							$(this).off('load error');

							var cover_art = (event.type == 'load') ? lastfm_cover_art : 'img/no-cover-art-640.png?'+project_serial;
							$('div#nowplaying_cover_art_div').css('background-image', 'url("'+cover_art+'")');

							if(type == 'manual') endRefreshNowplaying();

							if(nowplaying.uri != '') showNotification(nowplaying.title, nowplaying.artist+' (click to skip)', cover_art, 'remote_control_next', 4);
						});
					});
				}
				else
				{
					$('img#nowplaying_cover_art_preload_img').attr('src', 'img/album-24.png?'+project_serial).attr('src', nowplaying.cover_art).on('load error', function(event)
					{
						$(this).off('load error');

						var cover_art = (event.type == 'load') ? nowplaying.cover_art : 'img/no-cover-art-640.png?'+project_serial;
						$('div#nowplaying_cover_art_div').css('background-image', 'url("'+cover_art+'")');

						if(type == 'manual') endRefreshNowplaying();

						if(nowplaying.uri != '') showNotification(nowplaying.title, nowplaying.artist+' (click to skip)', cover_art, 'remote_control_next', 4);
					});
				}

				hideDiv('div#bottom_actionbar_inner_center_div > div');
				$('div#bottom_actionbar_inner_center_div > div').attr('title', nowplaying.artist+' - '+nowplaying.title+' ('+nowplaying.tracklength+')').html(hsc(nowplaying.title));
				fadeInDiv('div#bottom_actionbar_inner_center_div > div');

				highlightNowplayingListItem();

				if(ua_is_android_app)
				{
					Android.JSsetSharedString('NOWPLAYING_ARTIST', nowplaying.artist);
					Android.JSsetSharedString('NOWPLAYING_TITLE', nowplaying.title);
				}
				else if(integrated_in_msie)
				{
					if(nowplaying.play_pause == 'play')
					{
						window.external.msSiteModeShowButtonStyle(ie_thumbnail_button_play_pause, ie_thumbnail_button_style_play);
					}
					else
					{
						window.external.msSiteModeShowButtonStyle(ie_thumbnail_button_play_pause, ie_thumbnail_button_style_pause);
					}
				}

				refreshRecentlyPlayedActivity();
				refreshQueueActivity();
			}
			else
			{
				$('div#bottom_actionbar_inner_center_div > div').attr('title', nowplaying.artist+' - '+nowplaying.title+' ('+nowplaying.tracklength+')').html(hsc(nowplaying.title));
			}
		}
	}).fail(function()
	{
		timeout_nowplaying_error = setTimeout(function()
		{
			nowplaying_refreshing = false;
			nowplaying_last_data = '';
			nowplaying_last_uri = '';

			$('div#bottom_actionbar_inner_center_div > div').html('Connection failed');
		}, 1000);
	});
}

function endRefreshNowplaying()
{
	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		if(isVisible('div#nowplaying_div'))
		{
			$('div#nowplaying_cover_art_div').removeClass('hide_nowplaying_cover_art_animation').addClass('prepare_nowplaying_cover_art_animation');

			setTimeout(function()
			{
				$('div#nowplaying_cover_art_div').addClass('show_nowplaying_cover_art_animation').one(event_transitionend, function()
				{
					$('div#nowplaying_cover_art_div').removeClass('prepare_nowplaying_cover_art_animation show_nowplaying_cover_art_animation hide_nowplaying_cover_art_animation');
				});
			}, 25);
		}
		else
		{
			$('div#nowplaying_cover_art_div').removeClass('prepare_nowplaying_cover_art_animation show_nowplaying_cover_art_animation hide_nowplaying_cover_art_animation');
		}
	}
	else
	{
		if(isVisible('div#nowplaying_div'))
		{
			var changeside = parseInt(window_width * 2);
			$('div#nowplaying_cover_art_div').stop().css('left', changeside+'px').animate({ left: '0' }, 500, 'easeOutExpo');
		}
		else
		{
			$('div#nowplaying_cover_art_div').css('left', '');
		}
	}
}

function autoRefreshNowplaying(action)
{
	if(action == 'start' && settings_nowplaying_refresh_interval >= 5)
	{
		var cookie = { id: 'nowplaying_last_update', expires: 3650 };
		$.cookie(cookie.id, getCurrentTime(), { expires: cookie.expires });

		interval_nowplaying_auto_refresh = setInterval(function()
		{
			if(getCurrentTime() - parseInt($.cookie(cookie.id)) > settings_nowplaying_refresh_interval * 1000)
			{
				timeout_nowplaying_auto_refresh = setTimeout(function()
				{
					if(!nowplaying_refreshing && !nowplaying_cover_art_moving && !isDisplayed('div#black_cover_div') && !isDisplayed('div#transparent_cover_div')) refreshNowplaying('silent');
				}, 2000);

				$.cookie(cookie.id, getCurrentTime(), { expires: cookie.expires });
			}
		}, 1000);
	}
	else if(action == 'reset' && interval_nowplaying_auto_refresh != null)
	{
		clearInterval(interval_nowplaying_auto_refresh);
		clearTimeout(timeout_nowplaying_auto_refresh);

		autoRefreshNowplaying('start');
	}
}

function highlightNowplayingListItem()
{
	var cookie = { id: 'nowplaying_uri' };

	if(!$('div.list_item_main_div').length || !isCookie(cookie.id)) return;

	var nowplaying_uri = $.cookie(cookie.id);

	$('div.list_item_main_div').each(function()
	{
		var item = $(this);

		if($(item).attr('data-trackuri'))
		{
			var icon = $('div.list_item_main_inner_icon_div > div', item);
			var text = $('div.list_item_main_inner_text_upper_div', item);

			if($(icon).hasClass('playing_24_img_div') && $(text).hasClass('bold_text'))
			{
				$(icon).removeClass('playing_24_img_div').addClass('track_24_img_div');
				$(text).removeClass('bold_text');
			}

			if($(item).data('trackuri') == nowplaying_uri)
			{
				$(icon).removeClass('track_24_img_div').addClass('playing_24_img_div');
				$(text).addClass('bold_text');
			}
		}
	});
}

// Cover art

function getCoverArt(uri)
{
	xhr_cover_art.abort();

	xhr_cover_art = $.post('main.php?'+getCurrentTime(), { action: 'get_cover_art', data: uri }, function(xhr_data)
	{
		if($('div#cover_art_div').length)
		{
			if(xhr_data == 'error')
			{
				showToast('Could not get cover art', 2);
			}
			else
			{
				$('img#cover_art_preload_img').attr('src', 'img/album-24.png?'+project_serial).attr('src', xhr_data).on('load error', function(event)
				{
					if(event.type == 'load')
					{
						$('div#cover_art_art_div').css('background-image', 'url("'+xhr_data+'")');
					}
					else
					{
						showToast('Could not load cover art', 2);
					}
				});
			}
		}
	});
}

function setCoverArtSize()
{
	if(!$('div#cover_art_art_div').length) return;

	var container_width = $('div#cover_art_div').outerWidth();
	var cover_art_width = $('div#cover_art_art_div').data('width');
	var cover_art_height = $('div#cover_art_art_div').data('height');

	if(cover_art_width > container_width)
	{
		var ratio = container_width / cover_art_width;
		var cover_art_height = Math.floor(cover_art_height * ratio);
		var minimum_height = $('div#cover_art_art_div').height();

		var size = (cover_art_height < minimum_height) ? 'auto '+minimum_height+'px' : container_width+'px auto';

		$('div#cover_art_art_div').css('background-size', size);
	}
	else
	{
		$('div#cover_art_art_div').css('background-size', cover_art_width+'px auto');
	}
}

// Recently played

function refreshRecentlyPlayedActivity()
{
	if(isActivity('recently-played', '')) refreshActivity();
}

function clearRecentlyPlayed()
{
	$.get('recently-played.php?clear', function()
	{
		refreshRecentlyPlayedActivity();
	});
}

// Queue

function queueUri(artist, title, uri)
{
	$.post('queue.php?queue_uri&'+getCurrentTime(), { artist: artist, title: title, uri: uri }, function(xhr_data)
	{
		if(xhr_data == 'spotify_is_not_running')
		{
			showToast('Spotify is not running', 2);
		}
		else
		{
			showToast('Track queued', 2);
			refreshQueueActivity();
		}
	});
}

function queueUris(uris, randomly)
{
	showToast('Queuing tracks', 2);

	$.post('queue.php?queue_uris&'+getCurrentTime(), { uris: uris, randomly: randomly }, function(xhr_data)
	{
		setTimeout(function()
		{
			if(xhr_data == 'spotify_is_not_running')
			{
				showToast('Spotify is not running', 2);
			}
			else if(xhr_data == 'error')
			{
				showToast('Could not queue tracks', 2);
			}
			else
			{
				showToast(getTracksCount(xhr_data)+' queued', 2);
				refreshQueueActivity();
			}
		}, 1000);
	});
}

function moveQueuedUri(id, sortorder, direction)
{
	$.post('queue.php?move&'+getCurrentTime(), { id: id, sortorder: sortorder, direction: direction }, function(xhr_data)
	{
		refreshQueueActivity();
	});
}

function removeFromQueue(id, sortorder)
{
	$.post('queue.php?remove&'+getCurrentTime(), { id: id, sortorder: sortorder }, function()
	{
		refreshQueueActivity();
	});
}

function clearQueue()
{
	$.get('queue.php?clear', function()
	{
		refreshQueueActivity();
	});
}

function refreshQueueActivity()
{
	if(isActivity('queue', '')) refreshActivity();
}

// Playlists

function addToPlaylist(title, uri, is_authorized_with_spotify)
{
	if(is_authorized_with_spotify)
	{
		if(getUriType(uri) == 'local')
		{
			showToast('Not possible for local files', 4);
		}
		else
		{
			$.get('playlists.php?get_playlists_with_starred', function(xhr_data)
			{
				var playlists = $.parseJSON(xhr_data);

				var actions = new Array();

				var i = 0;

				for(var playlist in playlists)
				{
					actions[i] = { text: hsc(playlist), keys: ['actions', 'uri', 'uris'], values: ['hide_dialog add_uris_to_playlist', playlists[playlist], uri] }

					i++;
				}

				showActionsDialog({ title: hsc(title), actions: actions });
			});
		}
	}
	else
	{
		changeActivity('profile', '', '');
	}
}

function addUrisToPlaylist(uri, uris)
{
	$.post('playlists.php?add_uris_to_playlist&'+getCurrentTime(), { uri: uri, uris: uris }, function(xhr_data)
	{
		if(xhr_data == 'error')
		{
			showToast('Could not add track to playlist', 4);
		}
		else
		{
			var toast = (parseInt(xhr_data) == 1) ? 'track' : 'tracks';
			showToast(xhr_data+' '+toast+' added to playlist', 4);
		}
	});
}

function browsePlaylist(uri, is_authorized_with_spotify)
{
	if(is_authorized_with_spotify)
	{
		changeActivity('playlists', 'browse', 'uri='+uri);
	}
	else
	{
		changeActivity('profile', '', '');
	}
}

function importPlaylists(uris)
{
	var uris = $.trim(uris);
	var validate = uris.split(' ');

	var invalid = false;

	for(var i = 0; i < validate.length; i++)
	{
		if(getUriType(validate[i]) != 'playlist') invalid = true;
	}

	if(invalid)
	{
		showToast('One or more invalid playlist URIs', 4);
		focusTextInput('input:text#import_playlists_uris_input');
	}
	else
	{
		blurTextInput();
		activityLoading();

		$.post('playlists.php?import_playlists&'+getCurrentTime(), { uris: uris }, function(xhr_data)
		{
			if(xhr_data == 'error')
			{
				showDialog({ title: 'Import playlists', body_class: 'dialog_message_div', body_text: 'Could not import one or more playlists. Try again.', button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
			}
			else
			{
				var toast = (parseInt(xhr_data) == 1) ? 'playlist' : 'playlists';
				showToast(xhr_data+' '+toast+' imported', 4);
			}

			changeActivity('playlists', '', '');
		});
	}
}

function confirmImportSpotifyPlaylists(is_authorized_with_spotify)
{
	if(is_authorized_with_spotify)
	{
		showDialog({ title: 'Import from Spotify', body_class: 'dialog_message_div', body_text: 'This will import your playlists from Spotify. Collaborative playlists will not be imported because of a limitation in Spotify\'s web API. You can temporarily mark them as not collaborative or import them manually.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog import_spotify_playlists'] } });
	}
	else
	{
		changeActivity('profile', '', '');
	}
}

function importSpotifyPlaylists()
{
	activityLoading();

	xhr_activity = $.get('playlists.php?import_spotify_playlists', function(xhr_data)
	{
		changeActivity('playlists', '', '');
		if(xhr_data == 'error')
		{
			showDialog({ title: 'Import from Spotify', body_class: 'dialog_message_div', body_text: 'Could not import playlists. Try again.', button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
		}
		else
		{
			var toast = (parseInt(xhr_data) == 1) ? 'playlist' : 'playlists';
			showToast(xhr_data+' '+toast+' imported', 4);
		}
	});
}

function createPlaylist(name, make_public)
{
	if(name == '')
	{
		showToast('Playlist name can not be empty', 4);
		focusTextInput('input:text#create_playlist_name_input');
	}
	else
	{
		activityLoading();

		$.post('playlists.php?create_playlist&'+getCurrentTime(), { name: name, make_public: make_public }, function(xhr_data)
		{
			changeActivity('playlists', '', '');

			if(xhr_data == 'error')
			{
				showDialog({ title: 'Create playlist', body_class: 'dialog_message_div', body_text: 'Could not create playlist. Try again.', button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
			}
			else
			{
				showToast('Playlist "'+xhr_data+'" created', 4);
			}
		});
	}
}

function removePlaylist(id)
{
	$.post('playlists.php?remove_playlist&'+getCurrentTime(), { id: id }, function()
	{
		refreshPlaylistsActivity();
	});
}

function refreshPlaylistsActivity()
{
	if(isActivity('playlists', '')) refreshActivity();
}

// Library

function save(artist, title, uri, is_authorized_with_spotify, element)
{
	if(is_authorized_with_spotify)
	{
		$.post('library.php?save&'+getCurrentTime(), { artist: artist, title: title, uri: uri }, function(xhr_data)
		{
			if(xhr_data == 'error')
			{
				showToast('Could not save track to Spotify', 4);
			}
			else if(shc(xhr_data, 'removed'))
			{
				if($('div.img_div', element).length)
				{
					$('div.img_div', element).removeClass('remove_24_img_div').addClass('save_24_img_div');
				}
				else
				{
					var html = $(element).html();
					$(element).html(html.replace('Remove', 'Save'));
					showToast(xhr_data, 2);
				}
			}
			else
			{
				if($('div.img_div', element).length)
				{
					$('div.img_div', element).removeClass('save_24_img_div').addClass('remove_24_img_div');
				}
				else
				{
					var html = $(element).html();
					$(element).html(html.replace('Save', 'Remove'));
					showToast(xhr_data, 2);
				}
			}

			refreshLibraryActivity();
		});
	}
	else
	{
		changeActivity('profile', '', '');
	}
}

function remove(uri, is_authorized_with_spotify)
{
	if(is_authorized_with_spotify)
	{
		$.post('library.php?remove&'+getCurrentTime(), { uri: uri }, function(xhr_data)
		{
			refreshLibraryActivity();

			if(xhr_data == 'error') showToast('Could not remove track from Spotify', 4);
		});
	}
	else
	{
		changeActivity('profile', '', '');
	}
}

function confirmImportSavedSpotifyTracks(is_authorized_with_spotify)
{
	if(is_authorized_with_spotify)
	{
		showDialog({ title: 'Import from Spotify', body_class: 'dialog_message_div', body_text: 'This will import your saved tracks from Spotify. Artists and albums can not currently be managed through Spotify\'s web API. Repeat this action whenever you have saved tracks to your library outside of '+project_name+'.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog import_saved_spotify_tracks'] } });
	}
	else
	{
		changeActivity('profile', '', '');
	}
}

function importSavedSpotifyTracks()
{
	activityLoading();

	xhr_activity = $.get('library.php?import_saved_spotify_tracks', function(xhr_data)
	{
		changeActivity('library', '', '');

		if(xhr_data == 'error')
		{
			showDialog({ title: 'Import from Spotify', body_class: 'dialog_message_div', body_text: 'Could not import saved tracks from Spotify. Try again.', button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
		}
		else
		{
			var toast = (parseInt(xhr_data) == 1) ? 'track' : 'tracks';
			showToast(xhr_data+' '+toast+' imported', 4);
		}
	});
}

function refreshLibraryActivity()
{
	if(isActivity('library', '')) refreshActivity();
}

// Search

function getSearch(string)
{
	if(string == '')
	{
		focusTextInput('input:text#search_input');
	}
	else
	{
		blurTextInput();

		$.cookie('settings_sort_search_tracks', 'default', { expires: 3650 });

		changeActivity('search', 'search', 'string='+string);
	}
}

function clearSearchHistory()
{
	$.get('search.php?clear', function()
	{
		refreshSearchActivity();
	});
}

function refreshSearchActivity()
{
	if(isActivity('search', '')) refreshActivity();
}

// Artists

function browseArtist(uri)
{
	if(getUriType(uri) == 'local')
	{
		showToast('Not possible for local files', 4);
	}
	else
	{
		changeActivity('artist', '', 'uri='+uri);
	}
}

function getArtistBiography(artist)
{
	var button_text = $('div#green_artist_biography_button_div').html();

	$('div#green_artist_biography_button_div').html('Wait...');

	$.post('artist.php?get_biography&'+getCurrentTime(), { artist: artist }, function(xhr_data)
	{
		if(xhr_data == 'error')
		{
			showToast('Could not get biography', 4);
		}
		else if(xhr_data == 'no_biography')
		{
			showToast('No biography available', 4);
		}
		else
		{
			$('div#green_artist_biography_button_div').hide();
			$('div#artist_biography_div').html(xhr_data).show();
		}

		$('div#green_artist_biography_button_div').html(button_text);
	});
}

// Albums

function browseAlbum(uri)
{
	if(uri == '') return;

	if(getUriType(uri) == 'local')
	{
		showToast('Not possible for local files', 4);
	}
	else
	{
		changeActivity('album', '', 'uri='+uri);
	}
}

// Profile

function authorizeWithSpotify()
{
	if(ua_is_ios && ua_is_standalone) $.removeCookie('current_activity_'+project_version);

	var uri = window.location.href.replace(window.location.hash, '')+'#profile//spotify_token=';
	var installed = parseInt($.cookie('installed_'+project_version));

	window.location.href = project_website+'api/1/spotify/authorize/?redirect_uri='+encodeURIComponent(uri)+'&state='+installed;
}

function confirmAuthorizeWithSpotify()
{
	showDialog({ title: 'Authorize with Spotify', body_class: 'dialog_message_div', body_text: 'This will redirect you to Spotify\'s website where you must log in as the same user you are logged in as in the Spotify desktop client.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog authorize_with_spotify'] } });
}

function deauthorizeFromSpotify()
{
	$.get('profile.php?deauthorize_from_spotify', function()
	{
		replaceActivity('profile', '', '');
	});
}

function confirmDeauthorizeFromSpotify()
{
	showDialog({ title: 'Deauthorize from Spotify', body_class: 'dialog_message_div', body_text: 'This will deauthorize you from Spotify. Normally you only do this if you are going to authorize as another Spotify user.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog deauthorize_from_spotify'] } });
}

// Settings

function saveSetting(setting, value)
{
	var cookie = { id: setting, value: value, expires: 3650 };
	$.cookie(cookie.id, cookie.value, { expires: cookie.expires });
	showToast('Tap top right icon to apply', 2);
}

function applySettings()
{
	if(ua_is_ios && ua_is_standalone) $.removeCookie('current_activity_'+project_version);
	window.location.replace('.');
}

function disableSetting(div)
{
	$(div+' > div.setting_text_div > div:first-child').addClass('setting_not_supported_div');
	$(div+' > div.setting_text_div > div:last-child').html('Not supported on this device.');
	$(div+' > div.setting_edit_div > input.setting_checkbox').attr('disabled', 'disabled');
	$(div+' > div.setting_edit_div > select.setting_select').attr('disabled', 'disabled');
}

function confirmRemoveAllPlaylists()
{
		showDialog({ title: 'Remove all playlists', body_class: 'dialog_message_div', body_text: 'This will remove all playlists from '+project_name+'. They will not be deleted from Spotify.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog remove_all_playlists'] } });
}

function removeAllPlaylists()
{
	$.get('playlists.php?remove_all_playlists', function()
	{
		showToast('All playlists removed', 4);
	});
}

function confirmRemoveAllSavedItems()
{
		showDialog({ title: 'Remove all saved items', body_class: 'dialog_message_div', body_text: 'This will remove all saved items from '+project_name+'. They will not be deleted from Spotify.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog remove_all_saved_items'] } });
}

function removeAllSavedItems()
{
	$.get('library.php?remove_all_saved_items', function()
	{
		showToast('All saved items removed', 4);
	});
}

function confirmClearCache()
{
	showDialog({ title: 'Clear cache', body_class: 'dialog_message_div', body_text: 'This will clear the cache for playlists, albums, etc.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog clear_cache'] } });
}

function clearCache()
{
	$.post('main.php?'+getCurrentTime(), { action: 'clear_cache' }, function()
	{
		showToast('Cache cleared', 2);
	});
}

function confirmRestoreToDefault()
{
	showDialog({ title: 'Restore', body_class: 'dialog_message_div', body_text: 'This will restore all settings, messages, warnings, etc.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog restore_to_default'] } });
}

function restoreToDefault()
{
	activityLoading();

	setTimeout(function()
	{
		removeAllCookies();
		window.location.replace('.');
	}, 2000);
}

// Share

function shareUri(title, uri)
{
	if(getUriType(decodeURIComponent(uri)) == 'local')
	{
		showToast('Not possible for local files', 4);
	}
	else if(ua_is_android_app)
	{
		setTimeout(function()
		{
			Android.JSshare(title, decodeURIComponent(uri));
		}, 250);
	}
	else
	{
		showDialog({ title: title, body_class: 'dialog_share_div', body_text: '<div title="Share on Facebook" class="actions_div" data-actions="open_external_activity" data-uri="http://www.facebook.com/sharer.php?u='+uri+'" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_48_div facebook_48_img_div"></div></div><div title="Share on Twitter" class="actions_div" data-actions="open_external_activity" data-uri="https://twitter.com/share?url='+uri+'" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_48_div twitter_48_img_div"></div></div><div title="Share on Google+" class="actions_div" data-actions="open_external_activity" data-uri="https://plus.google.com/share?url='+uri+'" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_48_div googleplus_48_img_div"></div></div>', button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
	}
}

// UI

function setCss()
{
	$('style', 'head').empty();

	window_width = $(window).width();
	window_height = $(window).height();

	if(ua_is_android && !ua_is_standalone || ua_is_ios && !ua_is_standalone)
	{
		var padding = parseInt($('div#activity_div').css('padding-top'));
		var min_height = window_height - padding * 2 + 128;

		$('div#activity_div').css('min-height', min_height+'px');
	}

	$('div#nowplaying_div').css('bottom', '-'+window_height+'px');

	$('div#menu_div').data('cssleft', $('div#menu_div').css('left'));
	$('div#nowplaying_div').data('cssbottom', $('div#nowplaying_div').css('bottom'));

	$('style', 'head').append('.show_nowplaying_animation { transform: translate3d(0, -'+window_height+'px, 0); -webkit-transform: translate3d(0, -'+window_height+'px, 0); -moz-transform: translate3d(0, -'+window_height+'px, 0); -ms-transform: translate3d(0, -'+window_height+'px, 0); } .hide_nowplaying_animation { transform: translate3d(0, 0, 0); -webkit-transform: translate3d(0, 0, 0); -moz-transform: translate3d(0, 0, 0); -ms-transform: translate3d(0, 0, 0); }');
}

function showDiv(div)
{
	if(!isOpaque(div)) $(div).css('opacity', '1');
}

function hideDiv(div)
{
	if(isOpaque(div)) $(div).removeClass('show_div_animation hide_div_animation').css('opacity', '0');
}

function fadeInDiv(div)
{
	setTimeout(function()
	{
		if(ua_supports_csstransitions)
		{
			$(div).removeClass('hide_div_animation').addClass('show_div_animation');
		}
		else
		{
			$(div).stop().animate({ opacity: '1' }, 250, 'easeInCubic');
		}
	}, 25);
}

function fadeOutDiv(div)
{
	setTimeout(function()
	{
		if(!isOpaque(div)) return;

		if(ua_supports_csstransitions)
		{
			$(div).removeClass('show_div_animation').addClass('hide_div_animation');
		}
		else
		{
			$(div).stop().animate({ opacity: '0' }, 250, 'easeInCubic');
		}
	}, 25);
}

function setCardVerticalSize()
{
	var container_width = $('div.cards_vertical_div').outerWidth();
	var margin = parseInt($('div.card_vertical_div').css('margin-right'));
	var divide = 6;

	if(container_width <= 480)
	{
		divide = 2;
	}
	else if(container_width <= 800)
	{
		divide = 4;
	}

	var size = parseInt(container_width / divide) - margin * 2;

	$('div.card_vertical_div').width(size);
	$('div.card_vertical_cover_art_div').height(size);

	$('div.cards_vertical_div').css('visibility', 'visible');
}

function focusTextInput(id)
{
	if(ua_supports_touch)
	{
		$('input:text').blur();
	}
	else
	{
		$(id).focus();
	}
}

function blurTextInput()
{
	$('input:text').blur();
}

function scrollToTop(animate)
{
	if($(window).scrollTop() != 0)
	{
		if(animate)
		{
			$('html, body').animate({ scrollTop: 0 }, 250);
		}
		else
		{
			window.scrollTo(0, 0);
		}
	}
}

// Toasts

function showToast(text, duration)
{
	clearTimeout(timeout_show_toast);
	clearTimeout(timeout_hide_toast_first);
	clearTimeout(timeout_hide_toast_second);

	$('div#toast_div > div > div').html(text);
	$('div#toast_div').show();

	var width = $('div#toast_div').outerWidth();
	var height = $('div#toast_div').outerHeight();
	var margin = parseInt(width / 2);
	var border_radius = parseInt(height / 2);

	$('div#toast_div').css('margin-left', '-'+margin+'px').css('border-radius', border_radius+'px');

	fadeInDiv('div#toast_div');

	var duration = parseInt(duration * 1000);

	timeout_show_toast = setTimeout(function()
	{
		clearTimeout(timeout_hide_toast_first);
		clearTimeout(timeout_hide_toast_second);

		timeout_hide_toast_first = setTimeout(function()
		{
			fadeOutDiv('div#toast_div');

			timeout_hide_toast_second = setTimeout(function()
			{
				$('div#toast_div').hide();
			}, 250);
		}, duration);
	}, 250);
}

// Dialogs

function showDialog(dialog)
{
	if(isDisplayed('div#dialog_div')) return;

	$('div#black_cover_div').show();

	setTimeout(function()
	{
		if(ua_supports_csstransitions)
		{
			$('div#black_cover_div').removeClass('show_black_cover_div_animation hide_black_cover_div_animation').addClass('show_black_cover_div_animation');
		}
		else
		{
			$('div#black_cover_div').stop().animate({ opacity: '0.5' }, 250, 'easeOutQuad');
		}
	}, 25);

	setTimeout(function()
	{
		$('div#dialog_div').html('<div title="'+dialog.title+'" id="dialog_header_div">'+dialog.title+'</div><div id="dialog_body_div"><div id="'+dialog.body_class+'">'+dialog.body_text+'</div></div><div id="dialog_buttons_div"><div id="dialog_button1_div" class="actions_div" data-highlightclass="light_grey_highlight" onclick="void(0)">'+dialog.button1.text+'</div></div>');

		for(var i = 0; i < dialog.button1.keys.length; i++)
		{
			$('div#dialog_button1_div').data(dialog.button1.keys[i], dialog.button1.values[i]);
		}

		if(typeof dialog.button2 != 'undefined')
		{
			$('div#dialog_buttons_div').append('<div id="dialog_button2_div" class="actions_div" data-highlightclass="light_grey_highlight" onclick="void(0)">'+dialog.button2.text+'</div>');

			for(var i = 0; i < dialog.button2.keys.length; i++)
			{
				$('div#dialog_button2_div').data(dialog.button2.keys[i], dialog.button2.values[i]);
			}
		}

		$('div#dialog_div').show();

		var height = $('div#dialog_div').outerHeight();
		var margin = height / 2;

		$('div#dialog_div').css('margin-top', '-'+margin+'px');

		var max_body_height = parseInt($('div#dialog_body_div').css('max-height'));
		var body_height = $('div#dialog_body_div')[0].scrollHeight;

		if(body_height > max_body_height)
		{
			scrolling_black_cover_div = true;

			$('div#dialog_body_div').css('overflow-y', 'scroll');
		}

		if(ua_supports_csstransitions && ua_supports_csstransforms3d)
		{
			$('div#dialog_div').addClass('prepare_dialog_animation');

			setTimeout(function()
			{
				$('div#dialog_div').addClass('show_dialog_animation');
			}, 25);
		}
		else
		{
			fadeInDiv('div#dialog_div');
		}

		nativeAppCanCloseCover();
	}, 125);
}

function showActionsDialog(dialog)
{
	var title = dialog.title;
	var actions = dialog.actions;
	var body = '';

	for(var i = 0; i < actions.length; i++)
	{
		var data = '';

		for(var f = 0; f < actions[i].keys.length; f++)
		{
			data += 'data-'+actions[i].keys[f]+'="'+actions[i].values[f]+'" ';
		}

		body += '<div title="'+actions[i].text+'" class="actions_div" '+data+' data-highlightclass="light_grey_highlight" onclick="void(0)">'+actions[i].text+'</div>';
	}

	showDialog({ title: title, body_class: 'dialog_actions_div', body_text: body, button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
}

function showDetailsDialog(dialog)
{
	var title = dialog.title;
	var details = dialog.details;
	var body = '';

	for(var i = 0; i < details.length; i++)
	{
		body += '<div title="'+details[i].value+'"><b>'+details[i].detail+':</b> '+details[i].value+'</div>';
	}

	showDialog({ title: title, body_class: 'dialog_details_div', body_text: body, button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
}

function hideDialog()
{
	if(!isDisplayed('div#dialog_div')) return;

	scrolling_black_cover_div = false;

	$('div#dialog_div').hide();

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#dialog_div').removeClass('prepare_dialog_animation show_dialog_animation');
	}
	else
	{
		hideDiv('div#dialog_div');
	}

	setTimeout(function()
	{
		if(isDisplayed('div#dialog_div')) return;

		if(ua_supports_csstransitions)
		{
			$('div#black_cover_div').addClass('hide_black_cover_div_animation').one(event_transitionend, function()
			{
				if(!isDisplayed('div#dialog_div')) $('div#black_cover_div').hide().removeClass('show_black_cover_div_animation hide_black_cover_div_animation');
			});
		}
		else
		{
			$('div#black_cover_div').stop().animate({ opacity: '0' }, 250, 'easeOutQuad', function()
			{
				if(!isDisplayed('div#dialog_div')) $('div#black_cover_div').hide();
			});
		}
	}, 25);

	setTimeout(function()
	{
		checkForDialogs();
	}, 500);

	nativeAppCanCloseCover();
}

function acceptDialog()
{
	if(!isDisplayed('div#dialog_div')) return;

	pointer_moved = false;

	if($('div#dialog_button2_div').length)
	{
		$('div#dialog_button2_div').trigger(pointer_event);
	}
	else
	{
		$('div#dialog_button1_div').trigger(pointer_event);
	}
}

function closeDialog()
{
	if(!isDisplayed('div#dialog_div')) return;

	pointer_moved = false;
	$('div#dialog_button1_div').trigger(pointer_event);
}

function checkForDialogs()
{
	if(isDisplayed('div#dialog_div')) return;

	if(!ua_is_supported)
	{
		var cookie = { id: 'hide_unsupported_browser_dialog_'+project_version, value: 'true', expires: 7 };
		if(!isCookie(cookie.id)) showDialog({ title: 'Browser warning', body_class: 'dialog_message_div', body_text: 'You are using an unsupported browser. If things do not work as they should, you know why.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'Help', keys : ['actions', 'uri'], values: ['open_external_activity', project_website+'?requirements'] } });
	}

	if(!ua_supports_csstransitions || !ua_supports_csstransforms3d)
	{
		var cookie = { id: 'hide_software_accelerated_animations_dialog_'+project_version, value: 'true', expires: 3650 };
		if(!isCookie(cookie.id)) showDialog({ title: 'Browser warning', body_class: 'dialog_message_div', body_text: 'Your browser does not fully support hardware accelerated animations. Simple animations will be used instead, which may result in a less elegant experience.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'Help', keys : ['actions', 'uri'], values: ['open_external_activity', project_website+'?requirements'] } });
	}

	var latest_version = $.cookie('latest_version');

	if(settings_check_for_updates && parseFloat(latest_version) > project_version)
	{
		var cookie = { id: 'hide_update_available_dialog_'+project_version, value: 'true', expires: 7 };
		if(!isCookie(cookie.id)) showDialog({ title: 'Update available', body_class: 'dialog_message_div', body_text: project_name+' '+latest_version+' has been released!', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'Download', keys : ['actions', 'uri'], values: ['open_external_activity', project_website+'?download'] } });
	}

	if(ua_is_android)
	{
		if(ua_is_android_app)
		{
			var cookie = { id: 'hide_android_app_versions_mismatch_dialog_'+project_version, value: 'true', expires: 1 };

			if(!isCookie(cookie.id))
			{
				var show_dialog = false;

				if(('JSgetVersions' in window.Android))
				{
					var versions = $.parseJSON(Android.JSgetVersions());

					var app_version = project_version;
					var app_minimum_version = parseFloat(versions[1]);
					var android_app_version = parseFloat(versions[0]);
					var android_app_minimum_version = project_android_app_minimum_version;

					if(app_version < app_minimum_version || android_app_version < android_app_minimum_version) show_dialog = true;
				}
				else
				{
					show_dialog = true;
				}

				if(show_dialog) showDialog({ title: 'App versions mismatch', body_class: 'dialog_message_div', body_text: 'The '+project_name+' version you are running is not compatible with this Android app version. Make sure you are running the latest version of both '+project_name+' and the Android app.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] } });
			}

			var cookie = { id: 'hide_android_hardware_buttons_dialog_'+project_version, value: 'true', expires: 3650 };
			if(!isCookie(cookie.id)) showDialog({ title: 'Android app tip', body_class: 'dialog_message_div', body_text: 'You can use the hardware volume buttons on your device to control Spotify\'s volume.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] } });

			var installed = parseInt($.cookie('installed_'+project_version));
			var cookie = { id: 'hide_rate_on_google_play_dialog_'+project_version, value: 'true', expires: 3650 };

			if(!isCookie(cookie.id) && ('JSgetPackageName' in window.Android) && getCurrentTime() > installed + 1000 * 3600 * 24)
			{
				var package_name = Android.JSgetPackageName();
				showDialog({ title: 'Like this app?', body_class: 'dialog_message_div', body_text: 'Please rate '+project_name+' on Google Play.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'Rate', keys : ['actions', 'uri'], values: ['open_external_activity', 'market://details?id='+package_name] } });
			}
		}
		else
		{
			var cookie = { id: 'hide_android_app_dialog_'+project_version, value: 'true', expires: 1 };
			if(!isCookie(cookie.id)) showDialog({ title: 'Android app', body_class: 'dialog_message_div', body_text: 'You should install the Android app. It will give you an experience much more similar to a native app, with many additional features.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'Download', keys : ['actions', 'uri'], values: ['open_external_activity', 'market://search?q=pub:'+encodeURIComponent(project_developer)] } });
		}
	}
	else if(ua_is_ios)
	{
		if(ua_is_standalone)
		{
			var cookie = { id: 'hide_ios_back_gesture_dialog_'+project_version, value: 'true', expires: 3650 };
			if(!isCookie(cookie.id)) showDialog({ title: 'iOS tip', body_class: 'dialog_message_div', body_text: 'Since you are running fullscreen and your device has no back button, you can swipe in from the right to go back.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] } });
		}
		else
		{
			var cookie = { id: 'hide_ios_home_screen_dialog_'+project_version, value: 'true' };

			if(!isCookie(cookie.id))
			{
				if(shc(ua, 'iPad'))
				{
					cookie.expires = 28;
					showDialog({ title: 'iPad tip', body_class: 'dialog_message_div', body_text: 'Add '+project_name+' to your home screen to get fullscreen like a native app.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'How to', keys : ['actions', 'uri'], values: ['open_external_activity', project_website+'?add_to_home_screen'] } });
				}
				else
				{
					cookie.expires = 1;
					showDialog({ title: 'iPhone/iPod warning', body_class: 'dialog_message_div', body_text: 'To function correctly, '+project_name+' should be added to your home screen.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'How to', keys : ['actions', 'uri'], values: ['open_external_activity', project_website+'?add_to_home_screen'] } });
				}
			}
		}
	}
	else if(ua_is_pinnable_msie && !window.external.msIsSiteMode())
	{
		var cookie = { id: 'hide_windows_desktop_integration_dialog_'+project_version, value: 'true', expires: 3650 };
		if(!isCookie(cookie.id)) showDialog({ title: 'Windows desktop tip', body_class: 'dialog_message_div', body_text: 'Pin '+project_name+' to the taskbar to get additional features.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'How to', keys : ['actions', 'uri'], values: ['open_external_activity', project_website+'?windows_desktop_integration'] } });
	}
	else if(ua_is_os_x && !ua_is_standalone)
	{
		var cookie = { id: 'hide_ox_x_integration_dialog_'+project_version, value: 'true', expires: 3650 };
		if(!isCookie(cookie.id)) showDialog({ title: 'OS X tip', body_class: 'dialog_message_div', body_text: 'Install Fluid to run '+project_name+' as a standalone app.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'How to', keys : ['actions', 'uri'], values: ['open_external_activity', project_website+'?os_x_integration'] } });
	}
}

// Forms

function submitForm(form)
{
	var value = $('input:text', form).val();
	var hint = $('input:text', form).data('hint');

	if(value != hint) $(form).trigger('submit');
}

// Notifications

function requestNotificationsPermission()
{
	if(!ua_supports_notifications) return;

	if(Notification.permission != 'denied') Notification.requestPermission();
}

function showNotification(title, body, icon, onclick, duration)
{
	if(!settings_notifications || !ua_supports_notifications) return;

	clearTimeout(timeout_notification);

	if(notification != null) notification.close();

	notification = new Notification(title, { body: body, icon: icon })

	if(onclick == 'remote_control_next')
	{
		notification.onclick = function()
		{
			remoteControl('next');
		}
	}

	var duration = parseInt(duration * 1000);

	timeout_notification = setTimeout(function()
	{
		notification.close();
	}, duration);
}

// Native apps

function nativeAppLoad(is_paused)
{
	if(ua_is_android_app)
	{
		if(settings_keep_screen_on)
		{
			Android.JSkeepScreenOn(true);
		}
		else
		{
			Android.JSkeepScreenOn(false);
		}

		if(settings_pause_on_incoming_call)
		{
			Android.JSsetSharedBoolean('PAUSE_ON_INCOMING_CALL', true);
		}
		else
		{
			Android.JSsetSharedBoolean('PAUSE_ON_INCOMING_CALL', false);
		}

		if(settings_flip_to_pause)
		{
			Android.JSflipToPause(true);
		}
		else
		{
			Android.JSflipToPause(false);
		}

		if(settings_persistent_notification)
		{
			Android.JSsetSharedBoolean('PERSISTENT_NOTIFICATION', true);
		}
		else
		{
			Android.JSsetSharedBoolean('PERSISTENT_NOTIFICATION', false);
		}

		var shareUri = urlToUri(Android.JSgetSharedString('SHARE_URI'));

		if(shareUri != '')
		{
			var type = getUriType(shareUri);

			if(type == 'playlist' || type == 'starred')
			{
				$.get('profile.php?is_authorized_with_spotify', function(xhr_data)
				{
					browsePlaylist(shareUri, stringToBoolean(xhr_data));
				});
			}
			else if(type == 'track' || type == 'album')
			{
				browseAlbum(shareUri);
			}
			else if(type == 'artist')
			{
				browseArtist(shareUri);
			}
			else
			{
				showToast('Invalid URI', 2);
			}

			Android.JSsetSharedString('SHARE_URI', '');
		}
	}
}

function nativeAppAction(action)
{
	if(action == 'play_pause')
	{
		remoteControl('play_pause');
	}
	else if(action == 'pause')
	{
		remoteControl('pause');
	}
	else if(action == 'volume_down')
	{
		adjustVolume('down');
	}
	else if(action == 'volume_up')
	{
		adjustVolume('up');
	}
	else if(action == 'back')
	{
		goBack();
	}
	else if(action == 'menu')
	{
		toggleMenu();
	}
	else if(action == 'search')
	{
		changeActivity('search', '', '');
	}
}

function changeNativeAppComputer()
{
	if(ua_is_android_app) showDialog({ title: 'Change computer', body_class: 'dialog_message_div', body_text: 'You can always go back to the list of computers by long-pressing the back button on your device.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys: ['actions'], values: ['confirm_change_native_app_computer'] } });
}

function confirmChangeNativeAppComputer()
{
	if(ua_is_android_app) Android.JSfinishActivity();
}

function nativeAppCanCloseCover()
{
	if(isVisible('div#menu_div') || isDisplayed('div#top_actionbar_overflow_actions_div') || isVisible('div#nowplaying_div') || isDisplayed('div#nowplaying_actionbar_overflow_actions_div') || isDisplayed('div#dialog_div'))
	{
		if(ua_is_android_app) Android.JSsetSharedBoolean('CAN_CLOSE_COVER', true);
	}
	else
	{
		if(ua_is_android_app) Android.JSsetSharedBoolean('CAN_CLOSE_COVER', false);
	}
}

// Desktop integration

function integrateInMSIE()
{
	try
	{
		ie_thumbnail_button_previous = window.external.msSiteModeAddThumbBarButton('img/previous.ico?'+project_serial, 'Previous');
		ie_thumbnail_button_play_pause = window.external.msSiteModeAddThumbBarButton('img/play.ico?'+project_serial, 'Play');
		ie_thumbnail_button_next = window.external.msSiteModeAddThumbBarButton('img/next.ico?'+project_serial, 'Next');
		ie_thumbnail_button_volume_mute = window.external.msSiteModeAddThumbBarButton('img/volume-mute.ico?'+project_serial, 'Mute');
		ie_thumbnail_button_volume_down = window.external.msSiteModeAddThumbBarButton('img/volume-down.ico?'+project_serial, 'Volume down');
		ie_thumbnail_button_volume_up = window.external.msSiteModeAddThumbBarButton('img/volume-up.ico?'+project_serial, 'Volume up');

		ie_thumbnail_button_style_play = 0;
		ie_thumbnail_button_style_pause = window.external.msSiteModeAddButtonStyle(ie_thumbnail_button_play_pause, 'img/pause.ico?'+project_serial, 'Pause');

		window.external.msSiteModeShowThumbBar();

		document.addEventListener('msthumbnailclick', onClickMSIEthumbnailButton, false);

		integrated_in_msie = true;
	}
	catch(exception)
	{

	}
}

function onClickMSIEthumbnailButton(button)
{
	if(button.buttonID == ie_thumbnail_button_previous)
	{
		remoteControl('previous');
	}
	else if(button.buttonID == ie_thumbnail_button_play_pause)
	{
		remoteControl('play_pause');
	}
	else if(button.buttonID == ie_thumbnail_button_next)
	{
		remoteControl('next');
	}
	else if(button.buttonID == ie_thumbnail_button_volume_mute)
	{
		adjustVolume('mute');
	}
	else if(button.buttonID == ie_thumbnail_button_volume_down)
	{
		adjustVolume('down');
	}
	else if(button.buttonID == ie_thumbnail_button_volume_up)
	{
		adjustVolume('up');
	}
}

// Keyboard shortcuts

function enableKeyboardShortcuts()
{
	$.getScript('js/mousetrap.js?'+project_serial, function()
	{
		Mousetrap.bind('1', function() { adjustVolume('mute'); }, 'keyup');
		Mousetrap.bind('2', function() { adjustVolume('down'); }, 'keyup');
		Mousetrap.bind('3', function() { adjustVolume('up'); }, 'keyup');
		Mousetrap.bind('q', function() { changeActivity('playlists', '', ''); }, 'keyup');
		Mousetrap.bind('w', function() { changeActivity('library', '', ''); }, 'keyup');
		Mousetrap.bind('e', function() { changeActivity('discover', '', ''); }, 'keyup');
		Mousetrap.bind('r', function() { changeActivity('search', '', ''); }, 'keyup');
		Mousetrap.bind('a', function() { toggleNowplaying(); }, 'keyup');
		Mousetrap.bind('s', function() { changeActivity('recently-played', '', ''); }, 'keyup');
		Mousetrap.bind('d', function() { changeActivity('queue', '', ''); }, 'keyup');
		Mousetrap.bind('z', function() { remoteControl('previous'); }, 'keyup');
		Mousetrap.bind('x', function() { remoteControl('play_pause'); }, 'keyup');
		Mousetrap.bind('c', function() { remoteControl('next'); }, 'keyup');
		Mousetrap.bind('enter', function() { acceptDialog(); }, 'keyup');
		Mousetrap.bind('esc', function() { goBack(); }, 'keyup');
	});
}

// Check stuff

function checkForErrors()
{
	var cookie = { id: 'test', value: 'true' };
	$.cookie(cookie.id, cookie.value);

	var error_code = (!isCookie(cookie.id)) ? 5 : project_error_code;

	$.removeCookie(cookie.id);

	return error_code;
}

function checkForUpdates(type)
{
	var latest_version_cookie = { id: 'latest_version', expires: 3650 };
	var latest_version = parseFloat($.cookie(latest_version_cookie.id));

	var last_update_check_cookie = { id: 'last_update_check', value: getCurrentTime(), expires: 3650 };
	var last_update_check = $.cookie(last_update_check_cookie.id);

	if(type == 'manual')
	{
		activityLoading();

		xhr_activity = $.get('main.php?check_for_updates', function(xhr_data)
		{
			if(xhr_data == 'error')
			{
				$.removeCookie(latest_version_cookie.id);
			}
			else
			{
				$.cookie(latest_version_cookie.id, xhr_data, { expires: latest_version_cookie.expires });
			}

			changeActivity('settings', '', '');
		});

		$.cookie(last_update_check_cookie.id, last_update_check_cookie.value, { expires: last_update_check_cookie.expires });
	}
	else if(type == 'auto' && settings_check_for_updates)
	{
		if(!isCookie(last_update_check_cookie.id) || !isNaN(last_update_check) && getCurrentTime() - last_update_check > 1000 * 3600 * 24)
		{
			$.get('main.php?check_for_updates', function(xhr_data)
			{
				if(xhr_data == 'error')
				{
					$.removeCookie(latest_version_cookie.id);
				}
				else
				{
					$.cookie(latest_version_cookie.id, xhr_data, { expires: latest_version_cookie.expires });
				}
			});

			$.cookie(last_update_check_cookie.id, last_update_check_cookie.value, { expires: last_update_check_cookie.expires });
		}

		if(latest_version > project_version) $('div#update_available_indicator_div').removeClass('settings_24_img_div').addClass('update_available_24_img_div');
	}
}

function booleanToString(bool)
{
	return (bool) ? 'true' : 'false';
}

function stringToBoolean(string)
{
	return (string == 'true');
}

function shc(string, characters)
{
	var string = string.toLowerCase();
	var characters = characters.toLowerCase();

	return (string.indexOf(characters) != -1);
}

function ssw(string, start)
{
	start = start.replace('.', '\.').replace('*', '\*').replace('/', '\/');
	var regExp = new RegExp('^'+start);
	return (string.match(regExp));
}

function isDisplayed(id)
{
	return ($(id).is(':visible'));
}

function isVisible(id)
{
	return ($(id).css('visibility') == 'visible');
}

function isOpaque(id)
{
	return ($(id).css('opacity') == '1');
}

function textInputHasFocus()
{
	return ($('input:text').is(':focus'));
}

function getCurrentTime()
{
	return new Date().getTime();
}

function getTracksCount(count)
{
	return (count > 1) ? count+' tracks' : count+' track';
}

function getMSIEVersion()
{
	var re = ua.match(/Mozilla\/\d+\.\d+ \(Windows NT \d+\.\d+;.*Trident\/\d+\.\d+;.*rv:(\d+)\.\d+\)/);
	return (re) ? parseInt(re[1]) : 0;
}

// Manipulate stuff

function hsc(string)
{
	return String(string).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

// URIs

function getUriType(uri)
{
	var type = 'unknown';

	if(uri.match(/^spotify:artist:\w{22}$/) || uri.match(/^http:\/\/open\.spotify\.com\/artist\/\w{22}$/))
	{
		type = 'artist';
	}
	else if(uri.match(/^spotify:track:\w{22}$/) || uri.match(/^http:\/\/open\.spotify\.com\/track\/\w{22}$/))
	{
		type = 'track';
	}
	else if(uri.match(/^spotify:local:[^:]+:[^:]*:[^:]+:\d+$/) || uri.match(/^http:\/\/open\.spotify\.com\/local\/[^\/]+\/[^\/]*\/[^\/]+\/\d+$/))
	{
		type = 'local';
	}
	else if(uri.match(/^spotify:album:\w{22}$/) || uri.match(/^http:\/\/open\.spotify\.com\/album\/\w{22}$/))
	{
		type = 'album';
	}
	else if(uri.match(/^spotify:user:[^:]+:playlist:\w{22}$/) || uri.match(/^http:\/\/open\.spotify\.com\/user\/[^\/]+\/playlist\/\w{22}$/))
	{
		type = 'playlist';
	}
	else if(uri.match(/^spotify:user:[^:]+:starred$/) || uri.match(/^http:\/\/open\.spotify\.com\/user\/[^\/]+\/starred$/))
	{
		type = 'starred';
	}
	else if(uri.match(/^http:\/\/o\.scdn\.co\/\w+\/\w+$/) || uri.match(/^https:\/\/\w+\.cloudfront\.net\/\w+\/\w+$/))
	{
		type = 'cover_art';
	}

	return type;
}

function uriToUrl(uri)
{
	if(ssw(uri, 'spotify:'))
	{
		var type = getUriType(uri);

		if(type == 'artist')
		{
			uri = uri.replace('spotify:artist:', 'http://open.spotify.com/artist/');
		}
		else if(type == 'track')
		{
			uri = uri.replace('spotify:track:', 'http://open.spotify.com/track/');
		}
		else if(type == 'local')
		{
			uri = uri.replace('spotify:local:', '').replace(/:/g, '/');
			uri = 'http://open.spotify.com/local/'+uri;
		}
		else if(type == 'album')
		{
			uri = uri.replace('spotify:album:', 'http://open.spotify.com/album/');
		}
		else if(type == 'playlist')
		{
			uri = uri.split(':');
			uri = 'http://open.spotify.com/user/'+uri[2]+'/playlist/'+uri[4];
		}
		else if(type == 'starred')
		{
			uri = uri.split(':');
			uri = 'http://open.spotify.com/user/'+uri[2]+'/starred';
		}
	}

	return uri;
}

function urlToUri(uri)
{
	if(ssw(uri, 'http://open.spotify.com/'))
	{
		var type = getUriType(uri);

		if(type == 'artist')
		{
			uri = uri.replace('http://open.spotify.com/artist/', '');
			uri = 'spotify:artist:'+uri;
		}
		else if(type == 'track')
		{
			uri = uri.replace('http://open.spotify.com/track/', '');
			uri = 'spotify:track:'+uri;
		}
		else if(type == 'local')
		{
			uri = uri.replace('http://open.spotify.com/local/', '').replace(/\//g, ':');
			uri = 'spotify:local:'+uri;
		}
		else if(type == 'album')
		{
			uri = uri.replace('http://open.spotify.com/album/', '');
			uri = 'spotify:album:'+uri;
		}
		else if(type == 'playlist')
		{
			uri = uri.replace('http://open.spotify.com/user/', '').replace(/\//g, ':');
			uri = 'spotify:user:'+uri;
		}
		else if(type == 'starred')
		{
			uri = uri.replace('http://open.spotify.com/user/', '').replace(/\//g, ':');
			uri = 'spotify:user:'+uri;
		}
	}

	return uri;
}

// Cookies

function isCookie(id)
{
	return (typeof $.cookie(id) != 'undefined');
}

function removeAllCookies()
{
	var cookies = $.cookie();

	for(var cookie in cookies)
	{
		if(cookies.hasOwnProperty(cookie)) $.removeCookie(cookie);
	}
}
