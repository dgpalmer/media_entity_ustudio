<?php

namespace Drupal\media_entity_ustudio\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Drupal\file\Entity\File;

/**
 * Plugin implementation of the 'uStudioUpload' widget.
 *
 * @FieldWidget(
 *   id = "ustudio_upload",
 *   label = @Translation("uStudio Upload Widget"),
 *   field_types = {
 *     "ustudio_upload"
 *   }
 * )
 */
class uStudioUploadWidget extends WidgetBase {

  public $fetcher;

  public $access_token;

  public $config;

  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings)
  {
    $this->fetcher = \Drupal::service('media_entity_ustudio.fetcher');
    $this->config = \Drupal::config('media_entity_ustudio.settings');
    $this->access_token = $this->config->get('access_token');
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    dpm('formElement');
    $entity = $items->getEntity();

    $element += [
      '#element_validate' => [[get_class($this), 'validateFormElement']],
    ];

    $element['#attached']['library'][] = 'media_entity_ustudio/drupal.uStudio';

    $values = $form_state->getValues();
    if ($this->access_token) {

      $studios = $this->fetcher->retrieveStudios($this->access_token);
      $values = $form_state->getValues();
      $studio = !empty($values['ustudio_upload'][0]['studio_uid']) ? $values['ustudio_upload'][0]['studio_uid'] : $this->config->get('studio');
      $element['studio_uid'] = [
        '#type' => 'select',
        '#title' => $this->t('Default Studio'),
        '#options' => $studios,
        '#size' => 1,
        '#required' => TRUE,
        '#default_value' => $studio,
        '#ajax' => [
          'callback' => [get_class($this), 'retrieveDestinations'],
          'event' => 'change',
          'wrapper' => 'edit-destination-uid',
          'progress' => [
            'type' => 'throbber',
            'message' => t('Grabbing Studio Destinations and Collections...'),
          ],
        ],
      ];

      $element['destination'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'edit-destination-uid'
        ]
      ];

      /**
       * Destinations
       */

      if ($studio && $this->access_token) {

        // Destinations
        $destinations = $this->fetcher->retrieveDestinations($this->access_token, $studio);
        if (!empty($destinations)) {
          $element['destination']['destination_uid'] = $this->destinationSelect($destinations);
          // If we have an existing configuration
          if ($destination = $this->config->get('destination')) {
            $element['destination']['destination_uid']['#default_value'] = $destination;
          }
        }
      }

      $element['upload'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'edit-ustudio-upload'
        ]
      ];
      $element['upload']['upload_file'] = [
        '#type' => 'managed_file',
        '#title' => $element['#title'],
        '#upload_location' => 'temporary://ustudio_videos',
        '#upload_validators' => [
          'file_validate_extensions' => ['mp4'],
        ],
      ];
//      $file_upload = '<div id="ustudio-file" class="form-item"><label for="edit-submitted-file">Video File <span class="form-required" title="This field is required.">*</span></label><input required="required" placeholder="Your Video File" type="file" id="video-file" name="file" class="form-text required"><div class="description">Upload your video file here. Accepted File extensions: mp4, mov, avi.</div></div>';
/*      $element['upload']['file'] = [
        '#type' => 'item',
        '#markup' => $file_upload,
      ];*/
      $progress_bar = '<div id=\"upload-progress\" class=\"upload-progress\"><div class=\"upload-progress-bar\" id=\"upload-progress-bar\"></div></div>';
      $element['upload']['progress'] = [
        '#type' => 'item',
        '#markup' => $progress_bar
      ];
      $element['upload']['link'] = [
        '#type' => 'hidden',
        '#attributes' => [
          'id' => 'ustudio-embed-link'
        ]
      ];
      $element['upload']['asset_url'] = [
        '#type' => 'hidden',
        '#attributes' => [
          'id' => 'ustudio-asset-url'
        ]
      ];
      $element['upload']['upload_submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Upload File'),
        '#ajax' => [
          'callback' =>  [$this, 'storeFileForUpload'],
//          'callback' =>  [get_class($this), 'storeFileForUpload'],
          'event' => 'click',
          'wrapper' => 'edit-ustudio-upload',
          'progress' => [
            'type' => 'throbber',
            'message' => t('Uploading to uStudio'),
          ],
        ]
      ];

    }
    return $element;
  }

  public function storeFileForUpload(array &$form, FormStateInterface &$form_state) {
    dpm('storeFileForUpload');
    $values = $form_state->getValues();
    $studio = $values['ustudio_upload'][0]['studio_uid'];
    $destination = $values['ustudio_upload'][0]['destination']['destination_uid'];

    $file = $values['ustudio_upload'][0]['upload']['upload_file'][0];
    $embed_code = 'LUL';

    $media_name = $values['name'][0]['value'];
    if (!empty($media_name)) {
      if ($file = File::load($values['ustudio_upload'][0]['upload']['upload_file'][0])) {
        dpm('file');
        $data = [
          "title" => $values['name'][0]['value'],
          "description" => "This is the first test from asf8.dd",
          "keywords" => ["test", "donovan", "drupal", "2132231"],
          "category" => "entertainment"
        ];

        // Create the uStudio Video via the uStudio Fetcher
        $video = $this->fetcher->createVideo($this->access_token, $studio, $data);

        // Upload the file to the uStudio Video via the uStudio Fetcher
        $upload = $this->fetcher->uploadVideo($this->access_token, $video['upload_url'], $file);

        // Publish the uStudio Video to the chosen destination via the uStudio Fetcher
        $publish = $this->fetcher->publishVideo($this->access_token, $studio, $destination, $video['uid']);


        $embed_code = 'https://embed.ustudio.com/embed/' . $destination . '/' . $video['uid'];
        $form['ustudio_upload']['widget'][0]['upload']['link']['#value'] = $embed_code;
        $form['ustudio_upload']['widget'][0]['upload']['asset_url']['#value'] = $video['upload_url'];
        $progress_script = '<script src="' . $video['upload_url'] . '?X-Progress-ID=upload_progress&callback=trackUpload' . '"></script>';

        $form['ustudio_upload']['widget'][0]['upload']['progress_script'] = [
          '#type' => 'item',
          '#markup' => $progress_script,
        ];
      }
    }

    return $form['ustudio_upload']['widget'][0]['upload'];
  }

  /**
   * Form element validation handler for uStudio upload widget form element.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateFormElement(array &$element, FormStateInterface &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return $element['ustudio_upload'];
  }

  /**
   * Helper Function to Retrieve Destinations and Render the Form Element
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public static function retrieveDestinations(array $form, FormStateInterface $form_state) {
    dpm('retrieveDestinations ajax');
    return $form['ustudio_upload']['widget'][0]['destination'];
  }

  /**
   * Helper function to build a dropdown select for Collections
   *
   * @param $studios
   * @return array
   */
  protected function collectionSelect($collections) {
    return [
      '#type' => 'select',
      '#title' => $this->t('Default Collection'),
      '#options' => $collections,
      '#size' => 1,
      '#required' => TRUE,
      '#empty_value' => "",
    ];
  }

  /**
   * Helper function to build a dropdown select for Destinations
   *
   * @param $studios
   * @return array
   */
  protected function destinationSelect($destinations) {
    return [
      '#type' => 'select',
      '#title' => $this->t('Destination'),
      '#options' => $destinations,
      '#size' => 1,
      '#required' => TRUE,
      '#empty_value' => "",
    ];
  }
}
