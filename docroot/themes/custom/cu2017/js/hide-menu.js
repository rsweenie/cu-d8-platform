(function($) {
  $(document).ready(function() {
    var timeOut = null,
      $megaToggle = $('#mega_menu_toggle')[0],
      $megaMenu = $('#mega_menu'),
      $headerNav = $('#header_nav'),
      $headerMenu = $('header .header_top_section_menu'),
      mobileTabletSize = 768;
    // meganm.creighton.acsitefactory.com or local.creighton.com
    if ((window.location.href.indexOf('local.creighton.com') > -1) || (window.location.href.indexOf('meganm.test-creighton.acsitefactory.com') > -1)) {
      $headerMenu.addClass('hidden-menu');
      $megaMenu.addClass('hidden-menu');
      $(window).on('resize', function() {
        clearTimeout(timeOut);
        // set resize functionality to fire after 100 milliseconds - Abe
        timeOut = setTimeout(function() {
          // if width of window > mobileTabletSize, remove menu open class, close menu button - Abe
          if ($(window).width() > mobileTabletSize) {
            $megaMenu.css('display', 'none');
            ($headerNav, $megaToggle).classList.remove('open');
          }
        }, 100);
      });
    } else {
      ($headerMenu).removeClass('hidden-menu').css('display', 'block');
      ($megaMenu).removeClass('hidden-menu');
    }
  });
})(jQuery);
