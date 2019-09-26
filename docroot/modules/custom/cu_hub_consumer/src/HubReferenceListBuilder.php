<?php 

namespace Drupal\cu_hub_consumer;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\cu_hub_consumer\Entity\HubReferenceType;

/**
 * Provides a listing of hub references.
 */
class HubReferenceListBuilder extends EntityListBuilder {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * Constructs a new HubReferenceListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter, RedirectDestinationInterface $redirect_destination) {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_formatter;
    $this->redirectDestination = $redirect_destination;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static($entity_type, $container
      ->get('entity.manager')
      ->getStorage($entity_type
      ->id()), $container
      ->get('date.formatter'), $container
      ->get('redirect.destination'));
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = array(
      'title' => $this
        ->t('Title'),
      'type' => array(
        'data' => $this
          ->t('Content type'),
        'class' => array(
          RESPONSIVE_PRIORITY_MEDIUM,
        ),
      ),
      'status' => $this
        ->t('Status'),
      'changed' => array(
        'data' => $this
          ->t('Updated'),
        'class' => array(
          RESPONSIVE_PRIORITY_LOW,
        ),
      ),
    );

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {

    /** @var \Drupal\cu_hub_consumer\Entity\HubReferenceInterface $entity */
    $uri = $entity
      ->urlInfo();
    $options = $uri
      ->getOptions();
    $uri
      ->setOptions($options);

    $row['title']['data'] = array(
      '#type' => 'link',
      '#title' => $entity
        ->label(),
      '#suffix' => ' ' . drupal_render($mark),
      '#url' => $uri,
    );

    $type = HubReferenceType::load($entity->bundle());
    $row['type'] = $type->label();

    $row['status'] = $entity
      ->isPublished() ? $this
      ->t('published') : $this
      ->t('not published');

    $row['changed'] = $this->dateFormatter
      ->format($entity
      ->getChangedTime(), 'short');

    $row['operations']['data'] = $this
      ->buildOperations($entity);
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $destination = $this->redirectDestination
      ->getAsArray();
    foreach ($operations as $key => $operation) {
      $operations[$key]['query'] = $destination;
    }
    return $operations;
  }
  
}
