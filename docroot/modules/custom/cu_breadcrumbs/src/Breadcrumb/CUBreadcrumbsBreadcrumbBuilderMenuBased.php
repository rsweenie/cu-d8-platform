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
 * Creates breadcrumbs for content pages using a menu based approach.
 */
class CUBreadcrumbsBreadcrumbBuilderMenuBased extends CUBreadcrumbsBreadcrumbBuilderBase {

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
    $breadcrumb = parent::build($route_match);

    // Create breadcrumbs from current node and parents in main nav menu
    // and append to breadcrumb.
    $node = $this->getNodeObject($route_match->getParameter('node'));
    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');
    $links = $menu_link_manager->loadLinksByRoute('entity.node.canonical', ['node' => $node->nid->value]);
    if ($link = reset($links)) {
      if ($link->getParent()) {
        foreach (array_reverse($menu_link_manager->getParentIds($link->getParent())) as $parent) {
          $parent = $menu_link_manager->createInstance($parent);
          $breadcrumb->addLink(Link::fromTextAndUrl($parent->getTitle(), $parent->getUrlObject()));
        }
      }
    }

    return $breadcrumb;
  }

}
