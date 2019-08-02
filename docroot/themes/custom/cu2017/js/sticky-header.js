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
      content: 'main'
    },
    attach: function (context, settings) {
      var stickyHeaderAction = this.initStickyHeader();
      
      //console.log(settings);
      //var toolbarPresent = (settings);
      //console.log(toolbarPresent);

      $(window, context).once('sticky')
        .scroll(stickyHeaderAction.makeHeaderSticky.bind(this));
    },
    getTabletStart: function(){
    return (creightonEdu.css_breakpoints.tablet_start);
    },
    getToolbarHeight: function () {
      return $(this.elements.tbAdmnTray).height() + $(this.elements.toolBar).height();
    },
    getSearchBarHeight: function () {
      return $(this.elements.topSearchBar).height();
    },
    getTotalHeight: function () {
      return $(this.elements.topNavSection).height() + 
        $(this.elements.alertsId).height() + $(this.elements.topSearchBar).height();
    },

    initStickyHeader: function () {
      //var $header = $(this.elements.headerMain);
      var toolbarHeight= $('#toolbar-bar').height();
      var searchBar = $('header .header_top_section_search_wrapper .cu-query').height();
      var headerTop = $('.header_top_section').height();
      //var sticky = $header.offset().top;
      //var $content = $(this.elements.content);
      var tablet = 768;
      //var tablet = this.getTabletStart();
      //var mobile = parseInt(headerTop + searchBar);
     //console.log(tablet)


      
      return {
        makeHeaderSticky: function () {
          if ($(window).width() < tablet) {     
            headerTop = $('#toolbar-bar').height() + $('header .header_top_section_search_wrapper .cu-query').height();
             
            console.log(headerTop);
           }
           else {
            headerTop = headerTop;
           }
          if (window.pageYOffset > (headerTop)) {
            $('body').addClass('sticky-header');        
           // $('body.front.toolbar-vertical').addClass('responsive-toolbar');
          } else {
            $('body').removeClass('sticky-header');
            //$('body.front.toolbar-vertical').removeClass('responsive-toolbar');
          }
        
        },
      }
    }
  }

})(jQuery, Drupal, window);