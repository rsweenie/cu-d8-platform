/**
 * @file 
 * Provides sticky header behavior.
 */

(function($, Drupal, window) {
  
  'use strict';

  /**
   * Attaches the table drag behavior to tables.
   *
   * @type {Drupal~behavior}
   * 
   * @prop {Drupal~behaviorAttach} attach
   *  Makes the header sticky even when user is logged in and
   *  there is a toolbar.
   */
  Drupal.behaviors.cuStickyHeader = {
    elements: {
      topNavSection: '.header_top_section',
      alertsId: '#alerts',
      topSearchBar: 'header .header_top_section_search_wrapper .cu-query',
      tbAdmnTray: '#toolbar-item-administration-tray',
      toolBar: '#toolbar-bar',
      headerBgWrap: '.header_bg_wrapper',
      main: 'main',
      headerBanner: 'header[role=banner]',
    },
    attach: function (context, settings) {
      var stickyHeaderAction = this.initStickyHeader();

      // console.log(settings);
      // consider lazy loading here and/or using once
      if (typeof settings.toolbar !== 'undefined') {
        $(window, context).once('sticky', stickyHeaderAction.stickyHeader)
          .scroll(stickyHeaderAction.stickyHeader);
      } else {
        $(window, context).once('sticky', stickyHeaderAction.stickyHeader)
          .scroll(stickyHeaderAction.stickyHeader);
      }
    },
    getToolbarHeight: function () {
      return $(this.elements.tbAdmnTray).height() + $(this.elements.toolBar).height();
    },
    getTotalHeight: function () {
      return $(this.elements.topNavSection).height() + 
        $(this.elements.alertsId).height() + $(this.elements.topSearchBar).height();
    },
    loadTopOffsetVals: function () {
      return $(this.elements.headerBanner).offset().top;
    },
    loadHeightVals: function () {
      return {
        tbH: this.getToolbarHeight(),
        total: this.getTotalHeight(),
      }
    },
    initStickyHeader: function () {
      var heightValues = this.loadHeightVals();
      var sticky = this.loadTopOffsetVals();
      var parentObject = this; // Could bind 'this' when called and remove reference here. 
                      // But then $(window) instead of $(this) must be used in stickyHeader function

      return {
        stickyHeader: function () {
          if ($(this).scrollTop() >= heightValues.total) {
            $(parentObject.elements.headerBgWrap).addClass('fixed').css('margin-top', '0');
            //Need to add class with padding-top: 85px (i know, i know) to avoid "jumping content" when stickying the header
            $(parentObject.elements.main).addClass('sticky-top');

            //If the toolbar is present, add the toolbarheight to the top of sticky header. Still needs work. 
            if ($(parentObject.elements.toolBar).length) {  // Might need to do this early somewhere else to reduce cyclomatic complexity = Rick =
                $(parentObject.elements.headerBgWrap).css('top', heightValues.tbH);
            } else {
                $(parentObject.elements.headerBgWrap).css('top', '0');
            }
          } else {
              $(parentObject.elements.headerBgWrap).removeClass('fixed').css('margin-top', '40px');
              $(parentObject.elements.main).removeClass('sticky-top');
          }
        },
        altStickyHeader: function () {
          // var foo = $(parentObject.elements.headerBanner);
          // console.log(foo);
          if (window.pageYOffset > sticky) {
            $(parentObject.elements.headerBanner).addClass('sticky-top');
          } else {
            $(parentObject.elements.headerBanner).removeClass('sticky-top');
          }
        }
      }
    }
  }
})(jQuery, Drupal, window);