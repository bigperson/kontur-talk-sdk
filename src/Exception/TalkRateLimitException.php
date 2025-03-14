<?php

namespace Kontur\Talk\Exception;

use GuzzleHttp\Exception\ClientException;
use Throwable;

/**
 * Исключение при превышении лимита запросов к API
 */
class TalkRateLimitException extends TalkApiException
{
    public function __construct($messageOrException = '', int $code = 429, ?Throwable $previous = null)
    {
        if ($messageOrException instanceof ClientException) {
            $message = $messageOrException->getMessage();
            $code = $messageOrException->getCode();
            $previous = $messageOrException;
        } else {
            $message = $messageOrException;
        }

        parent::__construct($message, $code, $previous);
    }
}
