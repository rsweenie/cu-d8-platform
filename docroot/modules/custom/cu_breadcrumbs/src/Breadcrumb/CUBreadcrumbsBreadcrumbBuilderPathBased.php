<?php

namespace Drupal\cu_breadcrumbs\Breadcrumb;

use Drupal\node\Entity\Node;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\UrlHelper;
use Drupal\easy_breadcrumb\EasyBreadcrumbBuilder;
use Drupal\cu_breadcrumbs\CUBreadcrumbsBreadcrumbBuilderBase;

/**
 * Creates breadcrumbs for content pages using a path based approach.
 */
class CUBreadcrumbsBreadcrumbBuilderPathBased extends CUBreadcrumbsBreadcrumbBuilderBase {

  /**
   * Instance of the easy_breadcrumb builder.
   *
   * @var Drupal\easy_breadcrumb\EasyBreadcrumbBuilder
   */
  protected $easyBuilder;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    return $this->getEasyBreadcrumbBuilder()->applies($attributes);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = parent::build($route_match);

    // Get the easy breadcrumb version.
    $easy_breadcrumb = $this->getEasyBreadcrumbBuilder()->build($route_match);

    // Add all of the easy breadcrumb links onto our breadcrumb object.
    foreach ($easy_breadcrumb->getLinks() as $link) {
      $breadcrumb->addLink($link);
    }

    // Duplicate cache info.
    $breadcrumb->addCacheableDependency($easy_breadcrumb);

    return $breadcrumb;
  }

  /**
   * Returns an instance of the easy breadcrumb builder.
   *
   * @return void
   */
  protected function getEasyBreadcrumbBuilder() {
    if (!isset($this->easyBuilder)) {
      $this->easyBuilder = \Drupal::service('easy_breadcrumb.breadcrumb');
    }

    return $this->easyBuilder;
  }

}
