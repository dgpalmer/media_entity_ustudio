/**
 * @file
 */
console.log('file loaded');
(function ($, Drupal) {
  "use strict";

  console.log('use strict');

  function trackUpload(progress) {
      console.log(progress);
  }

  Drupal.behaviors.media_entity_ustudio = {

      attach: function (context, settings) {
          console.log('attached');
          var embed_url = $("input#ustudio-embed-link").val();
          if (embed_url) {
            $("#edit-embed-code-0-value").val(embed_url);
          }
      },
      track_progress: function (context) {
          var asset_url = $(".js-form-item-ustudio-upload-0-upload-asset-url input").val();

          var progressURL = asset_url + "?X-Progress-ID=upload_progress&callback=trackUpload";


          console.log('track progress');
          var progress = false;
          if (progress) {
              console.log('Progress: ' + event.percent);
              $progressBar.css('width',(event.percent * 100) + '%');
          }
      }

}

})(jQuery, Drupal);
