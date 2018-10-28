<?php

namespace Drupal\myroute_breadcrumb\Entity;

use Drupal\myroute_breadcrumb\MyrouteBreadcrumbInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the MyrouteBreadcrumb entity.
 *
 * @ConfigEntityType(
 *   id = "myroute_breadcrumb",
 *   label = @Translation("MyrouteBreadcrumb"),
 *   handlers = {
 *     "list_builder" = "Drupal\myroute_breadcrumb\Controller\MyrouteBreadcrumbListBuilder",
 *     "form" = {
 *       "add" =    "Drupal\myroute_breadcrumb\Form\MyrouteBreadcrumbForm",
 *       "edit" =   "Drupal\myroute_breadcrumb\Form\MyrouteBreadcrumbForm",
 *       "delete" = "Drupal\myroute_breadcrumb\Form\MyrouteBreadcrumbDeleteForm"
 *     }
 *   },
 *   config_prefix = "myroute_breadcrumb",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "route_name",
 *     "items",
 *     "logic",
 *     "conditions",
 *     "weight",
 *   },
 *   links = {
 *     "canonical" =   "/admin/seo/myroute_breadcrumb/list/{myroute_breadcrumb}",
 *     "edit-form" =   "/admin/seo/myroute_breadcrumb/list/{myroute_breadcrumb}/edit",
 *     "delete-form" = "/admin/seo/myroute_breadcrumb/list/{myroute_breadcrumb}/delete",
 *     "collection" =  "/admin/seo/myroute_breadcrumb/list"
 *   }
 * )
 */
class MyrouteBreadcrumb extends ConfigEntityBase implements MyrouteBreadcrumbInterface {
  /**
   * The MyrouteBreadcrumb ID.
   *
   * @var string
   */
  protected $id;


  /**
   * The MyrouteBreadcrumb label.
   *
   * @var string
   */
  protected $label;


  /**
   * @var string
   */
  protected $route_name;


  /**
   * The weight.
   *
   * @var int
   */
  protected $weight = 0;


  /**
   * @var array
   */
  protected $items = [];


  /**
   * The configuration of conditions.
   *
   * @var array
   */
  protected $conditions = [];

  /**
   * Tracks the logic used to compute, either 'and' or 'or'.
   *
   * @var string
   */
  protected $logic = 'and';


  /**
   * The plugin collection that holds the conditions.
   *
   * @var \Drupal\Component\Plugin\LazyPluginCollection
   */
  protected $conditionCollection;


  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'conditions' => $this->getConditions(),
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return $this->route_name;
  }

  /**
   * {@inheritdoc}
   */
  public function setRouteName($route_name) {
    $this->route_name = $route_name;
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems() {
    return $this->items;
  }

  /**
   * {@inheritdoc}
   */
  public function setItems($items) {
    $this->items = $items;
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getLogic() {
    return $this->logic;
  }

  /**
   * {@inheritdoc}
   */
  public function setLogic($logic) {
    $this->logic = $logic;
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    if (!$this->conditionCollection) {
      $this->conditionCollection = new ConditionPluginCollection(\Drupal::service('plugin.manager.condition'), $this->get('conditions'));
    }
    return $this->conditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getCondition($condition_id) {
    return $this->getConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function addCondition($configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getConditions()
      ->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function removeCondition($condition_id) {
    $this->getConditions()->removeInstanceId($condition_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = parent::getCacheTags();
    return Cache::mergeTags($tags, ['myroute_breadcrumb:' . $this->id]);
  }

}
