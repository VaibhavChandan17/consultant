<?php

namespace Drupal\new_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Connection;
use Drupal\sws_rest_api\Utils\MenuHelper;
use Drupal\Core\Cache\CacheableMetadata;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\sws_configs\Helper\CommonHelper;

/**
 * Provides a SWS Header Menu API.
 *
 * @RestResource(
 *   id = "demo_resource",
 *   label = @Translation("Demo Resource"),
 *   uri_paths = {
 *     "canonical" = "/demo/header"
 *   }
 * )
 */
class DemoResource extends ResourceBase {

  /**
   * Drupal\Core\Database\Connection definition.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * A list of menu items.
   *
   * @var array
   */
  protected $menuItems = [];

  /**
   * A instance of the http request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    Request $request,
    Connection $connection
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->request = $request;
    $this->db = $connection;
    $this->host = \Drupal::request();
    $this->commonHelper = CommonHelper::instance();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('database')
    );
  }

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Return json responde.
   */
  public function get() {
    if (!(\Drupal::currentUser()->hasPermission('access content'))) {
      throw new AccessDeniedHttpException();
    }

    try {
      $menuName = $this->commonHelper->getMainNavigationName();
      $footerMenuName = $this->commonHelper->getFooterNavigationName();
      $this->headerItems = MenuHelper::menu($menuName, 0, 1);
      $this->footerItems = MenuHelper::menu($footerMenuName, 0, 1);
      $this->footerSocialIcons = MenuHelper::getSocialIcons();
      // Return response.
      $response = new ResourceResponse([
        'responseCode' => 200,
        'headerMenu' => array_values($this->headerItems),
        'footerMenu' => array_values($this->footerItems),
        'footerSocialIcons' => array_values($this->footerSocialIcons),
        'message' => 'Success',
      ], 200);
      $build = [
        '#cache' => [
          'contexts' => ['url.query_args'],
          'tags' => [
            'config:system.menu.main',
            'config:system.menu.footer',
          ],
          'max-age' => 1800,
        ],
      ];

      $cache_metadata = CacheableMetadata::createFromRenderArray($build);
      return $response->addCacheableDependency($cache_metadata);
    }
    catch (\Exception $e) {
      $this->logger->error('Message from @module: @message.', [
        '@module' => 'wtw_rest_api',
        '@message' => 'Error occured in North East Header Section API. ' . $e->getMessage(),
      ]);

      return new ResourceResponse([
        'responseCode' => 500,
        'headerMenu' => [],
        'social' => [],
        'message' => $e->getMessage(),
      ], 200);

    }
  }

}
