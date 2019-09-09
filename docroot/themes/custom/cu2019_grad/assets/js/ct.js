'use strict';

(function ($, Drupal) {
  Drupal.behaviors.ct = {
    attach: function (context, settings) {
      $('.ct',context).each(function () {
        $('.btn-expand',this).on('click',function() {
          $(this).prev().toggleClass('expanded');
          var expanded = $(this).prev().hasClass('expanded');
          if(expanded) {
            $(this).text( $(this).data('toggletext').expanded );
          } else {
            $(this).text( $(this).data('toggletext').collapsed );
          }
        });
      });
    }
  }
})(jQuery, Drupal);

//# sourceMappingURL=ct.js.map
