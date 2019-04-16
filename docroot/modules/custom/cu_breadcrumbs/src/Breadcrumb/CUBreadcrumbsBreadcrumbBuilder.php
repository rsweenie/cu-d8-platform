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
  const SETTINGS = 'cu_breadcrumbs.settings';
  /** @var string Menu Name */
  const MENU_NAME = 'breadcrumbs-menu';
  /**
   * Checks content type of current node to determine if it gets a breadcrumb.
   */
  public function applies(RouteMatchInterface $attributes) {
    //sometimes param node is actually the nid (usually just revisions)
    $node = $this->getNodeObject($attributes->getParameter('node'));
    // If there's a node, do the code.
    if (!empty($node)) {
      //return the apply value(1 or 0, true or false)
      return \Drupal::config(static::SETTINGS)
                      ->get($node->type->entity->get('type'))['apply'];
    }

  }

  /**
   * Builds the breadcrumb.
   */
  public function build(RouteMatchInterface $route_match) {
    // new breadcrumb
    $breadcrumb = new Breadcrumb();
    // Get and loop through breadcrumbs menu.
    if ($menu_tmp = $this->createMenu()) {
      if ($menu_tmp['#items']) {
        foreach ($menu_tmp['#items'] as $item) {
          //add breadcrumbs-menu link to breadcrumb
          $breadcrumb->addLink(Link::fromTextAndUrl($item['title'], $item['url']));
        }
      }
    }
    
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

  //return a menu or false
  private function createMenu(){
    $menu_name = static::MENU_NAME;
    $menu_tree = \Drupal::menuTree();
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    
    //if tree is not empty, create the menu
    if($tree = $menu_tree->load($menu_name, $parameters)){
      $manipulators = [ // Only show links that are accessible for the current user.
                        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
                        // Use the default sorting of menu links.
                        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
                      ];

      $tree = $menu_tree->transform($tree, $manipulators);
      return $menu_tree->build($tree);
    }
    //return false if tree is empty
    return false;
  }

  //returns a node object
  private function getNodeObject($obj){
    // Get node object, node revisions are not the same as nodes and are not passed as objects.
    // the above error was causing the original build function to display a warning message.
    if (gettype($obj) === 'string') {
      //if node is a string then its the nid not the actual node.
      return Node::load($obj);
    }
    return $obj;
  }
}
