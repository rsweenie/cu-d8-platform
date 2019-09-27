'use strict';

(function ($, Drupal) {
  Drupal.behaviors.fsbg = {
    attach: function (context, settings) {
      // Hide image and add background image
      $('.card.img-behind, header.interior_page > .field, .hp_hero_bg',context).each(function() {
        var bg = $('img',this).attr('src');
        $(this).css({'background-image':'url('+bg+')'});
        $(this).addClass('js-background');
      });

      // Menu/Hero hover interaction
      $('nav.mega .menu-level-0 .menu-item').each(function() {
        if( $('.menu-type-tier1', this).length ) {
          $(this).on('mouseover',function() {
            $('body').addClass('menuhover');
          })
          .on('mouseout',function() {
            $('body').removeClass('menuhover');
          });
        }
      });
      // Open all external links in new tab
      $("a", context).each(function() {
        var a = new RegExp('/' + window.location.host + '/');
         if(!a.test(this.href)) {
             $(this).click(function(event) {
                 event.preventDefault();
                 event.stopPropagation();
                 window.open(this.href, '_blank');
             });
         }
      });

      // $('figure.img_vid.video', context).each(function() {
      //   $(this).on('click', function() {
      //     var src = $('iframe', this).attr('src') + '&autoplay=1';
      //     $('iframe', this).attr('src', src);
      //     $(this).addClass('playing');
      //   })
      // });
    }
};
})(jQuery, Drupal);

//# sourceMappingURL=site.js.map
