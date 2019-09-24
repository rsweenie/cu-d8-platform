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
 * Creates breadcrumbs for content pages.
 */
class CUBreadcrumbsBreadcrumbBuilder extends CUBreadcrumbsBreadcrumbBuilderBase implements BreadcrumbBuilderInterface {

  /**
   * The builder instance.
   *
   * @var \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface0
   */
  protected $builders;
  
  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    $default_builder = $this->getGlobalSettings()->get('builder');
    $default_builder = !empty($default_builder) ? $default_builder : 'system';

    // Try and find settings based on node type.
    // Sometimes param node is actually the nid (usually just revisions)
    if ($node = $this->getNodeObject($attributes->getParameter('node'))) {
      $node_builder = $this->getSettings()->get($node->type->entity->get('type'))['builder'];

      // If not set to use default, then use whatever the builder gives us.
      if ($node_builder != 'default') {
        if ($builder = $this->getBuilder($default_builder)) {
          return $this->getBuilder($node_builder)->applies($attributes);
        }
        return FALSE;
      }
    }

    // Else we can try the default builder.
    if ($builder = $this->getBuilder($default_builder)) {
      return $builder->applies($attributes);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $default_builder = $this->getGlobalSettings()->get('builder');
    $default_builder = !empty($default_builder) ? $default_builder : 'system';

    // Try and find settings based on node type.
    // Sometimes param node is actually the nid (usually just revisions)
    if ($node = $this->getNodeObject($route_match->getParameter('node'))) {
      $node_builder = $this->getSettings()->get($node->type->entity->get('type'))['builder'];

      if ($builder = $this->getBuilder($node_builder)) {
        return $builder->build($route_match);
      }
    }

    // Else we can try the default builder.
    if ($builder = $this->getBuilder($default_builder)) {
      return $builder->build($route_match);
    }
  }

  /**
   * Returns the builder that matches settings.
   *
   * @return void
   */
  protected function getBuilder($builder_name) {
    if (!isset($this->builders[$builder_name])) {
      switch ($builder_name) {
        case 'empty':
        $this->builders[$builder_name] = new CUBreadcrumbsBreadcrumbBuilderEmpty();
          break;

        case 'path':
          $this->builders[$builder_name] = new CUBreadcrumbsBreadcrumbBuilderPathBased();
          break;

        case 'menu':
          $this->builders[$builder_name] = new CUBreadcrumbsBreadcrumbBuilderMenuBased();
          break;

        case 'system':
        default:
          $this->builders[$builder_name] = NULL;
          break;
      }
    }

    return $this->builders[$builder_name];
  }

}
