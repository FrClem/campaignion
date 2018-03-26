<?php

namespace Drupal\campaignion_newsletters;

/**
 * Form builder integration for the newsletter webform component.
 */
class FormBuilderElementNewsletter extends \FormBuilderWebformElement {

  /**
   * {@inheritdoc}
   */
  public function configurationForm($form, &$form_state) {
    $form = parent::configurationForm($form, $form_state);

    $component = $this->element['#webform_component'];

    // In order for the groups to work we need a flattened array of elements.
    // @see form_builder_field_configure_pre_render()
    $edit = _webform_edit_newsletter($component);
    $form['options'] = array(
      '#form_builder' => array('property_group' => 'options'),
      '#tree' => TRUE,
    );
    $form['options']['lists'] = $edit['extra']['lists'];
    $form['value'] = $edit['value'];
    $form['title'] = $edit['extra']['description'];
    $form['opt_in_implied'] = $edit['extra']['opt_in_implied'];
    $form['send_welcome'] = $edit['extra']['send_welcome'];
    $dp['#form_builder']['property_group'] = 'display';
    $form['display'] = $edit['extra']['display'] + $dp;
    $form['checkbox_label'] = $edit['extra']['checkbox_label'] + $dp;
    $form['radio_labels'] = $edit['extra']['radio_labels'] + $dp;
    $form['optin_statement'] = $edit['extra']['optin_statement'];
    $form['no_is_optout'] = $edit['extra']['no_is_optout'];
    $form['optout_all_lists'] = $edit['extra']['optout_all_lists'];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationSubmit(&$form, &$form_state) {
    $form_state['values']['newsletter_lists'] = $form_state['values']['options']['lists'];
    parent::configurationSubmit($form, $form_state);

    $component = &$this->element['#webform_component'];
    $values = $form_state['values'];
    $component['extra']['lists'] = $values['options']['lists'];
    $component['value'] = $values['value'];
    $component['extra']['description'] = $values['title'];
    $component['extra']['opt_in_implied'] = $values['opt_in_implied'];
    $component['extra']['send_welcome'] = $values['send_welcome'];
    $component['extra']['display'] = $values['display'];
    $component['extra']['radio_labels'] = $values['radio_labels'];
    $component['extra']['checkbox_label'] = $values['checkbox_label'];
    $component['extra']['optin_statement'] = $values['optin_statement'];
    $component['extra']['no_is_optout'] = $values['no_is_optout'];
    $component['extra']['optout_all_lists'] = $values['optout_all_lists'];
  }

}
