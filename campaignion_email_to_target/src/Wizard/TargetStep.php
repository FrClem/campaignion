<?php

namespace Drupal\campaignion_email_to_target\Wizard;

use \Drupal\campaignion\Forms\EntityFieldForm;

class TargetStep extends \Drupal\campaignion_wizard\WizardStep {
  protected $step  = 'target';
  protected $title = 'Target';

  public function stepForm($form, &$form_state) {
    $form = parent::stepForm($form, $form_state);
    $field = $this->wizard->parameters['email_to_target']['options_field'];
    $this->fieldForm = new EntityFieldForm('node', $this->wizard->node, [$field]);
    $form += $this->fieldForm->formArray($form_state);
    return $form;
  }

  public function validateStep($form, &$form_state) {
    $this->fieldForm->validate($form, $form_state);
  }

  public function submitStep($form, &$form_state) {
    $this->fieldForm->submit($form, $form_state);
  }

  public function checkDependencies() {
    return isset($this->wizard->node->nid);
  }
}
