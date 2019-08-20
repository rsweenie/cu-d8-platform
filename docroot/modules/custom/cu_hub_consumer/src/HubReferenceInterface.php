<?php

namespace Drupal\cu_hub_consumer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a hub reference type entity.
 *
 * Hub reference types are bundles for hub reference items. They are used to group
 * hub references with the same semantics.
 *
 */
interface HubReferenceInterface extends ContentEntityInterface, EntityChangedInterface {

}
