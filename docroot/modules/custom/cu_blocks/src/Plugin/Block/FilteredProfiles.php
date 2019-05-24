<?php

namespace Drupal\cu_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * TODO: class docs.
 *
 * @Block(
 *   id = "cu_blocks_filtered_profiles",
 *   admin_label = @Translation("Filtered Profiles"),
 *   category = @Translation("CU Blocks"),
 * )
 */
class FilteredProfiles extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Returns the user-facing block label.
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    // Indicates whether the block should be shown.
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Builds and returns the renderable array for this block plugin.

    // TODO: generate a views block based on hard coded view name (for now)
    // and configured taxonomy tid.
    $block = [
      '#markup' => $this->t('This will be a views block...'),
    ];

    return $block;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfigurationValue($key, $value) {
    // Sets a particular value in the block settings.
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Returns the configuration form elements specific to this block plugin.

    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    // TODO: either an autocomplete or a dropdown for this taxonomy
    $form['filtered_profiles_tid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Profile Type'),
      '#description' => $this->t('Select the profile type you would like featured in this block.'),
      '#default_value' => isset($config['filtered_profiles_tid']) ? $config['filtered_profiles_tid'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    // Adds block type-specific validation for the block form.
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Adds block type-specific submission handling for the block form.
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineNameSuggestion() {
    // Suggests a machine name to identify an instance of this block.
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Form validation handler.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Form submission handler.
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // The cache contexts associated with this object.
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // The cache tags associated with this object.
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // The maximum age for which this object may be cached.
  }

}
