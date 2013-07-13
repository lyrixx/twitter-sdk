<?php

namespace Lyrixx\Twitter\Exception;

use Guzzle\Http\Exception\ClientErrorResponseException;

/**
 * ApiClientException.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class ApiClientException extends \LogicException implements ExceptionInterface
{
    public function __construct($message, $code, ClientErrorResponseException $e)
    {
        parent::__construct($message, $code, $e);
    }

    public function getResponse()
    {
        return $this->getPrevious()->getResponse();
    }
}
