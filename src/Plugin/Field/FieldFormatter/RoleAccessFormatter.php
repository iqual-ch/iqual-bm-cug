<?php

namespace Drupal\iq_pb_cug\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the role access formatter.
 *
 * @FieldFormatter(
 *     id = "role_access_formatter",
 *     module ="iq_pb_cug",
 *     label = @Translation("Role access"),
 *     field_types = {
 *         "role_access"
 *     }
 * )
 */
class RoleAccessFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    return [];
  }

}
