<?php

namespace Drupal\cu_breadcrumbs\Breadcrumb;

use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\UrlHelper;
use Drupal\cu_breadcrumbs\Breadcrumb\CUBreadcrumbModel;

/**
 * Creates breadcrumbs for content pages and news/spotlight content types.
 */
class CUBreadcrumbsBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * Checks content type of current node to determine if it gets a breadcrumb.
   */
  public function applies(RouteMatchInterface $attributes) {

    $node = $attributes->getParameter('node');
    // Get node object, node revisions are not the same as nodes and are not passed as objects.
    if (gettype($node) === 'string') {
      $node = Node::load($attributes->getParameter('node'));
    }
    // If there's a node, do the code.
    if (!empty($node)) {
      //make get by id a static call returning the model.
      $cu_breadcrumb = new CUBreadcrumbModel();
      //get that breadcrumb model
      $cu_breadcrumb->getById($node->type->entity->get('uuid'));
      //return the apply value(1 or 0, true or false)
      return $cu_breadcrumb->get('apply');
    }
  }

  /**
   * Builds the breadcrumb.
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    // Create first breadcrumb to www.creighton.edu.
    $url = Url::fromUri('https://www.creighton.edu');
    $breadcrumb->addLink(Link::fromTextAndUrl('Home', $url));

    // Get and loop through breadcrumbs menu.
    $menu_name = 'breadcrumbs-menu';
    $menu_tree = \Drupal::menuTree();
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    $tree = $menu_tree->load($menu_name, $parameters);
    if (count($tree) > 0) {
      $manipulators = [
          // Only show links that are accessible for the current user.
          ['callable' => 'menu.default_tree_manipulators:checkAccess'],
          // Use the default sorting of menu links.
          ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ];
      $tree = $menu_tree->transform($tree, $manipulators);
      $menu_tmp = $menu_tree->build($tree);
      if ($menu_tmp['#items']) {
        foreach ($menu_tmp['#items'] as $item) {
          $item_url = $item['url']->toString();
          if (UrlHelper::isExternal($item_url)) {
            $breadcrumb->addLink(Link::fromTextAndUrl($item['title'], $item['url']));
          }
          else {
            if ($item['url']->getRouteName() == 'front') {
              $breadcrumb->addLink(Link::fromTextAndUrl($item['title'], $item['url']->getRouteName()));
            }
            else {
              $breadcrumb->addLink(Link::fromTextAndUrl($item['title'], $item['url']->getInternalPath()));
            }
          }
        }
      }
    }
    // Create breadcrumbs from current node and parents in main nav menu.
    $node = \Drupal::routeMatch()->getParameter('node');

    if ($node instanceof NodeInterface) {
      $nid = $node->id();
    }
    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');
    $links = $menu_link_manager->loadLinksByRoute('entity.node.canonical', ['node' => $nid]);
    if ($link = reset($links)) {
      if ($link->getParent()) {
        $parents = array_reverse($menu_link_manager->getParentIds($link->getParent()));
        foreach ($parents as $parent) {
          $parent = $menu_link_manager->createInstance($parent);
          $parent_title = $parent->getTitle();
          $parent_url = $parent->getUrlObject();
          $breadcrumb->addLink(Link::fromTextAndUrl($parent_title, $parent_url));
        }
      }
    }
    return $breadcrumb;
    // }.
  }

}
