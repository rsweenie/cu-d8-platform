<?php

namespace Drupal\cu_hub_consumer\Hub;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Plugin\ObjectWithPluginCollectionInterface;
use Drupal\cu_hub_consumer\Entity\HubReferenceInterface;
use Drupal\cu_hub_consumer\Entity\HubReferenceTypeInterface;

/**
 * Defines the interface for hub reference source plugins.
 *
 * Hub reference sources provide the critical link between hub_reference items in Drupal and the
 * actual hub_reference itself, which exists independently of Drupal. Each
 * hub reference source works with a certain kind of hub reference. For example, programs and
 * degrees can both be catalogued in a similar way as hub_reference items, but
 * they need very different handling to actually display them.
 *
 * Each hub reference type needs exactly one source. A single source can be used on many
 * hub reference types.
 *
 * Examples of possible sources are:
 * - Program: handles academic programs,
 * - Degree: handles academic degrees,
 * - Faculty: handles faculty profiles,
 * - ...
 *
 * Their responsibilities are:
 * - Defining how hub_reference is represented (stored). Hub reference sources are not
 *   responsible for actually storing the hub_reference. They only define how it is
 *   represented on a hub_reference item (usually using some kind of a field).
 * - Validating a hub_reference item before it is saved. The entity constraint system
 *   will be used to ensure the valid structure of the hub_reference item.
 * - Providing a default name for a hub_reference item. This will save users from
 *   manually entering the name when it can be reliably set automatically.
 * - Providing metadata specific to the given hub_reference type. For example, hub_reference
 *   sources get information available through the hub API and make it available to Drupal.
 * - Mapping metadata to the hub_reference item. Metadata that a hub_reference source exposes
 *   can automatically be mapped to the fields on the hub_reference item. Hub reference
 *   sources will be able to define how this is done.
 *
 * @see \Drupal\cu_hub_consumer\Annotation\HubReferenceSource
 * @see \Drupal\cu_hub_consumer\Hub\ReferenceSourceBase
 * @see \Drupal\cu_hub_consumer\Hub\ReferenceSourceManager
 * @see \Drupal\cu_hub_consumer\Entity\HubReferenceTypeInterface
 * @see plugin_api
 */
interface ReferenceSourceInterface extends PluginInspectionInterface, ConfigurableInterface, DependentPluginInterface, ConfigurablePluginInterface, PluginFormInterface, ObjectWithPluginCollectionInterface {

  /**
   * Default empty value for metadata fields.
   */
  const METADATA_FIELD_EMPTY = '_none';

  /**
   * Gets a list of metadata attributes provided by this plugin.
   *
   * Most hub_reference sources have associated metadata, describing attributes
   * such as:
   * - title
   * - alias
   * - site
   * - ...
   *
   * This method should list all metadata attributes that a hub_reference source MAY
   * offer. In other words: it is possible that a particular hub_reference item does
   * not contain a certain attribute.
   *
   * (The term 'attributes' was chosen because it cannot be confused
   * with 'fields' and 'properties', both of which are concepts in Drupal's
   * Entity Field API.)
   *
   * @return array
   *   Associative array with:
   *   - keys: metadata attribute names
   *   - values: human-readable labels for those attribute names
   */
  public function getMetadataAttributes();

  /**
   * Gets the value for a metadata attribute for a given hub_reference item.
   *
   * @param \Drupal\cu_hub_consumer\Entity\HubReferenceInterface $hub_reference
   *   A hub_reference item.
   * @param string $attribute_name
   *   Name of the attribute to fetch.
   *
   * @return mixed|null
   *   Metadata attribute value or NULL if unavailable.
   */
  public function getMetadata(HubReferenceInterface $hub_reference, $attribute_name);

  /**
   * Get the source field definition for a hub_reference type.
   *
   * @param \Drupal\cu_hub_consumer\Entity\HubReferenceTypeInterface $type
   *   A hub_reference type.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface|null
   *   The source field definition, or NULL if it doesn't exist or has not been
   *   configured yet.
   */
  public function getSourceFieldDefinition(HubReferenceTypeInterface $type);

  /**
   * Creates the source field definition for a type.
   *
   * @param \Drupal\cu_hub_consumer\Entity\HubReferenceTypeInterface $type
   *   The hub_reference type.
   *
   * @return \Drupal\field\FieldConfigInterface
   *   The unsaved field definition. The field storage definition, if new,
   *   should also be unsaved.
   */
  public function createSourceField(HubReferenceTypeInterface $type);

  /**
   * Prepares the hub_reference type fields for this source in the view display.
   *
   * This method should normally call
   * \Drupal\Core\Entity\Display\EntityDisplayInterface::setComponent() or
   * \Drupal\Core\Entity\Display\EntityDisplayInterface::removeComponent() to
   * configure the hub_reference type fields in the view display.
   *
   * @param \Drupal\cu_hub_consumer\Entity\HubReferenceTypeInterface $type
   *   The hub_reference type which is using this source.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The display which should be prepared.
   *
   * @see \Drupal\Core\Entity\Display\EntityDisplayInterface::setComponent()
   * @see \Drupal\Core\Entity\Display\EntityDisplayInterface::removeComponent()
   */
  public function prepareViewDisplay(HubReferenceTypeInterface $type, EntityViewDisplayInterface $display);

  /**
   * Prepares the hub_reference type fields for this source in the form display.
   *
   * This method should normally call
   * \Drupal\Core\Entity\Display\EntityDisplayInterface::setComponent() or
   * \Drupal\Core\Entity\Display\EntityDisplayInterface::removeComponent() to
   * configure the hub_reference type fields in the form display.
   *
   * @param \Drupal\cu_hub_consumer\Entity\HubReferenceTypeInterface $type
   *   The hub_reference type which is using this source.
   * @param \Drupal\Core\Entity\Display\EntityFormDisplayInterface $display
   *   The display which should be prepared.
   *
   * @see \Drupal\Core\Entity\Display\EntityDisplayInterface::setComponent()
   * @see \Drupal\Core\Entity\Display\EntityDisplayInterface::removeComponent()
   */
  public function prepareFormDisplay(HubReferenceTypeInterface $type, EntityFormDisplayInterface $display);

  /**
   * Get the primary value stored in the source field.
   *
   * @param \Drupal\cu_hub_consumer\Entity\HubReferenceInterface $hub_reference
   *   A hub_reference item.
   *
   * @return mixed
   *   The source value.
   *
   * @throws \RuntimeException
   *   If the source field for the hub_reference source is not defined.
   */
  public function getSourceFieldValue(HubReferenceInterface $hub_reference);

  /**
   * Returns the matching ResourceType object.
   *
   * @return \Drupal\cu_hub_consumer\Hub\ResourceTypeInterface
   */
  public function getResourceType();

  /**
   * REturns a list of resource fields that should be exposed as computed fields.
   *
   * @return void
   */
  public function getExposedFields();

}
