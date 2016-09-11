<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Traits;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use \Symfony\Component\BrowserKit\Response;
use PHPUnit_Framework_Assert as Assertions;

/**
 * Trait WebApi.
 *
 * This trait is an adapted copy of the behat web-api-extension to work with
 * the mink browser and thus using the session from it.
 *
 * @method \Behat\Mink\Mink getMink()
 *
 * @package Nuvole\Drupal\Behat\Traits
 */
trait WebApi {

  /**
   * Request parameters.
   *
   * @var array
   */
  private $request = array();

  /**
   * Request headers.
   *
   * @var array
   */
  private $headers = array();

  /**
   * Response object reference.
   *
   * @var Response
   */
  private $response;

  /**
   * List of placeholders to be replaced in URL, request or response body.
   *
   * @var array
   */
  private $placeholders = array();

  /**
   * CSRF authentication token.
   *
   * @var null
   */
  private $token = NULL;

  /**
   * Adds Basic Authentication header to next request.
   *
   * @Given /^I am authenticating as "([^"]*)" with "([^"]*)" password$/
   */
  public function iAmAuthenticatingAs($username, $password) {
    $this->removeHeader('Authorization');
    $authorization = base64_encode($username . ':' . $password);
    $this->addHeader('Authorization', 'Basic ' . $authorization);
  }

  /**
   * Sets a HTTP Header.
   *
   * @param string $name
   *   Header name.
   * @param string $value
   *   Header value.
   *
   * @Given /^I set header "([^"]*)" with value "([^"]*)"$/
   */
  public function iSetHeaderWithValue($name, $value) {
    $this->addHeader($name, $value);
  }

  /**
   * Sends HTTP request to specific relative URL.
   *
   * @param string $method
   *   Request method.
   * @param string $url
   *   Relative URL.
   *
   * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)"$/
   */
  public function assertSendRequest($method, $url) {
    $url = $this->prepareUrl($url);
    $this->request['method'] = $method;
    $this->request['uri'] = $url;

    $this->sendRequest();
  }

  /**
   * Sends HTTP request to specific URL with field values from Table.
   *
   * @param string $method
   *   Request method.
   * @param string $url
   *   Relative URL.
   * @param TableNode $post
   *   Table of post values.
   *
   * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with values:$/
   */
  public function assertSendRequestWithValues($method, $url, TableNode $post) {
    $url = $this->prepareUrl($url);
    $fields = array();

    foreach ($post->getRowsHash() as $key => $val) {
      $fields[$key] = $this->replacePlaceholder($val);
    }

    $this->request['method'] = $method;
    $this->request['uri'] = $url;
    $this->request['content'] = json_encode($fields);

    $this->sendRequest();
  }

  /**
   * Sends HTTP request to specific URL with raw body from PyString.
   *
   * @param string $method
   *   Request method.
   * @param string $url
   *   Relative URL.
   * @param PyStringNode $string
   *   Request body.
   *
   * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with body:$/
   * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with "([^"]+)" body:$/
   */
  public function assertSendRequestWithBody($method, $url, PyStringNode $string, $format = 'json') {
    $url = $this->prepareUrl($url);
    $string = $this->replacePlaceholder(trim($string));

    $this->request['method'] = $method;
    $this->request['uri'] = $url;
    $this->request['content'] = $string;

    $this->addHeader('Content-Type', "application/$format");
    $this->addHeader('Accept', "application/$format");

    $this->sendRequest();
  }

  /**
   * Sends HTTP request to specific URL with form data from PyString.
   *
   * @param string $method
   *   Request method.
   * @param string $url
   *   Relative URL.
   * @param PyStringNode $body
   *   Request body.
   *
   * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with form data:$/
   */
  public function assertSendRequestWithFormData($method, $url, PyStringNode $body) {
    $url = $this->prepareUrl($url);
    $body = $this->replacePlaceholder(trim($body));

    // TODO: make sure this results in the desired request.
    $fields = array();
    parse_str(implode('&', explode("\n", $body)), $fields);

    $this->request['method'] = $method;
    $this->request['uri'] = $url;
    $this->request['content'] = http_build_query($fields);

    $this->sendRequest();
  }

  /**
   * Checks that response has specific status code.
   *
   * @param string $code
   *   Status code.
   *
   * @throws \Exception
   *    If response codes do not match.
   *
   * @Then /^(?:the )?response code should be (\d+)$/
   */
  public function theResponseCodeShouldBe($code) {
    $expected = intval($code);
    $actual = intval($this->getResponse()->getStatus());
    try {
      Assertions::assertSame($expected, $actual);
    }
    catch (\Exception $e) {
      $this->printResponse();
      throw new \Exception("Response returned $actual while $expected was expected.");
    }
  }

  /**
   * Checks that response body contains JSON from PyString.
   *
   * Do not check that the response body /only/ contains the JSON from PyString.
   *
   * @param PyStringNode $jsonString
   *    JSON string.
   *
   * @throws \RuntimeException
   *    If JSON string cannot be parsed.
   *
   * @Then /^(?:the )?response should contain json:$/
   */
  public function theResponseShouldContainJson(PyStringNode $jsonString) {
    $text = $this->replacePlaceholder($jsonString->getRaw());
    $expected = json_decode($text, TRUE);
    $actual = $this->parseResponse($this->getResponse());

    if (NULL === $expected) {
      throw new \RuntimeException(
        "Can not convert expected to json:\n" . $this->replacePlaceholder($jsonString->getRaw())
      );
    }

    Assertions::assertGreaterThanOrEqual(count($expected), count($actual));
    foreach ($expected as $key => $needle) {
      Assertions::assertArrayHasKey($key, $actual);
      Assertions::assertEquals($expected[$key], $actual[$key]);
    }
  }

