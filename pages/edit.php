<?php

/*
 * 	This page contains the form for updating the plugin settings
 * 
 * Admin can save a new username for the fake user, upload an image to use for the profile pic,
 * and toggle recptcha off and on, and change recaptcha keys
 * 
 */

// only admins can see this page
admin_gatekeeper();


//set the page title
$title  = elgg_echo('AU_anonymous_comments:settings');

//get our user object for anonymous user
$user = get_user(elgg_get_plugin_setting('anon_guid','AU_anonymous_comments'));

// get the URL of our users profile pic
if($user){
$icon = $user->getIcon();
}

$user_guid = $user->guid;

// Current setting for recaptcha - "yes" or "no" - whether to use recaptcha or not
$recaptcha = elgg_get_plugin_setting('recaptcha','AU_anonymous_comments');

// Current setting for recaptcha style - white/red/blackglass/clean - or nothing - defaults to red
$recaptcha_style = elgg_get_plugin_setting('recaptcha_style','AU_anonymous_comments');

// Current public key
$public_key = elgg_get_plugin_setting('public_key', 'AU_anonymous_comments');

// Current private key
$private_key = elgg_get_plugin_setting('private_key', 'AU_anonymous_comments');

// Current ssl setting
$usessl = elgg_get_plugin_setting('usessl', 'AU_anonymous_comments');

// start form
$form = "<div style=\"margin: 15px;\">";
// username text input
$form .= "<label>" . elgg_echo("AU_anonymous_comments:name_description") . "</label>";
$form .= elgg_view('input/text', array('name' => 'name', 'value' => $user->name));
$form .= "<br><br>";

//display current profile pic
$form .= "<label>" . elgg_echo("AU_anonymous_comments:current_icon") . "</label><br>";
$form .= "<img src=\"$icon\" style=\"margin: 10px;\"><br>";

// file upload form for new profile pic
$form .= "<label>" . elgg_echo("AU_anonymous_comments:change_icon") . "</label>";
$form .= elgg_view('input/file', array('name' => 'upload')) . "<br><br>";

// radio buttons - use recaptcha yes/no
$form .= "<label>" . elgg_echo("AU_anonymous_comments:use_recaptcha") . "</label><br>";
$form .= "<input type=\"radio\" name=\"recaptcha\" value=\"yes\"";
if($recaptcha != "no"){ $form .= " checked"; }
$form .= ">";
$form .= "<label>" . elgg_echo("AU_anonymous_comments:yes") . "</label><br>";
$form .= "<input type=\"radio\" name=\"recaptcha\" value=\"no\"";
if($recaptcha == "no"){ $form .= " checked"; }
$form .= ">";
$form .= "<label>" . elgg_echo("AU_anonymous_comments:no") . "</label><br>";
$form .= "<br><br>";

//radio buttons for recaptcha style
$form .= "<label>" . elgg_echo("AU_anonymous_comments:recaptcha_style") . "</label><br>";
$form .= "<input type=\"radio\" name=\"recaptcha_style\" value=\"white\"";
if($recaptcha_style == "white"){ $form .= " checked"; }
$form .= ">";
$form .= "<label>" . elgg_echo("AU_anonymous_comments:white") . "</label><br>";
$form .= "<input type=\"radio\" name=\"recaptcha_style\" value=\"red\"";
if($recaptcha_style == "red"){ $form .= " checked"; }
$form .= ">";
$form .= "<label>" . elgg_echo("AU_anonymous_comments:red") . "</label><br>";
$form .= "<input type=\"radio\" name=\"recaptcha_style\" value=\"blackglass\"";
if($recaptcha_style == "blackglass"){ $form .= " checked"; }
$form .= ">";
$form .= "<label>" . elgg_echo("AU_anonymous_comments:blackglass") . "</label><br>";
$form .= "<input type=\"radio\" name=\"recaptcha_style\" value=\"clean\"";
if($recaptcha_style == "clean"){ $form .= " checked"; }
$form .= ">";
$form .= "<label>" . elgg_echo("AU_anonymous_comments:clean") . "</label><br>";
$form .= "<br><br>";

// radio buttons - use ssl recaptcha yes/no
$form .= "<label>" . elgg_echo('AU_anonymous_comments:usessl') . "</label><br>";
$form .= "<input type=\"radio\" name=\"usessl\" value=\"yes\"";
if($usessl != "no"){ $form .= " checked"; }
$form .= ">";
$form .= "<label>" . elgg_echo("AU_anonymous_comments:yes") . "</label><br>";
$form .= "<input type=\"radio\" name=\"usessl\" value=\"no\"";
if($usessl == "no"){ $form .= " checked"; }
$form .= ">";
$form .= "<label>" . elgg_echo("AU_anonymous_comments:no") . "</label><br>";
$form .= "<br><br>";

// text inputs for public and private keys for recaptcha
$form .= "<div>" . elgg_echo('AU_anonymous_comments:recaptcha_key_instruction');
$form .= "<br><a href=\"https://www.google.com/recaptcha/admin/create\">https://www.google.com/recaptcha/admin/create</a>";
$form .= "</div>";
$form .= "<label>" . elgg_echo("AU_anonymous_comments:public_key") . "</label>";
$form .= elgg_view('input/text', array('name' => 'public_key', 'value' => $public_key));
$form .= "<br><br>";

$form .= "<label>" . elgg_echo("AU_anonymous_comments:private_key") . "</label>";
$form .= elgg_view('input/text', array('name' => 'private_key', 'value' => $private_key));
$form .= "<br><br>";


// submit button
$form .= elgg_view('input/submit', array('value' => elgg_echo("AU_anonymous_comments:submit")));
$form .= "</div>";

// parameters for form generation - enctype must be 'multipart/form-data' for file uploads 
$form_vars = array();
$form_vars['body'] = $form;
$form_vars['name'] = 'update_AU_anonymous_comments_settings';
$form_vars['enctype'] = 'multipart/form-data';
$form_vars['action'] = 'action/AU_anonymous_comments_settings';

// create the form
$area =  elgg_view('input/form', $form_vars);

// place the form into the elgg layout
$body = elgg_view_layout('one_column', array('content' => $area));

// display the page
echo elgg_view_page($title, $body);