<?php

namespace Lyrixx\Twitter\Tests;

use Guzzle\Http\Client;
use Guzzle\Plugin\Mock\MockPlugin;
use Lyrixx\Twitter\Twitter;

/**
 * TwitterTest.
 *
 * All results and fixture values come from
 * https://dev.twitter.com/docs/auth/creating-signature
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class TwitterTest extends \PHPUnit_Framework_TestCase
{
    private $twitter;
    private $mockPlugin;
    private $logger;

    public function setUp()
    {
        $consumerKey = 'xvz1evFS4wEEPTGEFPHBog';
        $accessToken = '370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb';
        $consumerSecret = 'kAcSOqF21Fu85e7zjz7ZN2U4ZRhfV3WpwPAoE3Z7kBw';
        $tokenSecret = 'LswwdoUaIvS8ltyTt5jkRh4J50vUPVVHtR2YPi5kE';

        $this->mockPlugin = new MockPlugin();
        $client = new Client();
        $client->addSubscriber($this->mockPlugin);

        $this->logger = $this->getMock('Psr\Log\LoggerInterface');

        $this->twitter = new Twitter($consumerKey, $accessToken, $consumerSecret, $tokenSecret, null, null, $client, $this->logger);
    }

    public function testQueryWith200Response()
    {
        $this->mockPlugin->addResponse($this->mockPlugin->getMockFile(__DIR__.'/fixtures/GET_200_user_timeline.json'));

        $this->logger
            ->expects($this->never())
            ->method('error')
        ;

        $response = $this->twitter->query('GET', 'statuses/user_timeline');

        $this->assertInstanceOf('Guzzle\Http\Message\Response', $response);
        $tweets = json_decode($response->getBody(), true);

        $this->assertCount(20, $tweets);
    }

    /**
     * @expectedException Lyrixx\Twitter\Exception\ApiClientException
     * @expectedExceptionMessage The request is not valid (status code: "401", reason phrase: "Unauthorized").
     */
    public function testQueryWith401Response()
    {
        $this->mockPlugin->addResponse($this->mockPlugin->getMockFile(__DIR__.'/fixtures/GET_401_user_timeline.json'));

        $this->logger
            ->expects($this->once())
            ->method('error')
        ;

        $this->twitter->query('GET', 'statuses/user_timeline');
    }

    /**
     * @expectedException Lyrixx\Twitter\Exception\ApiServerException
     * @expectedExceptionMessage Something went wrong with upstream.
     */
    public function testQueryWith500Response()
    {
        $this->mockPlugin->addResponse($this->mockPlugin->getMockFile(__DIR__.'/fixtures/GET_500_user_timeline.json'));

        $this->logger
            ->expects($this->once())
            ->method('error')
        ;

        $this->twitter->query('GET', 'statuses/user_timeline');
    }

    public function testCreateRequest()
    {
        $parameters = array(
            'status' => 'Hello Ladies + Gentlemen, a signed OAuth request!',
        );

        $request = $this->twitter->createRequest('POST', 'statuses/update', $parameters);

        $this->assertInstanceOf('Guzzle\Http\Message\Request', $request);
        $this->assertNotNull($request->getHeader('Authorization'));
        $this->assertSame('https://api.twitter.com/1.1/statuses/update.json', $request->getUrl());
        $this->assertSame($parameters['status'], $request->getPostField('status'));
    }

    public function tearDown()
    {
        $this->twitter = null;
        $this->mockPlugin = null;
        $this->logger = null;
    }
}
