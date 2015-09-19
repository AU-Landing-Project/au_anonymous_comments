<?php

namespace AU\AnonymousComments;

echo '<div class="pas">';
echo "<label>" . elgg_echo("AU_anonymous_comments:use_recaptcha") . "</label><br>";
echo elgg_view('input/dropdown', array(
	'name' => 'params[recaptcha]',
	'value' => $vars['entity']->recaptcha ? $vars['entity']->recaptcha : 'yes',
	'options_values' => array(
		'yes' => elgg_echo('option:yes'),
		'no' => elgg_echo('option:no')
	)
));
echo '</div>';


echo '<div class="pas">';
echo "<label>" . elgg_echo("AU_anonymous_comments:recaptcha_style") . "</label><br>";
echo elgg_view('input/dropdown', array(
	'name' => 'params[recaptcha_style]',
	'value' => $vars['entity']->recaptcha_style ? $vars['entity']->recaptcha_style : 'red',
	'options_values' => array(
		'light' => elgg_echo("AU_anonymous_comments:recaptcha:light"),
		'dark' => elgg_echo("AU_anonymous_comments:recaptcha:dark")
	)
));
echo '</div>';

echo '<div class="pas">';
echo "<label>" . elgg_echo("AU_anonymous_comments:public_key") . "</label>";
echo elgg_view('input/text', array(
	'name' => 'params[public_key]',
	'value' => $vars['entity']->public_key
));
$link = elgg_view('output/url', array(
	'text' => 'https://www.google.com/recaptcha/admin/create',
	'href' => 'https://www.google.com/recaptcha/admin/create',
	'target' => '_blank'
));
echo elgg_view('output/longtext', array(
	'value' => elgg_echo('AU_anonymous_comments:recaptcha_key_instruction', array($link)),
	'class' => 'elgg-subtext'
));
echo '</div>';


echo '<div class="pas">';
echo "<label>" . elgg_echo("AU_anonymous_comments:private_key") . "</label>";
echo elgg_view('input/text', array(
	'name' => 'params[private_key]',
	'value' => $vars['entity']->private_key
));
$link = elgg_view('output/url', array(
	'text' => 'https://www.google.com/recaptcha/admin/create',
	'href' => 'https://www.google.com/recaptcha/admin/create',
	'target' => '_blank'
));
echo elgg_view('output/longtext', array(
	'value' => elgg_echo('AU_anonymous_comments:recaptcha_key_instruction', array($link)),
	'class' => 'elgg-subtext'
));
echo '</div>';


echo '<div class="pas">';
echo "<label>" . elgg_echo("AU_anonymous_comments:setting:add_to_river") . "</label><br>";
echo elgg_view('input/dropdown', array(
	'name' => 'params[add_to_river]',
	'value' => $vars['entity']->add_to_river ? $vars['entity']->add_to_river : 'yes',
	'options_values' => array(
		'yes' => elgg_echo('option:yes'),
		'no' => elgg_echo('option:no')
	)
));
echo '</div>';