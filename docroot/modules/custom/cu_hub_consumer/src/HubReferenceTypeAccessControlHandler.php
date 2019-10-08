<?php

namespace Drupal\cu_hub_consumer;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the "hub reference" entity type.
 *
 * @see \Drupal\cu_hub_consumer\Entity\HubReferenceType
 */
class HubReferenceTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected $viewLabelOperation = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($operation === 'view label') {
      return AccessResult::allowedIfHasPermission($account, 'view cu hub references');
    }
    else {
      return parent::checkAccess($entity, $operation, $account);
    }
  }

}
