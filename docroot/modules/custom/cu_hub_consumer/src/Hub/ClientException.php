<?php

namespace Drupal\cu_hub_consumer\Hub;

use GuzzleHttp\Exception\ClientException as GuzzleClientException;

/**
 * Exception thrown if hub data cannot be fetched.
 *
 * @internal
 *   This is an internal part of the hub system and should only be used by
 *   hub-related code.
 */
class ClientException extends \Exception {

  /**
   * The URL of the resource.
   *
   * @var string
   */
  protected $url;

  /**
   * ClientException constructor.
   *
   * @param string $message
   *   The exception message.
   * @param string $url
   *   The URL of the endpoint. Can be the actual endpoint URL or the canonical
   *   URL.
   * @param array $data
   *   (optional) The raw endpoint data, if available.
   * @param \Exception $previous
   *   (optional) The previous exception, if any.
   */
  public function __construct($message, $url, \Exception $previous = NULL) {
    $this->url = $url;
    parent::__construct($message, 0, $previous);
  }

  /**
   * Gets the URL of the endopint which caused the exception.
   *
   * @return string
   *   The URL of the endpoint.
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * Returns a previous guzzle client exception, if it exists.
   *
   * @return \Throwable | NULL
   */
  public function getGuzzleClientException() {
    $e = $this;

    // Go up the prev chain till we find a client exception.
    while ($e = $e->getPrevious()) {
      if ($e instanceof GuzzleClientException) {
        return $e;
      }
    }
  }

  /**
   * Returns the response code from the server, if it exists.
   *
   * @return mixed
   */
  public function getResponseCode() {
    if ($client_exception = $this->getGuzzleClientException()) {
      return $client_exception->getCode();
    }
  }

}
