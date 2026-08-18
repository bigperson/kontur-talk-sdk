<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;

/**
 * API для работы с пользователями (`/api/Users`)
 */
class Users extends ApiClient
{
    /**
     * Последовательно перебирает всех пользователей пространства (курсорная постраничная выдача)
     *
     * @param string|null $offset Курсор постраничной загрузки
     * @param int|null $top Максимальное количество записей на странице
     * @param bool $includeDisabled Включать заблокированных пользователей
     * @param bool $includeGuests Включать гостевых пользователей
     * @return array TalkUserScanResult: {users: TalkUser[], offset}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function scan(
        ?string $offset = null,
        ?int $top = null,
        bool $includeDisabled = false,
        bool $includeGuests = false
    ): array {
        $params = [
            'includeDisabled' => $includeDisabled ? 'true' : 'false',
            'includeGuests' => $includeGuests ? 'true' : 'false',
        ];

        if ($offset !== null) {
            $params['offset'] = $offset;
        }

        if ($top !== null) {
            $params['top'] = $top;
        }

        return $this->client->get('Users/scan', $params);
    }

    /**
     * Ищет пользователей пространства по фильтрам
     *
     * @param array $filters Query-параметры: query, email[] (повторяющийся параметр), role, post, top,
     *              skip и другие поля спецификации
     * @return array TalkUserSearchResult: {users: TalkUser[]}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function search(array $filters = []): array
    {
        return $this->client->get('Users', $filters);
    }

    /**
     * Получает пользователя по ключу
     *
     * @param string $userKey Ключ пользователя
     * @return array TalkUser: key, email, firstname, surname, disabled, userType, ...
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function getByKey(string $userKey): array
    {
        return $this->client->get("Users/{$userKey}");
    }
}
