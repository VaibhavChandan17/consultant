<?php

namespace Drupal\sws_rest_api\Utils;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\menu_item_extras\Entity\MenuItemExtrasMenuLinkContent;

/**
 * Class MenuHelper.
 */
class MenuHelper {

  /**
   * Header Menu function.
   *
   * @param string $menu_name
   *   Menu Name.
   * @param int $maxDepth
   *   Menu max depth.
   * @param int $minDepth
   *   Menu min depth.
   *
   * @return array
   *   Return Array.
   */
  public static function menu($menu_name = 'new-menu', $maxDepth = 0, $minDepth = 1) {
    // Load the tree based on this set of parameters.
    $menuItems = [];
    // Create the parameters.
    $parameters = new MenuTreeParameters();
    // $parameters->onlyEnabledLinks();.
    // $parameters->setMaxDepth($maxDepth);.
    $parameters->setMinDepth($minDepth);

    // Load the tree based on this set of parameters.
    $menu_tree = \Drupal::menuTree();
    $tree = $menu_tree->load($menu_name, $parameters);

    // Return if the menu does not exist or has no entries.
    if (empty($tree)) {
      return [];
    }

    // Transform the tree using the manipulators you want.
    $manipulators = [
      // Only show links that are accessible for the current user.
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      // Use the default sorting of menu links.
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);

    // Finally, build a renderable array from the transformed tree.
    $menu = $menu_tree->build($tree);

    // Return if the menu has no entries.
    if (empty($menu['#items'])) {
      return [];
    }
    return static::getMenuItems($menu['#items'], $menuItems, $menu_name);
  }

  /**
   * Generate the menu tree we can use in JSON.
   *
   * @param array $tree
   *   The menu tree.
   * @param array $items
   *   The already created items.
   */
  public static function getMenuItems(array $tree, array &$items = [], $menu_name) {
    $parentValue = NULL;
    $outputValues = [
      'linkName',
      'linkNameTranslated',
      'target',
      'linkUrl',
      'className',
      'spa',
      'nofollow',
      'field_image',
    ];

    // Loop through the menu items.
    foreach ($tree as $item_value) {

      /** @var \Drupal\Core\Menu\MenuLinkInterface $org_link */
      $org_link = $item_value['original_link'];

      /** @var \Drupal\Core\Url $url */
      $url = $item_value['url'];

      $newValue = [];

      foreach ($outputValues as $valueKey) {
        if (!empty($valueKey)) {
          static::getElementValue($newValue, $valueKey, $org_link, $url);
        }
      }

      $newValue['hasChild'] = FALSE;
      if (!empty($item_value['below'])) {
        $newValue['hasChild'] = TRUE;
        if ($menu_name == 'footer') {
          $parentValue = $newValue;
          $newValue = [];
          static::getMenuItems($item_value['below'], $newValue, $menu_name);
        }
        else {
          $newValue['childrens'] = [];
          static::getMenuItems($item_value['below'], $newValue['childrens'], $menu_name);
        }
      }
      if ($parentValue) {
        array_unshift($newValue, $parentValue);
        $items[] = $newValue;
      }
      else {
        $items[] = $newValue;
      }
    }
    return $items;
  }

