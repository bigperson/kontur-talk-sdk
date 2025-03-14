<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\TalkClient;

class Roles extends ApiClient
{
    /**
     * Получить список всех ролей
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->client->get('roles');
    }

    /**
     * Получить роль по ID
     *
     * @param string $roleId ID роли
     * @param bool $includeUsersCount Включать ли количество пользователей с этой ролью
     * @return array
     */
    public function get(string $roleId, bool $includeUsersCount = false): array
    {
        $params = [];

        if ($includeUsersCount) {
            $params['includeUsersCount'] = 'true';
        }

        return $this->client->get("roles/{$roleId}", $params);
    }

    /**
     * Создать новую роль
     *
     * @param string $title Название роли
     * @param string|null $description Описание роли
     * @param array $permissions Массив разрешений
     * @return array
     */
    public function create(string $title, ?string $description = null, array $permissions = []): array
    {
        $data = [
            'title' => $title,
            'permissions' => $permissions
        ];

        if ($description !== null) {
            $data['description'] = $description;
        }

        return $this->client->post('roles', $data);
    }

    /**
     * Обновить существующую роль
     *
     * @param string $roleId ID роли
     * @param string $title Название роли
     * @param string|null $description Описание роли
     * @param array $permissions Массив разрешений
     * @return array
     */
    public function update(string $roleId, string $title, ?string $description = null, array $permissions = []): array
    {
        $data = [
            'title' => $title,
            'permissions' => $permissions
        ];

        if ($description !== null) {
            $data['description'] = $description;
        }

        return $this->client->put("roles/{$roleId}", $data);
    }

    /**
     * Получить роли пользователя
     *
     * @param string $userKey Ключ пользователя
     * @return array
     */
    public function getUserRoles(string $userKey): array
    {
        return $this->client->get("Users/{$userKey}/roles");
    }

    /**
     * Управление ролями пользователя
     *
     * @param string $userKey Ключ пользователя
     * @param array $addedRoles Массив ID ролей для добавления
     * @param array $removedRoles Массив ID ролей для удаления
     * @return array
     */
    public function manageUserRoles(string $userKey, array $addedRoles = [], array $removedRoles = []): array
    {
        $data = [
            'addedRoleIds' => $addedRoles,
            'removedRoleIds' => $removedRoles
        ];

        return $this->client->post("Users/{$userKey}/roles", $data);
    }

    /**
     * Удалить роль
     *
     * @param string $roleId ID роли
     * @return array
     */
    public function delete(string $roleId): array
    {
        return $this->client->delete("roles/{$roleId}");
    }

    /**
     * Получить роль по умолчанию
     *
     * @return array
     */
    public function getDefault(): array
    {
        return $this->client->get('roles/default');
    }

    /**
     * Обновить роль по умолчанию
     *
     * @param array $permissions Массив разрешений
     * @return array
     */
    public function updateDefault(array $permissions): array
    {
        return $this->client->post('roles/default', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Получить список всех доступных разрешений
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->client->get('permissions');
    }
}
