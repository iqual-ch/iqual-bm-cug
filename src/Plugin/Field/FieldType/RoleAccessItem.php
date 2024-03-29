<?php

namespace Drupal\iq_pb_cug\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the role access content field type.
 *
 * @FieldType(
 *     id = "role_access",
 *     label = @Translation("Role access"),
 *     module = "iq_pb_cug",
 *     description = @Translation("The needed role to access this node."),
 *     default_widget = "entity_reference_autocomplete",
 *     default_formatter = "role_access_formatter",
 *     list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList",
 * )
 */
class RoleAccessItem extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    // @todo Change the autogenerated stub
    $element = parent::storageSettingsForm($form, $form_state, $has_data);
    $element['target_type']['#options'] = ["user_role" => $element['target_type']['#options'][array_key_last($element['target_type']['#options'])]['user_role']];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'target_type' => 'user_role',
    ] + parent::defaultStorageSettings();
  }

}
