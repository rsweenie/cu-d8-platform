/**
 * @file 
 * IIFE (Immediately Invoked Function Execution or Self Invoking Function) 
 * https://developer.mozilla.org/en-US/docs/Glossary/IIFE?source=post_page---------------------------
 * 
 */
var creightonEdu = window.creightonEdu || {};
(function(ce_lib) {
  ce_lib(window.jQuery, window, document); // dependency injection
}(function ($, window, document) {

  'use strict';
  
  // same as document ready
  $(function() {
    // Use Drupal behavior attach method instead of $(document).ready();
    // https://www.drupal.org/docs/8/api/javascript-api/javascript-api-overview
  });

  // Namespace/Singleton object - these methods are available immediately on page load
  creightonEdu = (function() {
    // Private variables
    var $main_mega_nav = null;
    var _expandedAttrs = {'aria-expanded': true};
    var _collapsedAttrs = {'aria-expanded': false};
    var _keyCodes = {
      ENTER: 13,
      ESCAPE: 27,
      DOWN: 40,
      UP: 38
    };
    
    //  public methods
    return {
      deviceProfile: {
        isiOS: navigator.userAgent.match(/(iPod|iPhone|iPad)/),
        isDroid: navigator.userAgent.match(/Android/),
        isSamsung: navigator.userAgent.match(/SAMSUNG|SGH-[I|N|T]|GT-[I|P|N]|SM-[N|P|T|Z|G]|SHV-E|SCH-[I|J|R|S]|SPH-L/i),
        isTouch: typeof window.hasOwnProperty === "function" && !!window.hasOwnProperty("ontouchstart"),
        width: window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth
      }, css_breakpoints: {  // reference sass/partials/utility/variables/_grid.scss
        'mobile_end': 767,
        'tablet_start': 768,
        'tablet_end': 1050,
        'desk_start': 1051
      },
    };
  })();
}));

// creightonEdu.fullMenu = function(wrapper) {
//   // private variables
//   var $main_mega_nav = null,
//       $ = null,
//       _expandedAttrs = {'aria-expanded': true},
//       _collapsedAttrs = {'aria-expanded': false},
//       _keyCodes = {
//         ENTER: 13,
//         ESCAPE: 27,
//         DOWN: 40,
//         UP: 38
//       };
// 
//   // priviledged methods
//   this.initialize = function() {
//     $main_mega_nav.on('click touchend keydown', function (event) {
//       if (event.type === 'keydown' && event.keyCode === _keyCodes.ESCAPE) {
//         if ($('.sub-nav').hasClass('open')) {
//           $('.sub-nav').removeClass('open');
//           $(this).toggleClass('open');
//           return false;
//         }
//       }
// 
//       if (event.type === 'keydown' && event.keyCode === _keyCodes.ENTER) {
//         $('.sub-nav').toggleClass('open');
//         $(this).toggleClass('open');
//         return false;
//       }
// 
//       if (event.type === 'click' || event.type === 'touchend') {
//         $('.sub-nav').toggleClass('open');
//         $(this).toggleClass('open');
//         return false;
//       }
//     });
// 
//     $('.sub-nav a').on('keydown', $main_mega_nav.selector, $main_mega_nav.selector, function (event) {
//       if (event.type === 'keydown' && event.keyCode === _keyCodes.ESCAPE) {
//         if ($('.sub-nav').hasClass('open')) {
//           $('.sub-nav').removeClass('open');
//           $main_mega_nav.removeClass('open');
//           return false;
//         }
//       }
//     });
//   };
// 
//   this.open = function () {
//     $('.sub-nav').removeClass('open');
//     $(this).toggleClass('open');
//   };
// 
//   this.close = function () {
// 
//   };
// 
//   // Set it up
//   if (typeof wrapper !== 'undefined' || typeof wrapper === 'string') {
//     $ = jQuery;
//     $main_mega_nav = $(wrapper);
//     this.initialize();
//   } else {
//     console.log('Missing parameter: jQuery selector');
//   }
// };