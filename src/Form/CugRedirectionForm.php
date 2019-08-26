<?php

namespace Drupal\iq_pb_cug\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\RoleInterface;

/**
 * Class CugRedirectionForm.
 *
 * @package Drupal\iq_pb_cug\Form
 */
class CugRedirectionForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'cug_redirection_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('redirect_cug.settings');
        $default_redirection = $config->get('default_redirection');
        $savedPathRoles = $config->get('cug_redirection');

        $form['default_redirection'] = [
            '#type' => 'textfield',
            '#title' => 'Default redirection',
            '#size' => 60,
            '#maxlength' => 128,
            '#description' => $this->t('Add a valid url for the default page'),
            '#default_value' => isset($default_redirection) ? $default_redirection : '',
        ];

        $form['roles'] = [
            '#type' => 'fieldset',
            '#title' => t('All roles'),
        ];
        /** @var  $role \Drupal\user\Entity\Role */
        foreach (Role::loadMultiple() as $role) {
            if ($role->id() != "anonymous") {
                $is_cug_user = false;
                if($role->getThirdPartySetting('iq_pb_cug', 'closed_user_group')) {
                    $is_cug_user = true;
                }
                if ($is_cug_user) {
                    $form['roles'][$role->id()] = [
                        '#type' => 'textfield',
                        '#title' => $role->label(),
                        '#size' => 60,
                        '#maxlength' => 128,
                        '#description' => $this->t('Add a valid url for the user role %s', ['%s' => $role->label()]),
                        '#default_value' => isset($savedPathRoles[$role->id()]) ? $savedPathRoles[$role->id()] : '',
                    ];
                }
            }
        }

        // $form['exclude_urls'] = [
        //     '#type' => 'textarea',
        //     '#title' => $this->t('Exclude url from redirection'),
        //     '#description' => $this->t('One url per line. Redirection on this urls will be skipped. You can use wildcard "*".'),
        //     '#default_value' => $config->get('exclude_urls'),
        // ];

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
    public function validateForm(array &$form, FormStateInterface $form_state)
    {

        foreach (user_role_names() as $user => $name) {
            if ($user == "anonymous") {
                continue;
            }
            $path = $form_state->getValue($user);
            if (!empty($path)) {
                if (!(preg_match('/^[#?\/]+/', $path) || $path == '<front>')) {
                    $form_state->setErrorByName($user, t('This URL %url is not valid for role %role.', [
                        '%url' => $form_state->getValue($user),
                        '%role' => $name,
                    ]));
                }
                $is_valid = \Drupal::service('path.validator')->isValid($path);
                if ($is_valid == null) {
                    $form_state->setErrorByName($user, t('Path does not exists.'));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $loginUrls = [];
        foreach (user_role_names() as $user => $name) {
            if ($form_state->getValue($user) == '<front>') {
                $loginUrls[$user] = '/';
            } else {
                $loginUrls[$user] = $form_state->getValue($user);
                $form_state->getValue($user);
            }
        }
        $this->config('redirect_cug.settings')
            ->set('cug_redirection', $loginUrls)
            ->set('default_redirection', $form_state->getValue('default_redirection'))
        // ->set('exclude_urls', $form_state->getValue('exclude_urls'))
            ->save();

        parent::submitForm($form, $form_state);
    }

    /**
     * Get Editable config names.
     *
     * @inheritDoc
     */
    protected function getEditableConfigNames()
    {
        return ['redirect_cug.settings'];
    }

}
