<?php

namespace Drupal\campaignion\Wizard;

class DonationWizard extends NodeWizard {
  public $steps = array(
    'content' => 'DonationContentStep',
    'form'    => 'WebformStep',
    'emails'  => 'DonationEmailStep',
    'thank'   => 'ThankyouStep',
    'confirm' => 'ConfirmStep',
  );
}
