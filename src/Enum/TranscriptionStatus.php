<?php

namespace Kontur\Talk\Enum;

/**
 * Статус транскрипции (поле `status` в ответе `GET /api/recordings/{recordingKey}/transcript`)
 */
enum TranscriptionStatus: string
{
    case InProgress = 'inProgress';
    case Error = 'error';
    case Complete = 'complete';
}
