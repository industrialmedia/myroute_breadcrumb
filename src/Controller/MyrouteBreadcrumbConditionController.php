<?php

namespace Drupal\myroute_breadcrumb\Controller;

use Drupal\myroute_breadcrumb\Entity\MyrouteBreadcrumb;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MyrouteBreadcrumbConditionController.
 *
 * @package Drupal\myroute_breadcrumb\Controller
 */
class MyrouteBreadcrumbConditionController extends ControllerBase {

  /**
   * Drupal\Core\Condition\ConditionManager definition.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * Constructs
   *
   * @param \Drupal\Core\Condition\ConditionManager $condition_manager
   *   The condition manager.
   */
  public function __construct(ConditionManager $condition_manager) {
    $this->conditionManager = $condition_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Condition\ConditionManager $condition_manager */
    $condition_manager = $container->get('plugin.manager.condition');
    return new static(
      $condition_manager
    );
  }


  /**
   * Presents a list of conditions to add to the myroute_breadcrumb entity.
   *
   * @param \Drupal\myroute_breadcrumb\Entity\MyrouteBreadcrumb $myroute_breadcrumb
   *   The myroute_breadcrumb entity
   * @return array
   *   The condition selection page.
   */
  public function selectCondition(MyrouteBreadcrumb $myroute_breadcrumb) {
    $build = [
      '#theme' => 'links',
      '#links' => [],
    ];
    $available_plugins = $this->conditionManager->getDefinitions();
    foreach ($available_plugins as $condition_id => $condition) {
      $build['#links'][$condition_id] = [
        'title' => $condition['label'],
        'url' => Url::fromRoute('myroute_breadcrumb.condition_add', [
          'myroute_breadcrumb' => $myroute_breadcrumb->id(),
          'condition_id' => $condition_id,
        ]),
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 'auto',
          ]),
        ],
      ];
    }
    return $build;
  }

}
