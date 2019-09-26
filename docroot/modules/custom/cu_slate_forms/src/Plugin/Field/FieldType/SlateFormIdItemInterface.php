<?php

namespace Drupal\cu_slate_forms\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Url;

/**
 * Defines an interface for the slate form ID field item.
 */
interface SlateFormIdItemInterface extends FieldItemInterface {

  /**
   * Gets the redirect URL object.
   *
   * @return \Drupal\Core\Url
   *   Returns a Url object.
   */
  public function getRedirectUrl();

}
