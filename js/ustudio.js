/**
 * @file
 */
console.log('file loaded');
(function ($, Drupal) {
  "use strict";

  console.log('use strict');

  function trackProgress(tempData) {
      console.log('trackProgress called');
      console.log(tempData);
  }

  Drupal.behaviors.media_entity_ustudio = {

      attach: function (context) {
          function _init() {
              console.log('hello');
          }
      },
      track_progress: function (context) {
          if (progress) {
              console.log('Progress: ' + event.percent);
              $progressBar.css('width',(event.percent * 100) + '%');
          }
      }

}

})(jQuery, Drupal);
