<?php

namespace Lyrixx\Twitter\Tests;

use Lyrixx\Twitter\OAuth;

/**
 * OAuthTest.
 *
 * All results and fixture values come from
 * https://dev.twitter.com/docs/auth/creating-signature
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class OAuthTest extends \PHPUnit_Framework_TestCase
{
    private $method;
    private $url;
    private $parameters;
    private $oauth;

    public function setUp()
    {
        $this->method = 'POST';
        $this->url = 'https://api.twitter.com/1/statuses/update.json';
        $this->parameters = array(
            'status' => 'Hello Ladies + Gentlemen, a signed OAuth request!',
            'include_entities' => true,
            'oauth_nonce' => 'kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg', // Have to harcode it because it's a generated random value
            'oauth_timestamp' => '1318622958', // Have to harcode it because it's a generated timed value
        );
        $consumerKey = 'xvz1evFS4wEEPTGEFPHBog';
        $accessToken = '370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb';
        $consumerSecret = 'kAcSOqF21Fu85e7zjz7ZN2U4ZRhfV3WpwPAoE3Z7kBw';
        $accessTokenSecret = 'LswwdoUaIvS8ltyTt5jkRh4J50vUPVVHtR2YPi5kE';
        $this->oAuth = new OAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
    }

    public function testGenerateAuthorizationHeader()
    {
        $result = $this->oAuth->generateAuthorizationHeader($this->method, $this->url, $this->parameters);

        $expected = 'OAuth oauth_consumer_key="xvz1evFS4wEEPTGEFPHBog", oauth_nonce="kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg", oauth_signature="tnnArxj06cWHq44gCs1OSKk%2FjLY%3D", oauth_signature_method="HMAC-SHA1", oauth_timestamp="1318622958", oauth_token="370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb", oauth_version="1.0"';

        $this->assertSame($expected, $result);
    }

    public function testGenerateSignature()
    {
        $object  = new \ReflectionObject($this->oAuth);
        $method = $object->getMethod('generateSignature');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->oAuth, array($this->method, $this->url, $this->parameters));

        $expected = 'tnnArxj06cWHq44gCs1OSKk/jLY=';

        $this->assertSame($expected, $result);
    }

    public function testGenerateSigninKey()
    {
        $object  = new \ReflectionObject($this->oAuth);
        $method = $object->getMethod('generateSigningKey');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->oAuth, array());

        $expected = 'kAcSOqF21Fu85e7zjz7ZN2U4ZRhfV3WpwPAoE3Z7kBw&LswwdoUaIvS8ltyTt5jkRh4J50vUPVVHtR2YPi5kE';

        $this->assertSame($expected, $result);
    }

    public function testGenerateSigningString()
    {
        $object  = new \ReflectionObject($this->oAuth);
        $method = $object->getMethod('generateSigningString');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->oAuth, array($this->method, $this->url, $this->parameters));

        $expected = 'POST&https%3A%2F%2Fapi.twitter.com%2F1%2Fstatuses%2Fupdate.json&include_entities%3Dtrue%26oauth_consumer_key%3Dxvz1evFS4wEEPTGEFPHBog%26oauth_nonce%3DkYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D1318622958%26oauth_token%3D370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb%26oauth_version%3D1.0%26status%3DHello%2520Ladies%2520%252B%2520Gentlemen%252C%2520a%2520signed%2520OAuth%2520request%2521';

        $this->assertSame($expected, $result);
    }

    public function testEncodeParameters()
    {
        $object  = new \ReflectionObject($this->oAuth);
        $method = $object->getMethod('encodeParameters');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->oAuth, array($this->parameters));

        $expected = 'include_entities=true&oauth_consumer_key=xvz1evFS4wEEPTGEFPHBog&oauth_nonce=kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1318622958&oauth_token=370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb&oauth_version=1.0&status=Hello%20Ladies%20%2B%20Gentlemen%2C%20a%20signed%20OAuth%20request%21';

        $this->assertSame($expected, $result);
    }

    public function tearDown()
    {
        $this->method = null;
        $this->url = null;
        $this->parameters = null;
        $this->oAuth = null;
    }
}
