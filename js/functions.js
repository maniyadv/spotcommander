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
			setActivityActions('<div title="Retry" class="actions_div" data-actions="reload_activity" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_32_div reload_32_img_div"></div></div>');
			setActivityActionsVisibility('visible');
			setActivityContent('<div id="activity_inner_div"><div id="activity_message_div"><div><div class="img_div img_64_div information_64_img_div"></div></div><div>Request failed. Make sure you are connected. Tap the top right icon to retry.</div></div></div>');

			fadeInDiv('div#activity_div');
		}, 1000);
	});

	if(ua_is_ios && ua_is_standalone)
	{
		var cookie = { id: 'current_activity_'+project_version, value: JSON.stringify({ activity: activity.activity, subactivity: activity.subactivity, args: activity.args }), expires: 1 };

		if(isActivity('playlists', '', ''))
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

	scrollToTop();

	clearTimeout(timeout_activity_loading);

	timeout_activity_loading = setTimeout(function()
	{
		setActivityActionsVisibility('hidden');
		setActivityTitle('Wait...');

		setActivityContent('<div id="activity_loading_div"><div class="img_div img_110_10_div loading_110_img_div"></div></div>');
		fadeInDiv('div#activity_div');
	}, 500);
}

function activityLoaded()
{
	// All
	clearTimeout(timeout_activity_error);

	checkForDialogs();
	checkForUpdates('auto');

	if(!ua_is_standalone) scrollToTop();

	var data = getActivityData();
	var title = (typeof data.title == 'undefined') ? 'Unknown' : data.title;

	setActivityTitle(title);

	if(typeof data.actions != 'undefined')
	{
		setActivityActions('<div title="'+data.actions.icon[0]+'" class="actions_div" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_32_div '+data.actions.icon[1]+'"></div></div>');
		setActivityActionsVisibility('visible');

		for(var i = 0; i < data.actions.keys.length; i++)
		{
			$('div.actions_div', 'div#top_actionbar_inner_right_div > div').data(data.actions.keys[i], data.actions.values[i]);
		}		
	}
	else
	{
		setActivityActionsVisibility('hidden');
	}

	if(typeof data.overflow_actions != 'undefined')
	{
		$('div#top_actionbar_overflow_actions_inner_div').empty();

		for(var i = 0; i < data.overflow_actions.length; i++)
		{
			var highlight_arrow = (i == 0) ? 'data-highlightotherelement="div#top_actionbar_overflow_actions_arrow_div" data-highlightotherelementparent="div#top_actionbar_overflow_actions_div" data-highlightotherelementclass="up_arrow_green_highlight"' : '';

			$('div#top_actionbar_overflow_actions_inner_div').append('<div class="actions_div" data-highlightclass="green_highlight" '+highlight_arrow+' onclick="void(0)">'+data.overflow_actions[i].text+'</div>');

			for(var f = 0; f < data.overflow_actions[i].keys.length; f++)
			{
				$('div.actions_div', 'div#top_actionbar_overflow_actions_inner_div').last().data(data.overflow_actions[i].keys[f], data.overflow_actions[i].values[f]);
			}
		}
	}

	if(typeof data.cover_art_uri != 'undefined' && data.cover_art_uri != '') getCoverArt(data.cover_art_uri);

	// Activities
	if(isActivity('settings', '', ''))
	{
		if(ua_is_android_app) $('div#android_app_settings_div').show();
	}

	// Text fields
	if($('div.input_text_div').length)
	{
		if(ua_supports_touch)
		{
			clearTimeout(timeout_activity_loaded_input_text);

			timeout_activity_loaded_input_text = setTimeout(function()
			{
				$('div.input_text_div').css('visibility', 'visible');

				if(ua_supports_csstransitions && ua_supports_csstransforms3d)
				{
					$('div.input_text_div').addClass('prepare_input_text_animation');

					setTimeout(function()
					{
						$('div.input_text_div').addClass('show_input_text_animation');
					}, 25);
				}
				else
				{
					fadeInDiv('div.input_text_div');
				}
			}, 500);
		}
		else
		{
			$('div.input_text_div').css('visibility', 'visible').css('opacity', '1');

			if($('input:text#search_input').length) focusTextInput('input:text#search_input');
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
		if(isActivity(a.activity, a.subactivity, a.args)) setActivityContent(xhr_data);
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
	var cookie = { id: 'hide_first_time_activity_'+project_version, value: 'true', expires: 36500 };

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

function isActivity(activity, subactivity, args)
{
	var a = getActivity();
	return (a.activity == activity && a.subactivity == subactivity && a.args == args);
}

function goBack()
{
	if(ua_is_ios && ua_is_standalone)
	{
		if(!isDisplayed('div#transparent_cover_div') && !isDisplayed('div#black_cover_div') && !isVisible('div#nowplaying_div') && !textInputHasFocus()) history.back();
	}
	else
	{
		if(isDisplayed('div#dialog_div'))
		{
			pointer_moved = false;
			$('div#dialog_button1_div').trigger(pointer_event);
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

// Menus

function toggleMenu()
{
	if(!isVisible('div#menu_div'))
	{
		showMenu();
	}
	else
	{
		hideMenu();
	}
}

function showMenu()
{
	if(isVisible('div#menu_div') || isDisplayed('div#transparent_cover_div') || isDisplayed('div#black_cover_div') || isVisible('div#nowplaying_div') || textInputHasFocus()) return;

	$('div#transparent_cover_div').show();
	$('div#menu_div').css('visibility', 'visible');

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#menu_div').addClass('show_menu_animation');
	}
	else
	{
		$('div#menu_div').stop().animate({ left: '0' }, 250, 'easeOutExpo');
	}

	nativeAppCanCloseCover();
}

function hideMenu()
{
	if(!isVisible('div#menu_div')) return;

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#menu_div').addClass('hide_menu_animation').one(event_transitionend, function()
		{
			$('div#menu_div').css('visibility', 'hidden').removeClass('show_menu_animation hide_menu_animation');
			$('div#transparent_cover_div').hide();

			nativeAppCanCloseCover();
		});
	}
	else
	{
		$('div#menu_div').stop().animate({ left: $('div#menu_div').data('cssleft') }, 250, 'easeOutExpo', function()
		{
			$('div#menu_div').css('visibility', 'hidden');
			$('div#transparent_cover_div').hide();

			nativeAppCanCloseCover();
		});
	}
}

function showActivityOverflowActions()
{
	if(isDisplayed('div#top_actionbar_overflow_actions_div')) return;

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
			var timeout = (xhr_data == 'queue_is_empty') ? 2500 : 500;

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
	var cookie = { id: 'hide_adjust_volume_control_dialog_'+project_version, value: 'true', expires: 36500 };

	if(!isCookie(cookie.id))
	{
		showDialog({ title: 'Adjust volume', body_class: 'dialog_message_div', body_text: 'With this action you can toggle between adjusting Spotify\'s volume and the system volume.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires', 'volumecontrol'], values: ['hide_dialog set_cookie adjust_volume_control', cookie.id, cookie.value, cookie.expires, control] } });
	}
	else
	{
		var cookie = { id: 'settings_volume_control', value: control, expires: 36500 };
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
	var cookie = { id: 'hide_shuffle_repeat_dialog_'+project_version, value: 'true', expires: 36500 };

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

function playUriRandomly(uri)
{
	var cookie = { id: 'hide_play_uri_randomly_dialog_'+project_version, value: 'true', expires: 36500 };

	if(!isCookie(cookie.id))
	{
		showDialog({ title: 'Play randomly', body_class: 'dialog_message_div', body_text: 'This action plays the media, toggles shuffle off/on and skips one track to ensure random playback. Shuffle must already be enabled. Advertisements may prevent this from working.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires', 'uri'], values: ['hide_dialog set_cookie play_uri_randomly', cookie.id, cookie.value, cookie.expires, uri] } });
	}
	else
	{
		startRefreshNowplaying();

		$.post('main.php?'+getCurrentTime(), { action: 'play_uri_randomly', data: uri }, function()
		{
			refreshNowplaying('manual');
		});
	}
}

function startTrackRadio(uri, play_first)
{
	var cookie = { id: 'hide_start_track_radio_dialog_'+project_version, value: 'true', expires: 36500 };

	if(getUriType(uri) == 'local')
	{
		showToast('Not possible for local files', 2);
	}
	else if(!isCookie(cookie.id))
	{
		showDialog({ title: 'Start track radio', body_class: 'dialog_message_div', body_text: 'For this to work, you must leave the mouse cursor over the now playing cover art (avoid the resize button) on the computer running Spotify, or it will fail badly. If the up keyboard button is simulated wrong number of times, enable the fix in settings. It may not work on all tracks.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys: ['actions', 'cookieid', 'cookievalue', 'cookieexpires', 'uri', 'playfirst'], values: ['hide_dialog set_cookie start_track_radio', cookie.id, cookie.value, cookie.expires, uri, play_first] } });
	}
	else
	{
		showToast('Starting track radio', 2);

		var data = JSON.stringify([uri, play_first, settings_start_track_radio_fix]);

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

function playArtist(uri)
{
	if(getUriType(uri) == 'local')
	{
		showToast('Not possible for local files', 2);
	}
	else
	{
		if(getUriType(uri) != 'artist') showToast('Playing artist', 2);

		$.post('main.php?'+getCurrentTime(), { action: 'play_artist', data: uri }, function(xhr_data)
		{
			if(xhr_data == 'error')
			{
				showToast('Could not look up artist', 2);
			}
			else
			{
				startRefreshNowplaying();

				setTimeout(function()
				{
					refreshNowplaying('manual');
				}, 1000);
			}
		});
	}
}

// Now playing

function toggleNowplaying()
{
	if(!isVisible('div#nowplaying_div'))
	{
		hideActivityOverflowActions();
		hideMenu();
		showNowplaying();
	}
	else
	{
		hideNowplayingOverflowActions();
		hideNowplaying();
	}
}

function showNowplaying()
{
	if(isVisible('div#nowplaying_div') || textInputHasFocus()) return;

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
			$('div#nowplaying_div').css('visibility', 'hidden').removeClass('show_nowplaying_animation hide_nowplaying_animation');

			nativeAppCanCloseCover();
		});
	}
	else
	{
		$('div#nowplaying_div').stop().animate({ bottom: $('div#nowplaying_div').data('cssbottom') }, 500, 'easeOutExpo', function()
		{
			$('div#nowplaying_div').css('visibility', 'hidden');

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

	nativeAppCanCloseCover();
}

function startRefreshNowplaying()
{
	nowplaying_refreshing = true;

	xhr_nowplaying.abort();

	clearTimeout(timeout_nowplaying_error);

	hideNowplayingOverflowActions();

	$('div#nowplaying_actionbar_right_div > div').removeClass('actions_div').css('opacity', '0.5');

	$('div#bottom_actionbar_inner_center_div > div').html('Refreshing...');

	if(ua_supports_csstransitions && ua_supports_csstransforms3d)
	{
		$('div#nowplaying_cover_art_div').css('transition', '').css('transform', '').css('-webkit-transition', '').css('-webkit-transform', '').css('-moz-transition', '').css('-moz-transform', '').css('-o-transition', '').css('-o-transform', '').css('-ms-transition', '').css('-ms-transform', '');
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

			var cookie = { id: 'nowplaying_uri', value: nowplaying.uri, expires: 36500 };
			$.cookie(cookie.id, cookie.value, { expires: cookie.expires });

			$('div#nowplaying_actionbar_right_div > div').css('opacity', '');

			$('div#nowplaying_actionbar_right_div').html('<div title="'+nowplaying.actions.icon[0]+'" class="actions_div" data-highlightclass="green_highlight" onclick="void(0)"><div class="img_div img_32_div '+nowplaying.actions.icon[1]+'"></div></div>');

			for(var i = 0; i < nowplaying.actions.keys.length; i++)
			{
				$('div.actions_div', 'div#nowplaying_actionbar_right_div').data(nowplaying.actions.keys[i], nowplaying.actions.values[i]);
			}

			$('div#nowplaying_actionbar_overflow_actions_inner_div').empty();

			for(var i = 0; i < nowplaying.overflow_actions.length; i++)
			{
				var highlight_arrow = (i == 0) ? 'data-highlightotherelement="div#nowplaying_actionbar_overflow_actions_arrow_div" data-highlightotherelementparent="div#nowplaying_actionbar_overflow_actions_div" data-highlightotherelementclass="up_arrow_green_highlight"' : '';

				$('div#nowplaying_actionbar_overflow_actions_inner_div').append('<div class="actions_div" data-highlightclass="green_highlight" '+highlight_arrow+' onclick="void(0)">'+nowplaying.overflow_actions[i].text+'</div>');

				for(var f = 0; f < nowplaying.overflow_actions[i].keys.length; f++)
				{
					$('div.actions_div', 'div#nowplaying_actionbar_overflow_actions_inner_div').last().data(nowplaying.overflow_actions[i].keys[f], nowplaying.overflow_actions[i].values[f]);
				}
			}

			$('div#nowplaying_cover_art_div').data('uri', nowplaying.uri).attr('title', nowplaying.album+' ('+nowplaying.released+')');

			$('img#nowplaying_cover_art_preload_img').attr('src', 'img/album-24.png').attr('src', nowplaying.cover_art).on('load error', function(event)
			{
				$(this).off('load error');
				var cover_art = (event.type == 'load') ? nowplaying.cover_art : 'img/no-cover-art-640.png';
				$('div#nowplaying_cover_art_div').css('background-image', 'url("'+cover_art+'")');
				if(type == 'manual') endRefreshNowplaying();
			});

			$('div#nowplaying_artist_div').attr('title', nowplaying.artist).html(hsc(nowplaying.artist));
			$('div#nowplaying_title_div').attr('title', nowplaying.title+' ('+nowplaying.tracklength+')').html(hsc(nowplaying.title));

			$('input#nowplaying_volume_slider').val(nowplaying.current_volume);
			$('span#nowplaying_volume_level_span').html(nowplaying.current_volume);

			$('div#nowplaying_play_pause_div').removeClass('play_64_img_div pause_64_img_div').addClass(nowplaying.play_pause+'_64_img_div');

			$('div#bottom_actionbar_inner_left_div > div > div > div').removeClass('play_32_img_div pause_32_img_div').addClass(nowplaying.play_pause+'_32_img_div');

			if(type == 'manual' || nowplaying_last_uri != nowplaying.uri)
			{
				nowplaying_last_uri = nowplaying.uri;

				hideDiv('div#bottom_actionbar_inner_center_div > div');
				$('div#bottom_actionbar_inner_center_div > div').attr('title', nowplaying.artist+' - '+nowplaying.title+' ('+nowplaying.tracklength+')').html(hsc(nowplaying.title));
				fadeInDiv('div#bottom_actionbar_inner_center_div > div');
			}
			else
			{
				$('div#bottom_actionbar_inner_center_div > div').attr('title', nowplaying.artist+' - '+nowplaying.title+' ('+nowplaying.tracklength+')').html(hsc(nowplaying.title));
			}

			highlightNowplayingListItem();

			if(integrated_in_ubuntu_unity)
			{
				var ubuntu_unity_cover_art = (nowplaying.cover_art == 'img/no-cover-art-640.png') ? project_website+'img/ubuntu-unity-no-cover-art.png' : nowplaying.cover_art;
				var ubuntu_unity_metadata = { title: nowplaying.title, album: nowplaying.album, artist: nowplaying.artist, artLocation: ubuntu_unity_cover_art }

				ubuntu_unity.MediaPlayer.setTrack(ubuntu_unity_metadata);

				if(nowplaying.play_pause == 'play')
				{
					ubuntu_unity.MediaPlayer.setPlaybackState(ubuntu_unity.MediaPlayer.PlaybackState.PAUSED);
				}
				else
				{
					ubuntu_unity.MediaPlayer.setPlaybackState(ubuntu_unity.MediaPlayer.PlaybackState.PLAYING);
				}
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

			getRecentlyPlayed();
			getQueue();
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
		$('div#nowplaying_cover_art_div').removeClass('hide_nowplaying_cover_art_animation').addClass('prepare_nowplaying_cover_art_animation');

		setTimeout(function()
		{
			$('div#nowplaying_cover_art_div').addClass('show_nowplaying_cover_art_animation').one(event_transitionend, function()
			{
				$('div#nowplaying_cover_art_div').removeClass('prepare_nowplaying_cover_art_animation show_nowplaying_cover_art_animation hide_nowplaying_cover_art_animation ');
			});
		}, 25);
	}
	else
	{
		var changeside = parseInt(window_width * 2);
		$('div#nowplaying_cover_art_div').stop().css('left', changeside+'px').animate({ left: '0' }, 500, 'easeOutExpo');
	}
}

function autoRefreshNowplaying(action)
{
	if(action == 'start' && settings_nowplaying_refresh_interval >= 5)
	{
		var cookie = { id: 'nowplaying_last_update', expires: 36500 };
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
				$('img#cover_art_preload_img').attr('src', 'img/album-24.png').attr('src', xhr_data).on('load error', function(event)
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

// Recently played

function getRecentlyPlayed()
{
	if(isActivity('recently-played', '', '')) refreshActivity();
}

function clearRecentlyPlayed()
{
	$.get('recently-played.php?clear', function()
	{
		getRecentlyPlayed();
	});
}

// Queue

function getQueue()
{
	if(isActivity('queue', '', '')) refreshActivity();
}

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
			getQueue();
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
				getQueue();
			}
		}, 1000);
	});
}

function moveQueuedUri(id, sortorder, direction)
{
	$.post('queue.php?move&'+getCurrentTime(), { id: id, sortorder: sortorder, direction: direction }, function(xhr_data)
	{
		getQueue();
	});
}

function removeFromQueue(id, sortorder)
{
	$.post('queue.php?remove&'+getCurrentTime(), { id: id, sortorder: sortorder }, function()
	{
		getQueue();
	});	
}

function clearQueue()
{
	$.get('queue.php?clear', function()
	{
		getQueue();	
	});
}

// Playlists

function getPlaylists()
{
	if(isActivity('playlists', '', '')) refreshActivity();
}

function addPlaylists(uris)
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
		$('div.below_form_div').html('One or more invalid playlist URIs!');
		focusTextInput('input:text#add_playlists_uris_input');
	}
	else
	{
		blurTextInput();
		activityLoading();

		xhr_activity = $.post('playlists.php?add_uris&'+getCurrentTime(), { uris: uris }, function(xhr_data)
		{
			changeActivity('playlists', '', '');
			if(xhr_data == 'error') showDialog({ title: 'Add playlists', body_class: 'dialog_message_div', body_text: 'Could not get the name for one or more playlists. They may be invalid. Try again.', button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
		});
	}
}

function confirmAddSpotifyPlaylists()
{
	showDialog({ title: 'Add from Spotify', body_class: 'dialog_message_div', body_text: 'You should only do this if your Spotify playlists are not listed automatically.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog add_spotify_playlists'] } });
}

function addSpotifyPlaylists()
{
	activityLoading();

	xhr_activity = $.post('playlists.php?add_from_spotify&'+getCurrentTime(), function(xhr_data)
	{
		changeActivity('playlists', '', '');
		if(xhr_data == 'error') showDialog({ title: 'Add from Spotify', body_class: 'dialog_message_div', body_text: 'Some playlists may not have been imported. Try again.', button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
	});	
}

function removePlaylist(id)
{
	$.post('playlists.php?remove&'+getCurrentTime(), { id: id }, function()
	{
		getPlaylists();
	});
}

// Starred

function getStarred()
{
	if(isActivity('starred', '', '')) refreshActivity();
}

function starUri(type, artist, title, uri, toast)
{
	$.post('starred.php?star&'+getCurrentTime(), { type: type, artist: artist, title: title, uri: uri }, function()
	{
		if(toast)
		{
			if(getUriType(uri) == 'track' || getUriType(uri) == 'local')
			{
				showToast('Track starred', 2);
			}
			else if(getUriType(uri) == 'album')
			{
				showToast('Album starred', 2);
			}
		}

		getStarred();
	});
}

function unstarUri(uri, toast)
{
	$.post('starred.php?unstar&'+getCurrentTime(), { uri: uri }, function()
	{
		if(toast)
		{
			if(getUriType(uri) == 'track' || getUriType(uri) == 'local')
			{
				showToast('Track unstarred', 2);
			}
			else if(getUriType(uri) == 'album')
			{
				showToast('Album unstarred', 2);
			}
		}

		getStarred();
	});
}

function importStarredTracks(uris)
{
	var uris = $.trim(uris);
	var validate = uris.split(' ');

	var invalid = false;

	for(var i = 0; i < validate.length; i++)
	{
		if(getUriType(validate[i]) != 'track' && getUriType(validate[i]) != 'local') invalid = true;
	}

	if(invalid)
	{
		$('div.below_form_div').html('One or more invalid track URIs!');
		focusTextInput('input:text#import_starred_tracks_uri_input');
	}
	else
	{
		blurTextInput();
		activityLoading();

		xhr_activity = $.post('starred.php?import_uris&'+getCurrentTime(), { uris: uris }, function(xhr_data)
		{
			changeActivity('starred', '', '');

			if(xhr_data == 'error') showDialog({ title: 'Import starred tracks', body_class: 'dialog_message_div', body_text: 'All tracks may not have been imported. Spotify\'s lookup API is unstable some times. You may have to try several times.', button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
		});
	}
}

function confirmImportStarredSpotifyTracks()
{
	showDialog({ title: 'Import from Spotify', body_class: 'dialog_message_div', body_text: 'This is very experimental. You may have to try several times for it to work, and when it does, it may take some time. Importing manually is recommended.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog import_starred_spotify_tracks'] } });
}

function importStarredSpotifyTracks()
{
	activityLoading();

	xhr_activity = $.post('starred.php?import_from_spotify&'+getCurrentTime(), function(xhr_data)
	{
		changeActivity('starred', '', '');
		if(xhr_data == 'error') showDialog({ title: 'Import from Spotify', body_class: 'dialog_message_div', body_text: 'All tracks may not have been imported. You may have to try several times.', button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
	});
}

// Search & lookup

function searchSpotify(string)
{
	if(string == '')
	{
		focusTextInput('input:text#search_input');
	}
	else
	{
		blurTextInput();

		$.cookie('settings_sort_search_tracks', 'default', { expires: 36500 });
		$.cookie('settings_sort_search_albums', 'default', { expires: 36500 });

		changeActivity('search', 'search', 'string='+string+'&region='+settings_region);
	}
}

function browseAlbum(uri)
{
	if(uri == '') return;

	if(getUriType(uri) == 'local')
	{
		showToast('Not possible for local files', 2);
	}
	else
	{
		changeActivity('album', '', 'uri='+uri);
	}
}

function getSearchHistory()
{
	if(isActivity('search', 'history', '')) refreshActivity();
}

function clearSearchHistory()
{
	$.get('search.php?clear', function()
	{
		getSearchHistory();
	});
}

// Settings

function saveSetting(setting, value)
{
	var cookie = { id: setting, value: value, expires: 36500 };
	$.cookie(cookie.id, cookie.value, { expires: cookie.expires });
	showToast('Tap top right icon to apply', 2);
}

function applySettings()
{
	if(ua_is_ios && ua_is_standalone) $.removeCookie('current_activity_'+project_version);
	window.location.replace('.');
}

// Cache

function confirmClearCache()
{
	showDialog({ title: 'Clear cache', body_class: 'dialog_message_div', body_text: 'This will clear the cache for playlists, albums, etc.<br><br>Continue?', button1: { text: 'No', keys : ['actions'], values: ['hide_dialog'] }, button2: { text: 'Yes', keys : ['actions'], values: ['hide_dialog clear_cache'] } });
}

function clearCache()
{
	showToast('Clearing cache', 2);

	setTimeout(function()
	{
		$.post('main.php?'+getCurrentTime(), { action: 'clear_cache' }, function()
		{
			showToast('Cache cleared', 2);
		});
	}, 2000);
}

// Restore to default

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

function shareUri(uri)
{
	if(getUriType(decodeURIComponent(uri)) == 'local')
	{
		showToast('Not possible for local files', 2);
	}
	else if(ua_is_android_app)
	{
		setTimeout(function()
		{
			Android.JSshare(decodeURIComponent(uri));
		}, 250);
	}
	else
	{
		showDialog({ title: 'Share', body_class: 'dialog_share_div', body_text: '<div title="Share on Facebook" class="actions_div" data-actions="open_external_activity" data-uri="http://www.facebook.com/sharer.php?u='+uri+'" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_48_div facebook_48_img_div"></div></div><div title="Share on Twitter" class="actions_div" data-actions="open_external_activity" data-uri="https://twitter.com/share?url='+uri+'" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_48_div twitter_48_img_div"></div></div><div title="Share on Google+" class="actions_div" data-actions="open_external_activity" data-uri="https://plus.google.com/share?url='+uri+'" data-highlightclass="light_grey_highlight" onclick="void(0)"><div class="img_div img_48_div googleplus_48_img_div"></div></div>', button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
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

	$('style', 'head').append('.show_nowplaying_animation { transform: translate3d(0, -'+window_height+'px, 0); -webkit-transform: translate3d(0, -'+window_height+'px, 0); -moz-transform: translate3d(0, -'+window_height+'px, 0); -o-transform: translate3d(0, -'+window_height+'px, 0); -ms-transform: translate3d(0, -'+window_height+'px, 0); } .hide_nowplaying_animation { transform: translate3d(0, 0, 0); -webkit-transform: translate3d(0, 0, 0); -moz-transform: translate3d(0, 0, 0); -o-transform: translate3d(0, 0, 0); -ms-transform: translate3d(0, 0, 0); }');
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

function showToast(text, duration)
{
	clearTimeout(timeout_show_toast);
	clearTimeout(timeout_hide_toast_first);
	clearTimeout(timeout_hide_toast_second);

	$('div#toast_div').html(text).show();

	var width = $('div#toast_div').outerWidth();
	var margin = parseInt(width / 2);

	$('div#toast_div').css('margin-left', '-'+margin+'px');

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

function scrollToTop()
{
	if($(window).scrollTop() != 1) window.scrollTo(0, 1);
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
		$('div#dialog_div').html('<div title="'+dialog.title+'" id="dialog_header_div">'+dialog.title+'</div><div id="dialog_body_div"><div id="'+dialog.body_class+'">'+dialog.body_text+'</div></div><div id="dialog_buttons_div"><div id="dialog_button1_div" class="actions_div" data-highlightclass="green_highlight" onclick="void(0)">'+dialog.button1.text+'</div></div>');

		for(var i = 0; i < dialog.button1.keys.length; i++)
		{
			$('div#dialog_button1_div').data(dialog.button1.keys[i], dialog.button1.values[i]);
		}

		if(typeof dialog.button2 != 'undefined')
		{
			$('div#dialog_buttons_div').append('<div id="dialog_button2_div" class="actions_div" data-highlightclass="green_highlight" onclick="void(0)">'+dialog.button2.text+'</div>');

			for(var i = 0; i < dialog.button2.keys.length; i++)
			{
				$('div#dialog_button2_div').data(dialog.button2.keys[i], dialog.button2.values[i]);
			}
		}

		$('div#dialog_div').show();

		var height = $('div#dialog_div').outerHeight();
		var margin = height / 2;

		$('div#dialog_div').css('margin-top', '-'+margin+'px');

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
	var dialog = $.parseJSON($.base64.decode(dialog));
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

		body += '<div title="'+actions[i].text+'" class="actions_div" '+data+' data-highlightclass="green_highlight" onclick="void(0)">'+actions[i].text+'</div>';
	}

	showDialog({ title: title, body_class: 'dialog_actions_div', body_text: body, button1: { text: 'Close', keys : ['actions'], values: ['hide_dialog'] } });
}

function showDetailsDialog(dialog)
{
	var dialog = $.parseJSON($.base64.decode(dialog));
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

function closeDialog()
{
	if(isDisplayed('div#dialog_div')) return;

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
		var cookie = { id: 'hide_software_accelerated_animations_dialog_'+project_version, value: 'true', expires: 36500 };
		if(!isCookie(cookie.id)) showDialog({ title: 'Browser warning', body_class: 'dialog_message_div', body_text: 'Your browser does not fully support hardware accelerated animations. Simple animations will be used instead, which may result in a less elegant experience.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'Help', keys : ['actions', 'uri'], values: ['open_external_activity', project_website+'?requirements'] } });
	}

	var latest_version = parseFloat($.cookie('latest_version'));

	if(settings_check_for_updates && latest_version > project_version)
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

			var cookie = { id: 'hide_android_hardware_buttons_dialog_'+project_version, value: 'true', expires: 36500 };
			if(!isCookie(cookie.id)) showDialog({ title: 'Android app tip', body_class: 'dialog_message_div', body_text: 'You can use the hardware volume buttons on your device to control Spotify\'s volume.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] } });

			var installed = parseInt($.cookie('installed'));
			var cookie = { id: 'hide_rate_on_google_play_dialog_'+project_version, value: 'true', expires: 36500 };

			if(!isCookie(cookie.id) && ('JSgetPackageName' in window.Android) && getCurrentTime() > installed + 1000 * 3600 * 24)
			{
				var package_name = Android.JSgetPackageName();
				var uri = 'market://details?id='+package_name;

				showDialog({ title: 'Like this app?', body_class: 'dialog_message_div', body_text: 'Please rate '+project_name+' on Google Play.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'Rate', keys : ['actions', 'uri'], values: ['open_external_activity', uri] } });
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
			var cookie = { id: 'hide_ios_back_gesture_dialog_'+project_version, value: 'true', expires: 36500 };
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
	else if(ua_is_ubuntu_unity)
	{
		var cookie = { id: 'hide_ubuntu_unity_integration_dialog_'+project_version, value: 'true', expires: 36500 };
		if(!isCookie(cookie.id)) showDialog({ title: 'Ubuntu Unity tip', body_class: 'dialog_message_div', body_text: 'Integrate '+project_name+' with Unity to get additional features.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'How to', keys : ['actions', 'uri'], values: ['open_external_activity', project_website+'?ubuntu_unity_integration'] } });
	}
	else if(ua_is_pinnable_msie && !window.external.msIsSiteMode())
	{
		var cookie = { id: 'hide_windows_desktop_integration_dialog_'+project_version, value: 'true', expires: 36500 };
		if(!isCookie(cookie.id)) showDialog({ title: 'Windows desktop tip', body_class: 'dialog_message_div', body_text: 'Pin '+project_name+' to the taskbar to get additional features.', button1: { text: 'Close', keys : ['actions', 'cookieid', 'cookievalue', 'cookieexpires'], values: ['hide_dialog set_cookie', cookie.id, cookie.value, cookie.expires] }, button2: { text: 'How to', keys : ['actions', 'uri'], values: ['open_external_activity', project_website+'?windows_desktop_integration'] } });
	}
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

		if(Android.JSgetSharedString('SHARE_STRING') != '')
		{
			var string = Android.JSgetSharedString('SHARE_STRING');

			if(getUriType(string) == 'track')
			{
				showToast('Track is being imported', 2);
				importStarredTracks(string);
			}
			else if(getUriType(string) == 'playlist')
			{
				showToast('Playlist is being added', 2);
				addPlaylists(string);
			}
			else
			{
				showToast('Invalid URI', 2);
			}

			Android.JSsetSharedString('SHARE_STRING', '');
		}
	}
}

function nativeAppAction(action)
{
	if(action == 'volume_down')
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
	else if(action == 'pause')
	{
		remoteControl('pause');
	}
}

function changeNativeAppUrl()
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

function integrateInUbuntuUnity()
{
	try
	{
		ubuntu_unity = window.external.getUnityObject(1.0);

		ubuntu_unity.init({ name: project_name, iconUrl: project_website+'img/ubuntu-unity-icon.png', onInit: function()
		{
			ubuntu_unity.MediaPlayer.onPrevious(function()
			{
				remoteControl('previous');
			});

			ubuntu_unity.MediaPlayer.onPlayPause(function()
			{
				remoteControl('play_pause');
			});

			ubuntu_unity.MediaPlayer.onNext(function()
			{
				remoteControl('next');
			});
		}});

		integrated_in_ubuntu_unity = true;
	}
	catch(exception)
	{

	}
}

function integrateInMSIE()
{
	try
	{
		ie_thumbnail_button_previous = window.external.msSiteModeAddThumbBarButton('img/previous.ico', 'Previous');
		ie_thumbnail_button_play_pause = window.external.msSiteModeAddThumbBarButton('img/play.ico', 'Play');
		ie_thumbnail_button_next = window.external.msSiteModeAddThumbBarButton('img/next.ico', 'Next');
		ie_thumbnail_button_volume_mute = window.external.msSiteModeAddThumbBarButton('img/volume-mute.ico', 'Mute');
		ie_thumbnail_button_volume_down = window.external.msSiteModeAddThumbBarButton('img/volume-down.ico', 'Volume down');
		ie_thumbnail_button_volume_up = window.external.msSiteModeAddThumbBarButton('img/volume-up.ico', 'Volume up');

		ie_thumbnail_button_style_play = 0;
		ie_thumbnail_button_style_pause = window.external.msSiteModeAddButtonStyle(ie_thumbnail_button_play_pause, 'img/pause.ico', 'Pause');

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
	$.getScript('js/mousetrap.js', function()
	{
		Mousetrap.bind('1', function() { adjustVolume('mute'); }, 'keyup');
		Mousetrap.bind('2', function() { adjustVolume('down'); }, 'keyup');
		Mousetrap.bind('3', function() { adjustVolume('up'); }, 'keyup');
		Mousetrap.bind('q', function() { changeActivity('playlists', '', ''); }, 'keyup');
		Mousetrap.bind('w', function() { changeActivity('starred', '', ''); }, 'keyup');
		Mousetrap.bind('e', function() { changeActivity('discover', '', ''); }, 'keyup');
		Mousetrap.bind('r', function() { changeActivity('search', '', ''); }, 'keyup');
		Mousetrap.bind('a', function() { toggleNowplaying(); }, 'keyup');
		Mousetrap.bind('s', function() { changeActivity('recently-played', '', ''); }, 'keyup');
		Mousetrap.bind('d', function() { changeActivity('queue', '', ''); }, 'keyup');
		Mousetrap.bind('z', function() { remoteControl('previous'); }, 'keyup');
		Mousetrap.bind('x', function() { remoteControl('play_pause'); }, 'keyup');
		Mousetrap.bind('c', function() { remoteControl('next'); }, 'keyup');
		Mousetrap.bind('esc', function() { goBack(); }, 'keyup');
	});
}

// Check stuff

function checkForErrors()
{
	var cookie = { id: 'test', value: 'true' };
	$.cookie(cookie.id, cookie.value);

	var code = (!isCookie(cookie.id)) ? 5 : error_code;

	$.removeCookie(cookie.id);

	return code;
}

function checkForUpdates(type)
{
	var latest_version_cookie = { id: 'latest_version', expires: 36500 };
	var latest_version = parseFloat($.cookie(latest_version_cookie.id));

	var last_update_check_cookie = { id: 'last_update_check', value: getCurrentTime(), expires: 36500 };
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

		if(latest_version > project_version) $('div#update_available_indicator_div').removeClass('settings_48_img_div').addClass('update_available_48_img_div');
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

function getInternetExplorerVersion()
{
	var rv = -1;

	if(navigator.appName == 'Microsoft Internet Explorer')
	{
		var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) != null) rv = parseFloat(RegExp.$1);
	}

	return rv;
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
	else if(uri.match(/^http:\/\/o\.scdn\.co\/\d+\/\w+$/) || uri.match(/^https:\/\/\w+\.cloudfront\.net\/\d+\/\w+$/))
	{
		type = 'cover_art';
	}

	return type;
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
