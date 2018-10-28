<?php

namespace Drupal\myroute_breadcrumb\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Connection;

/**
 * Class MyrouteBreadcrumbAutocomplete.
 */
class MyrouteBreadcrumbAutocomplete extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Database\Connection $database */
    $database = $container->get('database');
    return new static(
      $database
    );
  }


  public function getRouterAutocomplete(Request $request) {
    $string = $request->query->get('q');
    $matches = [];
    if ($string) {
      /* @var \Drupal\Core\Database\Query\Select $query */
      $query = $this->database->select('router', 'r');
      $query->fields('r', array('name', 'path'));
      $query->condition('r.name', '%' . $string . '%', 'LIKE');
      $query->range(0, 10);
      $result = $query->execute();
      foreach ($result as $row) {
        $label = Html::escape($row->name . ' (' . $row->path . ')');
        $value = Html::escape($row->name);
        $matches[] = ['value' => $value, 'label' => $label];
      }
    }
    return new JsonResponse($matches);
  }


}
