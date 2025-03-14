<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;

/**
 * API для работы с пользователями
 */
class Users extends ApiClient
{
    /**
     * Получает пользователя по ключу
     *
     * @param string $userKey Ключ пользователя
     * @return array Данные пользователя
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function getByKey(string $userKey): array
    {
        return $this->client->get("users/{$userKey}");
    }

    /**
     * Получает список всех пользователей пространства
     *
     * @param int $top Количество элементов в выдаче (максимум 1000)
     * @param string|null $offset Смещение для получения следующей страницы
     * @param string|null $role Фильтр по ролям пользователей
     * @param bool $includeDisabled Включать заблокированных пользователей
     * @return array Список пользователей
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function scan(
        int $top = 100,
        ?string $offset = null,
        ?string $role = null,
        bool $includeDisabled = false
    ): array {
        $params = [
            'top' => min($top, 1000),
            'includeDisabled' => $includeDisabled ? 'true' : 'false'
        ];

        if ($offset !== null) {
            $params['offset'] = $offset;
        }

        if ($role !== null) {
            $params['role'] = $role;
        }

        return $this->client->get('users/scan', $params);
    }

    /**
     * Получает список пользователей пространства по заданным параметрам
     *
     * @param int $top Количество элементов в выдаче (максимум 1000)
     * @param int $skip Количество пропускаемых элементов
     * @param string|null $query Запрос для фильтрации
     * @param array $emails Массив email для фильтрации
     * @param string|null $role Фильтр по ролям пользователей
     * @param bool $includeDisabled Включать заблокированных пользователей
     * @param bool $fillInMeetingStatus Включить статус участия в конференции
     * @return array Список пользователей
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function get(
        int $top = 100,
        int $skip = 0,
        ?string $query = null,
        array $emails = [],
        ?string $role = null,
        bool $includeDisabled = false,
        bool $fillInMeetingStatus = false
    ): array {
        $params = [
            'top' => min($top, 1000),
            'skip' => $skip,
            'includeDisabled' => $includeDisabled ? 'true' : 'false',
            'fillInMeetingStatus' => $fillInMeetingStatus ? 'true' : 'false'
        ];

        if ($query !== null) {
            $params['query'] = $query;
        }

        if ($role !== null) {
            $params['role'] = $role;
        }

        foreach ($emails as $email) {
            $params['email'][] = $email;
        }

        return $this->client->get('users', $params);
    }

    /**
     * Получает список всех пользователей и их статусы
     *
     * @param int $top Количество элементов в выдаче (максимум 1000)
     * @param int $skip Количество пропускаемых элементов
     * @return array Список пользователей со статусами
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getWithStatuses(int $top = 100, int $skip = 0): array
    {
        $params = [
            'top' => min($top, 1000),
            'skip' => $skip,
            'fillInMeetingStatus' => 'true'
        ];

        return $this->client->get('users', $params);
    }

    /**
     * Получает информацию о пользователе и его статусе по email
     *
     * @param string $email Email пользователя
     * @return array Информация о пользователе
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getByEmail(string $email): array
    {
        $params = [
            'query' => $email,
            'fillInMeetingStatus' => 'true'
        ];

        return $this->client->get('users', $params);
    }

    /**
     * Блокирует или восстанавливает учетную запись пользователя
     *
     * @param string $userKey Ключ пользователя
     * @param bool $disabled Флаг блокировки
     * @return array Результат операции
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function setPermissions(string $userKey, bool $disabled): array
    {
        return $this->client->put("users/{$userKey}/permissions", [
            'disabled' => $disabled
        ]);
    }

    /**
     * Обновляет, создает или восстанавливает пользователей
     *
     * @param array $users Массив данных пользователей
     * @return array Результат операции
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function createOrUpdate(array $users): array
    {
        // Ограничение в 30 пользователей для одного запроса
        if (count($users) > 30) {
            throw new TalkClientException(
                'Превышено допустимое количество пользователей для одного запроса (максимум 30)'
            );
        }

        return $this->client->post('users', $users);
    }

    /**
     * Обновляет аватар пользователя через сервис календарей
     *
     * @param string $userKey Ключ пользователя
     * @return array Результат операции
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function syncAvatar(string $userKey): array
    {
        return $this->client->post("users/{$userKey}/avatar/sync");
    }

    /**
     * Удаляет пользователя
     *
     * @param string $userKey Ключ пользователя
     * @return array Результат операции
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function delete(string $userKey): array
    {
        return $this->client->delete("users/{$userKey}");
    }

    /**
     * Получает список пользователей в роли
     *
     * @param string $roleId Идентификатор роли
     * @return array Список пользователей
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getByRole(string $roleId): array
    {
        return $this->client->get('Users/scan', ['role' => $roleId]);
    }

    /**
     * Удаляет аватар пользователя
     *
     * @param string $userKey Ключ пользователя
     * @return array Результат операции
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function deleteAvatar(string $userKey): array
    {
        return $this->client->delete("users/{$userKey}/avatar");
    }
}
