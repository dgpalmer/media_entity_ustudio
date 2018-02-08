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

      attach: function (context) {

          $("#upload-button", context).once("media_entity_ustudio").on('click', function() {
              console.log('media entity ustudio attached');

              // Check that the title is filled out
              if (!$("input[name*='upload_file']").val() || !$("#edit-name-0-value").val()) {
                  if (!$("input[name*='upload_file']").val()) {
                     console.log('File Missing');
                  }
                  if (!$("#edit-name-0-value").val()) {
                      console.log('Name Missing');
                  }
                 console.log('missing fields');
              } else {
                  var destination = $("#edit-ustudio-upload-0-destination-destination-uid").val();
                  var url = "/api/ustudio/video/create";
                  var data = {
                      title: $("#edit-name-0-value").val(),
                      studio: $("#edit-ustudio-upload-0-studio-uid").val(),
                      description: null,
                      tags: null,
                      category: "entertainment"
                  };
                  console.log(data);

                  /*                  $.ajax({
                                        method: 'POST',
                                        url: url,
                                        data: data
                                    }).done(function(msg) {

                                        console.log( "Data Saved: " + msg );
                                        console.log(msg.valueOf());*/

                  console.log('no mediaName');
                  url = "/api/ustudio/video/upload";

                  data = {
                      //  upload_url: msg.video.video_url;
                      upload_url: "https://upload-app09.ustudio.com/api/v2/studios/OrSRKVWh4X1J/videos/UEgRdQHhasxa/asset",
                      fid: $("#form-item-ustudio-upload-0-upload-upload-file input").val()
                  }
                  $.ajax({
                      method: 'POST',
                      url: url,
                      data: data
                  }).done(function (msg) {

                      console.log("Data Saved: " + msg);
                      console.log(msg.valueOf());
                  });
                  /*                  });
                                }*/
              }


          });
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
