<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;

/**
 * API для работы с комнатами (`/api/Rooms`)
 */
class Rooms extends ApiClient
{
    /**
     * Получает комнату по ключу
     *
     * @param string $roomName Ключ комнаты
     * @return array TalkRoom: roomName, title, description, conferenceId, stageConferenceId, pinCode,
     *               securityType (none|pinCode), allowAnonymous, anonymousAccessExpirationDate, enableLobby, ...
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function get(string $roomName): array
    {
        return $this->client->get("Rooms/{$roomName}");
    }

    /**
     * Создаёт или обновляет комнату
     *
     * @param string $roomName Ключ комнаты
     * @param array $params Тело TalkRoomParams: title, description, moderatorKeys[], allowAnonymous,
     *              anonymousAccessExpirationDate (ISO 8601), enableLobby, audioPolicy, videoPolicy,
     *              screenSharePolicy и другие поля спецификации
     * @return array TalkRoom — созданная/обновлённая комната
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function createOrUpdate(string $roomName, array $params): array
    {
        return $this->client->put("Rooms/{$roomName}", $params);
    }

    /**
     * Устанавливает или снимает PIN-код комнаты
     *
     * @param string $roomName Ключ комнаты
     * @param string|null $pinCode PIN-код (4-6 цифр); null — снять PIN-код
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function setPinCode(string $roomName, ?string $pinCode): void
    {
        $this->client->post("Rooms/{$roomName}/lock", ['pinCode' => $pinCode]);
    }

    /**
     * Принудительно завершает конференцию в комнате для всех участников
     *
     * @param string $roomName Ключ комнаты
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function endConference(string $roomName): void
    {
        $this->client->post("Rooms/{$roomName}/endconference");
    }
}
