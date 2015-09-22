<?php

namespace AU\AnonymousComments;

elgg_make_sticky_form('comments/anon_add');

$anon_name = get_input('anon_name');
$anon_email = get_input('anon_email');
$entity_guid = (int) get_input('entity_guid', 0, false);
$comment_guid = (int) get_input('comment_guid', 0, false);
$comment_text = get_input('generic_comment');

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

if (substr_count($comment_text, "http://") > 1 || substr_count($comment_text, "https://") > 1) {
	register_error(elgg_echo("AU_anonymous_comments:no_URLs_allowed"));
	forward(REFERER);
}

//simple check to ensure default text was overwritten
if (substr_count($comment_text, elgg_echo("AU_anonymous_comments:longtextwarning")) > 0) {
	register_error(elgg_echo("AU_anonymous_comments:didntdelete"));
	forward(REFERER);
}


//use stopforumspam to limit attempts to mess with comments
$url = "http://api.stopforumspam.com/api?ip=" . get_ip() . "&email=" . $anon_email . "&f=json";

// check stopforumspam
$curl = curl_init($url);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);

$contents = curl_exec($curl);
$aInfo = curl_getinfo($curl);

if ($aInfo['http_code'] === 200) {
	$data = json_decode($contents);
	$ip_frequency = $data->ip->frequency;
	if ($ip_frequency != 0) {
		// spammer
		register_error(elgg_acho('AU_anonymous_comments:stopforumspam_fail'));
		forward(REFERER);
	}
}

// Create a new comment on the target entity
$entity = get_entity($entity_guid);
if (!$entity) {
	register_error(elgg_echo("generic_comment:notfound"));
	forward(REFERER);
}

$user = get_anon_user();

// custom context for write permissions
elgg_push_context("AU_anonymous_comments_permissions");

$comment_text .= "\n\n- " . $anon_name;

$comment = new \ElggComment();
$comment->description = $comment_text;
$comment->owner_guid = $user->getGUID();
$comment->container_guid = $entity->getGUID();
$comment->access_id = $entity->access_id;
// flag for moderation
if (is_moderated($entity)) {
	$comment->au_moderated_comments_unapproved = 1;
}
$guid = $comment->save();

if (!$guid) {
	register_error(elgg_echo("generic_comment:failure"));
	forward(REFERER);
}

if (!is_moderated($entity)) {
	$owner = $entity->getOwnerEntity();

	notify_user($owner->guid, $user->guid, elgg_echo('generic_comment:email:subject', array(), $owner->language), elgg_echo('generic_comment:email:body', array(
		$entity->title,
		$anon_name . " ({$anon_email})",
		$comment_text,
		$entity->getURL(),
		$user->name,
		$user->getURL()
					), $owner->language), array(
		'object' => $comment,
		'action' => 'create',
			)
	);
}
else {
	$token = get_token($comment);
	$approveURL = elgg_normalize_url("auac/approve/{$token}");
	$deleteURL = elgg_normalize_url("auac/delete/{$token}");

	notify_user($entity->owner_guid, $user->guid, elgg_echo('AU_anonymous_comments:email:subject'), sprintf(
					elgg_echo('AU_anonymous_comments:email:body'), $entity->title, $user->name . " (name: $anon_name, email:$anon_email, IP: $ip_address )", $comment_text, $entity->getURL(), $approveURL, $deleteURL
			)
	);
	
	notify_user($owner->guid, $user->guid, elgg_echo('AU_anonymous_comments:email:subject', array(), $owner->language), elgg_echo('AU_anonymous_comments:email:body', array(
		$entity->title,
		$anon_name . " ({$anon_email})",
		$comment_text,
		$entity->getURL(),
		$approveURL,
		$deleteURL
					), $owner->language), array(
		'object' => $comment,
		'action' => 'create',
			)
	);
}

// Add to river
if (elgg_get_plugin_setting('add_to_river', PLUGIN_ID) == 'yes') {
	elgg_create_river_item(array(
		'view' => 'river/object/comment/create',
		'action_type' => 'comment',
		'subject_guid' => $user->guid,
		'object_guid' => $guid,
		'target_guid' => $entity_guid,
	));
}

elgg_pop_context();
elgg_clear_sticky_form('comments/anon_add');
system_message(elgg_echo('generic_comment:posted'));

forward(REFERER);
