<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ReferenceSource;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\cu_hub_consumer\Client\Resource;
use Drupal\cu_hub_consumer\Client\ResourceException;
use Drupal\cu_hub_consumer\Hub\ReferenceSourceBase;
use Drupal\cu_hub_consumer\Entity\HubReferenceInterface;
use Drupal\cu_hub_consumer\Entity\HubReferenceTypeInterface;
use Drupal\cu_hub_consumer\Client\ResourceFetcherInterface;
use Drupal\cu_hub_consumer\Client\UrlResolverInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Undocumented class
 * 
 * @HubReferenceSource(
 *   id = "node",
 *   label = @Translation("Node source"),
 *   description = @Translation("Source for nodes."),
 *   allowed_field_types = {"string"},
 *   deriver = "Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ReferenceSource\NodeDeriver",
 *   metadata_attributes = {
 *     "created" = @Translation("Created timestmap"),
 *     "changed" = @Translation("Changed timestamp"),
 *     "revision_timestamp" = @Translation("Revision timestamp"),
 *     "langcode" = @Translation("Language code"),
 *     "metatag_normalized" = @Translation("Metadata (normalized)"),
 *   }
 * )
 */
class Node extends ReferenceSourceBase {

}
