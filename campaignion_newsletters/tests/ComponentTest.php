<?php

namespace Drupal\campaignion_newsletters;

class ComponentTest extends \DrupalUnitTestCase {

  public function setUp() {
    require_once drupal_get_path('module', 'campaignion_newsletters') . '/campaignion_newsletters.component.inc';
  }

  public function testComponentDefaults() {
    $defaults = webform_component_invoke('newsletter', 'defaults');

    $expected_subset = array(
      'extra' => array(
        'opt_in_implied' => 1,
        'send_welcome' => 0,
        'optin_statement' => '',
      )
    );

    $this->assertArraySubset($expected_subset, $defaults);

    $component = webform_component_invoke('newsletter', 'edit', $defaults);

    $this->assertEqual('textarea', $component['extra']['optin_statement']['#type']);
  }

  public function testSubmitCheckbox() {
    $c['extra']['display'] = 'checkbox';

    // Not checked checkbox.
    $v['subscribed'] = 0;
    $this->assertEqual([''], _webform_submit_newsletter($c, $v));

    // Checked checkbox.
    $v['subscribed'] = 'subscribed';
    $this->assertEqual(['subscribed'], _webform_submit_newsletter($c, $v));
  }

  public function testSubmitRadios() {
    $c['extra']['display'] = 'radios';

    // Not checked checkbox.
    $v = 'no';
    $this->assertEqual([''], _webform_submit_newsletter($c, $v));

    // Checked checkbox.
    $v = 'yes';
    $this->assertEqual(['subscribed'], _webform_submit_newsletter($c, $v));
  }

  public function testTable() {
    $export = function ($v) {
      return _webform_table_newsletter(NULL, $v);
    };
    $this->assertEqual(t('not subscribed'), $export(NULL));
    $this->assertEqual(t('not subscribed'), $export(['0']));
    $this->assertEqual(t('subscribed'), $export(['subscribed']));
    // Old format - backwards compatibility.
    $this->assertEqual(t('subscribed'), $export(['subscribed' => 'subscribed']));
  }

  public function testCsvData() {
    $export = function ($v) {
      return _webform_csv_data_newsletter(NULL, [], $v);
    };
    $this->assertEqual(t('not subscribed'), $export(NULL));
    $this->assertEqual(t('not subscribed'), $export(['0']));
    $this->assertEqual(t('subscribed'), $export(['subscribed']));
    // Old format - backwards compatibility.
    $this->assertEqual(t('subscribed'), $export(['subscribed' => 'subscribed']));
  }

}
