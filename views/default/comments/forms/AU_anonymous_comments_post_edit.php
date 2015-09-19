<?php

/**
 *
 * Presents a comment form to a non-logged in person
 */
	// there is some bug with the extended view where this will be called multiple times
	// use a token counter to make sure that form is only displayed the first time

if (isset($vars['entity']) && !elgg_is_logged_in()) {
  
	//limit what can be posted
	htmlawed_init_mod();
	
  if (elgg_is_sticky_form('AU_anonymous_comments')) {
	extract(elgg_get_sticky_values('AU_anonymous_comments'));
	elgg_clear_sticky_form('AU_anonymous_comments');
  }
	//provide instructions to the user
	if(AU_anonymous_comments_is_moderated($vars['entity']->guid) && !elgg_is_logged_in()){
		$form_body= "<div style=\"clear: both\">" . elgg_echo('AU_anonymous_comments:moderated_notice') . "</div>";
	} else {
		$form_body=" ";
	}

	if($speakfreelyformcounter != 1){  //first time, create the form..
		$speakfreelyformcounter = 1; // now this variable is set, so the form shouldn't replicate
		
		require_once(elgg_get_plugins_path() . 'AU_anonymous_comments/lib/recaptchalib.php');
		$publickey = elgg_get_plugin_setting('public_key', 'AU_anonymous_comments'); // you got this from the signup page

		$form_body .= "<div class=\"contentWrapper\">";
		$form_body .= "<label>" . elgg_echo('AU_anonymous_comments:name') . "<br> " . elgg_view('input/text', array('name' => 'anon_name', 'value' => $anon_name, 'id' => 'AU_anonymous_comments_name_field')) . "</label> (" . elgg_echo('AU_anonymous_comments:required') . ")<br><br>";
		$form_body .= "<label>" . elgg_echo('AU_anonymous_comments:email') . "<br> " . elgg_view('input/text', array('name' => 'anon_email', 'value' => $anon_email, 'id' => 'AU_anonymous_comments_email_field')) . "</label> (" . elgg_echo('AU_anonymous_comments:required') . ")<br><br>";
		$form_body .= "<div class='longtext_editarea'><label>".elgg_echo("generic_comments:text")."</label><br />" . elgg_view('input/longtext',array('name' => 'generic_comment', 'value' =>elgg_echo('AU_anonymous_comments:longtextwarning'))) . "</div>";
		$form_body .= "<div id='recaptcha'>" . elgg_view('input/hidden', array('name' => 'entity_guid', 'value' => $vars['entity']->getGUID()));

		// if we have set recaptcha then display the output
		if(elgg_get_plugin_setting('recaptcha','AU_anonymous_comments') == "yes"){
			$recaptcha_style = elgg_get_plugin_setting('recaptcha_style','AU_anonymous_comments');
			if(empty($recaptcha_style)){
				$recaptcha_style = "red"; // set default	
			} 
 
			$form_body .= "<script type=\"text/javascript\">";
			$form_body .= "var RecaptchaOptions = {";
			$form_body .= "theme : '$recaptcha_style'";
			$form_body .= "};";
			$form_body .= "</script>"; 
			
      $usessl = elgg_get_plugin_setting('usessl', 'AU_anonymous_comments');
      $ssl = FALSE;
      if ($usessl == 'yes') {
        $ssl = TRUE;
      }
			$form_body .= recaptcha_get_html($publickey, '', $ssl);
		}

		$form_body .= elgg_view('input/submit', array('value' => elgg_echo("AU_anonymous_comments:post:comment"))) . "</div></div>";

		// output the form
		echo elgg_view('input/form', array('body' => $form_body, 'action' => "{$vars['url']}action/comments/anon_add"));
	}
}