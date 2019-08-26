<?php

namespace Drupal\cu_noreferrer\Plugin\Filter;

use Drupal\filter\Plugin\FilterBase;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\filter\FilterProcessResult;

/**
 * Provides a filter to apply the noreferrer attribute.
 *
 * @Filter(
 *   id = "noreferrer",
 *   title = @Translation("Add link types to links"),
 *   description = @Translation("Adds <code>rel=&quot;noopener&quot;</code> to links with a target and/or <code>rel=&quot;noreferrer&quot;</code> to non-whitelisted links."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   weight = 10
 * )
 */
class CUNoReferrerFilter extends FilterBase {

  /**
   * {@inheritdoc}
   * process function
   */
  public function process($text, $langcode) {
    SELF::filter($text,\Drupal::config('cu_noreferrer.settings'));
  }
  /**
   * static filter functino applies the rel attributes.
   */
  public static function filter($text,$config){
    $modified = FALSE;
    $result = new FilterProcessResult($text);
    $html_dom = SELF::load($text);

    $links = $html_dom->getElementsByTagName('a');
    $noopener = $config->get('noopener');
    $noreferrer = $config->get('noreferrer');
    foreach ($links as $link) {
      $types = [];
      if ($noopener && $link->getAttribute('target') !== '') {
        $types[] = 'noopener';
      }
      if ($noreferrer && ($href = $link->getAttribute('href')) && UrlHelper::isExternal($href) && !cu_noreferrer_is_whitelisted($href)) {
        $types[] = 'noreferrer';
      }
      if ($types) {
        $rel = $link->getAttribute('rel');
        foreach ($types as $type) {
          if(!strpos($rel, $type) !== false)
            $rel .= $rel ? " $type" : $type;
        }
        $link->setAttribute('rel', $rel);
        $modified = TRUE;
      }
    }
    if ($modified) {
      $result->setProcessedText($html_dom->saveHTML());
    }
    return $result;
  }


  /**
   * loads html without replacing html/meta elements attrubutes
   */
  private static function load($html){
    $dom = new \DomDocument();
    $dom->preserveWhiteSpace = FALSE;
    $document = <<<EOD
!html
EOD;

    $document = strtr($document, ["\n" => '', '!html' => $html]);

    @$dom->loadHTML($document);
    return $dom;
  }
}