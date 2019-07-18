'use strict';

(function ($, Drupal) {
  Drupal.behaviors.fsbg = {
    attach: function (context, settings) {
      $('.card.img-behind',context).each(function() {
        var bg = $('img',this).attr('src');
        $(this).css({'background-image':'url('+bg+')'});
        $(this).addClass('js-background');
      });
    }
};
})(jQuery, Drupal);
