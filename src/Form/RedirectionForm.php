<?php

namespace Drupal\iq_pb_cug\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to set redirections.
 *
 * @package Drupal\iq_pb_cug\Form
 */
class RedirectionForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Constructs a new RedirectionForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PathValidatorInterface $path_validator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->pathValidator = $path_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('path.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cug_redirection_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('iq_pb_cug.settings');
    $default_redirection = $config->get('default_redirection');
    $savedPathRoles = $config->get('cug_redirection');
    $login_destination = $config->get('login_destination');

    $form['default_redirection'] = [
      '#type' => 'textfield',
      '#title' => 'Default redirection',
      '#size' => 60,
      '#maxlength' => 128,
      '#description' => $this->t('Add a valid url for the default page'),
      '#default_value' => $default_redirection ?? '',
    ];
    $form['login_destination'] = [
      '#type' => 'textfield',
      '#title' => 'Login page',
      '#size' => 60,
      '#maxlength' => 128,
      '#description' => $this->t('Add a valid url for the login page'),
      '#default_value' => $login_destination ?? '/de/login',
    ];

    $form['roles'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('All roles'),
    ];

    /** @var \Drupal\user\Entity\Role[] $roles */
    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    foreach ($roles as $role) {
      if ($role->id() != "anonymous") {
        $is_cug_user = FALSE;
        if ($role->getThirdPartySetting('iq_pb_cug', 'closed_user_group')) {
          $is_cug_user = TRUE;
        }
        if ($is_cug_user) {
          $form['roles'][$role->id()] = [
            '#type' => 'textfield',
            '#title' => $role->label(),
            '#size' => 60,
            '#maxlength' => 128,
            '#description' => $this->t('Add a valid url for the user role %s', ['%s' => $role->label()]),
            '#default_value' => $savedPathRoles[$role->id()] ?? '',
          ];
        }
      }
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\user\Entity\Role[] $roles */
    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    foreach ($roles as $role) {
      $role_id = $role->id();
      $role_name = $role->label();

      if ($role_id == "anonymous") {
        continue;
      }

      $path = $form_state->getValue($role_id);
      if (!empty($path)) {
        if (!(preg_match('/^[#?\/]+/', (string) $path) || $path == '<front>')) {
          $form_state->setErrorByName($role_id, $this->t('This URL %url is not valid for role %role.', [
            '%url' => $path,
            '%role' => $role_name,
          ]));
        }
        $is_valid = $this->pathValidator->isValid($path);
        if ($is_valid == NULL) {
          $form_state->setErrorByName($role_id, $this->t('Path does not exists.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $loginUrls = [];
    /** @var \Drupal\user\Entity\Role[] $roles */
    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    foreach ($roles as $role) {
      $role_id = $role->id();
      $value = $form_state->getValue($role_id);

      if ($value == '<front>') {
        $loginUrls[$role_id] = '/';
      }
      else {
        $loginUrls[$role_id] = $value;
      }
    }

    $this->config('iq_pb_cug.settings')
      ->set('cug_redirection', $loginUrls)
      ->set('default_redirection', $form_state->getValue('default_redirection'))
      ->set('login_destination', $form_state->getValue('login_destination'))
        // ->set('exclude_urls', $form_state->getValue('exclude_urls'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Get Editable config names.
   *
   * @inheritDoc
   */
  protected function getEditableConfigNames() {
    return ['iq_pb_cug.settings'];
  }

}
