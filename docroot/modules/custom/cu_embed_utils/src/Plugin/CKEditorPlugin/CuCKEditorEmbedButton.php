//<?php
//
//namespace Drupal\cu_embed_utils\Plugin\CKEditorPlugin;
//
//use Drupal\ckeditor\CKEditorPluginBase;
//use Drupal\editor\Entity\Editor;
//
///**
// * Defines the "cu_ckeditor_embed_button" plugin.
// *
// * NOTE: The plugin ID ('id' key) corresponds to the CKEditor plugin name.
// * It is the first argument of the CKEDITOR.plugins.add() function in the
// * plugin.js file.
// *
// * @CKEditorPlugin(
// *   id = "cu_ckeditor_embed_button",
// *   label = @Translation("Cu ckeditor embed button")
// * )
// */
//class CuCKEditorEmbedButton extends CKEditorPluginBase {
//
//
//  /**
//   * {@inheritdoc}
//   *
//   * NOTE: The keys of the returned array corresponds to the CKEditor button
//   * names. They are the first argument of the editor.ui.addButton() or
//   * editor.ui.addRichCombo() functions in the plugin.js file.
//   */
//  public function getButtons() {
//    // Make sure that the path to the image matches the file structure of
//    // the CKEditor plugin you are implementing.
//    return [
//      'Cu ckeditor embed button' => [
//        'label' => t('Cu ckeditor embed button'),
//        'image' => 'modules/custom/cu_embed_utils/js/plugins/cu_ckeditor_embed_button/images/icon.gif',
//      ],
//    ];
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public function getFile() {
//    // Make sure that the path to the plugin.js matches the file structure of
//    // the CKEditor plugin you are implementing.
//    return drupal_get_path('module', 'cu_embed_utils') . '/js/plugins/cu_ckeditor_embed_button/plugin.js';
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public function isInternal() {
//    return FALSE;
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public function getDependencies(Editor $editor) {
//    return [];
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public function getLibraries(Editor $editor) {
//    return [];
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public function getConfig(Editor $editor) {
//    return [];
//  }
//
//}