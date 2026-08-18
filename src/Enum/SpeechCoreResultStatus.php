<?php

namespace Kontur\Talk\Enum;

/**
 * Статус результата SpeechCore (поле `status` в ответе саммари:
 * `GET /api/recordings/{recordingKey}/summary/{summarizationType}`,
 * `GET /api/recordings/v2/{recordingKey}/summary`)
 */
enum SpeechCoreResultStatus: string
{
    case NotFound = 'notFound';
    case InProgress = 'inProgress';
    case Failed = 'failed';
    case Success = 'success';
    case NotAvailable = 'notAvailable';
    case ServiceError = 'serviceError';
    case RecreateInProgress = 'recreateInProgress';
}
