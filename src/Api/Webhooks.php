<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\Enum\WebhookEventType;
use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;

/**
 * API для работы с вебхуками пространства (`/api/Webhooks`).
 *
 * Требует разрешений `application.webhooks.read` (чтение) и `application.webhooks.write`
 * (создание, активация, удаление) у API-ключа.
 *
 * Контракт доставки событий (документация разработчика, раздел "Работа с вебхуками") — SDK его
 * не разбирает, разбор входящих запросов остаётся на принимающей стороне:
 * - событие приходит JSON-телом с обязательными полями `eventId`, `eventType` и `time`
 *   (ISO 8601);
 * - `eventType` в доставке записан в PascalCase (`ConferencesStarted`, `ConferencesFinished`,
 *   `RecordingCompleted`, `TranscriptionReady`, `UserConnectedToRoom`,
 *   `UserDisconnectedFromRoom`), тогда как значения подписки в `events[]` — camelCase
 *   (см. {@see WebhookEventType}). Сопоставлять их напрямую нельзя;
 * - дополнительные поля зависят от типа: `ConferencesStarted` — `conferenceKey` и `roomName`,
 *   `ConferencesFinished` — `conferenceKey`, `RecordingCompleted` и `TranscriptionReady` —
 *   `recordingKey`, `UserConnectedToRoom` и `UserDisconnectedFromRoom` — `roomName`,
 *   `isAnonymous`, `userKey`, `anonymousId`;
 * - принимающий сервер обязан ответить 200 в течение 5 секунд.
 */
class Webhooks extends ApiClient
{
    /**
     * Получает список активных вебхуков пространства
     *
     * @return array Список TalkWebhook: [{webhookKey, title, activated, events[], url,
     *               customHeaders: [{name, value}], created}]
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getList(): array
    {
        return $this->client->get('Webhooks');
    }

    /**
     * Создаёт вебхук.
     *
     * Создание асинхронное: в ответ приходит ещё не активированный вебхук (`activated: false`),
     * а Толк отправляет на указанный `url` POST-запрос с телом `{"activationKey": "..."}`.
     * Полученный ключ нужно вернуть в {@see self::activate()} — до этого события не приходят.
     *
     * Спецификация объявляет лимит: 10 созданий в сутки на пространство; при его превышении
     * API отвечает 429, а SDK бросает {@see TalkRateLimitException}.
     *
     * @param array $data Тело TalkCreateWebhookRequest: title (обязательное, до 200 символов),
     *              url (обязательное, до 2048 символов), events[] — значения
     *              {@see WebhookEventType}, customHeaders[] — [{name, value}]
     * @return array TalkWebhook — созданный вебхук (webhookKey, activated, ...)
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function create(array $data): array
    {
        foreach ($data['events'] ?? [] as $event) {
            WebhookEventType::from($event);
        }

        return $this->client->post('Webhooks', $data);
    }

    /**
     * Активирует вебхук ключом, который Толк прислал на его URL после создания
     *
     * @param string $webhookKey Уникальный идентификатор вебхука
     * @param array $data Тело TalkActivateWebhookRequest: {activationKey}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function activate(string $webhookKey, array $data): void
    {
        $this->client->post("Webhooks/{$webhookKey}/activate", $data);
    }

    /**
     * Удаляет вебхук
     *
     * @param string $webhookKey Ключ удаляемого вебхука
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function delete(string $webhookKey): void
    {
        $this->client->delete("Webhooks/{$webhookKey}");
    }
}
