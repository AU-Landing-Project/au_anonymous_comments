<?php

namespace AU\AnonymousComments;

/**
 *
 * Presents a comment form to a non-logged in person
 */
// there is some bug with the extended view where this will be called multiple times
// use a token counter to make sure that form is only displayed the first time

if (!$vars['entity']) {
	return;
}

if (elgg_is_logged_in()) {
	return;
}

// prevent duplication of the view
if (elgg_get_config('AU_anonymous_comments_post_edit')) {
	return;
}
elgg_set_config('AU_anonymous_comments_post_edit', true);

echo elgg_view_form('comments/anon_add', array(), $vars);
