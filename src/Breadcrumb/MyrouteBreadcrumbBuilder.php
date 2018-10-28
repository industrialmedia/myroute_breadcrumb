<?php

namespace Drupal\myroute_breadcrumb\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Utility\Token;
use Drupal\myroute_breadcrumb\MyrouteBreadcrumbHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\myroute_breadcrumb\MyrouteBreadcrumbEvaluator;
use Drupal\Core\Path\PathValidatorInterface;


/**
 * Class MyrouteBreadcrumbBuilder.
 */
class MyrouteBreadcrumbBuilder implements BreadcrumbBuilderInterface {



  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The MyrouteBreadcrumbEvaluator service.
   *
   * @var \Drupal\myroute_breadcrumb\MyrouteBreadcrumbEvaluator
   */
  protected $myrouteBreadcrumbEvaluator;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The myroute breadcrumb helper service.
   *
   * @var \Drupal\myroute_breadcrumb\MyrouteBreadcrumbHelper
   */
  protected $myrouteBreadcrumbHelper;


  /**
   * Constructs a new MyrouteBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Utility\Token $token
   *   The token utility.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\myroute_breadcrumb\MyrouteBreadcrumbEvaluator $myroute_breadcrumb_evaluator
   *   The myroute breadcrumb evaluator.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator service.
   * @param \Drupal\myroute_breadcrumb\MyrouteBreadcrumbHelper $myroute_breadcrumb_helper
   *   The myroute breadcrumb helper service.
   */
  public function __construct(Token $token, EntityTypeManagerInterface $entity_type_manager, MyrouteBreadcrumbEvaluator $myroute_breadcrumb_evaluator, PathValidatorInterface $path_validator, MyrouteBreadcrumbHelper $myroute_breadcrumb_helper) {
    $this->token = $token;
    $this->entityTypeManager = $entity_type_manager;
    $this->myrouteBreadcrumbEvaluator = $myroute_breadcrumb_evaluator;
    $this->pathValidator = $path_validator;
    $this->myrouteBreadcrumbHelper = $myroute_breadcrumb_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    $route_name = $attributes->getRouteName();
    if ($this->myrouteBreadcrumbEvaluator->evaluateByRouteName($route_name)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {

    $route_name = $route_match->getRouteName();
    $breadcrumb = new Breadcrumb();
    if ($myroute_breadcrumb_id = $this->myrouteBreadcrumbEvaluator->evaluateByRouteName($route_name)) {
      /** @var \Drupal\myroute_breadcrumb\Entity\MyrouteBreadcrumb $myroute_breadcrumb */
      $myroute_breadcrumb = $this->entityTypeManager->getStorage('myroute_breadcrumb')
        ->load($myroute_breadcrumb_id);
      $items = $myroute_breadcrumb->getItems();
      $token_types = $this->myrouteBreadcrumbHelper->getTokenTypesByRouteName($route_name);
      $data = [];
      foreach ($token_types as $token_type => $parameter_name) {
        $parameter = $route_match->getParameter($parameter_name);
        $data[$token_type] = $parameter;
        $breadcrumb->addCacheableDependency($parameter);
      }
      foreach ($items as $item) {
        $item['name'] = $this->token->replace($item['name'], $data, array('clear' => TRUE));
        $item['link'] = $this->token->replace($item['link'], $data, array('clear' => TRUE));
        $item['link'] = str_replace('https://' . $_SERVER['HTTP_HOST'], '', $item['link']);
        $item['link'] = str_replace('http://' . $_SERVER['HTTP_HOST'], '', $item['link']);
        if ($item['link'] === '<front>') {
          $item['link'] = '/';
        }
        if (empty($item['name'])) {
          continue;
        }
        if (empty($item['link'])) {
          continue;
        }
        $item['name'] = Markup::create($item['name']);
        if ($url = $this->pathValidator->getUrlIfValid($item['link'])) {
          $link = Link::fromTextAndUrl($item['name'], $url);
          $breadcrumb->addLink($link);
        }
      }
    }
    if (!$breadcrumb->getLinks()) {
      $link = Link::createFromRoute(t('Home'), '<front>');
      $breadcrumb->addLink($link);
    }
    $breadcrumb->addCacheContexts(['route']);
    return $breadcrumb;
  }

}
