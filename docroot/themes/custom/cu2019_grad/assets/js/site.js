'use strict';

(function ($, Drupal) {
  Drupal.behaviors.fsbg = {
    attach: function (context, settings) {
      // Hide image and add background image
      $('.card.img-behind',context).each(function() {
        var bg = $('img',this).attr('src');
        $(this).css({'background-image':'url('+bg+')'});
        $(this).addClass('js-background');
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
