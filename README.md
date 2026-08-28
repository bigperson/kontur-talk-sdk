# PHP SDK для работы с API Kontur Talk

[![Tests](https://github.com/bigperson/kontur-talk-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/bigperson/kontur-talk-sdk/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/bigperson/kontur-talk-sdk/v/stable)](https://packagist.org/packages/bigperson/kontur-talk-sdk)
[![Total Downloads](https://poser.pugx.org/bigperson/kontur-talk-sdk/downloads)](https://packagist.org/packages/bigperson/kontur-talk-sdk)
[![License](https://poser.pugx.org/bigperson/kontur-talk-sdk/license)](https://packagist.org/packages/bigperson/kontur-talk-sdk)

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://www.php.net/)

Неофициальный PHP SDK для удобной интеграции с API сервиса Контур.Толк.

> **Важно:** Данный SDK не является официальным продуктом компании СКБ Контур и разрабатывается независимо.

Начиная с версии 2.0.0 SDK выровнен по официальной OpenAPI-спецификации Kontur.Talk и покрывает
только те endpoint'ы, что нужны для сценария "комната → встреча в календаре → опрос истории
конференций за записями/транскриптом/саммари", плюс вебхуки и отчёт по комнате. Пути и регистр букв в них взяты из спецификации
дословно (например, `Domain/recordings`, `Recordings/{key}/access`, `recordings/{key}/transcript` —
это разные endpoint'ы, а не опечатки). Ответы возвращаются как декодированный JSON (`array`) без
DTO-слоя — так методы SDK не расходятся со спецификацией со временем.

## Установка

```bash
composer require bigperson/kontur-talk-sdk
```

## Использование

### Инициализация клиента

```php
use Kontur\Talk\TalkClient;

// Создание клиента API
$client = new TalkClient('company', 'your-api-key');
```

### Комнаты (`$client->rooms`)

```php
// Получение информации о комнате
$room = $client->rooms->get('room-key');

// Создание или обновление комнаты (тело — как в TalkRoomParams спецификации)
$room = $client->rooms->createOrUpdate('room-key', [
    'title' => 'Демо для клиента',
    'description' => 'Обсуждение условий',
    'moderatorKeys' => ['user-key-1', 'user-key-2'],
    'allowAnonymous' => true,
    'anonymousAccessExpirationDate' => '2026-12-31T23:59:59Z',
    'enableLobby' => true,
    'audioPolicy' => 'none',
    'videoPolicy' => 'none',
    'screenSharePolicy' => 'none',
]);

// Установка PIN-кода
$client->rooms->setPinCode('room-key', '123456');

// Снятие PIN-кода
$client->rooms->setPinCode('room-key', null);

// Принудительное завершение конференции для всех участников
$client->rooms->endConference('room-key');
```

### Календарь встреч (`$client->calendar`)

Встреча создаётся "от имени" организатора — на его почтовом ящике. Комнату (`roomName`) нужно
создать заранее через `$client->rooms->createOrUpdate()`.

```php
// Создание встречи в календаре организатора
$event = $client->calendar->createEvent('manager@example.com', [
    'start' => '2026-09-01T10:00:00Z',
    'end' => '2026-09-01T11:00:00Z',
    'subject' => 'Демо для клиента',
    'roomName' => 'room-key',
    'enableAutoRecording' => true,
    'requiredExternalAttendeesEmails' => ['client@example.com'],
]);

echo $event['onlineMeetingUrl']; // ссылка на встречу

// Список встреч за период (start обязателен)
$events = $client->calendar->listEvents('manager@example.com', '2026-09-01T00:00:00Z');

// Обновление встречи
$client->calendar->updateEvent('manager@example.com', $event['id'], [
    'start' => '2026-09-01T10:30:00Z',
    'end' => '2026-09-01T11:30:00Z',
    'subject' => 'Демо для клиента',
    'roomName' => 'room-key',
]);

// Изменение состава участников
$client->calendar->updateAttendees('manager@example.com', $event['id'], [
    'requiredUserKeys' => ['user-key-1'],
]);

// Отмена встречи
$client->calendar->deleteEvent('manager@example.com', $event['id'], 'Встреча отменена');
```

### История конференций (`$client->conferencesHistory`)

```php
// Батчевый опрос комнат за период (до 50 комнат за вызов)
$history = $client->conferencesHistory->list(
    fromDate: '2026-09-01T00:00:00Z',
    roomNames: ['room-key-1', 'room-key-2']
);

// Конференция по ключу
$conference = $client->conferencesHistory->get('conference-key');

// Конференция с артефактами: записи, заметки, опросы, доски
$enriched = $client->conferencesHistory->getArtifacts('conference-key');
foreach ($enriched['artifacts']['recordings'] as $recording) {
    echo $recording['id'], ' ', $recording['status'], PHP_EOL;
}
```

### Записи (`$client->recordings`)

```php
use Kontur\Talk\Enum\SummaryType;

// Список записей пространства
$page = $client->recordings->listDomain(['top' => 20, 'orderMode' => 'byTimeNewFirst']);

// Запись по ключу
$recording = $client->recordings->getDomain('recording-key');

// Транскрипт
$transcript = $client->recordings->transcript('recording-key');

// Саммари (краткое содержание или протокол)
$summary = $client->recordings->summary('recording-key', SummaryType::ShortSummary->value);

// Транскрипт + оба саммари одним вызовом
$composite = $client->recordings->composite('recording-key');

// Права доступа к записи
$access = $client->recordings->getAccess('recording-key');
$client->recordings->patchAccess(
    'recording-key',
    userAccesses: [['userKey' => 'user-key', 'roleId' => 'viewer']],
    linkScope: 'domain'
);
```

Скачивание файла записи — методы транспортного уровня (`GET /api/Recordings/{key}/file`):

```php
// Адрес файла, без скачивания (запрос идёт без автоследования за редиректом)
$url = $client->downloadUrl('recording-key', '900p');

// Тело файла потоком
$stream = $client->download('recording-key', '900p');
file_put_contents('recording.mp4', $stream);
```

> Спецификация объявляет качество как `{qualityName}` в пути, но описывает его как query-параметр
> без соответствующего path-параметра. SDK следует объявлению параметра и передаёт качество как
> `?qualityName=...` — единственная трактовка, по которой вообще есть что подставлять в запрос.

### Информация об API-ключе (`$client->applications`)

```php
$accessInfo = $client->applications->accessInfo();

echo $accessInfo['expiredAt']; // срок действия ключа

$hasRecordingScope = (bool) array_filter(
    $accessInfo['scopes'],
    fn (array $scope) => $scope['type'] === 'recording'
);
```

### Пользователи (`$client->users`)

```php
// Постраничный перебор всех пользователей пространства
$page = $client->users->scan(top: 100);

// Поиск по фильтрам (email — повторяющийся параметр)
$found = $client->users->search([
    'email' => ['user1@example.com', 'user2@example.com'],
]);

// Пользователь по ключу
$user = $client->users->getByKey('user-key');
```

### Вебхуки (`$client->webhooks`)

Требуют разрешения `application.webhooks.read` / `application.webhooks.write` у API-ключа.
Создание вебхука — асинхронное: Толк присылает на указанный `url` запрос с телом
`{"activationKey": "..."}`, и до вызова `activate()` с этим ключом события не приходят.
На создание действует лимит — 10 запросов в сутки на пространство (сверх лимита API отвечает
429, SDK бросает `TalkRateLimitException`).

```php
use Kontur\Talk\Enum\WebhookEventType;

// Список активных вебхуков пространства
$webhooks = $client->webhooks->getList();

// Создание вебхука
$webhook = $client->webhooks->create([
    'title' => 'Записи в CRM',
    'url' => 'https://example.com/talk/webhook',
    'events' => [
        WebhookEventType::RecordingCompleted->value,
        WebhookEventType::ConferencesFinished->value,
    ],
    'customHeaders' => [
        ['name' => 'X-Project-Token', 'value' => 'secret'],
    ],
]);

echo $webhook['webhookKey'];
var_dump($webhook['activated']); // false — ждём ключ активации на своём URL

// Активация ключом, пришедшим на URL вебхука
$client->webhooks->activate($webhook['webhookKey'], ['activationKey' => $activationKey]);

// Удаление
$client->webhooks->delete($webhook['webhookKey']);
```

Разбор входящих событий SDK на себя не берёт — это дело принимающей стороны. Контракт доставки:
тело события — JSON с обязательными `eventId`, `eventType` и `time` (ISO 8601); принимающий
сервер обязан ответить 200 в течение 5 секунд. Дополнительные поля зависят от типа события:

| `eventType` в доставке | Дополнительные поля |
| --- | --- |
| `ConferencesStarted` | `conferenceKey`, `roomName` |
| `ConferencesFinished` | `conferenceKey` |
| `RecordingCompleted` | `recordingKey` |
| `TranscriptionReady` | `recordingKey` |
| `UserConnectedToRoom` | `roomName`, `isAnonymous`, `userKey`, `anonymousId` |
| `UserDisconnectedFromRoom` | `roomName`, `isAnonymous`, `userKey`, `anonymousId` |

> Осторожно с регистром: в подписке (`events[]`) типы записаны в camelCase
> (`recordingCompleted`), а в доставленном событии `eventType` приходит в PascalCase
> (`RecordingCompleted`). Передать `eventType` из вебхука напрямую в `WebhookEventType::from()`
> не получится — будет `\ValueError`.

### Отчёты по комнатам (`$client->roomReport`)

Требуют разрешения `application.reporting.read`. Даты — ISO 8601 в UTC, максимальный период —
365 дней.

```php
// Отчёт по комнате за период (to необязателен — по умолчанию по текущее время)
$report = $client->roomReport->statisticsReport(
    'room-key',
    '2026-09-01T00:00:00Z',
    '2026-09-30T00:00:00Z'
);

echo $report['participantCount'];

foreach ($report['roomParticipants'] as $participant) {
    // exitTime приходит пустым, если участник ещё в комнате
    echo $participant['participantName'], ' ',
        $participant['entryTime'], ' — ', $participant['exitTime'] ?? '…', PHP_EOL;
}
```

> Отчёт по всему пространству (`GET /api/RoomReport/statistics`) в SDK не вынесен: он отдаёт файл
> xlsx, а не JSON, — весь транспорт SDK построен на декодировании тела ответа в массив.

## Документация

Подробная документация по API доступна в официальной документации Контур.Толк.

## Обработка ошибок

SDK использует исключения для обработки ошибок:

```php
use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkRateLimitException;
use Kontur\Talk\Exception\TalkNotFoundException;

try {
    $room = $client->rooms->get('room-key');
} catch (TalkNotFoundException $e) {
    // Ресурс не найден
    echo "Ресурс не найден: " . $e->getMessage();
} catch (TalkRateLimitException $e) {
    // Превышены ограничения по количеству запросов
    echo "Превышен лимит запросов к API: " . $e->getMessage();
} catch (TalkApiException $e) {
    // Ошибка API (400-499)
    echo "Ошибка API: " . $e->getMessage();
} catch (TalkClientException $e) {
    // Общая ошибка клиента
    echo "Ошибка клиента: " . $e->getMessage();
}
```

Методы, принимающие значения перечислений (`Kontur\Talk\Enum\*`, например `SummaryType` в
`recordings->summary()`, `LinkAccessScope` в `recordings->patchAccess()` или `WebhookEventType`
в `webhooks->create()`), при недопустимом значении бросают стандартный `\ValueError` ещё до
отправки запроса.

## Требования

- PHP 8.2 или выше
- Guzzle HTTP 7.0 или выше
- Расширение JSON
