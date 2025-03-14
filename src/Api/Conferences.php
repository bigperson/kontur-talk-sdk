<?php

namespace Kontur\Talk\Api;

use DateTime;
use Kontur\Talk\TalkClient;

class Conferences extends ApiClient
{
    /**
     * Получить список конференций
     *
     * @param int|null $top Максимальное количество возвращаемых записей
     * @param string|null $offset Токен для постраничной загрузки
     * @param DateTime|null $startTime Начальное время для фильтрации
     * @param DateTime|null $endTime Конечное время для фильтрации
     * @param string|null $title Фильтр по названию конференции
     * @param string|null $participantEmail Фильтр по email участника
     * @param string|null $participantLogin Фильтр по логину участника
     * @param string|null $participantDisplayName Фильтр по отображаемому имени участника
     * @return array
     */
    public function getAll(
        ?int $top = 100,
        ?string $offset = null,
        ?DateTime $startTime = null,
        ?DateTime $endTime = null,
        ?string $title = null,
        ?string $participantEmail = null,
        ?string $participantLogin = null,
        ?string $participantDisplayName = null
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

        if ($title !== null) {
            $params['title'] = $title;
        }

        if ($participantEmail !== null) {
            $params['participantEmail'] = $participantEmail;
        }

        if ($participantLogin !== null) {
            $params['participantLogin'] = $participantLogin;
        }

        if ($participantDisplayName !== null) {
            $params['participantDisplayName'] = $participantDisplayName;
        }

        return $this->client->get('conferences', $params);
    }

    /**
     * Получить конференцию по ID
     *
     * @param string $conferenceId ID конференции
     * @return array
     */
    public function getById(string $conferenceId): array
    {
        return $this->client->get("conferences/{$conferenceId}");
    }

    /**
     * Создать новую конференцию
     *
     * @param array $conferenceData Данные конференции
     * @return array
     */
    public function create(array $conferenceData): array
    {
        return $this->client->post('conferences', $conferenceData);
    }

    /**
     * Обновить существующую конференцию
     *
     * @param string $conferenceId ID конференции
     * @param array $updateData Данные для обновления
     * @return array
     */
    public function update(string $conferenceId, array $updateData): array
    {
        return $this->client->put("conferences/{$conferenceId}", $updateData);
    }

    /**
     * Удалить конференцию
     *
     * @param string $conferenceId ID конференции
     * @return array
     */
    public function delete(string $conferenceId): array
    {
        return $this->client->delete("conferences/{$conferenceId}");
    }

    /**
     * Запустить конференцию
     *
     * @param string $conferenceId ID конференции
     * @return array
     */
    public function start(string $conferenceId): array
    {
        return $this->client->post("conferences/{$conferenceId}/start");
    }

    /**
     * Остановить конференцию
     *
     * @param string $conferenceId ID конференции
     * @return array
     */
    public function stop(string $conferenceId): array
    {
        return $this->client->post("conferences/{$conferenceId}/stop");
    }

    /**
     * Получить список участников конференции
     *
     * @param string $conferenceId ID конференции
     * @return array
     */
    public function getParticipants(string $conferenceId): array
    {
        return $this->client->get("conferences/{$conferenceId}/participants");
    }

    /**
     * Добавить участников в конференцию
     *
     * @param string $conferenceId ID конференции
     * @param array $participants Массив с данными участников
     * @return array
     */
    public function addParticipants(string $conferenceId, array $participants): array
    {
        return $this->client->post("conferences/{$conferenceId}/participants", [
            'participants' => $participants
        ]);
    }

    /**
     * Обновить данные участника конференции
     *
     * @param string $conferenceId ID конференции
     * @param string $userId ID пользователя
     * @param array $updateData Данные для обновления
     * @return array
     */
    public function updateParticipant(string $conferenceId, string $userId, array $updateData): array
    {
        return $this->client->put("conferences/{$conferenceId}/participants/{$userId}", $updateData);
    }

    /**
     * Удалить участника из конференции
     *
     * @param string $conferenceId ID конференции
     * @param string $userId ID пользователя
     * @return array
     */
    public function removeParticipant(string $conferenceId, string $userId): array
    {
        return $this->client->delete("conferences/{$conferenceId}/participants/{$userId}");
    }

    /**
     * Получить список записей конференции
     *
     * @param string $conferenceId ID конференции
     * @return array
     */
    public function getRecordings(string $conferenceId): array
    {
        return $this->client->get("conferences/{$conferenceId}/recordings");
    }

    /**
     * Начать запись конференции
     *
     * @param string $conferenceId ID конференции
     * @return array
     */
    public function startRecording(string $conferenceId): array
    {
        return $this->client->post("conferences/{$conferenceId}/recordings/start");
    }

    /**
     * Остановить запись конференции
     *
     * @param string $conferenceId ID конференции
     * @return array
     */
    public function stopRecording(string $conferenceId): array
    {
        return $this->client->post("conferences/{$conferenceId}/recordings/stop");
    }

    /**
     * Сгенерировать ссылку для присоединения к конференции
     *
     * @param string $conferenceId ID конференции
     * @param array $options Дополнительные параметры
     * @return array
     */
    public function generateJoinLink(string $conferenceId, array $options): array
    {
        return $this->client->post("conferences/{$conferenceId}/joinLink", $options);
    }
}
