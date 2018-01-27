<?php

namespace Drupal\media_entity_ustudio\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\media_entity_ustudio\uStudio\StudiosFetcher;

/**
 * Class test.
 */
class test extends ConfigFormBase {

  /**
   * Drupal\media_entity_ustudio\uStudio\StudiosFetcher definition.
   *
   * @var \Drupal\media_entity_ustudio\uStudio\StudiosFetcher
   */
  protected $mediaEntityUstudioStudiosFetcher;
  /**
   * Constructs a new test object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
      StudiosFetcher $media_entity_ustudio_studios_fetcher
    ) {
    parent::__construct($config_factory);
        $this->mediaEntityUstudioStudiosFetcher = $media_entity_ustudio_studios_fetcher;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
            $container->get('media_entity_ustudio.studios_fetcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'media_entity_ustudio.test',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('media_entity_ustudio.test');
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('media_entity_ustudio.test')
      ->save();
  }

}
