<?php

namespace Kontur\Talk\Enum;

/**
 * Тип разрешения для области API-ключа (поле `restrictionType` в ответе
 * `GET /api/domain/applications/access-info`).
 *
 * Не используется методами SDK для валидации входных значений — `Applications::accessInfo()`
 * не принимает параметров, это перечисление только для чтения значений из ответа.
 */
enum ScopeRestrictionType: string
{
    case Read = 'read';
    case ReadWrite = 'readWrite';
}
