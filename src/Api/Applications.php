<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkRateLimitException;

/**
 * API для получения информации о текущем API-ключе (`/api/domain/applications`)
 */
class Applications extends ApiClient
{
    /**
     * Получает срок действия и разрешённые области (scopes) текущего API-ключа.
     *
     * Ключевой endpoint для проверки ключа: срок действия истёк? какие scope выданы?
     * Отсутствие scope "recording" — сигнал для потребителя SDK переключить интеграцию
     * в режим "только ссылки" (без доступа к записям/транскриптам).
     *
     * @return array TalkDomainApplicationAccessInfo: {expiredAt, scopes: [{type, restrictionType}]}
     *               type — один из: profiles, calendar, calendarControl, rooms, reporting, kiosk,
     *               recording, routing, onlineStats, applications, roles, corpTelephony,
     *               spectatorRegistration, federations, redirect, streamEvents, deepfakeDetection,
     *               surveys, webhooks, messengerStats, messengerLicense, activeRecordings;
     *               restrictionType — read | readWrite
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function accessInfo(): array
    {
        return $this->client->get('domain/applications/access-info');
    }
}
