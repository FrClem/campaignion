<?php

namespace Drupal\campaignion_newsletters_dotmailer\Rest;

/**
 * This a simple JSON REST Client based on drupal_http_request().
 */
class Client {

  protected $endpoint;
  protected $options;

  /**
   * @param string $endpoint
   *   The base-URL for this API.
   * @param array $options
   *   Default options for all requests ie. credentials for Basic Auth.
   */
  public function __construct($endpoint, $options = []) {
    $this->endpoint = $endpoint;
    $this->options = $options;
  }

  /**
   * Send a GET request to the API.
   *
   * @param string $path
   *   The path to call.
   * @param array $query
   *   An array of query parameters.
   * @param array $options
   *   Options for @see drupal_http_request().
   *
   * @return array
   *   The decoded data from the response.
   */
  public function get($path, $query = [], $options = []) {
    $options['method'] = 'GET';
    return $this->send($path, $query, NULL, $options);
  }

  /**
   * Send a POST request to the API.
   *
   * @param string $path
   *   The path to call.
   * @param mixed $data
   *   JSON encodeable data.
   * @param array $query
   *   An array of query parameters.
   * @param array $options
   *   Options for @see drupal_http_request().
   *
   * @return array
   *   The decoded data from the response.
   */
  public function post($path, $query = [], $data = NULL, $options = []) {
    $options['method'] = 'POST';
    return $this->send($path, $query, $data, $options);
  }

  /**
   * Send a PUT request to the API.
   *
   * @param string $path
   *   The path to call.
   * @param mixed $data
   *   JSON encodeable data.
   * @param array $query
   *   An array of query parameters.
   * @param array $options
   *   Options for @see drupal_http_request().
   *
   * @return array
   *   The decoded data from the response.
   */
  public function put($path, $query = [], $data = NULL, $options = []) {
    $options['method'] = 'PUT';
    return $this->send($path, $query, $data, $options);
  }

  /**
   * Send a DELETE request to the API.
   *
   * @param string $path
   *   The path to call.
   * @param array $query
   *   An array of query parameters.
   * @param array $options
   *   Options for @see drupal_http_request().
   *
   * @return array
   *   The decoded data from the response.
   */
  public function delete($path, $query = [], $options = []) {
    $options['method'] = 'DELETE';
    return $this->send($path, $query, NULL, $options);
  }

  /**
   * This method does the actual hard-work in this class.
   */
  protected function send($path, $query = [], $data = NULL, $options = []) {
    if ($path{0} != '/') {
      $path = '/' . $path;
    }
    if ($query) {
      $path .= '?' . http_build_query($query);
    }

    // Encode data if needed.
    if ($data) {
      $options['headers']['Content-Type'] = 'application/json';
      $options['headers']['Accept'] = 'application/json';
      if (!is_string($data)) {
        $data = drupal_json_encode($data);
      }
      $options['data'] = $data;
    }

    $url = $this->endpoint . $path;
    $options += $this->options;
    $result = drupal_http_request($url, $options);

    // Turn errors into exceptions.
    if ($result->error) {
      var_dump($result);
      throw new HttpError($result);
    }
    return drupal_json_decode($result->data);
  }

}
