<?php

namespace Drupal\campaignion_action;

/**
 * Every ActionType (Petition) has to implement this interface.
 */
interface TypeInterface {

  /**
   * Check whether or not this action-type is a donation.
   *
   * @return bool
   *   TRUE if this action type should be considered a donation else FALSE.
   */
  public function isDonation();

}
