<?php

namespace Drupal\campaignion_newsletters_mailchimp;

use \Drupal\campaignion\Contact;
use \Drupal\campaignion\CRM\Import\Source\SourceInterface;
use \Drupal\campaignion\CRM\Export\SingleValueField;
use \Drupal\campaignion\CRM\Export\WrapperField;
use \Drupal\campaignion\CRM\Export\MappedWrapperField;
use \Drupal\campaignion\CRM\Export\DateField;
use \Drupal\campaignion\CRM\Export\KeyedField;
use \Drupal\campaignion\CRM\Export\TagsField;

class CampaignionContactExporter implements SourceInterface {
  protected $map;
  public function __construct(Contact $contact) {
    $wrappedContact = $contact->wrap();

    $this->map = array();
    $this->map['EMAIL'] = new WrapperField($wrappedContact, 'email');
    $this->map['FNAME'] = new SingleValueField($contact, 'first_name');
    $this->map['LNAME'] = new SingleValueField($contact, 'last_name');
    $this->map['SALUTATION'] = new WrapperField($wrappedContact, 'field_salutation');
    $this->map['TITLE'] = new WrapperField($wrappedContact, 'field_title');
    $this->map['GENDER'] = new WrapperField($wrappedContact, 'field_gender');
    $this->map['DATE_OF_BIRTH'] = new DateField($wrappedContact, 'field_date_of_birth', '%Y-%m-%d');
    $this->map['STREET'] = new KeyedField($wrappedContact, 'field_address', 'thoroughfare');
    $this->map['COUNTRY'] = new KeyedField($wrappedContact, 'field_address', 'country');
    $this->map['ZIP'] = new KeyedField($wrappedContact, 'field_address', 'postal_code');
    $this->map['CITY'] = new KeyedField($wrappedContact, 'field_address', 'locality');
    $this->map['REGION'] = new KeyedField($wrappedContact, 'field_address', 'administrative_area');
    $this->map['LANGUAGE'] = new WrapperField($wrappedContact, 'field_preferred_language');
    $this->map['CREATED'] = new DateField($wrappedContact, 'created', '%Y-%m-%d');
    $this->map['UPDATED'] = new DateField($wrappedContact, 'updated', '%Y-%m-%d');
    $this->map['TAGS'] = new TagsField($wrappedContact, 'supporter_tags');

  }

  public function value($key) {
    if (isset($this->map[$key])) {
      return $this->map[$key]->value();
    }
    return NULL;
  }
}
