<?php

namespace Kontur\Talk\Api;

use DateTime;
use Kontur\Talk\TalkClient;

class Meetings extends ApiClient
{
    /**
     * Получить список встреч
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

        return $this->client->get('meetings', $params);
    }

    /**
     * Получить встречу по ID
     *
     * @param string $meetingId ID встречи
     * @return array
     */
    public function getById(string $meetingId): array
    {
        return $this->client->get("meetings/{$meetingId}");
    }

    /**
     * Создать новую встречу
     *
     * @param array $meetingData Данные встречи
     * @return array
     */
    public function create(array $meetingData): array
    {
        return $this->client->post('meetings', $meetingData);
    }

    /**
     * Обновить существующую встречу
     *
     * @param string $meetingId ID встречи
     * @param array $updateData Данные для обновления
     * @return array
     */
    public function update(string $meetingId, array $updateData): array
    {
        return $this->client->put("meetings/{$meetingId}", $updateData);
    }

    /**
     * Удалить встречу
     *
     * @param string $meetingId ID встречи
     * @return array
     */
    public function delete(string $meetingId): array
    {
        return $this->client->delete("meetings/{$meetingId}");
    }
}
