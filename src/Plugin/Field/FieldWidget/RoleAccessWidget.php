<?php

namespace Drupal\iq_pb_cug\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;

/**
 * Plugin implementation of the Role access widget.
 *
 * @FieldWidget(
 *   id = "role_access_widget",
 *   module = "iq_pub_cug",
 *   label = @Translation("Role access"),
 *   field_types = {
 *     "role_access"
 *   },
 *   multiple_values = TRUE
 * )
 */
class RoleAccessWidget extends OptionsSelectWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $content_access = [];
    $content_access['content_access'] = [
      '#type' => 'details',
      '#title' => $this->t('Content access'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
      '#group' => 'advanced',
      '#weight' => 99998,
    ];
    $options = parent::formElement($items, $delta, $element, $form, $form_state);
    $content_access['content_access']['user_role'] = $options;
    return $content_access;
  }

  /**
   * {@inheritdoc}
   */
  protected function getOptions(FieldableEntityInterface $entity) {
    $options = parent::getOptions($entity);

    // Only list the CUG roles.
    foreach ($options as $role => $value) {
      $roleObj = Role::load($role);
      if (isset($roleObj)) {
        if (!$roleObj->getThirdPartySetting('iq_pb_cug', 'closed_user_group')) {
          unset($options[$role]);
        }
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = $values['content_access']['user_role'];
    // @todo Change the autogenerated stub
    return parent::massageFormValues($values, $form, $form_state);
  }

}
