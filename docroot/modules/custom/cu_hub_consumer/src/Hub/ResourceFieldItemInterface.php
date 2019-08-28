<?php

namespace Drupal\cu_hub_consumer\Hub;

/**
 * Defines an interface for a hub resource field plugin.
 */
interface ResourceFieldItemInterface {

  public function getParentList();

  public function setParentList(ResourceFieldItemListInterface $list);

  public function getParentResource();

  public function getValue();

  public function setValue($value);

  public function isEmpty();

  public function getString();

  /**
   * Builds a renderable array for a fully themed field item.
   *
   * @return array
   *   A renderable array for a themed field item.
   */
  public function view();

}
