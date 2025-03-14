<?php

namespace Kontur\Talk\Api;

use DateTime;
use Kontur\Talk\TalkClient;

class Kiosks extends ApiClient
{
    /**
     * Получить список киосков
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

        return $this->client->get('kiosks', $params);
    }

    /**
     * Получить киоск по ID
     *
     * @param string $kioskId ID киоска
     * @return array
     */
    public function getById(string $kioskId): array
    {
        return $this->client->get("kiosks/{$kioskId}");
    }

    /**
     * Создать новый киоск
     *
     * @param array $kioskData Данные киоска
     * @return array
     */
    public function create(array $kioskData): array
    {
        return $this->client->post('kiosks', $kioskData);
    }

    /**
     * Обновить существующий киоск
     *
     * @param string $kioskId ID киоска
     * @param array $updateData Данные для обновления
     * @return array
     */
    public function update(string $kioskId, array $updateData): array
    {
        return $this->client->put("kiosks/{$kioskId}", $updateData);
    }

    /**
     * Удалить киоск
     *
     * @param string $kioskId ID киоска
     * @return array
     */
    public function delete(string $kioskId): array
    {
        return $this->client->delete("kiosks/{$kioskId}");
    }
}
