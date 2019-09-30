<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Drupal\cu_hub_consumer\Hub\ResourceFieldItemBase;
use Drupal\Core\Url;

/**
 * Generic link resource field.
 * 
 * @HubResourceFieldType(
 *   id = "link",
 *   label = @Translation("Link"),
 *   description = @Translation("Link."),
 * )
 */
class LinkFieldItem extends ArrayFieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function mainPropertyName() {
    return 'uri';
  }

  /**
   * Gets the URL object.
   *
   * @return \Drupal\Core\Url
   *   Returns a Url object.
   */
  public function getUrl() {
    return Url::fromUri($this->uri, (array) $this->options);
  }

  /**
   * {@inheritdoc}
   */
  public function view() {
    $elements = [];

    if (!$this->isEmpty()) {
      // By default use the full URL as the link text.
      $url = $this->buildUrl();
      $link_title = $url->toString();

      if (!empty($this->title)) {
        // Unsanitized token replacement here because the entire link title
        // gets auto-escaped during link generation in
        // \Drupal\Core\Utility\LinkGenerator::generate().
        /*
        $link_title = \Drupal::token()->replace(
          $this->title,
          [
            $entity->getEntityTypeId() => $entity,
          ],
          [
            'clear' => TRUE,
          ]);
        */
        $link_title = $this->title;
      }

      // We output just the preprocessed version fo the text.
      $elements = [
        '#type' => 'link',
        '#title' => $link_title,
        '#url' => $url,
      ];
    }

    return $elements;
  }

  /**
   * Builds the \Drupal\Core\Url object for a link field item.
   *
   * @param \Drupal\link\LinkItemInterface $item
   *   The link field item being rendered.
   *
   * @return \Drupal\Core\Url
   *   A Url object.
   */
  protected function buildUrl() {
    $url = $this->getUrl() ?: Url::fromRoute('<none>');

    $settings = [];

    $options = isset($this->options) ? $this->options : [];
    $options += $url->getOptions();

    // Add optional 'rel' attribute to link options.
    if (!empty($settings['rel'])) {
      $options['attributes']['rel'] = $settings['rel'];
    }

    // Add optional 'target' attribute to link options.
    if (!empty($settings['target'])) {
      $options['attributes']['target'] = $settings['target'];
    }

    $url->setOptions($options);

    return $url;
  }

}
