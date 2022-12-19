<?php


namespace Drupal\onsched_api\Rest;


interface RestClientInterface {

  /**
   * Make a call to the OnSched REST API.
   *
   * @param string $path
   *   Path to resource, after the slash. Doesn't assume any base resource path
   * @param array $params
   *   Parameters to provide
   * @param string $method
   *   Method to initiate the call, such as GET or POST.  Defaults to GET.
   * @param bool $returnObject
   *   If true, return a Drupal\onsched_api\Rest\RestResponse;
   *   Otherwise, return vanilla json-decoded response body.
   *
   * @return mixed
   *   Response object or response data.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function apiCall(string $path, array $params = [], $method = 'GET', $returnObject = FALSE): mixed;


  /**
   * Check if the client is ready to perform API calls to OnSched
   *
   * @return bool
   *   FALSE if the client is not initialized. TRUE otherwise.
   */
  public function isInit(): bool;

  /**
   * Set options for Guzzle HTTP client.
   *
   * @param array $options
   *   The options to pass through to guzzle, as an associative array of option
   *   names and option values.
   *
   * @see http://docs.guzzlephp.org/en/latest/request-options.html
   *
   * @return \Drupal\onsched_api\Rest\RestClientInterface
   */
  public function setHttpClientOptions(array $options): RestClientInterface;

  /**
   * Set a single Guzzle HTTP client option.
   *
   * @param string $option_name
   *   The option name to set.
   * @param mixed $option_value
   *   The option value to set.
   *
   * @see setHttpClientOptions
   *
   * @return $this
   */
  public function setHttpClientOption($option_name, $option_value);

  /**
   * Getter for HTTP client options.
   *
   * @return mixed
   *   The client options from guzzle.
   */
  public function getHttpClientOptions();

  /**
   * Getter for a single, named HTTP client option.
   *
   * @param string $option_name
   *   The option name to get.
   *
   * @return mixed
   *   The client option from guzzle.
   */
  public function getHttpClientOption($option_name);
}