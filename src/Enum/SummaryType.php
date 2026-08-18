<?php

namespace Kontur\Talk\Enum;

/**
 * Тип саммаризации записи (`summarizationType` в `GET /api/recordings/{recordingKey}/summary/{summarizationType}`)
 */
enum SummaryType: string
{
    case ShortSummary = 'shortSummary';
    case Protocol = 'protocol';
}
