<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;

/**
 * API для работы с календарём встреч на почтовом ящике организатора (`/api/EmailCalendar`).
 *
 * `{email}` — почтовый ящик организатора: так встреча создаётся "от имени" менеджера.
 */
class Calendar extends ApiClient
{
    /**
     * Создаёт событие календаря (встречу) на почтовом ящике
     *
     * @param string $email Почтовый ящик организатора
     * @param array $data Тело CreateEmailCalendarEventModel: обязательные start, end, subject, roomName;
     *              опциональные timezone, description, allowAnonymous, pinCode, enableAutoRecording,
     *              requiredUserKeys[], optionalUserKeys[], requiredExternalAttendeesEmails[],
     *              optionExternalAttendeesEmails[] (опечатка "option..." — так в самой спецификации API)
     * @return array EmailCalendarItem: id, onlineMeetingUrl (ссылка на встречу), roomName, start, end,
     *               subject, organizer, requiredAttendees[], enableAutoRecording, isCancelled, ...
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function createEvent(string $email, array $data): array
    {
        return $this->client->post("EmailCalendar/{$email}", $data);
    }

    /**
     * Получает список событий календаря за период
     *
     * @param string $email Почтовый ящик организатора
     * @param string $start Начало периода (ISO 8601), обязателен
     * @param string|null $end Конец периода (ISO 8601)
     * @param int|null $take Максимальное количество событий
     * @return array EmailCalendarResult: {items: EmailCalendarItem[]}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function listEvents(string $email, string $start, ?string $end = null, ?int $take = null): array
    {
        $params = ['start' => $start];

        if ($end !== null) {
            $params['end'] = $end;
        }

        if ($take !== null) {
            $params['take'] = $take;
        }

        return $this->client->get("EmailCalendar/{$email}", $params);
    }

    /**
     * Обновляет событие календаря
     *
     * @param string $email Почтовый ящик организатора
     * @param string $eventId Идентификатор события
     * @param array $data Тело EditEmailCalendarEventModel (те же поля, что и при создании)
     * @return array EmailCalendarItem — обновлённое событие
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function updateEvent(string $email, string $eventId, array $data): array
    {
        return $this->client->put("EmailCalendar/{$email}/{$eventId}", $data);
    }

    /**
     * Отменяет (удаляет) событие календаря
     *
     * @param string $email Почтовый ящик организатора
     * @param string $eventId Идентификатор события
     * @param string|null $message Сообщение участникам об отмене
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function deleteEvent(string $email, string $eventId, ?string $message = null): void
    {
        $params = $message !== null ? ['message' => $message] : [];

        $this->client->delete("EmailCalendar/{$email}/{$eventId}", $params);
    }

    /**
     * Обновляет состав участников события
     *
     * @param string $email Почтовый ящик организатора
     * @param string $eventId Идентификатор события
     * @param array $data Тело EditEmailCalendarAttendeeModel: requiredUserKeys[], optionalUserKeys[],
     *              requiredExternalAttendeesEmails[], optionExternalAttendeesEmails[] (опечатка в API)
     * @return array EmailCalendarItem — событие с обновлённым составом участников
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function updateAttendees(string $email, string $eventId, array $data): array
    {
        return $this->client->post("EmailCalendar/{$email}/{$eventId}/attendees", $data);
    }
}
