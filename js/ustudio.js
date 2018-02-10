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
                console.log(response.video);
                // Upload the file now
                uploadVideo(response.video.signed_upload_url);
                trackUploadProgress(response.video.signed_upload_url);
            }
        });
    }

    /**
     * Upload a File to uStudio
     *
     * @param upload_url
     */
    function uploadVideo(upload_url) {
        console.log('uploadVideo');
        var reader = new FileReader();
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
     * Track the Upload Progress
     * @param upload_url
     */
    function trackUploadProgress(upload_url) {
        console.log('trackUploadProgress');
        console.log(upload_url);
        $.ajax({
            method: 'GET',
            url: upload_url,
            dataType: 'jsonp',
            success: function (result) {
                console.log(result);
            },
            error: function () {
                console.log("error");
            }
       // }).done(function (response) {
           /* console.log("status:");
            var status = response.progress.status;
            console.log(status);
            updateProgressTracker(response.progress.status.state);
            return response.progress.status.state;*/
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
            video: video.uid
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
                    updateProgressTracker("uploading");

                    var studio = $("#edit-ustudio-upload-0-studio-uid").val();
                    var destination = $("#edit-ustudio-upload-0-destination-destination-uid").val();

                    // Create the Video
                    createVideo(studio);

                }

            });
        }
    }
})(jQuery, Drupal);
