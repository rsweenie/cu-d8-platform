/**
 * @file
 * Provides JavaScript changes to ckeditor buttons.
 *
 * This file provides a mechanism for ckeditor button image substitution
 */
(function ($, Drupal, CKEDITOR) {
  "use strict";
  /**
   * @see {@link: https://www.drupal.org/docs/8/api/javascript-api/javascript-api-overview|Drupal}
   */
  Drupal.behaviors.ckeditorButtonChange = {
    ckeditor_buttons: {
      media_browser: {
        image_url: '/modules/custom/cu_entity_embed/images/media_browser.gif',
        style: 'background-position:0 0px;background-size:16px;',
        ckeditor_editor_mode_btn: 'span.cke_button_icon.cke_button__media_browser_icon',
        ckeditor_config_mode_btn: 'li[data-drupal-ckeditor-button-name="media_browser"]',
      },
      // another_button: {
      //   image_url: '/modules/custom/cu_entity_embed/images/gray.gif',
      //   style: 'background-position:0 0px;background-size:16px;',
      //   ckeditor_editor_mode_btn: 'span.cke_button_icon.cke_button__table_icon',
      //   ckeditor_config_mode_btn: 'span.cke_button_icon.cke_button__table_icon',
      // },
    },
    attach: function (context, settings) {
      this.initChangeButtonImages();
      var dtd = CKEDITOR.dtd;

      CKEDITOR.on('instanceReady', function() {
        // Display the drupal-entity element inline.
        dtd.$inline['drupal-entity'] = 1;
        dtd['p']['drupal-entity'] = 1;
        dtd['span']['drupal-entity'] = 1;
        dtd['span']['article'] = 1;
      });
      
      // This will listen for change on the CKEditor's edit mode dropdown since it currently somehow 
      // changes the button back to default image.
      $('select.filter-list.editor', context).change(this.initChangeButtonImages.bind(this));
    },
    changeContentCkeditorIcon: function (o) {
      var button = $(o.ckeditor_editor_mode_btn), 
          icon_style = 'background:url("' + o.image_url + '") !important;' + o.style;

      button.attr('style', icon_style);
    },
    changeConfigCkeditorIcon: function (o) {
      var button = $(o.ckeditor_config_mode_btn), 
          icon = button.find('img');

      if (icon.length > 0) {
        icon.attr('src', o.image_url);
      } else {
        button.attr('style', 'background:url(' + o.image_url + ') !important');
      }
    },
    getIsTextEditLoadContext: function () {
      var el = '#ckeditor-button-configuration';
      return ($(el).length > 0) ? true : false;
    },
    initChangeButtonImages: function () {
      if (this.getIsTextEditLoadContext()) {
        this.processChangeButtonImages(this.changeConfigCkeditorIcon);
      } else {
        this.processContentEditContext();
      }
    },
    processContentEditContext: function () {
      var maxAttempts = 4;
      
      for (var i = 0; i < maxAttempts; i++) {
        var that = this;
        setTimeout(function () {
          that.processChangeButtonImages(that.changeContentCkeditorIcon);
        }, 500);
      }
    },
    processChangeButtonImages: function (f) {
      for (var key in this.ckeditor_buttons) {
        f(this.ckeditor_buttons[key]);
      }
    }
  };
})(jQuery, Drupal, CKEDITOR);
