<?php
/**
 * @file
 * Abstract base class for email marketing providers
 */

namespace Drupal\campaignion_newsletters;

interface NewsletterProviderInterface {
  public function __construct(array $params);
  /**
   * Fetches current lists from the provider.
   *
   * @return array
   *   An array of Drupal\campaignion_newsletters\NewsletterList objects
   */
  public function getLists();

  /**
   * Fetches current lists of subscribers from the provider.
   *
   * @return array
   *   an array of subscribers.
   */
  public function getSubscribers($list);

  /**
   * Subscribe a user, given a newsletter identifier and email address.
   *
   * @return: True on success.
   */
  public function subscribe($newsletter, $mail);

  /**
   * Subscribe a user, given a newsletter identifier and email address.
   *
   * Should ignore the request if there is no such subscription.
   *
   * @return: True on success.
   */
  public function unsubscribe($newsletter, $mail);
}
