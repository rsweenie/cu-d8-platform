<?php

namespace Drupal\cu_footer_badge\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Footer Badge Block.
 *
 * @Block(
 *   id = "footerbadgeblock",
 *   admin_label = @Translation("Footer Badge block"),
 *   category = @Translation("CU Footer Badge"),
 * )
 */
class FooterBadgeBlock extends BlockBase {

  const ALT_TEXT = "Best Colleges by US News and World Report Regional Universities Midwest 2018";

  /**
   * {@inheritdoc}
   */
  public function build() {
    
    return [
      '#markup' => '<img src="/modules/custom/cu_footer_badge/images/badge.png" alt="' . $this->t(self::ALT_TEXT) . '" />',
    ];
  }

}
