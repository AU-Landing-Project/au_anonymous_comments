<?php

namespace AU\AnonymousComments;

$guids = (array) get_input('guid', array());
$review = get_input('review');

access_show_hidden_entities(true);

$deleted = false;
$approved = false;

foreach ($guids as $g) {
	$comment = get_entity($g);
	if (!($comment instanceof \ElggComment)) {
		continue;
	}
	
	$entity = $comment->getContainerEntity();
	if (!$entity || !$entity->canEdit()) {
		continue;
	}
	
	if ($review == 'delete' || $review == elgg_echo('AU_anonymous_comments:delete_checked')) {
		$comment->delete();
		$deleted = true;
	}
	
	if ($review == 'approve' || $review == elgg_echo('AU_anonymous_comments:approve_checked')) {
		$comment->enable();
		$approved = true;
	}
}

if ($deleted) {
	system_message(elgg_echo('AU_anonymous_comments:deleted'));
}

if ($approved) {
	system_message(elgg_echo('AU_anonymous_comments:approved'));
}

if (!$deleted && !$approved) {
	register_error(elgg_echo('AU_anonymous_comments:invalid:moderation'));
}

forward(REFERER);