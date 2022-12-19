<?php

namespace Drupal\onsched_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\onsched_api\Rest\RestClient;

/**
 * Service description.
 */
class Onsched implements OnschedInterface {

  /**
   * @var RestClient
   */
  private RestClient $restClient;

  /**
   * @var ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * @var ImmutableConfig
   */
  private ImmutableConfig $immutableConfig;

  /**
   * Constructor which initializes the consumer.
   * @param ConfigFactoryInterface $config_factory
   * @param RestClient $rest_client
   */
  public function __construct(ConfigFactoryInterface $config_factory, RestClient $rest_client) {

    $this->configFactory = $config_factory;
    $this->immutableConfig = $this->configFactory->get('onsched_api.settings');
    $this->restClient = $rest_client;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function listAppointments($user = NULL, $query_params = NULL): array {

    $query_string = http_build_query($query_params);

    return $this->restClient->apiCall('appointments' . !empty($query_string) ? '?' . $query_string : NULL);

  }

  /**
   * {@inheritdoc}
   */
  public function getAppointment($id): array {

    return $this->restClient->apiCall($id);

  }

  /**
   * {@inheritdoc}
   */
  public function createAppointment($user, $target, $data = [], $query_params = NULL): array {

    // pull in user entities here so the calling doesn't need to know about user data structure
    //$user_resource_id = $user->getResourceId($user);
    //$target_resource_id = $user->getResourceId($target);

    $user_resource_id = $user->field_onsched_resource_id->value;
    $target_resource_id = $user->field_onsched_resource_id->value;

    $query_string = http_build_query($query_params);

    return $this->restClient->apiCall('/appointments' . !empty($query_string) ? '?' . $query_string : NULL, $data);

  }

  /**
   * {@inheritdoc}
   */
  public function deleteAppointment($id) {

    return $this->restClient->apiCall($id);

  }
}
