<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\Enum\ScopeRestrictionType;
use Kontur\Talk\Enum\ScopeType;
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
     *               type — значение {@see ScopeType}; restrictionType — значение
     *               {@see ScopeRestrictionType}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function accessInfo(): array
    {
        return $this->client->get('domain/applications/access-info');
    }
}
