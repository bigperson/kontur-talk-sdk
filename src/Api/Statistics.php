<?php

namespace Kontur\Talk\Api;

use DateTimeInterface;
use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkRateLimitException;

/**
 * API для работы со статистикой
 */
class Statistics extends ApiClient
{
    /**
     * Получает статистику по онлайну
     *
     * @return array Информация об идущих конференциях и пользователях онлайн
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getOnline(): array
    {
        return $this->client->get('Domain/stats/online');
    }

    /**
     * Получает статистику по зарегистрированным пользователям
     *
     * @return array Статистика по пользователям
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getRegisteredUsers(): array
    {
        return $this->client->get('domain/statisticsTotal');
    }

    /**
     * Получает статистику по активным пользователям пространства
     *
     * @param DateTimeInterface|null $start Начало периода
     * @param DateTimeInterface|null $end Конец периода
     * @return array Статистика по активным пользователям
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getActiveUsers(?DateTimeInterface $start = null, ?DateTimeInterface $end = null): array
    {
        $params = [];

        if ($start !== null) {
            $params['start'] = $start->format('Y-m-d\TH:i:s.v\Z');
        }

        if ($end !== null) {
            $params['end'] = $end->format('Y-m-d\TH:i:s.v\Z');
        }

        return $this->client->get('domain/statistics', $params);
    }

    /**
     * Получает статистику по конференциям
     *
     * @param DateTimeInterface|null $fromDate Начало периода
     * @param DateTimeInterface|null $toDate Конец периода
     * @return array Статистика по конференциям
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getConferences(?DateTimeInterface $fromDate = null, ?DateTimeInterface $toDate = null): array
    {
        $params = [];

        if ($fromDate !== null) {
            $params['fromDate'] = $fromDate->format('Y-m-d\TH:i:s.v\Z');
        }

        if ($toDate !== null) {
            $params['toDate'] = $toDate->format('Y-m-d\TH:i:s.v\Z');
        }

        return $this->client->get('Domain/stats/conferences', $params);
    }

    /**
     * Получает статистику по количеству конференций, в которые подключался киоск за период
     *
     * @param DateTimeInterface|null $start Начало периода
     * @param DateTimeInterface|null $end Конец периода
     * @return array Статистика по киоскам
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getKiosks(?DateTimeInterface $start = null, ?DateTimeInterface $end = null): array
    {
        $params = [];

        if ($start !== null) {
            $params['start'] = $start->format('Y-m-d\TH:i:s.v\Z');
        }

        if ($end !== null) {
            $params['end'] = $end->format('Y-m-d\TH:i:s.v\Z');
        }

        return $this->client->get('Domain/stats/kiosks', $params);
    }

    /**
     * Получает статистику по киоскам, участвующим в конференциях в данный момент
     *
     * @return array Количество киосков в конференциях
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getKiosksOnline(): array
    {
        return $this->client->get('Domain/stats/kiosks/online');
    }

    /**
     * Получает статистику по идущим записям в пространстве
     *
     * @return array Количество идущих записей
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getActiveRecordings(): array
    {
        return $this->client->get('Domain/stats/recordings/online');
    }

    /**
     * Получает статистику по общему объему записей в пространстве
     *
     * @return array Объем записей в облачном хранилище
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getRecordingsTotalSize(): array
    {
        return $this->client->get('domain/stats/recordings/totalSize');
    }

    /**
     * Получает статистику по количеству активных трансляций и зрителей
     *
     * @return array Информация об идущих трансляциях и зрителях
     * @throws TalkApiException
     * @throws TalkClientException
     * @throws TalkRateLimitException
     */
    public function getStreamsOnline(): array
    {
        return $this->client->get('Domain/stats/streams/online');
    }

    /**
     * Получить дату истечения текущего тарифа
     *
     * @return array
     */
    public function getTariffExpirationDate(): array
    {
        return $this->client->get('domain/stats/tariffExpirationDate');
    }
}
