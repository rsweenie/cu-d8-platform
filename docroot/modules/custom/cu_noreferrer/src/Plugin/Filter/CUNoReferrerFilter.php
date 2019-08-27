<?php

namespace Drupal\cu_noreferrer\Plugin\Filter;

use Drupal\filter\Plugin\FilterBase;
use Drupal\Component\Utility\Html;
use Drupal\cu_noreferrer\Component\Utility\CUNoReferrer;
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
    CUNoReferrer::filter($text,\Drupal::config('cu_noreferrer.settings'));
  }
  
}