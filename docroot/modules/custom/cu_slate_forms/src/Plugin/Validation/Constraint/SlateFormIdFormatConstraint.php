<?php

namespace Drupal\cu_slate_forms\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for SlateFormId items to ensure the format is correct.
 *
 * @Constraint(
 *   id = "SlateFormIdFormat",
 *   label = @Translation("SlateFormId format valid for SlateFormId type.", context = "Validation"),
 * )
 */
class SlateFormIdFormatConstraint extends Constraint {

  /**
   * Message for when the value isn't a string.
   *
   * @var string
   */
  public $badType = "The slate form ID value must be a string.";

  /**
   * Message for when the value isn't in the proper format.
   *
   * @var string
   */
  public $badFormat = "The slate form ID value '@form_id' is not a valid form ID.";

  /**
   * Message for when the redirect URL is malformed.
   *
   * @var string
   */
  public $badRedirect = "The redirect '@uri' is invalid.";

}
