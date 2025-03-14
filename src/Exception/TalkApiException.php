<?php

namespace Kontur\Talk\Exception;

use GuzzleHttp\Exception\ClientException;
use Throwable;

/**
 * Исключение, связанное с ошибками API Kontur Talk
 */
class TalkApiException extends \Exception
{
    private string $errorMessage = 'Unknown error';

    public function __construct($messageOrException = '', int $code = 0, ?Throwable $previous = null)
    {
        if ($messageOrException instanceof ClientException) {
            $message = $messageOrException->getMessage();
            $code = $messageOrException->getCode();
            $previous = $messageOrException;

            $response = $messageOrException->getResponse();
            if ($response !== null) {
                $body = (string) $response->getBody();
                if (!empty($body)) {
                    try {
                        $data = json_decode($body, true);
                        if (is_array($data) && isset($data['errorMessage'])) {
                            $this->errorMessage = $data['errorMessage'];
                        }
                    } catch (\Throwable $e) {
                        // Ignore parsing errors
                    }
                }
            }
        } else {
            $message = $messageOrException;
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Получить сообщение об ошибке из ответа API
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
