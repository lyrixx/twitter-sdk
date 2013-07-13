Twitter SDK
===========

Installation
------------

    composer require lyrixx/twitter-sdk:0.1.*@dev

Usage
-----

[Create a twitter application](https://dev.twitter.com/apps) then

    <?php

    require __DIR__.'/vendor/autoload.php';

    use Lyrixx\Twitter\Twitter;

    // You can find them at: https://dev.twitter.com/apps > your app
    $consumerKey = 'xvz1evFS4wEEPTGEFPHBog';
    $accessToken = '370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb';
    $consumerSecret = 'kAcSOqF21Fu85e7zjz7ZN2U4ZRhfV3WpwPAoE3Z7kBw';
    $accessTokenSecret = 'LswwdoUaIvS8ltyTt5jkRh4J50vUPVVHtR2YPi5kE';

    $twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

    // Fetch yours last tweets
    $response = $twitter->query('GET', 'statuses/user_timeline');
    $tweets = json_decode($response->getBody(), true);

    // Search some tweets
    $response = $twitter->query('GET', 'search/tweets', array('q' => '#symfony2'));
    $tweets = json_decode($response->getBody(), true);

    // Tweet
    // Works only if your application has read/write scope
    try {
        $response = $twitter->query('POST', 'statuses/update');
    } catch (Lyrixx\Twitter\Exception\ApiClientException $e) {
        $response = $e->getResponse();
        $errors = json_decode($response->getBody(), true); // {"errors":[{"code":170,"message":"Missing required parameter: status"}]}
    }

    // No exception are throwed, it just works
    $response = $twitter->query('POST', 'statuses/update', array('status' => 'Playing with twitter API'));

License
-------

This library is under the MIT license. For the full copyright and license
information, please view the LICENSE file that was distributed with this source
code.
