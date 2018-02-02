<?php

namespace Drupal\media_entity_ustudio\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

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

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $entity = $items->getEntity();

    $element += [
      '#element_validate' => [[get_class($this), 'validateFormElement']],
    ];
    $config = \Drupal::config('media_entity_ustudio.settings');
    $access_token = $config->get('access_token');
    if ($access_token) {

      $element['ustudio_upload'] = [
        '#type' => 'file',
        '#title' => $element['#title'],
        '#default_value' => $items[$delta]->alias,
        '#required' => $element['#required'],
        '#maxlength' => 255,
        '#description' => $this->t('Specify an alternative path by which this data can be accessed. For example, type "/about" when writing an about page.'),
      ];

      $fetcher = \Drupal::service('media_entity_ustudio.fetcher');
      $studios = $fetcher->retrieveStudios($access_token);
      $values = $form_state->getValues();
      $studio = !empty($values['ustudio_upload'][0]['studio_uid']) ? $values['ustudio_upload'][0]['studio_uid'] : $config->get('studio');
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

      if ($studio && $access_token) {

        // Destinations
        $destinations = $fetcher->retrieveDestinations($access_token, $studio);
        if (!empty($destinations)) {
          $element['destination']['destination_uid'] = $this->destinationSelect($destinations);
          // If we have an existing configuration
          if ($destination = $config->get('destination')) {
            $element['destination']['destination_uid']['#default_value'] = $destination;
          }
        }
      }
    }
    return $element;
  }

  /**
   * Form element validation handler for URL alias form element.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateFormElement(array &$element, FormStateInterface $form_state) {
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
