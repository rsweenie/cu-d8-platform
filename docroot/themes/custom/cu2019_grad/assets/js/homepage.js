'use strict';

(function ($, Drupal) {
  // We don't use Drupal behaviors here as we want it to run ASAP.
  $('.paragraph--type--hp-hero').once('cuHpHero').each(function() {
    var $hero = $(this);
    var $backgrounds = $('.field--name-field-hp-hero-bg > .field__item', $hero);

    if ($backgrounds.length > 1) {
      var min = 0;
      var max = $backgrounds.length-1;
      var prevHero = sessionStorage.getItem('homepagePrevHero');
      var random = 0;

      // Find a random hero we didn't use the last time. Limit find attempts for speed.
      for (var i=0; i < 10; i++) {
        random = Math.floor(Math.random() * (max - min + 1)) + min;
        if (random != prevHero) {
          sessionStorage.setItem('homepagePrevHero', random);
          break;
        }
      }

      $backgrounds.addClass('hidden').eq(random).removeClass('hidden');
    }
  });
})(jQuery, Drupal);

//# sourceMappingURL=homepage.js.map
