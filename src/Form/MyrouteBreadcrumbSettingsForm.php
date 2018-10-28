<?php

namespace Drupal\myroute_breadcrumb\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


class MyrouteBreadcrumbSettingsForm extends ConfigFormBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myroute_breadcrumb_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['myroute_breadcrumb.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('myroute_breadcrumb.settings');
    $form['breadcrumb_front_name'] = [
      '#type' => 'textfield',
      '#title' => 'Название хлебной крошки на главную',
      '#default_value' => !empty($config->get('breadcrumb_front_name')) ? $config->get('breadcrumb_front_name') : '',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('myroute_breadcrumb.settings')
      ->set('breadcrumb_front_name', $form_state->getValue('breadcrumb_front_name'))
      ->save();
    parent::submitForm($form, $form_state);
  }


}
