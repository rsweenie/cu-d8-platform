/**
 * @file 
 * Provides sticky behavior on the header of the website.
 */
(function ($, Drupal, window) {

  'use strict';

  /**
   * Attaches the table drag behavior to tables.
   *
   * @type {Drupal~behavior}
   * 
   * @prop {Drupal~behaviorAttach} attach
   *   Make the Creighton header Banner sticky.
   */
  Drupal.behaviors.cuStickyHeaderAlt = {
    elements: {
      headerBanner: '.header_bg_wrapper',
    },
    attach: function (context, settings) {
      var stickyHeaderAction = this.initStickyHeader();
      console.log(settings);

      $(window, context).once('sticky')
        .scroll(stickyHeaderAction.makeHeaderSticky.bind(this));
    },
    initStickyHeader: function () {
      var $header = $(this.elements.headerBanner);
      var headerTop = $('.header_top_section').height();
      var sticky = $header.offset().top;

      return {
        makeHeaderSticky: function () {
          if (window.pageYOffset > (sticky-79)) {
            $('body').addClass('sticky-header');
          } else {
            $('body').removeClass('sticky-header');
          }
        },
      }
    }
  }
})(jQuery, Drupal, window);