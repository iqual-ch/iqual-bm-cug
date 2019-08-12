<?php

namespace Drupal\iq_pb_cug\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormState;
use Drupal\iq_pb_cug\CUGRoleListBuilder;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\iq_pb_cug\CUGRegisterForm;

/**
 * Controller routines for user routes.
 */
class CUGController extends ControllerBase {

    /**
     * Function to display page with CUG roles.
     *
     * @return array
     *   The form render array to display on the roles page.
     */
    public function rolePage() {
        $entity = $this->entityManager()->getStorage('user_role')->create([
            'type' => 'user_role',
        ]);

        //$form = $this->entityFormBuilder()->getForm($node);
        $form_state = new FormState();
        $user_role = \Drupal::entityTypeManager()->getListBuilder('user_role');
        $form = CUGRoleListBuilder::createInstance(\Drupal::getContainer(), $user_role->getStorage()->getEntityType())->buildForm([], $form_state);
        return $form;
    }

    /**
     * Handler function to add a new CUG role with a predefined value for the
     * third party setting that indicates that it is a CUG role.
     *
     * @return array
     *    The form render array for adding a new user role.
     */
    public function addRolePage() {
        $form_state_additions['complete_form']['closed_user_group']['#attributes']['readonly'] = 'readonly';
        $role = Role::create();
        $role->setThirdPartySetting('iq_pb_cug', 'closed_user_group', true);
        $form = \Drupal::service('entity.form_builder')->getForm($role, 'default', $form_state_additions);
        return $form;
    }

    /**
     * Handler function to add a new CUG user.
     *
     * @return array
     *    The form render array for adding a new user.
     */
    public function addUserPage() {
        $x = CUGRegisterForm::create(\Drupal::getContainer());
        $user = User::create();
        $form = \Drupal::service('entity.form_builder')->getForm($user);
        foreach ($form['account']['roles']['#options'] as $role_key => $role_label) {
            if (!Role::load($role_key)->getThirdPartySetting('iq_pb_cug','closed_user_group')) {
                unset($form['account']['roles']['#options'][$role_key]);
                unset($form['account']['roles'][$role_key]);
            }
        }

        return $form;
    }
}
