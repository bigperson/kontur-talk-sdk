<?php

namespace Kontur\Talk\Exception;

use GuzzleHttp\Exception\ServerException;
use Throwable;

/**
 * Базовое исключение клиента Kontur Talk
 */
class TalkClientException extends \Exception
{
    public function __construct($messageOrException = '', int $code = 0, ?Throwable $previous = null)
    {
        if ($messageOrException instanceof ServerException) {
            $message = $messageOrException->getMessage();
            $code = $messageOrException->getCode();
            $previous = $messageOrException;
        } else {
            $message = $messageOrException;
        }

        parent::__construct($message, $code, $previous);
    }
}
