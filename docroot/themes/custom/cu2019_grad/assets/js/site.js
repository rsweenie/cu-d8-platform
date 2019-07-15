'use strict';

(function ($, Drupal) {
  Drupal.behaviors.fsbg = {
    attach: function (context, settings) {
      $('.card.img-behind',context).each(function() {
        var bg = $('img',this).attr('src');
        $(this).css({'background-image':'url('+bg+')'});
        $(this).addClass('js-background');
      });

      $('figure.img_vid.video', context).each(function() {
        $(this).on('click', function() {
          var src = $('iframe', this).attr('src') + '&autoplay=1';
          $('iframe', this).attr('src', src);
          $(this).addClass('playing');
        })
      });
    }
};
})(jQuery, Drupal);
