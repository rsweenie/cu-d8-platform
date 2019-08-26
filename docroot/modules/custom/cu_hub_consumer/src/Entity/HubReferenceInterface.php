<?php

namespace Drupal\cu_hub_consumer\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface defining a hub reference type entity.
 *
 * Hub reference types are bundles for hub reference items. They are used to group
 * hub references with the same semantics.
 *
 */
interface HubReferenceInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Gets the hub reference item title.
   *
   * @return string
   *   The title of the hub reference item.
   */
  public function getTitle();

  /**
   * Sets the hub reference item title.
   *
   * @param string $title
   *   The title of the hub reference item.
   *
   * @return $this
   */
  public function setTitle($title);

  /**
   * Returns the hub entity source.
   *
   * @return \Drupal\cu_hub_consumer\HubEntitySourceInterface
   *   The hub entity source.
   */
  public function getSource();

}
