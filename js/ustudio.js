function trackUploadProgress(response){
    if (response.state !== "done") {
        if (response.state === "starting" ) {
            Drupal.behaviors.media_entity_ustudio.updateProgressTracker("uploading", 1, 0);
        } else {
            Drupal.behaviors.media_entity_ustudio.updateProgressTracker("uploading", response.size, response.received);
        }
        setTimeout(Drupal.behaviors.media_entity_ustudio.trackUploadProgress, 500);
    } else {
        Drupal.behaviors.media_entity_ustudio.trackInspectionProgress();
    }
}

/**
 * @file
 */
(function ($, Drupal) {
  "use strict";

    // Store Several selectors used frequently
    var $mediaSubmit = $("#edit-actions input#edit-submit");
    var $progress = $('#upload-progress');
    var $progressText = $('#upload-progress-text');
    var $progressBar = $('.upload-progress-bar');
    var $uploadButton = $('#upload-button span');
    var data = {};
    var percent = 0;
    var stateText = "Not Started...";
    var progress_url = '';
    var $upload_url;
    var $params;

    /**
     * Create the Video
     * @param studio
     */
    function createVideo(studio, destination) {
        var data = {
            title: $("#edit-name-0-value").val(),
            studio: studio,
            description: null,
            tags: null,
            category: "entertainment"
        };
        $.ajax({
            method: 'POST',
            url: "/api/ustudio/video/create",
            data: data
        }).done(function (response) {
            if (typeof response.video !== "undefined") {
                // Upload the file now
                uploadVideo(response.video.signed_upload_url);

                // Set the Upload URL Globally and Track the Progress
                Drupal.behaviors.media_entity_ustudio.setUploadUrl(response.video.signed_upload_url);
                Drupal.behaviors.media_entity_ustudio.trackUploadProgress();

                // Publish the video to uStudio
                publishVideo(studio, destination, response.video.uid);
            }
        });
    }

    /**
     * Upload a File to uStudio
     *
     * @param upload_url
     */
    function uploadVideo(upload_url) {
        var filesField = $("#edit-ustudio-upload-0-upload-upload-file");
        var input = filesField[0];
        if (input.files.length > 0 ) {
            var file = input.files[0];
            var formData = new FormData();
            formData.append('name', 'file');
            formData.append('contents', file);
            formData.append('filename', file.name);

            $.ajax({
                method: 'POST',
                url: upload_url,
                data: formData,
                contentType: false,
                processData: false,
                cache: false
            }).done(function (response) {
                console.log(response);
            });
        }

    }


    /**
     * Publish a video to uStudio
     *
     * @param studio
     * @param destination
     * @param video
     */
    function publishVideo(studio, destination, video) {
        data = {
            studio: studio,
            destination: destination,
            video: video
        };
        $.ajax({
            method: 'POST',
            url:  "/api/ustudio/video/publish",
            data: data
        }).done(function (response) {
            // Upload the embed code field with the player embed url
            var embed_player = response.video.player_embed_url;
            $("#edit-embed-code-0-value").val(embed_player);
        });
    }


    /**
     * Media Entity uStudio Drupal Behavior
     */
    Drupal.behaviors.media_entity_ustudio = {

        attach: function (context) {
            // Ensure this click handler is only added once
            $("#upload-button", context).once("media_entity_ustudio").on('click', function () {

                // Check that the title and file are filled out
                if (!$("#edit-name-0-value").val()) {

                    if (!$("input[name*='upload_file']").val()) {
                        console.log('File Missing');
                    }
                    if (!$("#edit-name-0-value").val()) {
                        console.log('Name Missing');
                    }
                } else {
                    $mediaSubmit.attr('disabled', true);
                    $progress.addClass("show");
                    Drupal.behaviors.media_entity_ustudio.updateProgressTracker("uploading", 1, 0);

                    var studio = $("#edit-ustudio-upload-0-studio-uid").val();
                    var destination = $("#edit-ustudio-upload-0-destination-destination-uid").val();

                    // Create the Video
                    createVideo(studio, destination);

                }

            });
        },
        setUploadUrl: function (url) {
            $upload_url = new URL(url);
            $params = $upload_url.searchParams;
        },

        /**
         * Track the Upload Progress
         */
        trackUploadProgress: function () {
            progress_url = $upload_url.origin + $upload_url.pathname + "/progress?X-Progress-ID=" + $params.get('X-Progress-ID') + "&callback=trackUploadProgress";
            $.ajax({
                url: progress_url,
                jsonp: "trackUploadProgress",
                dataType: "jsonp"
            });
        },

        /**
         * Track the Inspection Progress
         */
        trackInspectionProgress: function () {
            data = {
                signed_upload_url: $upload_url.href
            };
            $.ajax({
                method: 'POST',
                url: '/api/ustudio/video/upload_status',
                data: data
            }).done(function(response) {
                Drupal.behaviors.media_entity_ustudio.updateProgressTracker(response.progress.status.state, 1, 0);
                if (response.progress.status.state !== "finished") {
                    window.setTimeout(Drupal.behaviors.media_entity_ustudio.trackInspectionProgress, 500);
                }

            });

        },

        /**
         * Update the Progress Bar
         * @param state
         */
        updateProgressTracker: function(state, fileSize, fileReceived) {
            switch (state) {
                case 'uploading':
                    if (fileReceived !== 0) {
                        percent = Math.floor((fileReceived / fileSize) * 50);
                    } else {
                        percent = 0;
                    }
                    stateText = "Uploading..." + percent + "%";
                    break;
                case 'ingesting':
                    percent = 67;
                    stateText = "Ingesting...";
                    break;
                case 'inspecting':
                    percent = 85;
                    stateText = "Inspecting...";
                    break;
                case 'finished':
                    stateText = 'Done!';
                    percent = 100;
                    break;
            }
            // Update the Progress Bar
            $progressText.html(stateText);
            $progressBar.css('width', percent + '%');
        }
    }

})(jQuery, Drupal);
