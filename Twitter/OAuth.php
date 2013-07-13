<?php

namespace Lyrixx\Twitter;

/**
 * OAuth
 *
 * @see https://dev.twitter.com/docs/auth/creating-signature
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class OAuth
{
    private $consumerKey;
    private $accessToken;
    private $consumerSecret;
    private $accessTokenSecret;

    public function __construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret)
    {
        $this->consumerKey = $consumerKey;
        $this->accessToken = $accessToken;
        $this->consumerSecret = $consumerSecret;
        $this->accessTokenSecret = $accessTokenSecret;
    }

    public function generateAuthorizationHeader($method, $url, array $parameters = array())
    {
        // To keep the same oauth_nonce and timestamps with the signature
        $parameters = array_replace(array(
            'oauth_nonce' => mt_rand(),
            'oauth_timestamp' => time(),
        ), $parameters);

        $oAuthHeader = 'OAuth ';
        $parts = array(
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_nonce' => $parameters['oauth_nonce'],
            'oauth_signature' => $this->generateSignature($method, $url, $parameters),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => $parameters['oauth_timestamp'],
            'oauth_token' => $this->accessToken,
            'oauth_version' => '1.0',
        );

        foreach ($parts as $key => $value) {
            $oAuthHeader .= sprintf('%s="%s", ', rawurlencode($key), rawurlencode($value));
        }

        $oAuthHeader = rtrim($oAuthHeader, ', ');

        return $oAuthHeader;
    }

    private function generateSignature($method, $url, array $parameters = array())
    {
        $signinKey = $this->generateSigningKey($this->consumerSecret, $this->accessTokenSecret);
        $signinString = $this->generateSigningString($method, $url, $parameters, $this->consumerKey, $this->accessToken);

        return base64_encode(hash_hmac('sha1', $signinString, $signinKey, true));
    }

    private function generateSigningKey()
    {
        return sprintf('%s&%s', rawurlencode($this->consumerSecret), rawurlencode($this->accessTokenSecret));
    }

    private function generateSigningString($method, $url, array $parameters = array())
    {
        $encodedParameters = $this->encodeParameters($parameters);

        $params = array(strtoupper($method), $url, $encodedParameters);
        $params = array_map('rawurlencode', $params);

        return implode('&', $params);
    }

    private function encodeParameters(array $parameters = array())
    {
        $oauthParameters = array(
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_nonce' => mt_rand(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_token' => $this->accessToken,
            'oauth_version' => '1.0',
        );

        $oauthParameters = array_replace($oauthParameters, $parameters);

        ksort($oauthParameters);

        $oauthParameters = array_map(function($v) {
            if (is_bool($v)) {
                return $v ? 'true' : 'false';
            }

            return $v;
        }, $oauthParameters);

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return http_build_query($oauthParameters, '', '&', PHP_QUERY_RFC3986);
        }

        return str_replace('+', '%20', http_build_query($oauthParameters));
    }
}
