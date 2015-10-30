<?php
/**
 * @file
 * Contains trait class.
 */

namespace NuvoleWeb\Drupal\Behat\Traits;

use Behat\Gherkin\Node\TableNode;

/**
 * Trait OrganicGroups.
 *
 * @package Nuvole\Drupal\Behat\Traits
 */
trait OrganicGroups {

  /**
   * @Given :name has the :role role in the :group group
   * @Given :name is a :role of the :group group
   */
  public function assignUserToGroupRole($name, $role, $group) {

    $user = user_load_by_name($name);
    if (empty($user)) {
      throw new \Exception(sprintf('User "%s" does not exist.', $name));
    }

    // Discover the groups with the given name.
    $groups = $this->getGroupsByName($group);

    // Check that we only have one group to rule them all.
    $count = array_sum(array_map('count', $groups));
    if ($count == 0) {
      throw new \InvalidArgumentException("No such group '$group'.");
    }
    if ($count > 1) {
      throw new \InvalidArgumentException("Multiple groups with the name '$group' exist.");
    }

    // We only have one group. Retrieve it from the $groups array.
    $entity_type = key($groups);
    $group = reset($groups[$entity_type]);

    // Get the available roles for this group.
    list($entity_id, , $bundle) = entity_extract_ids($entity_type, $group);
    $roles = og_roles($entity_type, $bundle, $entity_id);

    // Check that the given role exists in the group.
    $rid = array_search($role, $roles);
    if ($rid === FALSE) {
      throw new \InvalidArgumentException("The '$group' group does not have a '$role' role.");
    }

    // Subscribe the user to the group.
    og_group($entity_type, $entity_id, array('entity' => $user));

    // Grant the OG role to the user.
    og_role_grant($entity_type, $entity_id, $user->uid, $rid);
  }

  /**
   * @Then :name can :op any :type content in the :group group
   */
  public function canOpAnyContentInTheGroup($name, $op, $type, $group)
  {
    $op = strtr($op, array('edit' => 'update'));
    $node = $this->loadNodeByName($group);
    $account = user_load_by_name($name);
    $access = og_user_access('node', $node->nid, "$op any $type content", $account);

    if (!$access) {
      $params = array(
        '@name' => $name,
        '@op' => $op,
        '@type' => $type,
        '@group' => $group,
      );
      throw new \Exception(format_string("@name can not @op any @type in @group.", $params));
    }
  }

  /**
   * @Then :name can not :op any :type content in the :group group
   */
  public function canNotOpAnyContentInTheGroup($name, $op, $type, $group)
  {
    try {
      $this->canOpAnyContentInTheGroup($name, $op, $type, $group);
    }
    catch (\Exception $e) {
      return;
    }
    $params = array(
      '@name' => $name,
      '@op' => $op,
      '@type' => $type,
      '@group' => $group,
    );
    throw new \Exception(format_string("@name can @op any @type in @group but shouldn't.", $params));
  }


  /**
   * @Then :name can :op any :type content in the following groups:
   */
  public function canOpAnyContentInTheFollowingGroups($name, $op, $type, TableNode $table)
  {
    foreach ($table->getHash() as $group) {
      $this->canOpAnyContentInTheGroup($name, $op, $type, $group['title']);
    }
  }

  /**
   * @Then :name can not :op any :type content in the following groups:
   */
  public function canNotOpAnyContentInTheFollowingGroups($name, $op, $type, TableNode $table)
  {
    foreach ($table->getHash() as $group) {
      $this->canNotOpAnyContentInTheGroup($name, $op, $type, $group['title']);
    }
  }


  /**
   * Returns a list of OG groups with the given name across all entities.
   *
   * @param $name
   *   The name of the groups to return.
   *
   * @return array
   *   An associative array, keyed by entity type, each value an indexed array
   *   of groups with the given name.
   */
  public function getGroupsByName($name) {
    $groups = array();

    foreach (og_get_all_group_bundle() as $entity_type => $bundles) {
      $entity_info = entity_get_info($entity_type);
      $query = new \EntityFieldQuery();
      $result = $query
        ->entityCondition('entity_type', $entity_type)
        ->entityCondition('bundle', array_keys($bundles), 'IN')
        ->fieldCondition(OG_GROUP_FIELD, 'value', 1, '=')
        ->propertyCondition($entity_info['entity keys']['label'], $name)
        // Make sure we can retrieve the data even if we are an anonymous user.
        ->addTag('DANGEROUS_ACCESS_CHECK_OPT_OUT')
        ->execute();

      if (!empty($result[$entity_type])) {
        $groups[$entity_type] = entity_load($entity_type, array_keys($result[$entity_type]));
      }
    }

    return $groups;
  }


}
