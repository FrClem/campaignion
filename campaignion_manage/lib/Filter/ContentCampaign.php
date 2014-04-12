<?php

namespace Drupal\campaignion_manage\Filter;

class ContentCampaign extends ContentNodeReference {
  public function __construct(\SelectQueryInterface $query) {
    $reference_field  = 'field_reference_to_campaign';
    $reference_column = 'field_reference_to_campaign_nid';
    parent::__construct($query, $reference_field, $reference_column);
  }

  public function formElement(array &$form, array &$form_state, array &$values) {
    $form['campaign'] = array(
      '#type'          => 'select',
      '#title'         => t('Campaign'),
      '#options'       => $this->getOptions(),
      '#default_value' => isset($values) ? $values : NULL,
    );
  }
  public function defaults() {
    return array('campaign' => reset($this->getOptions()));
  }
  public function title() { return t('Campaign'); }
}