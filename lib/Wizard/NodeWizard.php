<?php

namespace Drupal\campaignion\Wizard;

abstract class NodeWizard extends \Drupal\oowizard\Wizard {
  public $node;

  public function __construct($node = NULL, $type = NULL, $user = NULL) {
    foreach ($this->steps as &$class) {
      if ($class[0] != '\\') {
        $class = '\\' . __NAMESPACE__ . '\\' . $class;
      }
    }
    parent::__construct($user);
    if ($node == NULL) {
      $this->node             = $this->prepareNode($type);
      $this->formInfo['path'] = 'wizard/' . $this->node->type;
    } else {
      $this->node             = $node;
      $this->formInfo['path'] = 'node/' . $this->node->nid . '/wizard/%step';
    }

    drupal_set_title(t('Create ' . node_type_get_name($this->node)));
    if (isset($this->node->nid) && $this->node->status) {
      $this->formInfo += array(
        'show return' => TRUE,
        'return path' => 'node/' . $this->node->nid,
      );
    }
  }

  public function wizardForm() {
    $form = parent::wizardForm() + array(
      'wizard_head' => array(),
      'wizard_advanced' => array(),
    );
    $form['wizard_head']['trail'] = $this->trail();

    return $form;
  }

  public function prepareNode($type) {
    $node = (object) array('type' => $type);
    $node->uid  = $this->user->uid;
    $node->name = $this->user->name;
    $node->language = LANGUAGE_NONE;
    $node->title = '';
    return $node;
  }

  public function trailItems() {
    $trail = array();
    $accessible = TRUE;
    foreach ($this->stepHandlers as $urlpart => $step) {
      $is_current = $urlpart == $this->currentStep;
      $trail[] = array(
        'url' => strtr($this->formInfo['path'], array('%step' => $urlpart)),
        'title' => $step->getTitle(),
        'accessible' => $accessible = ($accessible && (!$is_current || $this->node->status) && $step->checkDependencies()),
        'current' => $urlpart == $this->currentStep,
      );
    }
    return $trail;
  }
}
