<?php

namespace Drupal\cu_breadcrumbs;

use Drupal\node\Entity\Node;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\UrlHelper;

/**
 * A base class for our breadcumb builders.
 */
abstract class CUBreadcrumbsBreadcrumbBuilderBase implements BreadcrumbBuilderInterface {

  /** @var string Config settings */
  const GLOBAL_SETTINGS = 'cu_breadcrumbs.global_settings';

  /** @var string Config settings */
  const SETTINGS = 'cu_breadcrumbs.settings';

  /** @var string Menu Name */
  const MENU_NAME = 'breadcrumbs-menu';

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
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

    // Expire cache contexts for settings and breadcrumb menu changes.
    $breadcrumb->addCacheableDependency($this->getGlobalSettings());
    $breadcrumb->addCacheableDependency($this->getSettings());
    // @TODO: add cacheability info for the breadcrumb menu.

    return $breadcrumb;
  }

  protected function getGlobalSettings() {
    return \Drupal::config(static::GLOBAL_SETTINGS);
  }

  protected function getSettings() {
    return \Drupal::config(static::SETTINGS);
  }

  /**
   * Return the breadcrumb menu.
   *
   * @return mixed
   */
  protected function createMenu(){
    $menu_name = static::MENU_NAME;
    $menu_tree = \Drupal::menuTree();
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    
    // if tree is not empty, create the menu
    if ($tree = $menu_tree->load($menu_name, $parameters)){
      $manipulators = [ // Only show links that are accessible for the current user.
                        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
                        // Use the default sorting of menu links.
                        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
                      ];

      $tree = $menu_tree->transform($tree, $manipulators);
      return $menu_tree->build($tree);
    }

    // return false if tree is empty
    return false;
  }

  /**
   * Loads a node object if needed.
   *
   * @param mixed $obj
   * @return void
   */
  protected function getNodeObject($obj){
    // Get node object, node revisions are not the same as nodes and are not passed as objects.
    // the above error was causing the original build function to display a warning message.
    if (is_scalar($obj)) {
      // if node is a string then its the nid not the actual node.
      return Node::load($obj);
    }

    return $obj;
  }

}
