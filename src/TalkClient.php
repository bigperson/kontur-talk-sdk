<?php

namespace Kontur\Talk;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Kontur\Talk\Api\Users;
use Kontur\Talk\Api\Roles;
use Kontur\Talk\Api\Rooms;
use Kontur\Talk\Api\Statistics;
use Kontur\Talk\Api\Meetings;
use Kontur\Talk\Api\Calendars;
use Kontur\Talk\Api\Recordings;
use Kontur\Talk\Api\Kiosks;
use Kontur\Talk\Api\ApiKeys;
use Kontur\Talk\Api\Reports;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkRateLimitException;
use Kontur\Talk\Exception\TalkNotFoundException;

/**
 * Основной класс клиента SDK для работы с API Kontur Talk
 */
class TalkClient
{
    /**
     * @var string URL API
     */
    private string $baseUrl;

    /**
     * @var string API ключ
     */
    private string $apiKey;

    /**
     * @var HttpClient HTTP клиент
     */
    private HttpClient $httpClient;

    /**
     * @var Users API для работы с пользователями
     */
    public Users $users;

    /**
     * @var Roles API для работы с ролями
     */
    public Roles $roles;

    /**
     * @var Rooms API для работы с комнатами
     */
    public Rooms $rooms;

    /**
     * @var Statistics API для работы со статистикой
     */
    public Statistics $statistics;

    /**
     * @var Meetings API для работы со встречами
     */
    public Meetings $meetings;

    /**
     * @var Calendars API для работы с календарями
     */
    public Calendars $calendars;

    /**
     * @var Recordings API для работы с записями
     */
    public Recordings $recordings;

    /**
     * @var Kiosks API для работы с киосками
     */
    public Kiosks $kiosks;

    /**
     * @var ApiKeys API для работы с API ключами
     */
    public ApiKeys $apiKeys;

    /**
     * @var Reports API для работы с отчетами
     */
    public Reports $reports;

    /**
     * Конструктор клиента API
     *
     * @param string $space Пространство Kontur Talk (например, "company")
     * @param string $apiKey API ключ для авторизации
     */
    public function __construct(string $space, string $apiKey)
    {
        $this->baseUrl = "https://{$space}.ktalk.ru/api";
        $this->apiKey = $apiKey;

        $this->httpClient = new HttpClient([
            'headers' => [
                'X-Auth-Token' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        // Инициализация API клиентов
        $this->users = new Users($this);
        $this->roles = new Roles($this);
        $this->rooms = new Rooms($this);
        $this->statistics = new Statistics($this);
        $this->meetings = new Meetings($this);
        $this->calendars = new Calendars($this);
        $this->recordings = new Recordings($this);
        $this->kiosks = new Kiosks($this);
        $this->apiKeys = new ApiKeys($this);
        $this->reports = new Reports($this);
    }

    /**
     * Отправляет GET запрос к API
     *
     * @param string $endpoint Конечная точка API
     * @param array $params Параметры запроса
     * @return array Ответ API
     * @throws TalkClientException
     * @throws TalkApiException
     * @throws TalkRateLimitException
     * @throws TalkNotFoundException
     */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $params]);
    }

    /**
     * Отправляет POST запрос к API
     *
     * @param string $endpoint Конечная точка API
     * @param array $data Данные для отправки
     * @param array $params Параметры запроса
     * @return array Ответ API
     * @throws TalkClientException
     * @throws TalkApiException
     * @throws TalkRateLimitException
     * @throws TalkNotFoundException
     */
    public function post(string $endpoint, array $data = [], array $params = []): array
    {
        $options = ['json' => $data];

        if (!empty($params)) {
            $options['query'] = $params;
        }

        return $this->request('POST', $endpoint, $options);
    }

    /**
     * Отправляет PUT запрос к API
     *
     * @param string $endpoint Конечная точка API
     * @param array $data Данные для отправки
     * @param array $params Параметры запроса
     * @return array Ответ API
     * @throws TalkClientException
     * @throws TalkApiException
     * @throws TalkRateLimitException
     * @throws TalkNotFoundException
     */
    public function put(string $endpoint, array $data = [], array $params = []): array
    {
        $options = ['json' => $data];

        if (!empty($params)) {
            $options['query'] = $params;
        }

        return $this->request('PUT', $endpoint, $options);
    }

    /**
     * Отправляет DELETE запрос к API
     *
     * @param string $endpoint Конечная точка API
     * @param array $params Параметры запроса
     * @return array Ответ API
     * @throws TalkClientException
     * @throws TalkApiException
     * @throws TalkRateLimitException
     * @throws TalkNotFoundException
     */
    public function delete(string $endpoint, array $params = []): array
    {
        return $this->request('DELETE', $endpoint, ['query' => $params]);
    }

    /**
     * Отправляет запрос к API
     *
     * @param string $method Метод запроса (GET, POST, PUT, DELETE)
     * @param string $endpoint Конечная точка API
     * @param array $options Опции запроса
     * @return array Ответ API
     * @throws TalkClientException
     * @throws TalkApiException
     * @throws TalkRateLimitException
     * @throws TalkNotFoundException
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        try {
            $response = $this->httpClient->request($method, $url, $options);
            $body = $response->getBody()->getContents();

            if (empty($body)) {
                return [];
            }

            return json_decode($body, true) ?? [];
        } catch (GuzzleException $e) {
            $statusCode = $e->getCode();

            if ($statusCode === 429) {
                throw new TalkRateLimitException('API rate limit exceeded', 429, $e);
            }

            if ($statusCode === 404) {
                throw new TalkNotFoundException('Resource not found', 404, $e);
            }

            if ($statusCode >= 400 && $statusCode < 500) {
                $responseBody = '';
                if (method_exists($e, 'getResponse') && $e->getResponse()) {
                    $responseBody = $e->getResponse()->getBody()->getContents();
                }
                $errorData = json_decode($responseBody, true) ?? [];
                $errorMessage = $errorData['errorMessage'] ?? 'API error';

                throw new TalkApiException($errorMessage, $statusCode, $e);
            }

            throw new TalkClientException('API request failed: ' . $e->getMessage(), $statusCode, $e);
        }
    }

    /**
     * Возвращает HTTP клиент
     *
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Возвращает базовый URL API
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
