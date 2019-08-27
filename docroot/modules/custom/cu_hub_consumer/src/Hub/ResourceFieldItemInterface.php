<?php

namespace Drupal\cu_hub_consumer\Hub;

/**
 * Defines an interface for a hub resource field plugin.
 */
interface ResourceFieldItemInterface {

  public function getParentList();

  public function setParentList(ResourceFieldItemListInterface $list);

  public function getValue();

  public function setValue($value);

  public function isEmpty();

  public function getString();

}
