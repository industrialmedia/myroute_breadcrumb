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
    $form['is_remove_current_url'] = array(
      '#type' => 'checkbox',
      '#title' => 'Удалять ссылку, если это сылка на текущую страницу',
      '#default_value' => !empty($config->get('is_remove_current_url')) ? TRUE : FALSE,
      '#description' => 'Крошка станет текстом, а не ссылкой',
    );
    $form['is_add_current_title'] = array(
      '#type' => 'checkbox',
      '#title' => 'Добавлять послейднюю крошку текущий заголовок',
      '#default_value' => !empty($config->get('is_add_current_title')) ? TRUE : FALSE,
    );
    $form['is_add_current_title_to_one'] = array(
      '#type' => 'checkbox',
      '#title' => 'Добавлять послейднюю крошку текущий заголовок, если кол-во крошек меньше двух',
      '#default_value' => !empty($config->get('is_add_current_title_to_one')) ? TRUE : FALSE,
      '#description' => 'Рекомендуется, чтобы избежать крошки из одной ссылки на главную',
      '#states' => [
        'visible' => [
          ':input[name="is_add_current_title"]' => ['checked' => FALSE],
        ],
      ],
    );
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
      ->set('is_remove_current_url', $form_state->getValue('is_remove_current_url'))
      ->set('is_add_current_title', $form_state->getValue('is_add_current_title'))
      ->set('is_add_current_title_to_one', $form_state->getValue('is_add_current_title_to_one'))
      ->save();
    parent::submitForm($form, $form_state);
  }


}
