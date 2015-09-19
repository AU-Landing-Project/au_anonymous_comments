<?php

namespace AU\AnonymousComments;

/**
 * Prevent hover menu stuff for our anonymous user
 * 
 * @param type $hook
 * @param type $type
 * @param type $return
 * @param type $params
 * @return array
 */
function hover_menu_hook($hook, $type, $return, $params) {
	$user = $params['entity'];
	$anon_user = get_anon_user();

	if ($user->guid == $anon_user->guid) {
		if (!elgg_is_admin_logged_in()) {
			return array();
		} else {
			// admin here, lets allow them access to the
			// edit settings/profile/avatar items
			$allowed = array(
				'profile:edit',
				'settings:edit'
			);
			
			foreach ($return as $key => $item) {
				if (in_array($item->getName(), $allowed)) {
					continue;
				}
				unset($return[$key]);
			}
			
			$return = array_values($return);
		}
	}

	return $return;
}

/**
 * 
 * @param type $hook
 * @param type $type
 * @param type $return
 * @param type $params
 * 
 * @return array();
 */
function user_icon_vars($hook, $type, $return, $params) {
	
	$user = ($params['entity'] instanceof \ElggUser) ? $params['entity'] : elgg_get_logged_in_user_entity();
	$anon_user = get_anon_user();
	
	if ($user->guid == $anon_user->guid) {
		$return['use_hover'] = false;
	}
	
	return $return;
}

/**
 * prevent emails to our anonymous user
 * 
 * @param type $hook
 * @param type $type
 * @param type $returnvalue
 * @param type $params
 * @return boolean
 */
function anon_email_hook($hook, $type, $returnvalue, $params) {
	$anon_user = get_anon_user();

	if ($anon_user && $anon_user->email == $params['to']) {
		return FALSE;
	}

	return $returnvalue;
}

/**
 * 
 * @param type $hook
 * @param type $type
 * @param type $return
 * @param type $params
 * @return boolean
 */
function permissions_check($hook, $type, $return, $params) {
	$context = elgg_get_context();
	if ($context == "AU_anonymous_comments_permissions") {
		return true;
	}

	return $return;
}
