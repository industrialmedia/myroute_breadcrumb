<?php

namespace Drupal\myroute_breadcrumb;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;

/**
 * Provides an interface for defining MyrouteBreadcrumb entities.
 */
interface MyrouteBreadcrumbInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {


  /**
   * Gets the route_name.
   *
   * @return string
   *   route_name of the MyrouteBreadcrumb.
   */
  public function getRouteName();


  /**
   * Sets the MyrouteBreadcrumb route_name.
   *
   * @param string $route_name
   *   The MyrouteBreadcrumb route_name.
   *
   * @return \Drupal\myroute_breadcrumb\MyrouteBreadcrumbInterface
   *   The called MyrouteBreadcrumb entity.
   */
  public function setRouteName($route_name);


  /**
   * Gets the weight.
   *
   * @return int
   *   weight of the MyrouteBreadcrumb.
   */
  public function getWeight();

  /**
   * Sets the MyrouteBreadcrumb weight.
   *
   * @param int $weight
   *   The MyrouteBreadcrumb weight.
   *
   * @return \Drupal\myroute_breadcrumb\MyrouteBreadcrumbInterface
   *   The called MyrouteBreadcrumb entity.
   */
  public function setWeight($weight);



  /**
   * Gets the items.
   *
   * @return array
   *   items of the MyrouteBreadcrumb.
   */
  public function getItems();

  /**
   * Sets the MyrouteBreadcrumb items.
   *
   * @param array $items
   *   The MyrouteBreadcrumb items.
   *
   * @return \Drupal\myroute_breadcrumb\MyrouteBreadcrumbInterface
   *   The called MyrouteBreadcrumb entity.
   */
  public function setItems($items);



  /**
   * Gets logic used to compute, either 'and' or 'or'.
   *
   * @return string
   *   Either 'and' or 'or'.
   */
  public function getLogic();

  /**
   * Sets logic used to compute, either 'and' or 'or'.
   *
   * @param string $logic
   *   Either 'and' or 'or'.
   */
  public function setLogic($logic);


  /**
   * Returns the conditions.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   *   An array of configured condition plugins.
   */
  public function getConditions();


  /**
   * Returns the condition.
   *
   * @param string $condition_id
   * @return \Drupal\Core\Condition\ConditionInterface
   *   condition.
   */
  public function getCondition($condition_id);


  /**
   *  Add condition to Conditions.
   *
   * @param array $configuration
   * @return \Drupal\Core\Condition\ConditionInterface
   *   condition.
   */
  public function addCondition($configuration);


  /**
   *  Remove condition from Conditions.
   *
   * @param string $condition_id
   * @return \Drupal\myroute_breadcrumb\MyrouteBreadcrumbInterface
   *   The called MyrouteBreadcrumb entity.
   */
  public function removeCondition($condition_id);


  

  
}
