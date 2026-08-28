# Журнал изменений

Все значимые изменения в этом проекте будут задокументированы в этом файле.

Формат основан на [Keep a Changelog](https://keepachangelog.com/ru/1.0.0/),
и этот проект следует [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Добавлено
- `Api\Webhooks` — вебхуки пространства: `getList()`, `create()`, `activate()`, `delete()`
  (`GET/POST /api/Webhooks`, `POST /api/Webhooks/{webhookKey}/activate`,
  `DELETE /api/Webhooks/{webhookKey}`). Создание асинхронное: Толк присылает `activationKey`
  на URL вебхука, его нужно вернуть в `activate()`; лимит создания — 10 запросов в сутки
  на пространство
- `Api\RoomReport` — отчёт по комнате за период: `statisticsReport()`
  (`GET /api/RoomReport/{roomName}/statistics/report`), с временем входа и выхода участников.
  Отчёт по всему пространству (`GET /api/RoomReport/statistics`) не вынесен — отдаёт xlsx,
  а не JSON
- `Kontur\Talk\Enum\WebhookEventType` — типы событий вебхука; `webhooks->create()` проверяет
  значения `events[]` до отправки запроса
- Контракт доставки событий описан в докблоке `Api\Webhooks` и в README: обязательные поля
  `eventId`/`eventType`/`time`, состав дополнительных полей по типам, требование ответить 200
  за 5 секунд и расхождение регистра (подписка — camelCase, доставленный `eventType` —
  PascalCase). Разбор входящих запросов остаётся на принимающей стороне, кода в SDK не добавляет
- Свойства `TalkClient`: `$webhooks`, `$roomReport`

## [2.0.0] - 2026-08-18

Полный рерайт SDK по официальной OpenAPI-спецификации Kontur.Talk ("Talk API", 103 путей).
Прежняя версия использовала пути и модели, придуманные "по аналогии" и не существующие
в реальном API — это ломающее изменение приводит публичную поверхность SDK в соответствие
со спецификацией.

### Изменено (ломающе)
- `Api\Rooms`: методы сведены к `get()`, `createOrUpdate()`, `setPinCode()`, `endConference()` —
  ровно тому, что есть в спецификации (`GET/PUT /api/Rooms/{roomName}`,
  `POST /api/Rooms/{roomName}/lock`, `POST /api/Rooms/{roomName}/endconference`).
  `createOrUpdate()` теперь принимает тело `array $params` вместо списка позиционных аргументов
- `Api\Recordings`: полностью пересобран под `Domain/recordings`, `Recordings/{key}/access`,
  `recordings/{key}/transcript`, `recordings/{key}/summary/{type}`, `recordings/v2/{key}/summary`
  (регистр путей — как в спецификации, разные пути не унифицированы)
- `Api\Users`: сведён к `scan()`, `search()`, `getByKey()` — по `/api/Users/scan`, `/api/Users`,
  `/api/Users/{userKey}`
- `TalkClient`: добавлен `patch()`; ответ с пустым телом (200/204) по-прежнему возвращается как `[]`;
  добавлены `downloadUrl()` и `download()` для `GET /api/Recordings/{recordingKey}/file` (адрес
  файла без скачивания и потоковое скачивание тела соответственно)
- Ответы всех методов остаются декодированным JSON (`array`) — DTO-слоя как не было, так и нет

### Добавлено
- `Api\Calendar` — встречи в календаре организатора: `createEvent()`, `listEvents()`,
  `updateEvent()`, `deleteEvent()`, `updateAttendees()` (`/api/EmailCalendar/{email}/...`)
- `Api\ConferencesHistory` — история конференций: `list()` (батчевый опрос комнат,
  `roomName` — повторяющийся query-параметр), `get()`, `getArtifacts()`
  (`/api/domain/conferencesHistory`, `/api/ConferencesHistory[/v2]/{conferenceKey}`)
- `Api\Applications` — проверка API-ключа: `accessInfo()` (срок действия и выданные scope,
  `/api/domain/applications/access-info`)
- `Kontur\Talk\Enum\SummaryType`, `LinkAccessScope`, `TranscriptionStatus`, `SpeechCoreResultStatus` —
  строковые backed enum'ы для валидации входных значений методов `Api\Recordings`

### Удалено
- `Api\Conferences`, `Api\Meetings`, `Api\Calendars`, `Api\ApiKeys`, `Api\Reports` — обращались
  к несуществующим путям (`conferences/*`, `meetings/*`, `calendars/*`, `apikeys/*`, `reports/*`)
- `Api\Kiosks`, `Api\Statistics`, `Api\Roles` — существуют в официальном API, но вне поддерживаемого
  сценария этого SDK (создание комнаты → встречи в календаре → опрос истории конференций за
  записями/транскриптом/саммари)
- Соответствующие свойства `TalkClient` (`$conferences`, `$meetings`, `$calendars`, `$apiKeys`,
  `$reports`, `$kiosks`, `$statistics`, `$roles`) и их тесты

## [1.0.0] - 2024-03-14

### Добавлено
- Первая стабильная версия SDK
- Поддержка всех основных API сервиса Kontur.Talk
- Полный набор тестов для всех компонентов SDK
- Подробная документация с примерами использования
- Настройка GitHub Actions для автоматического тестирования и деплоя 