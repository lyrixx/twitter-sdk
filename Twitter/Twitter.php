<?php

namespace Lyrixx\Twitter;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Message\Request;
use Lyrixx\Twitter\Exception\ApiClientException;
use Lyrixx\Twitter\Exception\ApiServerException;
use Psr\Log\LoggerInterface;

/**
 * Twitter.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class Twitter
{
    const END_POINT = 'https://api.twitter.com/1.1';

    private $consumerKey;
    private $consumerSecret;
    private $accessToken;
    private $accessTokenSecret;
    private $endPoint;
    private $oAuth;
    private $client;
    private $logger;

    public function __construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret, $endPoint = null, OAuth $oAuth = null, Client $client = null, LoggerInterface $logger = null)
    {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->accessToken = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;
        $this->endPoint = $endPoint ?: self::END_POINT;
        $this->oAuth = $oAuth ?: new OAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
        $this->client = $client ?: new Client();
        $this->logger = $logger;
    }

    public function query($method, $resource, array $parameters = array(), array $headers = array())
    {
        $request = $this->createRequest($method, $resource, $parameters, $headers);

        $response = $this->send($request);

        return $response;
    }

    public function createRequest($method, $resource, array $parameters = array(), array $headers = array())
    {
        $method = strtoupper($method);

        $url = sprintf('%s/%s.json', $this->endPoint, $resource);

        if (!array_key_exists('Authorization', $headers)) {
            $headers['Authorization'] = $this->oAuth->generateAuthorizationHeader($method, $url, $parameters);
        }
        $headers['Accept'] = '*/*';

        if ($parameters && 'GET' === $method) {
            $url .= '?'.http_build_query($parameters);
            $parameters = null;
        }

        return $this->client->createRequest($method, $url, $headers, $parameters);
    }

    private function send(Request $request)
    {
        try {
            $this->logger and $this->logger->debug(sprintf('%s "%s"', $request->getMethod(), $request->getUrl()));
            $this->logger and $this->logger->debug(sprintf("Request:\n%s", (string) $request));
            $response = $this->client->send($request);
            $this->logger and $this->logger->debug(sprintf("Response:\n%s", (string) $response));

            return $response;
        } catch (ClientErrorResponseException $e) {
            $this->logException($e);

            $statusCode = $e->getResponse()->getStatusCode();
            $reasonPhrase = $e->getResponse()->getReasonPhrase();
            $message = sprintf('The request is not valid (status code: "%d", reason phrase: "%s").', $statusCode, $reasonPhrase);

            throw new ApiClientException($message, 0, $e);

        } catch (BadResponseException $e) {
            $this->logException($e);

            throw new ApiServerException('Something went wrong with upstream.', 0, $e);
        }
    }

    private function logException(\Exception $e)
    {
        $message = sprintf("Exception: Class: \"%s\", Message: \"%s\", Response:\n%s",
            get_class($e),
            $e->getMessage(),
            (string) $e->getResponse()
        );
        $this->logger and $this->logger->error($message, array('exception' => $e));
    }
}
