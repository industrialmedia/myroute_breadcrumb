<?php

namespace Drupal\myroute_breadcrumb;

use Drupal\myroute_breadcrumb\Entity\MyrouteBreadcrumb;
use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class ConditionEvaluator.
 *
 * @package Drupal\myroute_breadcrumb
 */
class MyrouteBreadcrumbEvaluator {

  use ConditionAccessResolverTrait;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The context manager service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * @var array
   */
  protected $evaluations = [];


  /**
   * @var array
   */
  protected $evaluations_by_route_name = [];


  /**
   * Constructor.
   *
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The plugin context handler.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The lazy context repository service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(ContextHandlerInterface $context_handler, ContextRepositoryInterface $context_repository, EntityTypeManagerInterface $entity_type_manager) {
    $this->contextRepository = $context_repository;
    $this->contextHandler = $context_handler;
    $this->entityTypeManager = $entity_type_manager;
  }

  public function evaluateByRouteName($route_name) {
    if (!isset($this->evaluations_by_route_name[$route_name])) {
      $myroute_breadcrumbs = $this->entityTypeManager->getStorage('myroute_breadcrumb')
        ->loadByProperties(['route_name' => $route_name]);
      uasort($myroute_breadcrumbs, function (MyrouteBreadcrumb $a, MyrouteBreadcrumb $b) {
        $a = $a->getWeight();
        $b = $b->getWeight();
        if ($a == $b) {
          return 0;
        }
        return ($a < $b) ? -1 : 1;
      });
      if (!empty($myroute_breadcrumbs)) {
        foreach ($myroute_breadcrumbs as $myroute_breadcrumb) {
          /** @var \Drupal\myroute_breadcrumb\Entity\MyrouteBreadcrumb $myroute_breadcrumb */
          if ($this->evaluate($myroute_breadcrumb)) {
            $this->evaluations_by_route_name[$route_name] = $myroute_breadcrumb->id();
            return $this->evaluations_by_route_name[$route_name]; // Сразу возращаем, берем 1й из списка
          }
        }
      }
      if (!isset($this->evaluations_by_route_name[$route_name])) {
        $this->evaluations_by_route_name[$route_name] = FALSE;
      }
    }
    return $this->evaluations_by_route_name[$route_name];
  }

  /**
   * @param \Drupal\myroute_breadcrumb\Entity\MyrouteBreadcrumb $myroute_breadcrumb
   *
   * @return boolean
   */
  public function evaluate(MyrouteBreadcrumb $myroute_breadcrumb) {
    $id = $myroute_breadcrumb->id();
    if (!isset($this->evaluations[$id])) {
      /** @var ConditionPluginCollection $conditions */
      $conditions = $myroute_breadcrumb->getConditions();
      if (empty($conditions)) {
        return TRUE;
      }
      $logic = $myroute_breadcrumb->getLogic();
      if ($this->applyContexts($conditions, $logic)) {
        /** @var \Drupal\Core\Condition\ConditionInterface[] $conditions */
        $this->evaluations[$id] = $this->resolveConditions($conditions, $logic);
      }
      else {
        $this->evaluations[$id] = FALSE;
      }
    }
    return $this->evaluations[$id];
  }

  /**
   * @param \Drupal\Core\Condition\ConditionPluginCollection $conditions
   * @param string $logic
   *
   * @return bool
   */
  protected function applyContexts(ConditionPluginCollection &$conditions, $logic) {
    $have_1_testable_condition = FALSE;
    foreach ($conditions as $id => $condition) {
      if ($condition instanceof ContextAwarePluginInterface) {
        try {
          $contexts = $this->contextRepository->getRuntimeContexts(array_values($condition->getContextMapping()));
          //dump($contexts);
          $this->contextHandler->applyContextMapping($condition, $contexts);
          $have_1_testable_condition = TRUE;
        } catch (ContextException $e) {
          if ($logic == 'and') {
            // Logic is all and found condition with contextException.
            return FALSE;
          }
          $conditions->removeInstanceId($id);
        }

      }
      else {
        $have_1_testable_condition = TRUE;
      }
    }
    if ($logic == 'or' && !$have_1_testable_condition) {
      return FALSE;
    }
    return TRUE;
  }

}
