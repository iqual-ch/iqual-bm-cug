<?php

namespace Drupal\iq_pb_cug\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\iq_pb_cug\RoleListBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller routines for user routes.
 */
class RouteController extends ControllerBase {

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Constructs a new RouteController object.
   *
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   */
  public function __construct(EntityFormBuilderInterface $entity_form_builder, ContainerInterface $container) {
    $this->entityFormBuilder = $entity_form_builder;
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.form_builder'),
      $container
    );
  }

  /**
   * Function to display page with CUG roles.
   *
   * @return array
   *   The form render array to display on the roles page.
   */
  public function rolePage() {
    $form_state = new FormState();
    $user_role = $this->entityTypeManager()->getListBuilder('user_role');
    $form = RoleListBuilder::createInstance($this->container, $user_role->getStorage()->getEntityType())->buildForm([], $form_state);
    return $form;
  }

  /**
   * Add role.
   *
   * Handler function to add a new CUG role with a predefined value for the
   * third party setting that indicates that it is a CUG role.
   *
   * @return array
   *   The form render array for adding a new user role.
   */
  public function addRolePage() {
    $form_state_additions = [];
    $form_state_additions['complete_form']['closed_user_group']['#attributes']['readonly'] = 'readonly';
    /** @var \Drupal\user\Entity\Role $role */
    $role = $this->entityTypeManager()->getStorage('user_role')->create();
    $role->setThirdPartySetting('iq_pb_cug', 'closed_user_group', TRUE);
    $form = $this->entityFormBuilder->getForm($role, 'default', $form_state_additions);
    return $form;
  }

  /**
   * Handler function to add a new CUG user.
   *
   * @return array
   *   The form render array for adding a new user.
   */
  public function addUserPage() {
    /** @var \Drupal\user\Entity\User $user */
    $user = $this->entityTypeManager()->getStorage('user')->create();
    $form = $this->entityFormBuilder->getForm($user);
    foreach ($form['account']['roles']['#options'] as $role_key => $role_label) {
      /** @var \Drupal\user\Entity\Role $role */
      $role = $this->entityTypeManager()->getStorage('user_role')->load($role_key);
      if (!$role || !$role->getThirdPartySetting('iq_pb_cug', 'closed_user_group')) {
        unset($form['account']['roles']['#options'][$role_key]);
        unset($form['account']['roles'][$role_key]);
      }
    }

    return $form;
  }

}
