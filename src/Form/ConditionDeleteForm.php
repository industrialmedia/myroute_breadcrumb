<?php

namespace Drupal\myroute_breadcrumb\Form;


use Drupal\myroute_breadcrumb\Entity\MyrouteBreadcrumb;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting an condition.
 */
class ConditionDeleteForm extends ConfirmFormBase implements ContainerInjectionInterface {



  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The myroute_breadcrumb entity this selection condition belongs to.
   *
   * @var \Drupal\myroute_breadcrumb\Entity\MyrouteBreadcrumb
   */
  protected $myroute_breadcrumb;

  /**
   * The condition used by this form.
   *
   * @var \Drupal\Core\Condition\ConditionInterface
   */
  protected $condition;


  /**
   * Constructs
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Messenger\MessengerInterface $messenger */
    $messenger = $container->get('messenger');
    return new static(
      $messenger
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myroute_breadcrumb_condition_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the condition %name?', ['%name' => $this->condition->getPluginDefinition()['label']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->myroute_breadcrumb->urlInfo('edit-form');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, MyrouteBreadcrumb $myroute_breadcrumb = NULL, $condition_id = NULL) {
    $this->myroute_breadcrumb = $myroute_breadcrumb;
    $this->condition = $myroute_breadcrumb->getCondition($condition_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->myroute_breadcrumb->removeCondition($this->condition->getConfiguration()['uuid']);
    $this->myroute_breadcrumb->save();
    $this->messenger->addStatus($this->t('The condition %name has been removed.', ['%name' => $this->condition->getPluginDefinition()['label']]));
    $form_state->setRedirectUrl(Url::fromRoute('entity.myroute_breadcrumb.edit_form', [
      'myroute_breadcrumb' => $this->myroute_breadcrumb->id(),
    ]));
  }

}
