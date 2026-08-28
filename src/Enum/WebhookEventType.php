<?php

namespace Kontur\Talk\Enum;

/**
 * Тип события вебхука (`events[]` в `GET/POST /api/Webhooks`).
 *
 * Это значения подписки — camelCase. В самой доставке события Толк присылает `eventType`
 * в PascalCase (`RecordingCompleted` вместо `recordingCompleted`), поэтому напрямую
 * скармливать `eventType` из вебхука в `from()` нельзя — см. {@see \Kontur\Talk\Api\Webhooks}.
 */
enum WebhookEventType: string
{
    case RecordingCompleted = 'recordingCompleted';
    case TranscriptionReady = 'transcriptionReady';
    case UserConnectedToRoom = 'userConnectedToRoom';
    case UserDisconnectedFromRoom = 'userDisconnectedFromRoom';
    case ConferencesStarted = 'conferencesStarted';
    case ConferencesFinished = 'conferencesFinished';
}
