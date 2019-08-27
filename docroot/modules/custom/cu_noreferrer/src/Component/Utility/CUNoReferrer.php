<?php

namespace Drupal\cu_noreferrer\Component\Utility;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\filter\FilterProcessResult;

/**
 * Provides a static filter to apply the noreferrer attribute.
 */
class CUNoReferrer {

  /**
   * static filter function applies the rel attributes.
   */
  public static function filter($text,$config){
    $noopener = $config->get('cu_noreferrer.settings')->get('noopener');
    $noreferrer = $config->get('cu_noreferrer.settings')->get('noreferrer');
    $result = new FilterProcessResult($text);
    $html_dom = SELF::load($text);
    $links = $html_dom->getElementsByTagName('a');

    foreach ($links as $link) {
      $types = [];
      $rel = $link->getAttribute('rel');
      if (!SELF::relValueExists($rel,'noopener') && SELF::isNoopener($noopener, $link->getAttribute('target'))) {
        $types[] = 'noopener';
      }
      if (!SELF::relValueExists($rel,'noreferrer') && SELF::isNoreferrer($noreferrer, $link->getAttribute('href'))) {
        $types[] = 'noreferrer';
      }
      if (!empty($types)) {
        $value = implode(' ',$types);
        $rel .= $rel ? " $value" : $value;
        $link->setAttribute('rel', $rel);
        $result->setProcessedText($html_dom->saveHTML());
      }
    }
    return $result;
  }

  /**
   * checks if noopener value applies to the anchor tag
   * mostly for readbility
   */
  private static function isNoopener($apply_noopener, $target){
    return ($apply_noopener && $target !== '');
  }
  /**
   * checks if noreferrer value applies to anchor element
   * mostly for readability
   */
  private static function isNoreferrer($apply_noreferrer, $href){
    return ($apply_noreferrer 
            && $href
            && UrlHelper::isExternal($href) 
            && !cu_noreferrer_is_whitelisted($href));
  }
  /**
   * checks if the rel value already exists
   */
  private static function relValueExists($rel,$value){
    return (strpos($rel, $value) !== false);
  }


  /**
   * loads html without replacing html/meta elements attrubutes
   * because client side needs them
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