  /**
   * Get CSRF Token from service endpoint.
   *
   * Token wil be automatically set to each request if found.
   *
   * @see WebApi::sendRequest()
   *
   * @Given I get the authentication token from :url
   */
  public function iGetTheAuthenticationTokenFrom($url) {
    $url = $this->prepareUrl($url);
    $this->request['method'] = 'GET';
    $this->request['uri'] = $url;

    $response = $this->sendRequest()->getResponse();
    Assertions::assertSame(200, $response->getStatus());
    $content = $this->parseResponse($response);
    $this->token = $content['X-CSRF-Token'];
  }

  /**
   * Prints last response body.
   *
   * @Then print response
   */
  public function printResponse() {
    $request = $this->request;
    $response = $this->getResponse();

    echo sprintf(
      "%s %s => %d:\n%s",
      $request['method'],
      $request['uri'],
      $response->getStatus(),
      $response->getContent()
    );
  }

  /**
   * Prepare URL by replacing placeholders and trimming slashes.
   *
   * @param string $url
   *    URL string.
   *
   * @return string
   *    Processed URL string.
   */
  private function prepareUrl($url) {
    return ltrim($this->replacePlaceholder($url), '/');
  }

  /**
   * Sets place holder for replacement.
   *
   * You can specify placeholders, which will
   * be replaced in URL, request or response body.
   *
   * @param string $key
   *   Token name.
   * @param string $value
   *   Replace value.
   */
  public function setPlaceholder($key, $value) {
    $this->placeholders[$key] = $value;
  }

  /**
   * Replaces placeholders in provided text.
   *
   * @param string $string
   *    Placeholder string.
   *
   * @return string
   *    String with replaced placeholders.
   */
  protected function replacePlaceholder($string) {
    foreach ($this->placeholders as $key => $val) {
      $string = str_replace($key, $val, $string);
    }
    return $string;
  }

  /**
   * Add headers.
   *
   * @param string $name
   *    Header name.
   * @param string $value
   *    Header values.
   */
  protected function addHeader($name, $value) {
    $this->headers[$name] = $value;
    $this->getClient()->setHeader($name, $value);
  }

  /**
   * Removes a header given its name.
   *
   * @param string $header_name
   *    Header name.
   */
  protected function removeHeader($header_name) {
    if (isset($this->headers[$header_name])) {
      unset($this->headers[$header_name]);
      $this->getClient()->removeHeader($header_name);
    }
  }

  /**
   * Remove all headers previously set.
   */
  protected function removeAllHeaders() {
    $headers = array_keys($this->headers);
    foreach ($headers as $name) {
      $this->removeHeader($name);
    }
  }

  /**
   * Send request to web service.
   *
   * @return $this
   *    Return this object after performing the request.
   */
  protected function sendRequest() {
    drupal_static_reset();
    // Add defaults.
    $request = $this->request + [
      'method' => 'GET',
      'uri' => '',
      'parameters' => [],
      'files' => [],
      'server' => [],
      'content' => NULL,
      'changeHistory' => TRUE,
    ];

    if ($this->token) {
      $this->addHeader('X-CSRF-Token', $this->token);
    }

    // Replace entity ID placeholders in request content.
    foreach ($this->nodes as $node) {
      $this->setPlaceholder("[id:$node->title]", $node->nid);
    }
    foreach ($this->users as $user) {
      $this->setPlaceholder("[id:$user->name]", $user->uid);
    }
    foreach ($this->terms as $term) {
      $this->setPlaceholder("[id:$term->name]", $term->tid);
    }
    if (isset($this->comments)) {
      foreach ($this->comments as $comment) {
        $this->setPlaceholder("[id:$comment->subject]", $comment->cid);
      }
    }

    $request['content'] = $this->replacePlaceholder($request['content']);
    $request['uri'] = $this->replacePlaceholder($request['uri']);

    // Request URI must be absolute for Mink to work properly with subsequent
    // service requests in the same scenario.
    $request['uri'] = url($request['uri'], ['absolute' => TRUE]);
    $request['uri'] = urldecode($request['uri']);
    $this->getClient()->request($request['method'], $request['uri'], $request['parameters'], $request['files'], $request['server'], $request['content'], $request['changeHistory']);
    $this->response = $this->getClient()->getResponse();
    $this->removeAllHeaders();
    return $this;
  }

  /**
   * Get response after firing a request.
   *
   * @return Response
   *    Response object.
   */
  protected function getResponse() {
    return $this->response;
  }

  /**
   * Return parsed response content.
   *
   * @param Response $response
   *    Response object.
   *
   * @return array
   *    Parsed response content.
   */
  protected function parseResponse(Response $response) {
    return json_decode($response->getContent(), TRUE);
  }

  /**
   * Get current Mink session client.
   *
   * @return \Goutte\Client
   *    Return client object.
   */
  protected function getClient() {
    /** @var \Behat\Mink\Driver\GoutteDriver $driver */
    $driver = $this->getMink()->getSession()->getDriver();
    return $driver->getClient();
  }

}
