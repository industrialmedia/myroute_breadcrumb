<?php

namespace Drupal\myroute_breadcrumb\Form;

/**
 * Provides a form for editing an condition.
 */
class ConditionEditForm extends ConditionFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myroute_breadcrumb_condition_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareCondition($condition_id) {
    // Load the condition directly from the myroute_breadcrumb entity.
    return $this->myroute_breadcrumb->getCondition($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  protected function submitButtonText() {
    return $this->t('Update condition');
  }

  /**
   * {@inheritdoc}
   */
  protected function submitMessageText() {
    return $this->t('The %label condition has been updated.', ['%label' => $this->condition->getPluginDefinition()['label']]);
  }

}
