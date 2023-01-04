<?php


namespace Drupal\onsched_api\Rest;


use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Queue\RequeueException;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\onsched_api\Rest\RestException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use OAuth\OAuth2\Token\TokenInterface;
use Drupal\Component\Datetime\TimeInterface;

class RestClient implements RestClientInterface {

  private $REST_API_URL_SANDBOX;
  private $REST_API_URL_LIVE;
  private $TOKEN_API_URL_SANDBOX;
  private $TOKEN_API_URL_LIVE;
  private $CLIENT_ID_SANDBOX;
  private $CLIENT_ID_LIVE;
  private $CLIENT_SECRET1;
  private $CLIENT_SECRET2;

//  const TOKEN_API_URL_SANDBOX = '';
//  const TOKEN_API_URL_LIVE = '';
//
//  const CLIENT_ID_SANDBOX = '';
//  const CLIENT_ID_LIVE = '';
//
//  const CLIENT_SECRET1 = '';
//  const CLIENT_SECRET2 = '';


  /**
   * Response object.
   *
   * @var \GuzzleHttp\Psr7\Response
   */
  public Response $response;

  /**
   * GuzzleHttp client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * Config factory service.
   *
   * @var ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * OnSched API URL.
   *
   * @var Url
   */
  protected Url $apiUrl;

  /**
   *  Immutable config object.
   *
   * @var ImmutableConfig
   */
  protected ImmutableConfig $immutableConfig;

  /**
   * The state service.
   *
   * @var StateInterface
   */
  protected StateInterface $state;

  /**
   * The cache service.
   *
   * @var CacheBackendInterface
   */
  protected CacheBackendInterface $cache;

  /**
   * The JSON serializer service.
   *
   * @var Json
   */
  protected Json $json;


//  /**
//   * Active auth token.
//   *
//   * @var TokenInterface
//   */
//  protected TokenInterface $authToken;

  /**
   * HTTP OAuth token, standing in for full interface object
   *
   * @var string
   */
  private string $authToken;

  /**
   * HTTP client options.
   *
   * @var array
   */
  protected array $httpClientOptions;

  const CACHE_LIFETIME = 300;
  const LONGTERM_CACHE_LIFETIME = 86400;

  /**
   * @var TimeInterface
   */
  private TimeInterface $time;

  /**
   * @var String
   */
  private string $apiAuthUrl;

  /**
   * Constructor which initializes the consumer.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The GuzzleHttp Client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache service.
   * @param \Drupal\Component\Serialization\Json $json
   *   The JSON serializer service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The Time service.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config_factory, StateInterface $state, CacheBackendInterface $cache, Json $json, TimeInterface $time) {
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client;
    $this->immutableConfig = $this->configFactory->get('onsched_api.settings');
    $this->state = $state;
    $this->cache = $cache;
    $this->json = $json;
    $this->time = $time;
    $this->httpClientOptions = [];

    $this->REST_API_URL_SANDBOX = $this->immutableConfig->get('rest_api_url_sandbox');
    $this->REST_API_URL_LIVE = $this->immutableConfig->get('rest_api_url_live');
    $this->TOKEN_API_URL_SANDBOX = $this->immutableConfig->get('token_api_url_sandbox');
    $this->TOKEN_API_URL_LIVE = $this->immutableConfig->get('token_api_url_live');
    $this->CLIENT_ID_SANDBOX = $this->immutableConfig->get('client_id_sandbox');
    $this->CLIENT_ID_LIVE = $this->immutableConfig->get('client_id_live');
    $this->CLIENT_SECRET1 = $this->immutableConfig->get('client_secret_1');
    $this->CLIENT_SECRET2 = $this->immutableConfig->get('client_secret_2');

    return $this;
  }

  /**
   * Refresh the auth token
   */
  protected function refreshAuthToken(): void {
    try {
      $headers = [
        'client_secret' => '',
        'client_id'     => '',
        'scope'         => 'OnSchedApi',
        'grant_type'    => 'client_credentials'
      ];
      $data = json_encode([]);

      $this->response = new RestResponse($this->httpRequest($this->apiAuthUrl, $data, $headers));
    } catch (RequestException $e) {
      // RequestException gets thrown for any response status but 2XX.
      //$this->response = $e->getResponse();

      // Any exceptions besides 401 get bubbled up.
      if (!$this->response || $this->response->getStatusCode() != 401) {
        throw new RestException($this->response, $e->getMessage(), $e->getCode(), $e);
      }
      if ($body = $this->response->getBody()) {
        $this->authToken = json_decode($body->getContents())->token; // omg TODO
      }
    }
  }
  /**
   * {@inheritdoc}
   */
  public function apiCall($path, array $params = [], $method = 'GET', $returnObject = FALSE): mixed {

    try {
      $this->response = new RestResponse($this->apiHttpRequest($this->apiUrl, $params, $method));
    }
    catch (RequestException $e) {
      // RequestException gets thrown for any response status but 2XX.
      $this->response = $e->getResponse();

      // Any exceptions besides 401 get bubbled up.
      if (!$this->response || $this->response->getStatusCode() != 401) {
        throw new RestException($this->response, $e->getMessage(), $e->getCode(), $e);
      }
    }

    if ($this->response->getStatusCode() == 401) {
      // The session ID or OAuth token used has expired or is invalid: refresh
      // token. If refresh_token() throws an exception, or if apiHttpRequest()
      // throws anything but a RequestException, let it bubble up.
      $this->refreshAuthToken();

      try {
        $this->response = new RestResponse($this->apiHttpRequest($this->apiUrl, $params, $method));
      }
      catch (RequestException $e) {
        $this->response = $e->getResponse();
        throw new RestException($this->response, $e->getMessage(), $e->getCode(), $e);
      }
    }

    if (empty($this->response)
      || ((int) floor($this->response->getStatusCode() / 100)) != 2) {
      throw new RestException($this->response, $this->t('Unknown error occurred during API call "@call": status code @code : @reason', [
        '@call' => $path,
        '@code' => $this->response->getStatusCode(),
        '@reason' => $this->response->getReasonPhrase(),
      ]));
    }

    if ($returnObject) {
      return $this->response;
    }
    else {
      return json_decode($this->response->getBody());
    }
  }

