<?php

namespace Drupal\cu_breadcrumbs\Breadcrumb;


use Drupal\node\Entity\Node;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\UrlHelper;

/**
 * Creates breadcrumbs for content pages and news/spotlight content types.
 */
class CUBreadcrumbsBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /** @var string Config settings */
  const GLOBAL_SETTINGS = 'cu_breadcrumbs.global_settings';

  /**
   * The builder instance.
   *
   * @var \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface
   */
  protected $builder;
  
  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    return $this->getBuilder()->applies($attributes);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    return $this->getBuilder()->build($route_match);
  }

  /**
   * Returns the builder that matches settings.
   *
   * @return void
   */
  protected function getBuilder() {
    if (!isset($this->builder)) {
      $breadcrumb_builder = \Drupal::config(static::GLOBAL_SETTINGS)->get('builder');
      $breadcrumb_builder = !empty($breadcrumb_builder) ? $breadcrumb_builder : 'menu';
  
      switch ($breadcrumb_builder) {
        case 'path':
          $this->builder = new CUBreadcrumbsBreadcrumbBuilderPathBased();
          break;

        case 'menu':
        default:
          $this->builder = new CUBreadcrumbsBreadcrumbBuilderMenuBased();
          break;
      }
    }

    return $this->builder;
  }

}
