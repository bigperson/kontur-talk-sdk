<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;

/**
 * API для работы с отчётностью по комнатам (`/api/RoomReport`).
 *
 * Требует разрешения `application.reporting.read` у API-ключа.
 *
 * Соседний `GET /api/RoomReport/statistics` (отчёт о посещаемости всего пространства) сюда не
 * вынесен: он отдаёт файл xlsx, а не JSON, — весь транспорт SDK построен на декодировании тела
 * ответа в массив и такой ответ вернул бы пустой массив. Для комнаты нужные данные даёт
 * `statisticsReport()`.
 */
class RoomReport extends ApiClient
{
    /**
     * Получает отчёт по комнате за период.
     *
     * Даты — в формате ISO 8601, часовой пояс UTC (`2026-09-01T10:00:00.000Z`). Спецификация
     * ограничивает период 365 днями.
     *
     * @param string $roomName Ключ комнаты
     * @param string $from Начало периода (ISO 8601)
     * @param string|null $to Конец периода (ISO 8601); null — по текущее время
     * @return array RoomStatisticsReport: {roomName, from, to, participantCount,
     *               participantTotalDurations: [{participantName, participant, isGuest,
     *               participantEmail, sessionHall, duration}], roomParticipants:
     *               [{participantName, participantId, isGuest, participant, participantEmail,
     *               sessionHall, entryTime, exitTime, urlParams}], streamViewers[], metrics}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function statisticsReport(string $roomName, string $from, ?string $to = null): array
    {
        $params = ['from' => $from];

        if ($to !== null) {
            $params['to'] = $to;
        }

        return $this->client->get("RoomReport/{$roomName}/statistics/report", $params);
    }
}