  /**
   * Private helper to issue an OnSched API request.
   *
   * @param string $url
   *   Fully-qualified URL to resource.
   * @param array $params
   *   Parameters to provide.
   * @param string $method
   *   Method to initiate the call, such as GET or POST.  Defaults to GET.
   *
   * @return \GuzzleHttp\Psr7\Response
   *   Response object.
   *
   * @throws \Exception
   * @throws RequestException
   */
  protected function apiHttpRequest(string $url, array $params, string $method): Response {
    if (!$this->authToken) {
      throw new \Exception(t('Missing OAuth Token'));
    }

    $headers = [
      'Authorization' => 'OAuth ' . $this->authToken,
      'Content-type' => 'application/json',
    ];

    $data = NULL;

    if (!empty($params)) {
      $data = $this->json->encode($params);
    }
    return $this->httpRequest($url, $data, $headers, $method);
  }

  /**
   * Make the HTTP request. Wrapper around drupal_http_request().
   *
   * @param string $url
   *   Path to make request from.
   * @param null $data
   *   The request body.
   * @param array $headers
   *   Request headers to send as name => value.
   * @param string $method
   *   Method to initiate the call, such as GET or POST.  Defaults to GET.
   *
   * @return \GuzzleHttp\Psr7\Response
   *   Response object.
   *
   * @throws RequestException Request exception.
   */
  protected function httpRequest(string $url, $data = NULL, array $headers = [], $method = 'GET'): Response {
    // Build the request, including path and headers. Internal use.
    $args = NestedArray::mergeDeep($this->httpClientOptions, ['headers' => $headers, 'body' => $data]);
    return $this->httpClient->$method($url, $args);
  }

  /**
   * {@inheritdoc}
   */
  public function setHttpClientOptions(array $options): RestClientInterface {
    $this->httpClientOptions = NestedArray::mergeDeep($this->httpClientOptions, $options);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setHttpClientOption($option_name, $option_value) {
    $this->httpClientOptions[$option_name] = $option_value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getHttpClientOptions(): array {
    return $this->httpClientOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function getHttpClientOption($option_name) {
    return $this->httpClientOptions[$option_name];
  }

  /**
   * Make the HTTP request. Wrapper around drupal_http_request().
   */
  public function isInit(): bool {
    if (!$this->authToken) {
      return FALSE;
    }
    // Make a new authenticator class or use the core one TODO
//    if (!$this->authToken) {
//      $this->authToken = $this->authenticator->refreshToken();
//      return isset($this->authToken);
//    }
    return TRUE;
  }
}