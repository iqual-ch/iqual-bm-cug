<?php

namespace Drupal\iq_pb_cug\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for user routes.
 */
class CUGController extends ControllerBase {
    /**
     * Redirects users to their profile page.
     *
     * This controller assumes that it is only invoked for authenticated users.
     * This is enforced for the 'user.page' route with the '_user_is_logged_in'
     * requirement.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *   Returns a redirect to the profile of the currently logged in user.
     */
    public function userPage() {
        return $this->redirect('entity.user.canonical', ['user' => $this->currentUser()->id()]);
    }
    public function rolePage() {
        $entity = $this->entityManager()->getStorage('user_role')->create([
            'type' => 'user_role',
        ]);

        //$form = $this->entityFormBuilder()->getForm($node);

        //return $form;
    }
}
