<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Exception\RequestException;

/**
 * @see http://docs.guzzlephp.org/en/stable/testing.html
 * @see https://christrombley.me/blog/testing-guzzle-6-responses-with-laravel
 */
trait MockGuzzleClient
{
    public $guzzleMockHandler;

    /**
     * Set up for mocking Guzzle HTTP client.
     *
     * @param Response|RequestException $responses
     * @return void
     */
    public function setupGuzzleClientMock($responses = null)
    {
        $this->guzzleMockHandler = new MockHandler($responses);
        $handler = HandlerStack::create($this->guzzleMockHandler);
        $client = new Client(['handler' => $handler]);

        // Inject to Laravel service container
        $this->app->instance(Client::class, $client);
    }

    /**
     * Add responses to Guzzle client's mock queue.
     * Pass a Response or RequestException instance, or an array of them.
     *
     * @param array|Response|RequestException|integer $response
     * @param array       $headers
     * @param string      $body
     * @param string      $version
     * @param string|null $reason
     * @return void
     */
    public function appendToGuzzleQueue($response = 200, $headers = [], $body = '', $version = '1.1', $reason = null)
    {
        if (! $this->guzzleMockHandler) {
            $this->setupGuzzleClientMock();
        }

        if (is_array($response)) {
            foreach ($response as $single) {
                $this->appendToGuzzleQueue($single);
            }
            return;
        }

        if ($response instanceof Response || $response instanceof RequestException) {
            return $this->guzzleMockHandler->append($response);
        }

        return $this->guzzleMockHandler->append(new Response($response, $headers, $body, $version, $reason));
    }
}
