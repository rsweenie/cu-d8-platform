<?php

namespace Drupal\cu_hub_consumer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Xss;

/**
 * Plugin implementation of the 'hub_text_processed' formatter.
 *
 * @FieldFormatter(
 *   id = "hub_text_processed",
 *   label = @Translation("Hub processed text"),
 *   field_types = {
 *     "hub_text_long"
 *   }
 * )
 */
class HubProcessedTextFormatter extends FormatterBase {

  protected $allowedTags = array(
    'a',
    'abbr',
    'acronym',
    'address',
    'article',
    'aside',
    'b',
    'bdi',
    'bdo',
    'big',
    'blockquote',
    'br',
    'caption',
    'cite',
    'code',
    'col',
    'colgroup',
    'command',
    'dd',
    'del',
    'details',
    'dfn',
    'div',
    'dl',
    'dt',
    'em',
    'figcaption',
    'figure',
    'footer',
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'header',
    'hgroup',
    'hr',
    'i',
    'iframe',
    'img',
    'ins',
    'kbd',
    'li',
    'mark',
    'menu',
    'meter',
    'nav',
    'ol',
    'output',
    'p',
    'pre',
    'progress',
    'q',
    'rp',
    'rt',
    'ruby',
    's',
    'samp',
    'section',
    'small',
    'span',
    'strong',
    'sub',
    'summary',
    'sup',
    'table',
    'tbody',
    'td',
    'tfoot',
    'th',
    'thead',
    'time',
    'tr',
    'tt',
    'u',
    'ul',
    'var',
    'wbr',
  );

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      // We output just the preprocessed version fo the text.
      $elements[$delta] = [
        '#type' => 'inline_template',
        '#template' => '{{ value|raw }}',
        '#context' => [
          // We use a very permissive XSS filter as we assume the source is pretty safe.
          'value' => XSS::filter($item->hub_processed, $this->allowedTags),
        ],
      ];
    }

    return $elements;
  }

}
