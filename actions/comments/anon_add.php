<?php
/**
 * Elgg add comment action
 *
 * @package Elgg.Core
 * @subpackage Comments
 */
// action_gatekeeper(); removed as not needed since elgg 1.7

elgg_make_sticky_form('AU_anonymous_comments');

global $CONFIG;

$entity_guid = (int) get_input('entity_guid');

$comment_text = get_input('generic_comment');
$comment_text = strip_tags($comment_text, '<p><br><i><em><strong><b><ul><s>');
$anon_name = get_input('anon_name');
$anon_email = get_input('anon_email');
$ip_address = $_SERVER['REMOTE_ADDR'];

// check if recaptcha was correct - only if recaptcha is turned on
if(elgg_get_plugin_setting('recaptcha','AU_anonymous_comments') == "yes"){
	require_once(elgg_get_plugins_path() . 'AU_anonymous_comments/lib/recaptchalib.php');
	$privatekey = elgg_get_plugin_setting('private_key', 'AU_anonymous_comments');
	$resp = recaptcha_check_answer ($privatekey,
	$_SERVER["REMOTE_ADDR"],
	$_POST["recaptcha_challenge_field"],
	$_POST["recaptcha_response_field"]);

	if (!$resp->is_valid) {
		// What happens when the CAPTCHA was entered incorrectly
		register_error(elgg_echo('AU_anonymous_comments:recaptcha_fail'));
		forward(REFERRER);
	}
}

// use stopforumspam to limit attempts to mess with comments
	$url = "http://api.stopforumspam.com/api?ip=" .$ip_address ."email=".$anon_email. "&f=serial";

   // check stopforumspam
   $pCurl = curl_init($url);

   curl_setopt($pCurl, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($pCurl, CURLOPT_FOLLOWLOCATION, true);
   curl_setopt($pCurl, CURLOPT_TIMEOUT, 10);

   $contents = curl_exec($pCurl);
   $aInfo = curl_getinfo($pCurl);

   if($aInfo['http_code'] === 200) {
     $data = unserialize($contents);
     $ip_frequency = $data[ip][frequency];
     if($ip_frequency != '0'){
       // spammer
		register_error(elgg_acho('AU_anonymous_comments:stopforumspam_fail'));
		forward(REFERER);
     }
   }
// check if comment has content, if not send them back
if (empty($comment_text)) {
	register_error(elgg_echo("generic_comment:blank"));
	forward(REFERER);
}

// check if name was entered, if not send them back
if (empty($anon_name)) {
	register_error(elgg_echo("AU_anonymous_comments:name_blank"));
	forward(REFERER);
}

// check if name was entered, if not send them back
if (empty($anon_email)) {
	register_error(elgg_echo("AU_anonymous_comments:email_blank"));
	forward(REFERER);
}

//look for too many URLs - normally a sign of bad people
//one URL is allowed, just in case it's a trackback or whatever - but bad people seldom stick with one!

if (substr_count($comment_text, "http://")>1){
	register_error(elgg_echo("AU_anonymous_comments:no_URLs_allowed"));
	forward(REFERER);
	
}

//simple check to ensure default text was overwritten
if (substr_count($comment_text, elgg_echo("AU_anonymous_comments:longtextwarning"))>0){
	register_error(elgg_echo("AU_anonymous_comments:didntdelete"));
	forward(REFERER);
}


// Let's see if we can get an entity with the specified GUID
$entity = get_entity($entity_guid);
if (!$entity) {
	register_error(elgg_echo("generic_comment:notfound"));
	forward(REFERER);
}

$comment_text = $comment_text . "<br>- " . $anon_name;

// get the guid of our anonymous user
$anon_guid = elgg_get_plugin_setting('anon_guid','AU_anonymous_comments');

$user = get_user($anon_guid);

$annotation = create_annotation($entity->guid,
								'generic_comment',
$comment_text,
								"",
$user->guid,
$entity->access_id);

// tell user annotation posted
if (!$annotation) {
	register_error(elgg_echo("generic_comment:failure"));
	forward(REFERER);
}

	// special notification if AU_anonymous_comments plugin is enabled
	// notify if poster wasn't owner
	// Matt B: also only do default notification if entity is unmoderated
	if ($entity->owner_guid != $user->guid && !AU_anonymous_comments_is_moderated($entity_guid)) {
			
		notify_user($entity->owner_guid,
		$user->guid,
		elgg_echo('generic_comment:email:subject'),
		sprintf(
		elgg_echo('generic_comment:email:body'),
		$entity->title,
		$user->name." (name: $anon_name, email: $anon_email, IP: $ip_address )",
		$comment_text,
		$entity->getURL(),
		$user->name,
		$user->getURL()
		)
		);
	}

	// Matt B: if entity is moderated use custom notification
	if($entity->owner_guid != $user->guid && AU_anonymous_comments_is_moderated($entity_guid)){
		global $CONFIG;

		$approveURL = $CONFIG->url . "mod/AU_anonymous_comments/actions/annotation/review.php?id=" . $annotation . "&method=approve";
		$deleteURL = $CONFIG->url . "mod/AU_anonymous_comments/actions/annotation/review.php?id=" . $annotation . "&method=delete";

		notify_user($entity->owner_guid,
		$user->guid,
		elgg_echo('AU_anonymous_comments:email:subject'),
		sprintf(
		elgg_echo('AU_anonymous_comments:email:body'),
		$entity->title,
		$user->name." (name: $anon_name, email:$anon_email, IP: $ip_address )",
		$comment_text,
		$entity->getURL(),
		$approveURL,
		$deleteURL
		)
		);
	}



system_message(elgg_echo("generic_comment:posted"));

//do not add to river
//add_to_river('river/annotation/generic_comment/create', 'comment', $user->guid, $entity->guid, "", 0, $annotation);

elgg_clear_sticky_form('AU_anonymous_comments');

// Forward to the page the action occurred on
forward(REFERER);
