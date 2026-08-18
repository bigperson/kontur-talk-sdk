<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;

/**
 * API для работы с историей конференций (`/api/domain/conferencesHistory`, `/api/ConferencesHistory`)
 */
class ConferencesHistory extends ApiClient
{
    /**
     * Получает список конференций пространства за период — батчевый опрос комнат
     * (до 50 комнат за один вызов).
     *
     * @param string|null $fromDate Начало периода (ISO 8601)
     * @param string|null $toDate Конец периода (ISO 8601)
     * @param array $roomNames Список ключей комнат — передаётся повторяющимся query-параметром roomName
     * @param int|null $skip Количество пропускаемых записей
     * @param int|null $take Максимальное количество записей
     * @return array TalkConferenceInfos: {conferences: [{key, roomName, startTime, endTime, title,
     *               isPlannedMeeting, ...}]}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function list(
        ?string $fromDate = null,
        ?string $toDate = null,
        array $roomNames = [],
        ?int $skip = null,
        ?int $take = null
    ): array {
        $params = [];

        if ($fromDate !== null) {
            $params['fromDate'] = $fromDate;
        }

        if ($toDate !== null) {
            $params['toDate'] = $toDate;
        }

        if ($roomNames !== []) {
            $params['roomName'] = $roomNames;
        }

        if ($skip !== null) {
            $params['skip'] = $skip;
        }

        if ($take !== null) {
            $params['take'] = $take;
        }

        return $this->client->get('domain/conferencesHistory', $params);
    }

    /**
     * Получает конференцию по ключу
     *
     * @param string $conferenceKey Ключ конференции
     * @return array TalkConference: key, roomName, startTime, endTime, title, isPlannedMeeting,
     *               artifacts{participants, invitedParticipants, content[], ...}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function get(string $conferenceKey): array
    {
        return $this->client->get("ConferencesHistory/{$conferenceKey}");
    }

    /**
     * Получает конференцию с обогащёнными артефактами (записи, заметки, опросы, доски)
     *
     * @param string $conferenceKey Ключ конференции
     * @return array TalkEnrichedConference: key, roomName, startTime, endTime, title, isPlannedMeeting,
     *               artifacts{participants, invitedParticipants, notes, polls, whiteboards,
     *               recordings: [TalkConferenceRecording{id, title, createdDate, duration, status,
     *               allowAnonymousAccess, ...}]}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function getArtifacts(string $conferenceKey): array
    {
        return $this->client->get("ConferencesHistory/v2/{$conferenceKey}");
    }
}
