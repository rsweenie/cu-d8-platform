<?php 

namespace Drupal\cu_hub_api\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueHubPathConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Creates a new PathAliasConstraintValidator instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!($item = $items->first())) {
      return;
    }

    $field_name = $items
      ->getFieldDefinition()
      ->getName();

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $items
      ->getEntity();
    $entity_type_id = $entity
      ->getEntityTypeId();
    $id_key = $entity
      ->getEntityType()
      ->getKey('id');

    if ($entity->hasField('field_hub_site') && !$entity->field_hub_site->isEmpty()) {
      $hub_sites = $entity->field_hub_site->referencedEntities();
      $hub_site = reset($hub_sites);

      $value_taken = (bool) \Drupal::entityQuery($entity_type_id)
        ->condition($id_key, (int) $entity->id(), '<>')
        ->condition('field_hub_site.target_id', $hub_site->id())
        ->condition($field_name, $item->value)
        ->range(0, 1)
        ->count()
        ->execute();

      if ($value_taken) {
        $this->context
          ->addViolation($constraint->notUnique, [
          '%value' => $item->value,
          '@entity_type' => $entity
            ->getEntityType()
            ->getLowercaseLabel(),
          '@field_name' => Unicode::strtolower($items
            ->getFieldDefinition()
            ->getLabel()),
          '%site' => $hub_site->label(),
        ]);
      }
    }

  }

}
