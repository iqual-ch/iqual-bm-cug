<?php

namespace Drupal\iq_pb_cug;

use Drupal\Core\Entity\EntityInterface;
use Drupal\user\RoleListBuilder as ParentRoleListBuilder;

/**
 * Extend the RoleListBuilder class to filter only cug roles.
 *
 * @package Drupal\iq_pb_cug
 */
class RoleListBuilder extends ParentRoleListBuilder {

  /**
   * {@inheritDoc}
   */
  public function buildRow(EntityInterface $entity) {
    // Check if the role is part of the Closed User Group.
    if ($entity->getThirdPartySetting('iq_pb_cug', 'closed_user_group')) {
      $row = parent::buildRow($entity);
      $row['#weight'] = $entity->get($this->weightKey);
      // Add weight column.
      $row['weight']['#markup'] = $entity->getThirdPartySetting('iq_pb_cug', 'closed_user_group_weight');
      return $row;
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function buildOperations(EntityInterface $entity) {
    $op = parent::buildOperations($entity);
    // Do not show operation to edit permissions, except if it is an admin.
    if (!\Drupal::currentUser()->hasPermission('administer permissions')) {
      unset($op['#links']['permissions']);
    }
    return $op;
  }

}
