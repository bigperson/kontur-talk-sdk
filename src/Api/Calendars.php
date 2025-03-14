<?php

namespace Kontur\Talk\Api;

use DateTime;
use Kontur\Talk\TalkClient;

class Calendars extends ApiClient
{
    /**
     * Получить список календарей
     *
     * @param int|null $top Максимальное количество возвращаемых записей
     * @param string|null $offset Токен для постраничной загрузки
     * @return array
     */
    public function getAll(?int $top = 100, ?string $offset = null): array
    {
        $params = [
            'top' => $top
        ];

        if ($offset !== null) {
            $params['offset'] = $offset;
        }

        return $this->client->get('calendars', $params);
    }

    /**
     * Получить календарь по ID
     *
     * @param string $calendarId ID календаря
     * @return array
     */
    public function getById(string $calendarId): array
    {
        return $this->client->get("calendars/{$calendarId}");
    }

    /**
     * Создать новый календарь
     *
     * @param array $calendarData Данные календаря
     * @return array
     */
    public function create(array $calendarData): array
    {
        return $this->client->post('calendars', $calendarData);
    }

    /**
     * Обновить существующий календарь
     *
     * @param string $calendarId ID календаря
     * @param array $updateData Данные для обновления
     * @return array
     */
    public function update(string $calendarId, array $updateData): array
    {
        return $this->client->put("calendars/{$calendarId}", $updateData);
    }

    /**
     * Удалить календарь
     *
     * @param string $calendarId ID календаря
     * @return array
     */
    public function delete(string $calendarId): array
    {
        return $this->client->delete("calendars/{$calendarId}");
    }
}
