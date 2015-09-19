<?php

namespace AU\AnonymousComments;

$version = elgg_get_plugin_setting('version', PLUGIN_ID);
if (!$version) {
	elgg_set_plugin_setting('version', PLUGIN_VERSION, PLUGIN_ID);
}

// see if we have any saved settings for this plugin
$recaptcha = elgg_get_plugin_setting('recaptcha', PLUGIN_ID);
$recaptcha_style = elgg_get_plugin_setting('recaptcha_style', PLUGIN_ID);
$usessl = elgg_get_plugin_setting('usessl', PLUGIN_ID);
$add_to_river = elgg_get_plugin_setting('add_to_river', PLUGIN_ID);


// if we don't have a setting for recaptcha, default to yes, better to have it than not if unsure
if (!$recaptcha) {
	elgg_set_plugin_setting('recaptcha', 'yes', PLUGIN_ID);
}

if (!$recaptcha_style) {
	elgg_set_plugin_setting('recaptcha_style', 'light', PLUGIN_ID);
}

if (!$add_to_river) {
	elgg_set_plugin_setting('add_to_river', 'no', PLUGIN_ID);
}

//generate our fake user if it's not already existing
$user = get_anon_user();
