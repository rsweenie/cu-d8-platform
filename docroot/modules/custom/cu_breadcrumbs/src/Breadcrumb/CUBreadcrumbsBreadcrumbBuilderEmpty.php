<?php

namespace Drupal\cu_breadcrumbs\Breadcrumb;

use Drupal\node\Entity\Node;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\UrlHelper;
use Drupal\cu_breadcrumbs\CUBreadcrumbsBreadcrumbBuilderBase;

/**
 * Creates breadcrumbs for content pages thatr are always empty.
 */
class CUBreadcrumbsBreadcrumbBuilderEmpty extends CUBreadcrumbsBreadcrumbBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    return new Breadcrumb();
  }

}
