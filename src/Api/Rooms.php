<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;
use DateTime;
use Kontur\Talk\TalkClient;

/**
 * API для работы с комнатами
 */
class Rooms extends ApiClient
{
    /**
     * Получает комнату по ключу
     *
     * @param string $roomName Ключ комнаты
     * @return array Информация о комнате
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function get(string $roomName): array
    {
        return $this->client->get("rooms/{$roomName}");
    }

    /**
     * Создает или обновляет комнату
     *
     * @param string $roomName Ключ комнаты
     * @param string $title Заголовок комнаты
     * @param string|null $description Описание комнаты
     * @param array $moderatorKeys Список ключей пользователей-модераторов
     * @param \DateTimeInterface|null $anonymousAccessExpirationDate Дата истечения доступа для анонимных пользователей
     * @param bool $enableSessionHalls Включить сессионные залы
     * @param bool $enableLobby Включить комнату ожидания
     * @param string $audioPolicy Политика использования аудио (none, muted, disabled)
     * @param string $videoPolicy Политика использования видео (none, muted, disabled)
     * @param string $screenSharePolicy Политика демонстрации экрана (none, muted, disabled)
     * @param int $maxVideoQuality Максимальное качество видео
     * @return array Информация о созданной/обновленной комнате
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function createOrUpdate(
        string $roomName,
        string $title,
        ?string $description = null,
        array $moderatorKeys = [],
        ?\DateTimeInterface $anonymousAccessExpirationDate = null,
        bool $enableSessionHalls = false,
        bool $enableLobby = false,
        string $audioPolicy = 'none',
        string $videoPolicy = 'none',
        string $screenSharePolicy = 'none',
        int $maxVideoQuality = 0
    ): array {
        $data = [
            'title' => $title,
            'moderatorKeys' => $moderatorKeys,
            'enableSessionHalls' => $enableSessionHalls,
            'enableLobby' => $enableLobby,
            'audioPolicy' => $audioPolicy,
            'videoPolicy' => $videoPolicy,
            'screenSharePolicy' => $screenSharePolicy,
            'maxVideoQuality' => $maxVideoQuality
        ];

        if ($description !== null) {
            $data['description'] = $description;
        }

        if ($anonymousAccessExpirationDate !== null) {
            $data['anonymousAccessExpirationDate'] = $anonymousAccessExpirationDate->format('Y-m-d\TH:i:s.v\Z');
        }

        return $this->client->put("rooms/{$roomName}", $data);
    }

    /**
     * Добавляет модератора в комнату
     *
     * @param string $roomName Ключ комнаты
     * @param string $userRef Ключ пользователя или ID внешнего пользователя
     * @return array Результат операции
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function addModerator(string $roomName, string $userRef): array
    {
        return $this->client->post("rooms/{$roomName}/moderators/{$userRef}");
    }

    /**
     * Удаляет модератора из комнаты
     *
     * @param string $roomName Ключ комнаты
     * @param string $userRef Ключ пользователя или ID внешнего пользователя
     * @return array Результат операции
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function removeModerator(string $roomName, string $userRef): array
    {
        return $this->client->delete("rooms/{$roomName}/moderators/{$userRef}");
    }

    /**
     * Добавляет или удаляет PIN-код
     *
     * @param string $roomName Ключ комнаты
     * @param string|null $pinCode PIN-код (от 4 до 6 цифр) или пустая строка для удаления
     * @return array Результат операции
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function setPinCode(string $roomName, ?string $pinCode = null): array
    {
        $data = [
            'pinCode' => $pinCode ?? ''
        ];

        return $this->client->post("rooms/{$roomName}/lock", $data);
    }

    /**
     * Выполняет исходящий звонок в комнату
     *
     * @param string $roomName Ключ комнаты
     * @param string $roomTitle Название комнаты
     * @param string $callerUserKey Ключ пользователя, от лица которого производится звонок
     * @param array $callees Список вызываемых пользователей
     * @return array Результат операции
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function notifyCall(string $roomName, string $roomTitle, string $callerUserKey, array $callees): array
    {
        $data = [
            'roomTitle' => $roomTitle,
            'callerUserKey' => $callerUserKey,
            'callees' => $callees
        ];

        return $this->client->post("rooms/{$roomName}/notifyCall", $data);
    }

    /**
     * Отменяет оповещение о звонке в комнату
     *
     * @param string $roomName Ключ комнаты
     * @param array|null $callees Список вызываемых пользователей (null для отмены всех)
     * @return array Результат операции
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function cancelCall(string $roomName, ?array $callees = null): array
    {
        $data = [];

        if ($callees !== null) {
            $data['callees'] = $callees;
        }

        return $this->client->post("rooms/{$roomName}/cancelCall", $data);
    }

    /**
     * Принудительно завершает конференцию для всех участников
     *
     * @param string $roomName Ключ комнаты
     * @return array Результат операции
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function endConference(string $roomName): array
    {
        return $this->client->post("Rooms/{$roomName}/endconference");
    }

    /**
     * Получить список комнат
     *
     * @param int|null $top Максимальное количество возвращаемых записей
     * @param string|null $offset Токен для постраничной загрузки
     * @param string|null $title Фильтр по названию комнаты
     * @return array
     */
    public function getAll(
        ?int $top = 100,
        ?string $offset = null,
        ?string $title = null
    ): array {
        $params = [
            'top' => $top
        ];

        if ($offset !== null) {
            $params['offset'] = $offset;
        }

        if ($title !== null) {
            $params['title'] = $title;
        }

        return $this->client->get('rooms', $params);
    }

