<?php

namespace Drupal\campaignion_newsletters;

class NewsletterList {
  public $list_id;
  public $source;
  public $identifier;
  public $language;
  public $title;

  protected static $table = 'campaignion_newsletters_lists';
  protected static $key = array('list_id');
  protected static $values = array('source', 'identifier', 'title', 'language');
  protected static $serial = TRUE;

  public static function listAll() {
    $result = db_query('SELECT * FROM {campaignion_newsletters_lists} ORDER BY title');
    $lists = array();
    foreach ($result as $row) {
      $lists[$row->list_id] = new static($row);
    }
    return $lists;
  }

  public static function load($id) {
    $result = db_query('SELECT * FROM {campaignion_newsletters_lists} WHERE list_id=:id', array(':id' => $id));
    if ($row = $result->fetch()) {
      return new static($row);
    }
  }

  public static function byIdentifier($source, $identifier) {
    $result = db_query('SELECT * FROM {campaignion_newsletters_lists} WHERE source=:source AND identifier=:identifier', array(
      ':source' => $source,
      ':identifier' => $identifier,
    ));
    if ($row = $result->fetch()) {
      return new static($row);
    }
  }

  public static function fromData($data) {
    $adata = array();
    foreach ($data as $k => $v) {
      $adata[$k] = $v;
    }
    if ($item = self::byIdentifier($data['source'], $data['identifier'])) {
      unset($adata['list_id']);
      $item->__construct($adata);
      return $item;
    } else {
      return new static($data);
    }
  }

  public function __construct($data = array()) {
    foreach ($data as $k => $v) {
      $this->$k = $v;
    }
    if (!isset($this->language)) {
      $this->language = language_default('language');
    }
  }

  /**
   * Subscribe a single email-address to this newsletter.
   */
  public function subscribe($email, $fromProvider = FALSE) {
    $fields = array(
      'list_id' => $this->list_id,
      'email' => $email,
    );
    // MySQL supports multi-value merge queries, drupal does not so far,
    // so we could replace the following by a direct call to db_query().
    db_merge('campaignion_newsletters_subscriptions')
      ->key($fields)
      ->fields($fields)
      ->execute();

    if (!$fromProvider) {
      $provider = ProviderFactory::getInstance()->providerByKey($this->source);
      if ($provider) {
        $provider->subscribe($this, $email);
      }
    }
  }

  public function unsubscribe($email, $fromProvider = FALSE) {
    db_delete('campaignion_newsletters_subscriptions')
      ->condition('list_id', $this->list_id)
      ->condition('email', $email)
      ->execute();

    if (!$fromProvider) {
      $provider = ProviderFactory::getInstance()->providerByKey($this->source);
      if ($provider) {
        $provider->unsubscribe($this, $email);
      }
    }
  }

  public function isNew() {
    foreach (self::$key as $key) {
      if (isset($this->{$key})) {
        return FALSE;
      }
    }
    return TRUE;
  }

  public function save() {
    $new = TRUE;
    if ($this->isNew()) {
      $this->insert();
    } else {
      $this->update();
    }
  }

  protected function update() {
    $stmt = db_update(self::$table);
    foreach (self::$key as $key) {
      $stmt->condition($key, $this->{$key});
    }
    $stmt->fields($this->values(self::$values))
      ->execute();
  }

  protected function insert() {
    $cols = self::$values;
    if (!self::$serial) {
      $cols = array_merge($cols, self::$key);
    }
    $ret = db_insert(self::$table)
      ->fields($this->values($cols))
      ->execute();
    if (self::$serial) {
      $this->{self::$key[0]} = $ret;
    }
  }

  protected function values($keys) {
    $data = array();
    foreach ($keys as $k) {
      $data[$k] = isset($this->{$k}) ? $this->{$k} : NULL;
    }
    return $data;
  }

}