  /**
   * Generate the menu element value.
   *
   * @param array $returnArray
   *   The return array we want to add this item to.
   * @param string $key
   *   The key to use in the output.
   * @param \Drupal\Core\Menu\MenuLinkInterface $link
   *   The link from the menu.
   * @param \Drupal\Core\Url $url
   *   The URL object of the menu item.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function getElementValue(array &$returnArray, $key, MenuLinkInterface $link, Url $url) {
    $external = $url->isExternal();
    $routed = $url->isRouted();
    $existing = TRUE;
    $value = NULL;

    // Check if the url is a <nolink> and do not do anything for some keys.
    $itemsToRemoveWhenNoLink = ['linkUrl'];
    if (!$external && $routed && $url->getRouteName() === '<nolink>' && in_array($key, $itemsToRemoveWhenNoLink)) {
      return;
    }

    if ($external || !$routed) {
      $uri = $url->getUri();
    }
    else {
      try {
        $uri = $url->getInternalPath();
      }
      catch (\UnexpectedValueException $e) {
        $uri = Url::fromUri($url->getUri())->toString();
        $existing = FALSE;
      }
    }

    switch ($key) {
      case 'linkName':
        $value = $link->getTitle();

        break;

      case 'linkNameTranslated':
        $value = $link->getTitle();
        break;

      case 'target':
        $options = $link->getOptions();
        $value = isset($options['attributes']['target']) ? $options['attributes']['target'] : '';
        break;

      case 'linkUrl':
        if (!$external) {
          $urlstring = $url->toString(TRUE)->getGeneratedUrl() ? $url->toString(TRUE)->getGeneratedUrl() : '';
          if (strpos($urlstring, '#sections-') !== FALSE || strpos($urlstring, '?') !== FALSE) {
            $value = $url->toString(TRUE)->getGeneratedUrl();
          }
          else {
            try {
              $link_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/' . $url->getInternalPath());
              $value = $link_alias ? $link_alias : NULL;
            }
            catch (\Exception $e) {
              $value = '/';
            }
          }
        }

        if (!$routed) {
          $url->setAbsolute(FALSE);
          $value = $url
            ->toString(TRUE)
            ->getGeneratedUrl();
        }

        if (!$existing) {
          $value = Url::fromUri($url->getUri())->toString(TRUE)->getGeneratedUrl();
        }
        $value = str_replace('/web/', '/', $value);
        break;

      case 'className':
        $options = $link->getOptions();
        $value = isset($options['attributes']['class']) ? $options['attributes']['class'] : '';
        break;

      case 'spa':
        $value = TRUE;
        if ($external) {
          $value = FALSE;
        }

        break;

      case 'nofollow':
        $value = '0';
        if ($external) {
          // $value = NorthEastUrlHelper::nofollow($returnArray['linkUrl']);
        }

        break;

      case 'field_image':
        $menuitem = MenuItemExtrasMenuLinkContent::load($link->getMetaData()[entity_id]);
        if (!empty($menuitem) && !empty($menuitem->get('field_image'))) {
          $imgAlt = $menuitem->get('field_image')->getValue()[0]['alt'];
          $imgTitle = $menuitem->get('field_image')->getValue()[0]['title'];
          $imageobj = !empty($menuitem->get('field_image')->target_id) ? File::load($menuitem->get('field_image')->target_id) : [];
          if (!empty($imageobj)) {
            $image = file_create_url($imageobj->getFileUri());
          }
        }
        $value = [
          'image' => $image,
          'alt' => $imgAlt,
          'title' => $imgTitle,
        ];
        break;

      default:
        $value = NULL;
    }
    $returnArray[$key] = $value;
  }

  /**
   * Generate the social icons array we can use in JSON.
   *
   * @param array $items
   *   The already created items.
   */
  public static function getSocialIcons() {
    // Get Social Icons from configurations.
    $storage = \Drupal::service('entity_type.manager')->getStorage('sws_configurations');
    $ids = $storage->getQuery()
      ->condition('type', 'sws_social_icons')
      ->execute();
    foreach ($ids as $id) {
      $entity = $storage->load($id);
      $title = $entity->field_title->value;
      $url = $entity->field_url->value;
      $target_id = $entity->get('field_logo')->getValue()[0]['target_id'];
      $fileobj = !empty($target_id) ? File::load((int) $target_id) : [];
      $logoLink = !empty($fileobj) ? file_create_url($fileobj->getFileUri()) : '';
      $social_icons_data[] = [
        'title' => $title,
        'logo' => $logoLink,
        'url' => $url,
      ];
    }

    return $social_icons_data;
  }

}
