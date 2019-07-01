<?php

namespace Drupal\myroute_breadcrumb\Form;


use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\myroute_breadcrumb\Entity\MyrouteBreadcrumb;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Url;
use Drupal\myroute_breadcrumb\MyrouteBreadcrumbHelper;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Entity form for MyrouteBreadcrumb entity.
 */
class MyrouteBreadcrumbForm extends EntityForm implements ContainerInjectionInterface {


  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The myroute breadcrumb helper service.
   *
   * @var \Drupal\myroute_breadcrumb\MyrouteBreadcrumbHelper
   */
  protected $myrouteBreadcrumbHelper;


  /**
   * Constructs
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\myroute_breadcrumb\MyrouteBreadcrumbHelper $myroute_breadcrumb_helper
   *   The myroute breadcrumb helper service.
   */
  public function __construct(MessengerInterface $messenger, MyrouteBreadcrumbHelper $myroute_breadcrumb_helper) {
    $this->messenger = $messenger;
    $this->myrouteBreadcrumbHelper = $myroute_breadcrumb_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var MessengerInterface $messenger */
    $messenger = $container->get('messenger');
    /* @var MyrouteBreadcrumbHelper $myroute_breadcrumb_helper */
    $myroute_breadcrumb_helper = $container->get('myroute_breadcrumb.helper');
    return new static(
      $messenger,
      $myroute_breadcrumb_helper
    );
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\myroute_breadcrumb\Entity\MyrouteBreadcrumb $myroute_breadcrumb */
    $myroute_breadcrumb = $this->entity;
    $form['help'] = [
      '#markup' => '
        <ul>
          <li><strong>Укажите типы страниц для каких сработает шаблон:</strong><br />
          <em>роут</em> - один из зарегистрированных на сайте (нода, термин, товар, ...)<br /> 
          <em>условия</em> - для фильтрации страниц, любые доступные на сайте (словарь таксономии, тип ноды, текущая тема, ...). Если в списке нет - его нужно написать наследуя класс ConditionPluginBase</li>
          <li><strong>Заполните шаблоны для хлебных крошек:</strong><br />
          можно испльзовать любые допустимые токены, если нужного токена нет - его нужно написать hook_tokens</li>
        </ul>',
      '#weight' => -10,
    ];
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $myroute_breadcrumb->label(),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $myroute_breadcrumb->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\myroute_breadcrumb\Entity\MyrouteBreadcrumb::load',
      ),
      '#disabled' => !$myroute_breadcrumb->isNew(),
    );
    $form['route_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Route Name'),
      '#maxlength' => 255,
      '#default_value' => $myroute_breadcrumb->getRouteName(),
      '#required' => TRUE,
      '#autocomplete_route_name' => 'myroute_breadcrumb.router_autocomplete',
      '#description' => 'Если правило не зависит от роута, испльзуйте <strong>none</strong> (не рекомендуется, но иногда полезно для хлебных крошек по дефолту)',
    ];
    if (!$myroute_breadcrumb->isNew()) {
      $form['items_section'] = $this->createItemsSet($form, $form_state, $myroute_breadcrumb);
      $form['conditions_section'] = $this->createConditionsSet($form, $myroute_breadcrumb);
      $form['logic'] = [
        '#type' => 'radios',
        '#options' => [
          'and' => $this->t('All conditions must pass'),
          'or' => $this->t('Only one condition must pass'),
        ],
        '#default_value' => $myroute_breadcrumb->getLogic(),
      ];
    }
    return $form;
  }


  protected function createItemsSet(array $form, FormStateInterface $form_state, MyrouteBreadcrumb $myroute_breadcrumb) {
    $items = $myroute_breadcrumb->getItems();
    if ($items) {
      $num_items = $form_state->get('num_items');
      if (empty($num_items)) {
        $num_items = count($items);
      }
      $form_state->set('num_items', $num_items);
    }
    else {
      $num_items = $form_state->get('num_items');
    }
    if ($num_items === NULL) {
      $form_state->set('num_items', 1);
      $num_items = 1;
    }
    $form['items_section'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Items'),
      '#open' => TRUE,
      '#prefix' => '<div id="items-section-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['items_section']['items'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Link'),
      ],
      '#empty' => $this->t('There are no items.'),
    ];
    for ($i = 0; $i < $num_items; $i++) {
      $row = [];
      $row['name'] = [
        '#type' => 'textfield',
        '#title' => '',
        '#default_value' => !empty($items[$i]['name']) ? $items[$i]['name'] : '',
      ];
      $row['link'] = [
        '#type' => 'textfield',
        '#title' => '',
        '#default_value' => !empty($items[$i]['link']) ? $items[$i]['link'] : '',
      ];
      $form['items_section']['items'][] = $row;
    }
    $form['items_section']['help'] = [
      '#markup' => '<div class="help">Для поля ссылка укажите <strong>&lt;none&gt;</strong>, если она должна быть текстом</div>',
    ];
    $form['items_section']['actions'] = [
      '#type' => 'actions',
    ];
    $form['items_section']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['::itemsSectionAddOneSubmit'],
      '#ajax' => [
        'callback' => '::itemsSectionAjaxCallback',
        'wrapper' => 'items-section-fieldset-wrapper',
      ],
    ];
    if ($num_items > 1) {
      $form['items_section']['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove one'),
        '#submit' => ['::itemsSectionRemoveOneSubmit'],
        '#ajax' => [
          'callback' => '::itemsSectionAjaxCallback',
          'wrapper' => 'items-section-fieldset-wrapper',
        ],
      ];
    }
    $form['items_section']['token_tree'] = array(
      '#theme' => 'token_tree_link',
      '#token_types' => array_keys($this->myrouteBreadcrumbHelper->getTokenTypesByRouteName($myroute_breadcrumb->getRouteName())),
      '#show_restricted' => TRUE,
      '#show_nested' => FALSE,
    );
    return $form['items_section'];
  }


  protected function createConditionsSet(array $form, MyrouteBreadcrumb $myroute_breadcrumb) {
    $attributes = [
      'class' => ['use-ajax'],
      'data-dialog-type' => 'modal',
      'data-dialog-options' => Json::encode([
        'width' => 'auto',
      ]),
    ];
    $add_button_attributes = NestedArray::mergeDeep($attributes, [
      'class' => [
        'button',
        'button--small',
        'button-action',
        'form-item',
      ],
    ]);
    $form['conditions_section'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Conditions'),
      '#open' => TRUE,
    ];
    $form['conditions_section']['add_condition'] = [
      '#type' => 'link',
      '#title' => $this->t('Add new condition'),
      '#url' => Url::fromRoute('myroute_breadcrumb.condition_select', [
        'myroute_breadcrumb' => $myroute_breadcrumb->id(),
      ]),
      '#attributes' => $add_button_attributes,
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
        ],
      ],
    ];
    if ($conditions = $myroute_breadcrumb->getConditions()) {
      $form['conditions_section']['conditions'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Label'),
          $this->t('Description'),
          $this->t('Operations'),
        ],
        '#empty' => $this->t('There are no conditions.'),
      ];
      foreach ($conditions as $condition_id => $condition) {
        $row = [];
        $row['label']['#markup'] = $condition->getPluginDefinition()['label'];
        $row['description']['#markup'] = $condition->summary();
        $operations = [];
        $operations['edit'] = [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('myroute_breadcrumb.condition_edit', [
            'myroute_breadcrumb' => $myroute_breadcrumb->id(),
            'condition_id' => $condition_id,
          ]),
          'attributes' => $attributes,
        ];
        $operations['delete'] = [
          'title' => $this->t('Delete'),
          'url' => Url::fromRoute('myroute_breadcrumb.condition_delete', [
            'myroute_breadcrumb' => $myroute_breadcrumb->id(),
            'condition_id' => $condition_id,
          ]),
          'attributes' => $attributes,
        ];
        $row['operations'] = [
          '#type' => 'operations',
          '#links' => $operations,
        ];
        $form['conditions_section']['conditions'][$condition_id] = $row;
      }
    }
    return $form['conditions_section'];
  }


  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $myroute_breadcrumb = $this->entity;
    $is_new = $myroute_breadcrumb->isNew();
    $status = $myroute_breadcrumb->save();
    if ($status) {
      $this->messenger->addStatus($this->t('Saved the %label MyrouteBreadcrumb.', array(
        '%label' => $myroute_breadcrumb->label(),
      )));
    }
    else {
      $this->messenger->addStatus($this->t('The %label MyrouteBreadcrumb was not saved.', array(
        '%label' => $myroute_breadcrumb->label(),
      )));
    }
    if ($is_new) {
      $form_state->setRedirectUrl($myroute_breadcrumb->toUrl('edit-form'));
    }
    else {
      $form_state->setRedirectUrl($myroute_breadcrumb->toUrl('collection'));
    }
  }


  public function itemsSectionAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['items_section'];
  }


  public function itemsSectionAddOneSubmit(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_items');
    $add_button = $name_field + 1;
    $form_state->set('num_items', $add_button);
    $form_state->setRebuild();
  }

  public function itemsSectionRemoveOneSubmit(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_items');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_items', $remove_button);
    }
    $form_state->setRebuild();
  }


}
