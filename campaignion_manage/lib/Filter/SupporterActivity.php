<?php

namespace Drupal\campaignion_manage\Filter;

class SupporterActivity extends Base implements FilterInterface {
  protected $query;

  public function __construct(\SelectQueryInterface $query) {
    $this->query = $query;
  }

  protected function getOptions() {
    $activities_in_use = array('any_activity' => t('Any activity'));

    $query = clone $this->query;
    $query->innerJoin('campaignion_activity', 'act', "r.contact_id = act.contact_id");
    $fields =& $query->getFields();
    $fields = array();
    $query->condition('act.type', 'redhen_contact_create');
    $query->fields('act', array('type'));
    $query->groupBy('act.type');

    $activities_in_use += $query->execute()->fetchAllKeyed(0,0);

    $query = clone $this->query;
    $query->innerJoin('campaignion_activity', 'act', "r.contact_id = act.contact_id");
    $query->innerJoin('campaignion_activity_webform', 'wact', "act.activity_id = wact.activity_id");
    $query->innerJoin('node', 'n', "wact.nid = n.nid");
    $fields =& $query->getFields();
    $fields = array();
    $query->fields('n', array('nid', 'type', 'title'));
    $query->where('n.tnid = 0 OR n.tnid = n.nid');

    $options = array();
    foreach ($query->execute()->fetchAllAssoc('nid') as $nid => $action) {
      $activities_in_use[$action->type] = $action->type;
      $options['actions'][$action->type][$nid] = $action->title;
    }

    $available_activities = array(
      'any_activity'          => t('Any activity'),
      'redhen_contact_create' => t('Contact created'),
      'petition'              => t('Petition'),
      'donation'              => t('Donation'),
      'email_protest'         => t('Email Protest'),
      'webform'               => t('Flexible Form'),
    );
    $options['activity_types'] = array_intersect_key($available_activities, $activities_in_use);

    return $options;
  }

