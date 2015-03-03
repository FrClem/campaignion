<?php

namespace Drupal\campaignion\Wizard;

class NotificationEmail extends Email {
  protected function &getEmailForm(&$form_state) {
    $form = &parent::getEmailForm($form_state);

    $form['email_option']['#access'] = TRUE;
    $form['email_option']['#default_value'] = 'custom';
    unset($form['email_option']['#options']['component']);
    unset($form['email_option']['#options']['default']);
    $form['email_custom']['#access'] = TRUE;
    if (!$form['email_custom']['#default_value']) {
      $form['email_custom']['#default_value'] = 'noreply@example.com';
    }

    // we are using only custom and default options
    // therefore if the custom address is set to campaignion, we take this as the
    // default for the notification option
    if ($form['from_address_custom']['#default_value'] === 'you@example.com') {
      $form['from_address_custom']['#default_value'] = '';
    }
    $form['from_address_option']['#default_value'] = 'default';
    $form['from_address_option']['#options']['default'] = 'Default: <em class="placeholder">you@example.com</em>';

    return $form;
  }

  public function submit($form, &$form_state, $email_type) {
    $values = &$form_state['values'];
    $values[$this->form_id . '_email']['from_address_campaignion'] = 'you@example.com'; // Default notification from address
    parent::submit($form, $form_state, $email_type);
  }
}
