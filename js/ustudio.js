/**
 * @file
 */
(function ($, Drupal) {
  "use strict";

    // Store Several selectors used frequently
    var $mediaSubmit = $("#edit-actions input#edit-submit");
    var $progress = $('#upload-progress');
    var $progressText = $('#upload-progress-text');
    var $uploadButton = $('#upload-button span');
    var data = {};
    var percent = 0;
    var stateText = "Not Started...";

    /**
     * Create the Video
     * @param studio
     */
    function createVideo(studio) {
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
                return response.video;
            } else {
                return false;
            }
        });
    }

    /**
     * Upload a File to uStudio
     *
     * @param upload_url
     */
    function uploadVideo(upload_url) {
        $.ajax({
            method: 'POST',
            url: upload_url,
            data: data
        }).done(function (response) {
            // Code Here
        });
    }

    /**
     * Track the Upload Progress
     * @param upload_url
     */
    function trackUploadProgress(upload_url) {
        console.log(upload_url);
        $.ajax({
            method: 'get',
            url: upload_url
        }).done(function (response) {
            console.log("state:");
            var state = response.progress.status.state;
            console.log(state);
            updateProgressTracker(response.progress.status.state);
            return response.progress.status.state;
        });
    }

    /**
     * Update the Progress Bar
     * @param state
     */
    function updateProgressTracker(state) {
        switch (state) {
            case 'uploading':
                stateText = "Uploading...";
                percent = 25;
                break;
            case 'ingesting':
                stateText = "Ingesting...";
                percent = 50;
                break;
            case 'inspecting':
                stateText = "Inspecting...";
                percent = 75;
                break;
            case 'finished':
                stateText = 'Finished';
                percent = 100
                break;
        }
        // Update the Progress Bar
        $progressText.html(stateText);
        $progressBar.css('width', percent + '%');
    }

    /**
     * Upload a File to uStudio
     *
     * @param studio
     * @param destination
     * @param video
     */
    function publishVideo(studio, destination, video) {
        data = {
            studio: studio,
            destination: destination,
            video: video.uid
        }
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

                // Check that the title is filled out
                if (!$("input[name*='upload_file']").val() || !$("#edit-name-0-value").val()) {

                    if (!$("input[name*='upload_file']").val()) {
                        console.log('File Missing');
                    }
                    if (!$("#edit-name-0-value").val()) {
                        console.log('Name Missing');
                    }
                } else {
                    $mediaSubmit.attr('disabled', true);
                    $progress.addClass("show");
                    updateProgressTracker("uploading");

                    var studio = $("#edit-ustudio-upload-0-studio-uid").val();
                    var destination = $("#edit-ustudio-upload-0-destination-destination-uid").val();

                    // Create the Video
                    var video = createVideo(studio);

                    // If the video was successfully created
                    if (typeof video !== "undefined") {
                        // Upload the file now
                        var upload = uploadVideo(video.signed_upload_url);
                    }
                }

            });
        }
    }
})(jQuery, Drupal);
