<?php
namespace Drupal\custom_apis\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
/**
 * Provides a Demo Resource
 *
 * @RestResource(
 *   id = "articels",
 *   label = @Translation("Article listings"),
 *   uri_paths = {
 *     "canonical" = "get/articels"
 *   }
 * )
 */
 
class GetArticels extends ResourceBase {

  /**
   * Responds to entity GET requests.
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    $response = ['message' => 'Hello, this is a rest service'];
    return new ResourceResponse($response);
  }
}