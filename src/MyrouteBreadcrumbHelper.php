<?php

namespace Drupal\myroute_breadcrumb;

use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\token\TokenEntityMapperInterface;

class MyrouteBreadcrumbHelper {


  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * @var \Drupal\token\TokenEntityMapperInterface
   */
  protected $tokenEntityMapper;
  

  /**
   * Constructs
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $provider
   *   The route provider.
   * @param \Drupal\Token\TokenEntityMapperInterface $token_entity_mapper
   *   The token entity mapper.
   */
  public function __construct(RouteProviderInterface $provider, TokenEntityMapperInterface $token_entity_mapper) {
    $this->routeProvider = $provider;
    $this->tokenEntityMapper = $token_entity_mapper;
  }


  public function getTokenTypesByRouteName($route_name) {
    $route = $this->routeProvider->getRouteByName($route_name);
    $token_types = [];
    $path_variables = $route->compile()->getPathVariables();
    if ($path_variables) {
      $route_parameters = $route->getOption('parameters');
      foreach ($path_variables as $path_variable) {
        if (isset($route_parameters[$path_variable]['type'])) {
          $items = explode('entity:', $route_parameters[$path_variable]['type']);
          if (count($items) == 2) {
            $entity_type_id = $items[1];
            $token_type = $this->tokenEntityMapper->getTokenTypeForEntityType($entity_type_id);
            if (isset($token_types[$token_type])) {
              $token_types[$token_type . '2'] = $path_variable;
            }
            else {
              $token_types[$token_type] = $path_variable;
            }
          }
        }
      }
    }
    return $token_types;
  }


}