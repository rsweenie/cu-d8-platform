<?php 

namespace Drupal\cu_hub_api\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HubSitePathConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

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
  public function validate($item, Constraint $constraint) {
    if ($item->isEmpty()) {
      return;
    }

    $field_name = $item->getFieldDefinition()->getName();
    $target_type_id = $item->getFieldDefinition()->getSetting('target_type');

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $item
      ->getEntity();
    $entity_type_id = $entity
      ->getEntityTypeId();
    $id_key = $entity
      ->getEntityType()
      ->getKey('id');
    $entity_id = $entity->isNew() ? '0' : $entity->id();

    if ($item->target_id !== NULL) {
      $value_taken = (bool) \Drupal::entityQuery($entity_type_id)
        ->condition($id_key, $entity_id, '<>')
        ->condition($field_name . '.target_id', $item->target_id)
        ->condition($field_name . '.alias', $item->alias)
        ->range(0, 1)
        ->count()
        ->execute();

      if ($value_taken) {
        $target_entity = \Drupal::entityManager()->getStorage($target_type_id)->load($item->target_id);

        $this->context
          ->addViolation($constraint->notUnique, [
          '%alias' => $item->alias,
          '@entity_type' => $entity
            ->getEntityType()
            ->getLowercaseLabel(),
          '@field_name' => Unicode::strtolower($item
            ->getFieldDefinition()
            ->getLabel()),
          '%site' => $target_entity->label(),
        ]);
      }
    }

  }

}