  public function formElement(array &$form, array &$form_state, array &$values) {
    $frequency_id  = drupal_html_id('activity-frequency');
    $date_range_id = drupal_html_id('activity-date-range');
    $activity_type_id = drupal_html_id('activity-type');
    $options = $this->getOptions();
    $form['frequency'] = array(
      '#type'          => 'select',
      '#title'         => t('Activity frequency'),
      '#attributes'    => array('id' => $frequency_id),
      '#options'       => array('any' => t('Any frequency'), 'how_many' => t('How many times?')),
      '#default_value' => isset($values['frequency']) ? $values['frequency'] : NULL,
    );
    $form['how_many_op'] = array(
      '#type'          => 'select',
      '#title'         => t('Frequency operator'),
      '#options'       => array('=' => t('Exactly'), '>' => t('More than'), '<' => t('Less than')),
      '#states'        => array('visible' => array('#' . $frequency_id => array('value' => 'how_many'))),
      '#default_value' => isset($values['how_many_op']) ? $values['how_many_op'] : NULL,
    );
    $form['how_many_nr'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Specify number of times'),
      '#size'          => 10,
      '#maxlength'     => 10,
      '#states'        => array('visible' => array('#' . $frequency_id => array('value' => 'how_many'))),
      '#default_value' => isset($values['how_many_nr']) ? $values['how_many_nr'] : NULL,
      '#element_validate' => array('campaignion_manage_activity_how_many_validate'),
    );
    $form['activity'] = array(
      '#type'          => 'select',
      '#title'         => t('Activity'),
      '#attributes'    => array('id' => $activity_type_id),
      '#options'       => $options['activity_types'],
      '#default_value' => isset($values['activity']) ? $values['activity'] : NULL,
    );
    $activity_types = array(
      'donation'      => t('Donation Action'),
      'email_protest' => t('Email Protest Actions'),
      'petition'      => t('Petition Actions'),
      'webform'       => t('Flexible Form Actions'),
    );
    foreach ($activity_types as $type => $type_name) {
      if (!empty($options['actions'][$type])) {
        $form['action_' . $type] = array(
          '#type'          => 'select',
          '#title'         => $type_name,
          '#options'       => array('no_specific' => t('No specific action')) + $options['actions'][$type],
          '#states'        => array('visible' => array('#' . $activity_type_id => array('value' => $type))),
          '#default_value' => isset($values['action_' . $type]) ? $values['action_' . $type] : NULL,
        );
      }
    }
    $form['date_range'] = array(
      '#type'          => 'select',
      '#title'         => t('Date range'),
      '#attributes'    => array('id' => $date_range_id),
      '#options'       => array('all' => t('All dates'), 'range' => t('Date range'), 'before' => t('Before'), 'after' => t('After')),
      '#default_value' => isset($values['date_range']) ? $values['date_range'] : NULL,
    );
    $form['date_after'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('After'),
      '#description'   => t('Specify a date in the format YYYY/MM/DD'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'after'))),
      '#default_value' => isset($values['date_after']) ? $values['date_after'] : NULL,
    );
    $form['date_before'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('Before'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'before'))),
      '#default_value' => isset($values['date_before']) ? $values['date_before'] : NULL,
    );
    $form['date_range_after'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('After'),
      '#description'   => t('Specify a date in the format YYYY/MM/DD'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'range'))),
      '#default_value' => isset($values['date_after']) ? $values['date_after'] : NULL,
    );
    $form['date_range_before'] = array(
      '#type'          => 'date_popup',
      '#title'         => t('Before'),
      '#date_format'   => 'Y/m/d',
      '#states'        => array('visible' => array('#' . $date_range_id => array('value' => 'range'))),
      '#default_value' => isset($values['date_before']) ? $values['date_before'] : NULL,
    );
  }

  public function title() { return t('Activity'); }

  public function apply($query, array $values) {
    $inner = clone $query;
    $inner->innerJoin('campaignion_activity', 'act', "r.contact_id = act.contact_id");
    // "RedHen contact was edited" activities are never shown
    $inner->condition('act.type', 'redhen_contact_edit', '!=');
    $fields =& $inner->getFields();
    $fields = array();
    $inner->fields('r', array('contact_id'));
    $inner->groupBy('r.contact_id');

    if ($values['activity'] === 'redhen_contact_create') {
      $inner->condition('act.type', 'redhen_contact_create');
    }
    elseif ($values['activity'] !== 'any_activity') {
      $inner->innerJoin('campaignion_activity_webform', 'wact', "act.activity_id = wact.activity_id");
      $inner->innerJoin('node', 'n', "wact.nid = n.nid");
      if ($values['action_' . $values['activity']] !== 'no_specific') {
        $inner->where('n.nid = :nid OR n.tnid = :nid', array(':nid' => $values['action_' . $values['activity']]));
      }
      else {
        $inner->condition('n.type', $values['activity']);
      }
    }

    if ($values['frequency'] === 'how_many') {
      if ($values['activity'] === 'any_activity') {
        // when the user selects any activity but wants to filter for number of
        // activities we don't want to include "RedHen contact was created" activities
        $inner->condition('act.type', 'redhen_contact_create', '!=');
      }
      $inner->addExpression('COUNT(r.contact_id)', 'count_activities');
      $inner->havingCondition('count_activities', $values['how_many_nr'], $values['how_many_op']);
    }

    switch ($values['date_range']) {
      case 'range':
        $date_range = array(strtotime($values['date_range_after']), strtotime($values['date_range_before']));
        $inner->condition('act.created', $date_range, 'BETWEEN');
        break;
      case 'before':
        $before = strtotime($values['date_before']);
        $inner->condition('act.created', $before, '<');
        break;
      case 'after':
        $after  = strtotime($values['date_after']);
        $inner->condition('act.created', $after, '>');
        break;
    }
    $contact_ids = $inner->execute()->fetchCol(0);
    if (empty($contact_ids)) {
      $contact_ids = array('');
    }
    $query->condition('r.contact_id', $contact_ids, 'IN');
  }

  public function isApplicable($current) {
    $options = $this->getOptions();
    return empty($current) && count($options['activity_types']) > 1;
  }

  public function defaults() {
    $options = $this->getOptions();
    $options = array_keys($options['activity_types']);
    return array(
      'frequency'   => 'any',
      'how_many_op' => '=',
      'how_many_nr' => '1',
      'activity'    => reset($options),
      'date_range'  => 'all',
      'date_after'  => '',
      'date_before' => '',
      );
  }
}
