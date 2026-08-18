<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\Enum\LinkAccessScope;
use Kontur\Talk\Enum\SummaryType;
use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;

/**
 * API для работы с записями (`/api/Domain/recordings`, `/api/Recordings`, `/api/recordings`).
 *
 * Регистр в путях сохранён как в спецификации: `Domain/recordings`, `Recordings/{key}/access`,
 * `recordings/{key}/transcript` — это разные (хотя и похожие) endpoint'ы, а не опечатки.
 */
class Recordings extends ApiClient
{
    /**
     * Получает список записей пространства
     *
     * @param array $filters Query-параметры: startFrom, startTo, pageTokenString, query, title, top,
     *              orderMode и другие поля спецификации (например maxParticipantCount)
     * @return array TalkPage: {entities: TalkDomainConferenceRecording[], nextPageToken, prevPageToken}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function listDomain(array $filters = []): array
    {
        return $this->client->get('Domain/recordings/v2', $filters);
    }

    /**
     * Получает запись пространства по ключу
     *
     * @param string $recordingKey Ключ записи
     * @return array TalkDomainConferenceRecording: id, key, title, createdDate, roomName,
     *               participantsCount, size, duration, allowAnonymousAccess, ...
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function getDomain(string $recordingKey): array
    {
        return $this->client->get("Domain/recordings/{$recordingKey}");
    }

    /**
     * Получает права доступа к записи
     *
     * @param string $recordingKey Ключ записи
     * @return array RecordsResourceAccessResponse: {userAccesses: [{user, roleId}], linkAccess: {scope}}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function getAccess(string $recordingKey): array
    {
        return $this->client->get("Recordings/{$recordingKey}/access");
    }

    /**
     * Обновляет права доступа к записи
     *
     * @param string $recordingKey Ключ записи
     * @param array $userAccesses Список {userKey, roleId}
     * @param string|null $linkScope Область ссылочного доступа — значение {@see LinkAccessScope};
     *              null — не менять текущее значение
     * @param bool $forceUpdate Принудительно применить изменения
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function patchAccess(
        string $recordingKey,
        array $userAccesses,
        ?string $linkScope = null,
        bool $forceUpdate = false
    ): void {
        if ($linkScope !== null) {
            LinkAccessScope::from($linkScope);
        }

        $data = [
            'userAccesses' => $userAccesses,
            'forceUpdate' => $forceUpdate,
        ];

        if ($linkScope !== null) {
            $data['linkAccess'] = ['scope' => $linkScope];
        }

        $this->client->patch("Recordings/{$recordingKey}/access", $data);
    }

    /**
     * Получает транскрипт записи
     *
     * @param string $recordingKey Ключ записи
     * @return array TalkTranscript: {status, statusMessage, transcriptId, tracks: [{trackId, speaker,
     *               chunks: [{chunkId, startTimeOffsetInMillis, endTimeOffsetInMillis, text, words[],
     *               confidence}]}], errors[]}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function transcript(string $recordingKey): array
    {
        return $this->client->get("recordings/{$recordingKey}/transcript");
    }

    /**
     * Получает саммари записи заданного типа
     *
     * @param string $recordingKey Ключ записи
     * @param string $type Тип саммаризации — значение {@see SummaryType}
     * @return array TalkSummaryV2Result: {summaryId, status, startedAt, chunks: [{type, timestamp,
     *               version, text}], hidden}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function summary(string $recordingKey, string $type): array
    {
        $summaryType = SummaryType::from($type);

        return $this->client->get("recordings/{$recordingKey}/summary/{$summaryType->value}");
    }

    /**
     * Получает составной результат SpeechCore: транскрипт + оба типа саммари
     *
     * @param string $recordingKey Ключ записи
     * @return array TalkCompositeSpeechCoreResult: {transcriptionV2, shortSummaryV2, protocolV2}
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkNotFoundException
     * @throws TalkRateLimitException
     */
    public function composite(string $recordingKey): array
    {
        return $this->client->get("recordings/v2/{$recordingKey}/summary");
    }
}
