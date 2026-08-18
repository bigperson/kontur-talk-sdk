<?php

namespace Kontur\Talk\Enum;

/**
 * Область действия ссылочного доступа к записи (`linkAccess.scope`
 * в `GET/PATCH /api/Recordings/{recordingKey}/access`)
 */
enum LinkAccessScope: string
{
    case None = 'none';
    case Domain = 'domain';
    case Global = 'global';
}
