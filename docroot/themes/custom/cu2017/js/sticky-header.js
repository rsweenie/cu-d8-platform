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
      topNavSection: '.header_top_section',
      headerBanner: 'header',
      headerMain: 'header .header_bg_wrapper',
      topSearchBar: 'header .header_top_section_search_wrapper .cu-query',    
      alertsId: '#alerts',
      tbAdmnTray: '#toolbar-item-administration-tray',
      toolBar: '#toolbar-bar',
      transactionMenu: '.transaction_menu_wrapper',
      content: 'main'
    },
    state: {
      headerTop: 0
    },


    attach: function (context, settings) {
      var stickyHeaderAction = this.initStickyHeader();
      
      var toolbarPresent = (settings.toolbar);

      if (typeof settings.toolbar != 'undefined') {
        $('body').addClass('toolbar-adjust'); 
      }
      
      $(window, context).once('sticky')
        .scroll(stickyHeaderAction.makeHeaderSticky.bind(this));

        $(window, context).once('sticky-resize')
        .resize(stickyHeaderAction.makeHeaderSticky.bind(this));

        $(window, context).once('sticky-refresh')
        .ready(stickyHeaderAction.makeHeaderSticky.bind(this));
    },

    getDesktopHeight: function () {
      return $(this.elements.topNavSection).height() + 
        $(this.elements.alertsId).height();
    },

    getMobileHeight: function () {
      return $(this.elements.alertsId).height() + $(this.elements.topSearchBar).height() + $(this.elements.transactionMenu).height();
    },

    initStickyHeader: function () {
      var tablet = creightonEdu.css_breakpoints.tablet_start;
      var headerTop = null;
      
      return {
        makeHeaderSticky: function () {
          var toolBar = $(this.elements.toolBar);
          var desktopHeight = this.getDesktopHeight();
          var mobileHeight = this.getMobileHeight();

          if (toolBar == null) {
            toolBar = 0;
       }        
          if ($(window).width() < tablet) {     
            headerTop = mobileHeight;       
           }       
           else {
            headerTop = desktopHeight;
           }
          if (window.pageYOffset > (headerTop)) {
            $('body').addClass('sticky-header');            
          } else {
            $('body').removeClass('sticky-header');
          }
        
        },
      }
    }
  }

})(jQuery, Drupal, window);
