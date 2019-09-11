<?php

namespace Drupal\cu_hub_consumer\Plugin\Block;

use Drupal\cu_hub_consumer\Entity\HubReferenceInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\layout_builder\Plugin\Block\FieldBlock;

/**
 * Hub reference field block.
 *
 * Specific block class for Layout Builder's field block and variations to
 * ensure field replacement works.
 */
class HubReferenceFieldBlock extends FieldBlock {

}
