<?php

namespace Kontur\Talk;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Query;
use Kontur\Talk\Api\Applications;
use Kontur\Talk\Api\Calendar;
use Kontur\Talk\Api\ConferencesHistory;
use Kontur\Talk\Api\Recordings;
use Kontur\Talk\Api\Rooms;
use Kontur\Talk\Api\Users;
use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;
use Psr\Http\Message\StreamInterface;

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
     * @var Rooms API для работы с комнатами
     */
    public Rooms $rooms;

    /**
     * @var Calendar API для работы с календарём (создание встреч на почтовом ящике организатора)
     */
    public Calendar $calendar;

    /**
     * @var ConferencesHistory API для работы с историей конференций
     */
    public ConferencesHistory $conferencesHistory;

    /**
     * @var Recordings API для работы с записями
     */
    public Recordings $recordings;

    /**
     * @var Applications API для работы с информацией о текущем API-ключе
     */
    public Applications $applications;

    /**
     * @var Users API для работы с пользователями
     */
    public Users $users;

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
        $this->rooms = new Rooms($this);
        $this->calendar = new Calendar($this);
        $this->conferencesHistory = new ConferencesHistory($this);
        $this->recordings = new Recordings($this);
        $this->applications = new Applications($this);
        $this->users = new Users($this);
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
     * Отправляет PATCH запрос к API
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
    public function patch(string $endpoint, array $data = [], array $params = []): array
    {
        $options = ['json' => $data];

        if (!empty($params)) {
            $options['query'] = $params;
        }

        return $this->request('PATCH', $endpoint, $options);
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
     * Возвращает адрес для скачивания файла записи, не скачивая сам файл.
     *
     * Запрос выполняется с отключёнными редиректами: если API отвечает 3xx,
     * возвращается заголовок `Location`; если 2xx — возвращается адрес самого запроса
     * (редиректа не произошло, файл отдаётся напрямую по этому адресу).
     *
     * @param string $recordingKey Ключ записи
     * @param string|null $quality Качество видео (например, "900p"), null — без фильтра
     * @return string Адрес для скачивания файла
     * @throws TalkClientException
     * @throws TalkApiException
     * @throws TalkRateLimitException
     * @throws TalkNotFoundException
     */
    public function downloadUrl(string $recordingKey, ?string $quality = null): string
    {
        $url = $this->fileUrl($recordingKey, $quality);

        try {
            $response = $this->httpClient->request('GET', $url, ['allow_redirects' => false]);
        } catch (GuzzleException $e) {
            throw $this->mapException($e);
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 300 && $statusCode < 400) {
            return $response->getHeaderLine('Location');
        }

        return $url;
    }

    /**
     * Скачивает файл записи и возвращает поток с его содержимым.
     *
     * @param string $recordingKey Ключ записи
     * @param string|null $quality Качество видео (например, "900p"), null — без фильтра
     * @return StreamInterface Поток с телом файла
     * @throws TalkClientException
     * @throws TalkApiException
     * @throws TalkRateLimitException
     * @throws TalkNotFoundException
     */
    public function download(string $recordingKey, ?string $quality = null): StreamInterface
    {
        $url = $this->fileUrl($recordingKey, $quality);

        try {
            $response = $this->httpClient->request('GET', $url);
        } catch (GuzzleException $e) {
            throw $this->mapException($e);
        }

        return $response->getBody();
    }

    /**
     * Строит адрес `GET /api/Recordings/{recordingKey}/file` с опциональным query-параметром qualityName.
     *
     * В спецификации `qualityName` заявлен как сегмент пути (`.../file/{qualityName}`), но объявлен
     * как query-параметр (`in: query`) без соответствующего path-параметра — несогласованность самой
     * спецификации. Единственное трактование, по которому клиент вообще может подставить значение,
     * это query: `?qualityName=...`.
     *
     * @param string $recordingKey Ключ записи
     * @param string|null $quality Качество видео, null — без фильтра
     * @return string Полный адрес файла
     */
    private function fileUrl(string $recordingKey, ?string $quality): string
    {
        $url = $this->baseUrl . '/Recordings/' . rawurlencode($recordingKey) . '/file';

        if ($quality !== null) {
            $url .= '?' . Query::build(['qualityName' => $quality]);
        }

        return $url;
    }

    /**
     * Отправляет запрос к API
     *
     * @param string $method Метод запроса (GET, POST, PUT, PATCH, DELETE)
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
            throw $this->mapException($e);
        }
    }

    /**
     * Преобразует исключение Guzzle в исключение SDK по коду ответа.
     *
     * Возвращает \Exception, а не TalkClientException: TalkApiException (и его наследники
     * TalkNotFoundException/TalkRateLimitException) — не наследники TalkClientException,
     * это два независимых подкласса \Exception.
     *
     * @param GuzzleException $e
     * @return TalkClientException|TalkApiException
     */
    private function mapException(GuzzleException $e): \Exception
    {
        $statusCode = $e->getCode();

        if ($statusCode === 429) {
            return new TalkRateLimitException('API rate limit exceeded', 429, $e);
        }

        if ($statusCode === 404) {
            return new TalkNotFoundException('Resource not found', 404, $e);
        }

        if ($statusCode >= 400 && $statusCode < 500) {
            $responseBody = '';
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
            }
            $errorData = json_decode($responseBody, true) ?? [];
            $errorMessage = $errorData['errorMessage'] ?? 'API error';

            return new TalkApiException($errorMessage, $statusCode, $e);
        }

        return new TalkClientException('API request failed: ' . $e->getMessage(), $statusCode, $e);
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
