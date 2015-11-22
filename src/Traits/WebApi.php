<?php
/**
 * @file
 * Contains trait class.
 */

namespace NuvoleWeb\Drupal\Behat\Traits;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as Assertions;

/**
 * Trait WebApi.
 *
 * This trait is an adapted copy of the behat web-api-extension to work with
 * the mink browser and thus using the session from it.
 *
 * @package Nuvole\Drupal\Behat\Traits
 */
trait WebApi {

  /**
   * Request paramenters.
   *
   * @var array
   */
  private $request = array();

  /**
   * Response object reference.
   *
   * @var \Symfony\Component\BrowserKit\Response
   */
  private $response;

  /**
   * List of placeholders to be replaced in URL, request or response body.
   *
   * @var array
   */
  private $placeHolders = array();

  /**
   * Adds Basic Authentication header to next request.
   *
   * @param string $username
   * @param string $password
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
   * @param string $name  header name
   * @param string $value header value
   *
   * @Given /^I set header "([^"]*)" with value "([^"]*)"$/
   */
  public function iSetHeaderWithValue($name, $value) {
    $this->addHeader($name, $value);
  }

  /**
   * Sends HTTP request to specific relative URL.
   *
   * @param string $method request method
   * @param string $url    relative url
   *
   * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)"$/
   */
  public function iSendARequest($method, $url) {
    $url = $this->prepareUrl($url);
    $this->request['method'] = $method;
    $this->request['uri'] = $url;

    $this->sendRequest();
  }

  /**
   * Sends HTTP request to specific URL with field values from Table.
   *
   * @param string    $method request method
   * @param string    $url    relative url
   * @param TableNode $post   table of post values
   *
   * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with values:$/
   */
  public function iSendARequestWithValues($method, $url, TableNode $post) {
    $url = $this->prepareUrl($url);
    $fields = array();

    foreach ($post->getRowsHash() as $key => $val) {
      $fields[$key] = $this->replacePlaceHolder($val);
    }

    $this->request['method'] = $method;
    $this->request['uri'] = $url;
    $this->request['content'] = json_encode($fields);

    $this->sendRequest();
  }

  /**
   * Sends HTTP request to specific URL with raw body from PyString.
   *
   * @param string       $method request method
   * @param string       $url    relative url
   * @param PyStringNode $string request body
   *
   * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with body:$/
   */
  public function iSendARequestWithBody($method, $url, PyStringNode $string) {
    $url = $this->prepareUrl($url);
    $string = $this->replacePlaceHolder(trim($string));

    $this->request['method'] = $method;
    $this->request['uri'] = $url;
    $this->request['content'] = $string;

    $this->sendRequest();
  }

  /**
   * Sends HTTP request to specific URL with form data from PyString.
   *
   * @param string       $method request method
   * @param string       $url    relative url
   * @param PyStringNode $body   request body
   *
   * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with form data:$/
   */
  public function iSendARequestWithFormData($method, $url, PyStringNode $body) {
    $url = $this->prepareUrl($url);
    $body = $this->replacePlaceHolder(trim($body));

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
   * @param string $code status code
   *
   * @Then /^(?:the )?response code should be (\d+)$/
   */
  public function theResponseCodeShouldBe($code) {
    $expected = intval($code);
    $actual = intval($this->response->getStatus());
    Assertions::assertSame($expected, $actual);
  }

  /**
   * Checks that response body contains specific text.
   *
   * @param string $text
   *
   * @Then /^(?:the )?response should contain "([^"]*)"$/
   */
  public function theResponseShouldContain($text) {
    $expectedRegexp = '/' . preg_quote($text) . '/i';
    $actual = (string) $this->response->getContent();
    Assertions::assertRegExp($expectedRegexp, $actual);
  }

  /**
   * Checks that response body doesn't contains specific text.
   *
   * @param string $text
   *
   * @Then /^(?:the )?response should not contain "([^"]*)"$/
   */
  public function theResponseShouldNotContain($text) {
    $expectedRegexp = '/' . preg_quote($text) . '/';
    $actual = (string) $this->response->getContent();
    Assertions::assertNotRegExp($expectedRegexp, $actual);
  }

  /**
   * Checks that response body contains JSON from PyString.
   *
   * Do not check that the response body /only/ contains the JSON from PyString,
   *
   * @param PyStringNode $jsonString
   *
   * @throws \RuntimeException
   *
   * @Then /^(?:the )?response should contain json:$/
   */
  public function theResponseShouldContainJson(PyStringNode $jsonString) {
    $text = $this->replacePlaceHolder($jsonString->getRaw());
    $etalon = json_decode($text, TRUE);
    $actual = json_decode($this->response->getContent(), TRUE);

    if (null === $etalon) {
      throw new \RuntimeException(
        "Can not convert etalon to json:\n" . $this->replacePlaceHolder($jsonString->getRaw())
      );
    }

    Assertions::assertGreaterThanOrEqual(count($etalon), count($actual));
    foreach ($etalon as $key => $needle) {
      Assertions::assertArrayHasKey($key, $actual);
      Assertions::assertEquals($etalon[$key], $actual[$key]);
    }
  }

  /**
   * Prints last response body.
   *
   * @Then print response
   */
  public function printResponse() {
    $request = $this->request;
    $response = $this->response;

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
   *
   * @return string
   */
  private function prepareUrl($url) {
    return ltrim($this->replacePlaceHolder($url), '/');
  }

  /**
   * Sets place holder for replacement.
   *
   * You can specify placeholders, which will
   * be replaced in URL, request or response body.
   *
   * @param string $key   token name
   * @param string $value replace value
   */
  public function setPlaceHolder($key, $value) {
    $this->placeHolders[$key] = $value;
  }

  /**
   * Replaces placeholders in provided text.
   *
   * @param string $string
   *
   * @return string
   */
  protected function replacePlaceHolder($string) {
    foreach ($this->placeHolders as $key => $val) {
      $string = str_replace($key, $val, $string);
    }

    return $string;
  }

  /**
   * Adds header
   *
   * @param string $name
   * @param string $value
   */
  protected function addHeader($name, $value) {
    $this->getClient()->setHeader($name, $value);
  }

  /**
   * Removes a header identified by $headerName
   *
   * @param string $headerName
   */
  protected function removeHeader($headerName) {
    $this->getClient()->removeHeader($headerName);
  }

  /**
   * Send request to web service.
   */
  private function sendRequest() {
    // Add defaults
    $request = $this->request + [
      'method' => 'GET',
      'uri' => '',
      'parameters' => [],
      'files' => [],
      'server' => [],
      'content' => NULL,
      'changeHistory' => TRUE,
    ];
    $this->getClient()->request($request['method'], $request['uri'], $request['parameters'], $request['files'], $request['server'], $request['content'], $request['changeHistory']);
    $this->response = $this->getClient()->getResponse();
  }

  /**
   * @return \Behat\Mink\Driver\Goutte\Client
   */
  private function getClient() {
    return  $client = $this->getMink()->getSession()->getDriver()->getClient();
  }


}
