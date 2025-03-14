<?php

namespace Kontur\Talk\Api;

use DateTime;
use Kontur\Talk\TalkClient;

class ApiKeys extends ApiClient
{
    /**
     * Получить список API ключей
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->client->get('apikeys');
    }

    /**
     * Получить API ключ по ID
     *
     * @param string $keyId ID ключа
     * @return array
     */
    public function getById(string $keyId): array
    {
        return $this->client->get("apikeys/{$keyId}");
    }

    /**
     * Создать новый API ключ
     *
     * @param string $name Название ключа
     * @param array $permissions Массив разрешений
     * @param DateTime|null $expirationDate Дата истечения срока действия
     * @return array
     */
    public function create(string $name, array $permissions, ?DateTime $expirationDate = null): array
    {
        $data = [
            'name' => $name,
            'permissions' => $permissions
        ];

        if ($expirationDate !== null) {
            $data['expirationDate'] = $expirationDate->format('Y-m-d\TH:i:s.v\Z');
        }

        return $this->client->post('apikeys', $data);
    }

    /**
     * Обновить существующий API ключ
     *
     * @param string $keyId ID ключа
     * @param string $name Название ключа
     * @param array $permissions Массив разрешений
     * @param DateTime|null $expirationDate Дата истечения срока действия
     * @return array
     */
    public function update(string $keyId, string $name, array $permissions, ?DateTime $expirationDate = null): array
    {
        $data = [
            'name' => $name,
            'permissions' => $permissions
        ];

        if ($expirationDate !== null) {
            $data['expirationDate'] = $expirationDate->format('Y-m-d\TH:i:s.v\Z');
        }

        return $this->client->put("apikeys/{$keyId}", $data);
    }

    /**
     * Удалить API ключ
     *
     * @param string $keyId ID ключа
     * @return array
     */
    public function delete(string $keyId): array
    {
        return $this->client->delete("apikeys/{$keyId}");
    }
}
