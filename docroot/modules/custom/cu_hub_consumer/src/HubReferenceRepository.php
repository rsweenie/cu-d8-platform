<?php

namespace Drupal\cu_hub_consumer;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\Language;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class HubReferenceRepository allows easy access to hub references.
 */
class HubReferenceRepository {

  /**
   * Used to retrieve regex redirects.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a HubReferenceRepository object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $connection, RequestStack $request_stack) {
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->requestStack = $request_stack;
  }

    /**
   * Load hub reference entity by id.
   *
   * @param int $reference_id
   *   The hub reference id.
   *
   * @return \Drupal\cu_hub_consumer\Entity\HubReference|null
   *   The hub reference entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function load($reference_id) {
    /** @var \Drupal\cu_hub_consumer\Entity\HubReference $hub_reference */
    $hub_reference = $this->entityTypeManager->getStorage('cu_hub_reference')->load($reference_id);
    return $hub_reference;
  }

  /**
   * Loads multiple hub reference entities.
   *
   * @param array $reference_ids
   *   Hub reference ids to load.
   *
   * @return \Drupal\cu_hub_consumer\Entity\HubReference[]
   *   List of hub reference entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function loadMultiple(array $reference_ids = NULL) {
    /** @var \Drupal\cu_hub_consumer\Entity\HubReference[] $hub_references */
    $hub_references = $this->entityTypeManager->getStorage('cu_hub_reference')->loadMultiple($reference_ids);
    return $hub_references;
  }

}
