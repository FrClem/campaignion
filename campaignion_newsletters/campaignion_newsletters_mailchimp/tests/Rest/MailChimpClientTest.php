<?php

namespace Drupal\campaignion_newsletters_mailchimp\Rest;

class MailChimpClientTest extends \DrupalUnitTestCase {

  protected function mockClient() {
    $api = $this->getMockBuilder(MailChimpClient::class)
      ->setMethods(['get', 'send'])
      ->disableOriginalConstructor()
      ->getMock();
    return $api;
  }

  public function test_getPaged_empty() {
    $api = $this->mockClient();
    $api->expects($this->once())->method('get')->with(
      $this->equalTo('/lists'),
      $this->equalTo(['count' => 10, 'offset' => 0])
    )->willReturn(
      ['lists' => [], 'total_items' => 0]
    );
    $api->getPaged('/lists');
  }

  public function test_getPaged_onePage() {
    $api = $this->mockClient();
    $list = ['id' => 'a1234', 'name' => 'mocknewsletters'];
    $api->expects($this->once())->method('get')->with(
      $this->equalTo('/lists'),
      $this->equalTo(['count' => 10, 'offset' => 0])
    )->willReturn(
      ['lists' => [$list], 'total_items' => 1]
    );
    $api->getPaged('/lists');
  }

  public function test_getPaged_twoPages() {
    $api = $this->mockClient();
    $list = ['id' => 'a1234', 'name' => 'mocknewsletters'];
    $api->expects($this->exactly(2))->method('get')->withConsecutive(
      [$this->equalTo('/lists'), $this->equalTo(['count' => 3, 'offset' => 0])],
      [$this->equalTo('/lists'), $this->equalTo(['count' => 3, 'offset' => 3])]
    )->will($this->onConsecutiveCalls(
      ['lists' => [$list, $list, $list], 'total_items' => 6],
      ['lists' => [$list, $list, $list], 'total_items' => 6]
    ));
    $api->getPaged('/lists', [], [], 3);
  }

}

