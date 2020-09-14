<?php

namespace NuvoleWeb\Drupal\Driver\Cores;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Driver\Cores\Drupal8 as OriginalDrupal8;
use Drupal\file\Entity\File;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\system\Entity\Menu;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use NuvoleWeb\Drupal\Driver\Objects\Drupal8\EditableConfig;
use NuvoleWeb\Drupal\Driver\Objects\Drupal8\State;
use Webmozart\Assert\Assert;

/**
 * Extending the Drupal 8 direver with more methods.
 */
class Drupal8 extends OriginalDrupal8 implements CoreInterface {

  /**
   * {@inheritdoc}
   */
  public function convertLabelToNodeTypeId($type) {
    // First suppose that the id has been passed.
    if (NodeType::load($type)) {
      return $type;
    }
    $storage = \Drupal::entityTypeManager()->getStorage('node_type');
    $result = $storage->loadByProperties(['name' => $type]);
    Assert::notEmpty($result);
    return key($result);
  }

  /**
   * {@inheritdoc}
   */
  public function convertLabelToTermTypeId($type) {
    // First suppose that the id has been passed.
    if (Vocabulary::load($type)) {
      return $type;
    }
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary');
    $result = $storage->loadByProperties(['name' => $type]);
    Assert::notEmpty($result);
    return key($result);
  }