    /**
     * Получить комнату по ID
     *
     * @param string $roomId ID комнаты
     * @return array
     */
    public function getById(string $roomId): array
    {
        return $this->client->get("rooms/{$roomId}");
    }

    /**
     * Создать новую комнату
     *
     * @param array $roomData Данные комнаты
     * @return array
     */
    public function create(array $roomData): array
    {
        return $this->client->post('rooms', $roomData);
    }

    /**
     * Обновить существующую комнату
     *
     * @param string $roomId ID комнаты
     * @param array $updateData Данные для обновления
     * @return array
     */
    public function update(string $roomId, array $updateData): array
    {
        return $this->client->put("rooms/{$roomId}", $updateData);
    }

    /**
     * Удалить комнату
     *
     * @param string $roomId ID комнаты
     * @return array
     */
    public function delete(string $roomId): array
    {
        return $this->client->delete("rooms/{$roomId}");
    }

    /**
     * Получить список участников комнаты
     *
     * @param string $roomId ID комнаты
     * @return array
     */
    public function getParticipants(string $roomId): array
    {
        return $this->client->get("rooms/{$roomId}/participants");
    }

    /**
     * Добавить участников в комнату
     *
     * @param string $roomId ID комнаты
     * @param array $participants Массив с данными участников
     * @return array
     */
    public function addParticipants(string $roomId, array $participants): array
    {
        return $this->client->post("rooms/{$roomId}/participants", [
            'participants' => $participants
        ]);
    }

    /**
     * Обновить данные участника комнаты
     *
     * @param string $roomId ID комнаты
     * @param string $userId ID пользователя
     * @param array $updateData Данные для обновления
     * @return array
     */
    public function updateParticipant(string $roomId, string $userId, array $updateData): array
    {
        return $this->client->put("rooms/{$roomId}/participants/{$userId}", $updateData);
    }

    /**
     * Удалить участника из комнаты
     *
     * @param string $roomId ID комнаты
     * @param string $userId ID пользователя
     * @return array
     */
    public function removeParticipant(string $roomId, string $userId): array
    {
        return $this->client->delete("rooms/{$roomId}/participants/{$userId}");
    }

    /**
     * Запустить комнату
     *
     * @param string $roomId ID комнаты
     * @return array
     */
    public function start(string $roomId): array
    {
        return $this->client->post("rooms/{$roomId}/start");
    }

    /**
     * Остановить комнату
     *
     * @param string $roomId ID комнаты
     * @return array
     */
    public function stop(string $roomId): array
    {
        return $this->client->post("rooms/{$roomId}/stop");
    }

    /**
     * Сгенерировать ссылку для присоединения к комнате
     *
     * @param string $roomId ID комнаты
     * @param array $options Дополнительные параметры
     * @return array
     */
    public function generateJoinLink(string $roomId, array $options): array
    {
        return $this->client->post("rooms/{$roomId}/joinLink", $options);
    }
}
