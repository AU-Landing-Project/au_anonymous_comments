<?php
/**
 *Comment functionality
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Matt Beckett, Jon Dron
 * @copyright University of Athabasca 2011-2013
 */

/**
 *
 */
include_once 'lib/functions.php';

function AU_anonymous_comments_init() {
	
	// Extend system CSS with our own styles
	elgg_extend_view('css/elgg','AU_anonymous_comments/css');
	elgg_register_js('AU_anonymous_comments', elgg_get_site_url() . "mod/AU_anonymous_comments/js/javascript.js");
	
	// extend the form view to present a comment form to a logged out user
	elgg_extend_view('page/elements/comments', 'comments/forms/AU_anonymous_comments_post_edit');
	
	//add override for anonymous user profile
	elgg_extend_view('profile/details', 'profile/AU_anonymous_comments_pre_userdetails', 501);
	
	elgg_register_page_handler('AU_anonymous_comments','AU_anonymous_comments_page_handler');
	elgg_register_plugin_hook_handler('register', 'menu:user_hover', 'AU_anonymous_comments_hover_menu', 1000);
	elgg_register_plugin_hook_handler('email', 'system', 'AU_anonymous_comments_anon_email');
	 //register action to approve/delete comments
	elgg_register_action("annotation/review", elgg_get_plugins_path() . "AU_anonymous_comments/actions/annotation/review.php"); 
	//register action to save our anonymous comments
	elgg_register_action("comments/anon_add", elgg_get_plugins_path() . "AU_anonymous_comments/actions/comments/anon_add.php", 'public');
	
	//register action to save our plugin settings
	elgg_register_action("AU_anonymous_comments_settings", elgg_get_plugins_path() . "AU_anonymous_comments/actions/AU_anonymous_comments_settings.php", 'admin');
	
	// register plugin hook to monitor comment counts - return only the count of approved comments
	elgg_register_plugin_hook_handler('comments:count', 'all', 'AU_anonymous_comments_comment_count', 1000); 
		
	// override permissions for the rssimport_cron context
	elgg_register_plugin_hook_handler('permissions_check', 'all', 'AU_anonymous_comments_permissions_check');	
	  
	// prevent complications with editable comments
	elgg_register_plugin_hook_handler('editablecomments:canedit', 'comment', 'AU_anonymous_comments_editablecomments_check');
}



function AU_anonymous_comments_pagesetup() {

	if (elgg_get_context() == 'admin' && elgg_is_admin_logged_in()) {
    elgg_register_menu_item('page', array(
         'name' => 'AU_anonymous_comments',
         'href' => elgg_get_site_url() . 'AU_anonymous_comments/edit',
         'text' => elgg_echo('AU_anonymous_comments:settings'),
         'parent_name' => 'settings',
         'section' => 'configure',
     ));
	}
}

function AU_anonymous_comments_page_handler()
{

	if(include(elgg_get_plugins_path() . "AU_anonymous_comments/pages/edit.php")){
	  return TRUE;
	}
	
  return FALSE;
}


elgg_register_event_handler('init','system','AU_anonymous_comments_init');
elgg_register_event_handler('pagesetup','system','AU_anonymous_comments_pagesetup');
// check if newly created comment needs to be reviewed
elgg_register_event_handler('create','annotation','AU_anonymous_comments_check');

// check if newly created entity is public - if so moderate
elgg_register_event_handler('create','all','AU_anonymous_comments_entity_create');

// check if newly updated entity is public - if so moderate
elgg_register_event_handler('update','all','AU_anonymous_comments_entity_create');

// extend the form view to present a notice that comments are moderated
elgg_extend_view('page/components/list', 'comments/forms/AU_anonymous_comments_pre_edit', 501);