  /**
   * {@inheritdoc}
   */
  public function loadNodeByName($title) {
    $result = \Drupal::entityQuery('node')
      ->condition('title', $title)
      ->condition('status', NodeInterface::PUBLISHED)
      ->range(0, 1)
      ->execute();
    Assert::notEmpty($result);
    $nid = current($result);
    return Node::load($nid);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityIdByLabel($entity_type, $bundle, $label) {
    $result = $this->findEntityIdByLabel($entity_type, $bundle, $label);
    Assert::notNull($result, __METHOD__ . ": No Entity {$entity_type} with name {$label} found.");
    return $result;
  }

  /**
   * Get entity ID given its type, bundle and label without throwing exceptions.
   *
   * @param string $entity_type
   *   Entity type machine name.
   * @param string $bundle
   *   Entity bundle machine name, can be empty.
   * @param string $label
   *   Entity name.
   *
   * @return int|null
   *   Entity ID.
   */
  public function findEntityIdByLabel($entity_type, $bundle, $label) {
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $type = $storage->getEntityType();

    if ($type->hasKey('label')) {
      $label_key = $type->getKey('label');
    }
    else {
      // Fall back to the name field (for users for example) when the entity
      // type has no label key.
      $label_key = 'name';
    }

    $query = $storage->getQuery();
    if ($bundle) {
      $bundle_key = $type->getKey('bundle');
      $query->condition($bundle_key, $bundle);
    }
    $query->condition($label_key, $label);
    $query->range(0, 1);

    $query->accessCheck(FALSE);

    $result = $query->execute();
    if (empty($result)) {
      return NULL;
    }
    return current($result);
  }

  /**
   * {@inheritdoc}
   */
  public function loadUserByName($name) {
    $user = user_load_by_name($name);
    Assert::notEq($user, FALSE);
    return $user;
  }

  /**
   * {@inheritdoc}
   */
  public function nodeAccess($op, $name, $node) {
    $account = $this->loadUserByName($name);
    return $node->access($op, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeId($node) {
    return $node->id();
  }

  /**
   * {@inheritdoc}
   */
  public function loadTaxonomyTermByName($type, $name) {
    $result = \Drupal::entityQuery('taxonomy_term')
      ->condition('name', $name)
      ->condition('vid', $type)
      ->range(0, 1)
      ->execute();
    Assert::notEmpty($result);
    $id = current($result);
    return Term::load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function getTaxonomyTermId($term) {
    return $term->id();
  }

  /**
   * {@inheritdoc}
   */
  public function loadMenuItemByTitle($menu_name, $title) {
    $items = \Drupal::entityTypeManager()->getStorage('menu_link_content')
      ->loadByProperties([
        'menu_name' => $menu_name,
        'title' => $title,
      ]);
    if (!empty($items)) {
      return array_shift($items);
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createMenuStructure($menu_name, $menu_items) {
    if (!Menu::load($menu_name)) {
      throw new \InvalidArgumentException("Menu '{$menu_name}' not found.");
    }

    $weight = 0;
    $menu_links = [];
    foreach ($menu_items as $menu_item) {
      $values = [
        'title' => $menu_item['title'],
        'link' => ['uri' => $menu_item['uri']],
        'menu_name' => $menu_name,
        'weight' => $weight++,
      ];

      // Assign parent item.
      if ($menu_item['parent']) {
        $values['parent'] = $menu_item['parent'];
        $parent = $this->loadMenuItemByTitle($menu_name, $menu_item['parent']);
        if ($parent) {
          $values['parent'] = $parent->getPluginId();
        }
      }

      // Create menu link.
      $menu_link = MenuLinkContent::create($values);
      $menu_link->save();
      $menu_links[] = $menu_link;
    }

    return $menu_links;
  }

  /**
   * {@inheritdoc}
   */
  public function clearMenuCache() {
    \Drupal::cache('menu')->invalidateAll();
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateCacheTags(array $tags) {
    Cache::invalidateTags($tags);
  }

  /**
   * Create an entity.
   *
   * @param string $entity_type
   *   Entity type.
   * @param mixed $values
   *   The Values to create the entity with.
   * @param bool $save
   *   Indicate whether to directly save the entity or not.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   Entity object.
   */
  public function entityCreate($entity_type, $values, $save = TRUE) {
    if (!is_array($values)) {
      // Cast an object to array to be compatible with nodeCreate().
      $values = (array) $values;
    }

    $entity = $this->getStubEntity($entity_type, $values);

    foreach ($values as $name => $value) {
      $definition = $entity->getFieldDefinition($name);
      $settings = $definition->getSettings();
      switch ($definition->getType()) {
        case 'entity_reference':
        case 'entity_reference_revisions':
          $target_type = $settings['target_type'];
          $target_entity_type = \Drupal::entityTypeManager()->getDefinition($target_type);
          if (!($target_entity_type instanceof ContentEntityTypeInterface)) {
            // We only know how to get deal with content entities.
            break;
          }
          // Save references but don't save reference revisions.
          // This allows to reference entityies created earlier in the loop
          // to be referenced by name but keeps the behaviour for paragraphs.
          $save_target = $definition->getType() !== 'entity_reference_revisions';

          if (!is_array($value)) {
            $value = [$value];
          }
          $entities = [];

          $mapped = array_filter($value, function ($key) {
            return !is_int($key);
          }, ARRAY_FILTER_USE_KEY);
          if (!empty($mapped)) {
            $entities[] = $this->entityCreate($target_type, $mapped, $save_target);
          }

          $value = array_diff_key($value, $mapped);
          foreach ($value as $target_values) {
            if (!is_array($target_values)) {
              if (empty($target_values)) {
                // If there is no value, do nothing.
                continue;
              }
              // If here we don't encounter an array, only the label is given.
              $referenced = $this->findEntityIdByLabel($target_type, NULL, $target_values);
              if ($referenced === NULL && is_numeric($target_values)) {
                // For backwards compatibility we accept numeric ids.
                $referenced = $target_values;
              }
              Assert::notEmpty($referenced, __METHOD__ . ": No Entity {$target_type} with name {$target_values} found.");
              // At least for entity_reference_revisions we need to load the
              // entity to know which revision to reference.
              $referenced = \Drupal::entityTypeManager()->getStorage($target_type)->load($referenced);
              $entities[] = $referenced;
            }
            else {
              $entities[] = $this->entityCreate($target_type, $target_values, $save_target);
            }
          }

          $entity->{$name}->setValue($entities);
          break;

        case 'image':
          $entity->{$name}->setValue(['target_id' => $this->saveFile($value)->id()]);
          break;
      }
    }

    if ($save) {
      $entity->save();
    }

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function entityLoad($entity_type, $entity_id) {
    return \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);
  }

  /**
   * {@inheritdoc}
   */
  public function entityAddTranslation($entity, $language, array $values) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
    $translation = $this->getStubEntity($entity->getEntityTypeId(), $values);

    foreach ($values as $name => $value) {
      $definition = $translation->getFieldDefinition($name);
      $settings = $definition->getSettings();
      $source_values = $entity->get($name)->getValue();
      switch ($definition->getType()) {
        case 'entity_reference':
          if (in_array($settings['target_type'], ['node', 'taxonomy_term'])) {
            // @todo: only supports single values for the moment.
            $translation->{$name}->setValue($source_values);
          }
          break;

        case 'entity_reference_revisions':

          // When reference field is translatable then we will need to create
          // new entities and reference them.
          // @link https://www.drupal.org/node/2461695
          if ($definition->isTranslatable()) {
            $target_values = [];
            foreach ($source_values as $key => $item) {
              $_entity = $this->entityCreate($settings['target_type'], $value[$key]);
              $target_values[] = [
                'target_id' => $_entity->id(),
                'target_revision_id' => $_entity->getRevisionId(),
              ];
            }
            $translation->{$name}->setValue($target_values);
          }
          else {
            // Recurse over the referenced entities.
            foreach ($source_values as $key => $item) {
              $source = $this->entityLoad($settings['target_type'], $item['target_id']);
              $this->entityAddTranslation($source, $language, $value[$key]);
            }
          }
          break;
      }
    }

    // Add the translation to the entity.
    $translation = $entity->addTranslation($language, $translation->toArray());

    $translation->save();

    return $translation;
  }

  /**
   * {@inheritdoc}
   */
  public function state() {
    return new State();
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfig($name) {
    return new EditableConfig($name);
  }

  /**
   * Get stub entity.
   *
   * @param string $entity_type
   *   Entity type.
   * @param array $values
   *   Entity values.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   Entity object.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function getStubEntity($entity_type, array $values) {
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->create($values);
    if (!$entity instanceof ContentEntityInterface) {
      throw new EntityMalformedException("Only content entities are supported.");
    }
    return $entity;
  }

  /**
   * Save a file and return its id.
   *
   * @param string $source
   *   Source path relative to Drupal installation root.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Saved file object.
   */
  protected function saveFile($source) {
    $name = basename($source);
    $path = realpath(DRUPAL_ROOT . '/' . $source);
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $uri = $file_system->copy($path, 'public://' . $name, FileSystemInterface::EXISTS_REPLACE);
    $file = File::create(['uri' => $uri]);
    $file->save();
    return $file;
  }

}
