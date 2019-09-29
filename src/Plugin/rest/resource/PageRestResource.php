<?php

namespace Drupal\page_api\Plugin\rest\resource;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "page_rest_resource",
 *   label = @Translation("Page rest resource"),
 *   uri_paths = {
 *     "canonical" = "/page_json/{API}/{id}"
 *   }
 * )
 */
class PageRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var AccountProxyInterface
   */
  protected $currentUser;
  
  public $config;

  /**
   * Constructs a new PageRestResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param LoggerInterface $logger
   *   A logger instance.
   * @param AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    ConfigFactory $config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->config = $config;
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
      $container->get('logger.factory')->get('page_api'),
      $container->get('current_user'),
      $container->get('config.factory')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws HttpException
   *   Throws exception expected.
   */
  public function get($API, $id) {
    $config = $this->config->get('system.site');
    $api_key = $config->get('siteapikey');
    $entity = Node::load($id);
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if ($API != $api_key || $entity->getType() != 'page' || !$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    
    return new ResourceResponse($entity, 200);
  }

}
