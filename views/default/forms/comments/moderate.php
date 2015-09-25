<?php

namespace AU\AnonymousComments;

echo '<div class="hidden-inputs"></div>';
echo elgg_view('input/submit', array(
	'value' => elgg_echo('AU_anonymous_comments:approve_checked')
));
echo elgg_view('input/submit', array(
	'value' => elgg_echo('AU_anonymous_comments:delete_checked'),
	'class' => 'elgg-button elgg-button-delete'
));

