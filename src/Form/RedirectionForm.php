<?php

namespace Drupal\iq_pb_cug\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;

/**
 * Form to set redirections.
 *
 * @package Drupal\iq_pb_cug\Form
 */
class RedirectionForm extends ConfigFormBase {

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
    /** @var  \Drupal\user\Entity\Role $role */
    foreach (Role::loadMultiple() as $role) {
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

    foreach (user_role_names() as $user => $name) {
      if ($user == "anonymous") {
        continue;
      }
      $path = $form_state->getValue($user);
      if (!empty($path)) {
        if (!(preg_match('/^[#?\/]+/', (string) $path) || $path == '<front>')) {
          $form_state->setErrorByName($user, $this->t('This URL %url is not valid for role %role.', [
            '%url' => $form_state->getValue($user),
            '%role' => $name,
          ]));
        }
        $is_valid = \Drupal::service('path.validator')->isValid($path);
        if ($is_valid == NULL) {
          $form_state->setErrorByName($user, $this->t('Path does not exists.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $loginUrls = [];
    foreach (user_role_names() as $user => $name) {
      if ($form_state->getValue($user) == '<front>') {
        $loginUrls[$user] = '/';
      }
      else {
        $loginUrls[$user] = $form_state->getValue($user);
        $form_state->getValue($user);
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
