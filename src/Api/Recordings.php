<?php

namespace Kontur\Talk\Api;

use DateTime;
use Kontur\Talk\TalkClient;

class Recordings extends ApiClient
{
    /**
     * Получить список записей
     *
     * @param int|null $top Максимальное количество возвращаемых записей
     * @param string|null $offset Токен для постраничной загрузки
     * @param DateTime|null $startTime Начальное время для фильтрации
     * @param DateTime|null $endTime Конечное время для фильтрации
     * @return array
     */
    public function getAll(
        ?int $top = 100,
        ?string $offset = null,
        ?DateTime $startTime = null,
        ?DateTime $endTime = null
    ): array {
        $params = [
            'top' => $top
        ];

        if ($offset !== null) {
            $params['offset'] = $offset;
        }

        if ($startTime !== null) {
            $params['startTime'] = $startTime->format('Y-m-d\TH:i:s.v\Z');
        }

        if ($endTime !== null) {
            $params['endTime'] = $endTime->format('Y-m-d\TH:i:s.v\Z');
        }

        return $this->client->get('recordings', $params);
    }

    /**
     * Получить запись по ID
     *
     * @param string $recordingId ID записи
     * @return array
     */
    public function getById(string $recordingId): array
    {
        return $this->client->get("recordings/{$recordingId}");
    }

    /**
     * Удалить запись
     *
     * @param string $recordingId ID записи
     * @return array
     */
    public function delete(string $recordingId): array
    {
        return $this->client->delete("recordings/{$recordingId}");
    }

    /**
     * Получить ссылку для скачивания записи
     *
     * @param string $recordingId ID записи
     * @return array
     */
    public function getDownloadLink(string $recordingId): array
    {
        return $this->client->get("recordings/{$recordingId}/downloadLink");
    }
}
