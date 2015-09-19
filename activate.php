<?php

// see if we have any saved settings for this plugin
	$anon_guid = elgg_get_plugin_setting('anon_guid','AU_anonymous_comments');
	$recaptcha = elgg_get_plugin_setting('recaptcha','AU_anonymous_comments');
	$public_key = elgg_get_plugin_setting('public_key','AU_anonymous_comments');
	$private_key = elgg_get_plugin_setting('private_key','AU_anonymous_comments');
  $usessl = elgg_get_plugin_setting('usessl', 'AU_anonymous_comments');
	
	//if we don't have a public key, set default
	if(empty($public_key)){
		elgg_set_plugin_setting('public_key', '6LfviMMSAAAAACXdnUPHLHheWkAYIJ-m-8QAOy6R', 'AU_anonymous_comments');
	}
	
	//if we don't have a private key, set default
	
	if(empty($private_key)){
		elgg_set_plugin_setting('private_key', '6LfviMMSAAAAAIi_StJYyPXfRggSR9nEKPqkVqvU', 'AU_anonymous_comments');
	}
	
	// if we don't have a setting for recaptcha, default to yes, better to have it than not if unsure
	if(empty($recaptcha)){
		elgg_set_plugin_setting('recaptcha', 'yes', 'AU_anonymous_comments');	
	}
  
  if (empty($usessl)) {
    elgg_set_plugin_setting('usessl', 'no', 'AU_anonymous_comments');
  }
	
	//get all of the information on our fake user
	$user = get_user($anon_guid);
	
	if(!$user){ // our user is missing, make new one
		$anon_guid = set_anonymous_user();
		//save our fake users guid for the plugin to access
		elgg_set_plugin_setting('anon_guid', $anon_guid, 'AU_anonymous_comments');
	}