<?php

namespace Drupal\cu_slate_forms\Plugin\Validation\Constraint;

use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Constraint validator for SlateFormId items to ensure the format is correct.
 */
class SlateFormIdFormatConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    /* @var $item \Drupal\cu_slate_forms\Plugin\Field\FieldType\SlateFormIdItemInterface */
    if (isset($item)) {
      $form_id = $item->getValue()['id'];

      if (!is_string($form_id)) {
        $this->context->addViolation($constraint->badType);
      }
      else {
        if (!preg_match('/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}/', $form_id)) {
          /*
          $this->context->addViolation($constraint->badFormat, [
            '@form_id' => $form_id,
          ]);
          return;
          */
        }

        $redirect_url = NULL;
        try {
          /** @var \Drupal\Core\Url $url */
          $redirect_url = $item->getRedirectUrl();
        }
        // If the URL is malformed this constraint cannot check further.
        catch (\InvalidArgumentException $e) {}
        
        if ($redirect_url) {
          // Disallow external URLs using untrusted protocols.
          if ($redirect_url->isExternal() && !in_array(parse_url($redirect_url->getUri(), PHP_URL_SCHEME), UrlHelper::getAllowedProtocols())) {
            $this->context->addViolation($constraint->badRedirect, ['@uri' => $item->redirect]);
            return;
          }
        }

      }
    }
  }

}
