<?php

/**
 * Implements hook_campaignion_activity_type_info().
 *
 * … on behalf of the commend.module
 */
function comment_campaignion_activity_type_info() {
  $info['comment'] = 'Drupal\campaignion\Activity\CommentType';
}

/**
 * Implements hook_comment_insert().
 */
function campaignion_activity_comment_insert($comment) {
  if ($activity = \Drupal\campaignion\Activity\Comment::fromComment($comment)) {
    $activity->save();
  }
}
