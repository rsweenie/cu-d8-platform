<?php

namespace Drupal\cu_hub_consumer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'hub_resource_media_image' formatter.
 *
 * @FieldFormatter(
 *   id = "hub_resource_media_image",
 *   label = @Translation("Hub media image"),
 *   field_types = {
 *     "hub_resource"
 *   }
 * )
 */
class HubResourceMediaImageFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ModuleHandlerInterface $module_handler, ImageFactory $image_factory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->moduleHandler = $module_handler;
    $this->imageFactory = $image_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('module_handler'),
      $container->get('image.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'style_name' => '',
      'link_type' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    $elements = [];

    // If the imagecache_external module is enabled we can allow image style selection.
    if ($this->moduleHandler->moduleExists('imagecache_external')) {
      $image_styles = image_style_options(FALSE);
      $elements['style_name'] = [
        '#title' => t('Image style'),
        '#type' => 'select',
        '#default_value' => $settings['style_name'],
        '#empty_option' => t('None (original image)'),
        '#options' => $image_styles,
      ];
    }

    $link_types = [
      'content' => t('Content'),
      'file' => t('File'),
    ];
    $elements['link_type'] = [
      '#title' => t('Link image to'),
      '#type' => 'select',
      '#default_value' => $settings['link_type'],
      '#empty_option' => t('Nothing'),
      '#options' => $link_types,
    ];

    return $elements;
  }
  
  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $settings = $this->getSettings();
    $image_styles = image_style_options(FALSE);

    // Unset possible 'No defined styles' option.
    unset($image_styles['']);

    if ($this->moduleHandler->moduleExists('imagecache_external')) {
      if (isset($image_styles[$settings['style_name']])) {
        $summary[] = t('Image style: @style', [
          '@style' => $image_styles[$settings['style_name']],
        ]);
      }
      else {
        $summary[] = t('Original image');
      }
    }

    $link_types = [
      'content' => t('Linked to content'),
      'file' => t('Linked to cached file'),
    ];

    // Display this setting only if image is linked.
    if (isset($link_types[$settings['link_type']])) {
      $summary[] = $link_types[$settings['link_type']];
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $link_url = NULL;
    $link_file = FALSE;
    $link_type = $this->getSetting('link_type');
    // Check if the formatter involves a link.
    if ($link_type == 'content') {
      $entity = $items->getEntity();
      if (!$entity->isNew()) {
        $link_url = $entity->toUrl();
      }
    }
    elseif ($link_type == 'file') {
      $link_file = TRUE;
    }

    foreach ($items as $delta => $item) {
      $resource_obj = $item->value;
      $resource_type = $resource_obj->getResourceTypeId();

      // Replace any characters that aren't allowed.
      $resource_type = preg_replace('/:/i', '__', $resource_type);
      $resource_type = preg_replace('/[^a-z0-9_-]/i', '_', $resource_type);

      // Sanity check.
      if (isset($resource_obj->image[0]->uri[0])) {
        $url = $resource_obj->image[0]->uri[0]->getUrl();

        $image_path = $url->toString();
        $image_alt = isset($resource_obj->image[0]->meta['alt']) ? $resource_obj->image[0]->meta['alt'] : '';
        $image_title = isset($resource_obj->image[0]->meta['title']) ? $resource_obj->image[0]->meta['title'] : '';

        if ($this->moduleHandler->moduleExists('imagecache_external')) {
          $image_path = imagecache_external_generate_path($url->toString());
        }

        // Skip rendering this item if there is no image_path.
        if (!$image_path) {
          continue;
        }

        if ($link_file) {
          $link_url = Url::fromUri(file_create_url($image_path));
        }

        $image = $this->imageFactory->get($image_path);
        $style_name = $this->getSetting('style_name');
  
        $image_build_base = [
          '#width' => $image->getWidth(),
          '#height' => $image->getHeight(),
          '#uri' => $image_path,
          '#alt' => $image_alt,
          '#title' => $image_title,
        ];
  
        if (empty($style_name)) {
          $image_build = [
            '#theme' => 'image',
          ] + $image_build_base;
        }
        else {
          $image_build = [
            '#theme' => 'image_style',
            '#style_name' => $style_name,
          ] + $image_build_base;
        }
  
        if ($link_url) {
          $rendered_image = render($image_build);
          $elements[$delta] = Link::fromTextAndUrl($rendered_image, $url)->toRenderable();
        }
        else {
          $elements[$delta] = $image_build;
        }
      }
    }

    return $elements;
  }

}
