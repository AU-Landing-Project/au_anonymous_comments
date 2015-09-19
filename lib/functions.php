<?php

namespace AU\AnonymousComments;

/**
 * get our anonymous user
 * 
 * @staticvar type $anon_user
 * @return \ElggUser
 */
function get_anon_user() {
	static $anon_user;

	if ($anon_user) {
		return $anon_user;
	}

	$anon_guid = elgg_get_plugin_setting('anon_guid', PLUGIN_ID);
	$anon_user = get_user($anon_guid);
	if (!$anon_user) {
		$anon_user = set_anonymous_user();
	}

	return $anon_user;
}

/**
 * 
 * @return string ip | null
 */
function get_ip() {
	// note we need to look at these values first before REMOTE_ADDR
	// as cloud hosting routes through other servers giving false
	// or invalid internal ips
	//check ip from share internet
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$realip = $_SERVER['HTTP_CLIENT_IP'];
	}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$realip = $_SERVER['REMOTE_ADDR'];
	}
	return $realip;
}

/*
 *  This function will check to see if there is a user already created by the plugin (possibly from a 
 *  previous installation).  If so, it will get the ID of that user and save it for the plugin to use.
 *  If there is no user created already, it will create a fake user and save the ID for the plugin.
 *  A piece of metadata ($user->AU_anonymous_comments = true) is set to differentiate just in case someone decided
 *  to take our username.
 */

function set_anonymous_user() {

	//first see if a user has been created previously
	$users = elgg_get_entities_from_metadata(array(
		'types' => 'user',
		'metadata_name' => 'AU_anonymous_comments',
		'value' => true,
	));

	if (!$users) {
		//no previous user - create a new one
		//find available username
		$i = 1;
		$username = "AU_anonymous_comments_user1";
		$basename = "AU_anonymous_comments_user";
		while (get_user_by_username($username)) {
			$i++;
			$username = $basename . $i;
		}

		//let's make a user
		$anon_user = new \ElggUser();
		$anon_user->username = $username;
		$anon_user->email = "AU_anonymous_comments_user" . $i . "@example.com";
		$anon_user->name = elgg_echo('AU_anonymous_comments:display_name');
		$anon_user->access_id = ACCESS_PUBLIC;
		$anon_user->salt = _elgg_generate_password_salt();
		$anon_user->password = _elgg_generate_password_salt(); // doesn't need to match, we don't want people logging in anyway
		$anon_user->owner_guid = 0; // Users aren't owned by anyone, even if they are admin created.
		$anon_user->container_guid = 0; // Users aren't contained by anyone, even if they are admin created.
		$anon_user->save();

		// set the plugin-identifiable metadata
		$anon_user->AU_anonymous_comments = true;
	} else {
		// we found our user through metadata
		$anon_user = $users[0];
	}

	elgg_set_plugin_setting('anon_guid', $anon_user->guid, PLUGIN_ID);

	return $anon_user;
}

//
//	called when a comment is made, checks if object is moderated
//	if so adds to moderation list
//
function AU_anonymous_comments_check($event, $object_type, $obj) {

	if ($obj->name == "generic_comment" && AU_anonymous_comments_is_moderated($obj->entity_guid) && !elgg_is_logged_in()) {
		$entity = get_entity($obj->entity_guid);
		if ($entity->owner_guid != elgg_get_logged_in_user_guid()) {
			AU_anonymous_comments_add_to_review_list($obj);
			system_message(elgg_echo('AU_anonymous_comments:comment_success'));
		}
	}
}

//
//	this function adds the new comment id to the list that needs to be checked
//
function AU_anonymous_comments_add_to_review_list($obj) {

	$entity = get_entity($obj->entity_guid);

	$review_array = explode(',', $entity->unmoderated_comments);

	// add new comment id to array
	if (!is_array($review_array)) {
		$review_array = array();
	}

	if (!in_array($obj->id, $review_array)) {
		$review_array[] = $obj->id;
	}

	// save the new array
	AU_anonymous_comments_save_array($review_array, $entity);
}

//
//	this function saves the array as a list of ids separated by commas
//
function AU_anonymous_comments_save_array($review_array, $entity) {
	$context = elgg_get_context();
	elgg_set_context('AU_anonymous_comments_permissions');
	sort($review_array);
	//convert new array back into a list
	$review_list = implode(',', $review_array);

	//save the list
	$entity->unmoderated_comments = $review_list;

	elgg_set_context($context);
}

/**
 * this function returns true if the entity is being moderated
 * 
 * @param type $id
 * @return boolean
 */
function is_moderated($entity) {
	if (!elgg_instanceof($entity)) {
		return false;
	}

	if ($entity->is_moderated || $entity->access_id == ACCESS_PUBLIC) {
		return true;
	}

	return false;
}

//
// This function checks each object on creation (called by event handler)
//	if object has public access, set to moderated
//
function AU_anonymous_comments_entity_create($event, $object_type, $object) {
	if ($object_type == "object") {
		if ($object->access_id == ACCESS_PUBLIC) {
			$object->is_moderated = true;
		} else {
			$object->is_moderated = false;
		}
	}
}

/**
 * This function checks if the entity is being moderated, if so we need to count
 * and return the number of APPROVED comments, not total comments
 * called by commments:count plugin hook
 * 
 * @param type $hook
 * @param type $type
 * @param type $returnvalue
 * @param type $params
 * @return int
 */
function comment_count_hook($hook, $type, $returnvalue, $params) {
	if (is_moderated($params['entity']->guid)) {
		// get array of total comments
		$comments = $params['entity']->getAnnotations('generic_comment');
		// get array of comments awaiting review
		$unreviewed = explode(',', $params['entity']->unmoderated_comments);

		$count = 0;
		for ($i = 0; $i < count($comments); $i++) {
			$id = $comments[$i]->id;
			if (!empty($id)) {
				if (!in_array($id, $unreviewed)) {
					$count++;  // the comment isn't in our list to review, so count it as real	
				}
			}
		}

		return $count;
	}
}

//
//	removes a single item from an array
//	resets keys
//
function removeFromArray($value, $array) {
	if (!is_array($array)) {
		return $array;
	}
	if (!in_array($value, $array)) {
		return $array;
	}

	for ($i = 0; $i < count($array); $i++) {
		if ($value == $array[$i]) {
			unset($array[$i]);
			$array = array_values($array);
		}
	}

	return $array;
}

function AU_anonymous_comments_editablecomments_check($hook, $type, $return, $params) {
	$comment = $params['annotation'];

	$entity_guid = $comment->entity_guid;

	$mc_entity = get_entity($entity_guid);

	// get array of unreviewed comments
	$review_array = explode(',', $mc_entity->unmoderated_comments);

	if (in_array($comment->id, $review_array)) {
		return false;
	}
}

//
// sets htmlawed to filter more if user is not logged in
//

function htmlawed_init_mod() {
	global $CONFIG;
	$CONFIG->htmlawed_config = array(
		'safe' => true,
		'deny_attribute' => 'class, on*',
		'hook_tag' => 'htmlawed_hook',
		'anti_link_spam' => array('`.`', ''),
		'schemes' => '*:http,https,ftp,news,mailto,rtsp,teamspeak,gopher,mms,callto',
		'elements' => 'b, i, ul,li, u, blockquote, p, strong, em, s, ol, br,h1,h2,h3'
	);

	elgg_register_plugin_hook_handler('validate', 'input', 'htmlawed_filter_tags', 1);
}
