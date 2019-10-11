<?php

namespace Drupal\cu_hub_consumer;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access control handler for hub reference items.
 */
class HubReferenceAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer cu hub consumer')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    $type = $entity->bundle();
    switch ($operation) {
      case 'view':
        if ($entity->isPublished()) {
          $access_result = AccessResult::allowedIf($account->hasPermission('view cu hub reference'))
            ->cachePerPermissions()
            ->addCacheableDependency($entity);
          if (!$access_result->isAllowed()) {
            $access_result->setReason("The 'view cu hub reference' permission is required when the media item is published.");
          }
        }
        return $access_result;

      case 'delete':
        if ($account->hasPermission('delete cu hub reference')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        return AccessResult::neutral("The following permissions are required: 'delete cu hub reference'.")->cachePerPermissions();

      default:
        return AccessResult::neutral()->cachePerPermissions();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return parent::checkAccess($entity, $operation, $account);
  }

}
