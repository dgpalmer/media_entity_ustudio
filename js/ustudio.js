/**
 * @file
 */
console.log('file loaded');
(function ($, Drupal) {
  "use strict";

  console.log('use strict');

  function trackUpload(upload_url) {



      console.log(upload_url);
      var data = {
          signed_upload_url: upload_url
      };
      $.ajax({
          method: 'POST',
          url: "/api/ustudio/video/upload_status",
          data: data
      }).done(function (progress) {

          while (progress.status.state !== "finished" ) {
              trackUpload(upload_url);
          }
      });
  }

  function updateProgressTracker(state, size) {

  }

  Drupal.behaviors.media_entity_ustudio = {

      attach: function (context) {

          $("#upload-button", context).once("media_entity_ustudio").on('click', function() {

              // get progress bar
              var $progress = $('#upload-progress');
              var $progressBar = $('#upload-progress-bar');
              var $progressText = $('#upload-progress-text');

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
                  $progress.addClass("show");
                  $progressText.html("<h6>Uploading</h6>");
                  var studio= $("#edit-ustudio-upload-0-studio-uid").val();
                  var destination = $("#edit-ustudio-upload-0-destination-destination-uid").val();

                  // Instantiate data needed for Creating the uStudio Video
                  var url = "/api/ustudio/video/create";
                  var data = {
                      title: $("#edit-name-0-value").val(),
                      studio: studio,
                      description: null,
                      tags: null,
                      category: "entertainment"
                  };

                  // Ajax request to create the video
                  $.ajax({
                      method: 'POST',
                      url: url,
                      data: data
                  }).done(function (createMsg) {
                      if (typeof createMsg.video !== "undefined")
                      {
                          // Instantiate data needed for Uploading the uStudio Video
                          url = "/api/ustudio/video/upload";
                          data = {
                              upload_url: createMsg.video.upload_url,
                              fid: $("input[name*='upload_file']").val()
                          }

                          console.log(data);
                          // Ajax request to upload the video
                          $.ajax({
                              method: 'POST',
                              url: url,
                              data: data
                          }).done(function (msg) {
                              console.log(msg);

                              // If the file was uploaded, let's check the progress
                              // Lets also publish the video
                              if (msg.upload === true) {

                                  // Publish Video
                                  url = "/api/ustudio/video/publish";
                                  data = {
                                      studio: studio,
                                      destination: destination,
                                      video: createMsg.video.uid
                                  }

                                  $.ajax({
                                      method: 'POST',
                                      url: url,
                                      data: data
                                  }).done(function (publishMsg) {

                                      // track the upload
                                      trackUpload(createMsg.video.signed_upload_url);
                                      console.log(publishMsg);
                                      var embed_player = publishMsg.video.player_embed_url;
                                      $("#edit-embed-code-0-value").val(embed_player);
                                      // If the file was uploaded, let's check the progress
                                  });


                              }
                          });
                      }
                  });
              }


          });
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
