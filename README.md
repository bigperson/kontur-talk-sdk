# PHP SDK для работы с API Kontur Talk

[![Tests](https://github.com/bigperson/kontur-talk-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/bigperson/kontur-talk-sdk/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/bigperson/kontur-talk-sdk/v/stable)](https://packagist.org/packages/bigperson/kontur-talk-sdk)
[![Total Downloads](https://poser.pugx.org/bigperson/kontur-talk-sdk/downloads)](https://packagist.org/packages/bigperson/kontur-talk-sdk)
[![License](https://poser.pugx.org/bigperson/kontur-talk-sdk/license)](https://packagist.org/packages/bigperson/kontur-talk-sdk)

Неофициальный PHP SDK для удобной интеграции с API сервиса Контур.Толк.

> **Важно:** Данный SDK не является официальным продуктом компании СКБ Контур и разрабатывается независимо.

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

### Работа с пользователями

```php
// Получение списка пользователей
$users = $client->users->get(100, 0, null, [], null, false, true);

// Получение пользователя по ключу
$user = $client->users->getByKey('user-key');

// Создание или обновление пользователей
$result = $client->users->createOrUpdate([
    [
        'email' => 'user@example.com',
        'firstname' => 'Иван',
        'surname' => 'Иванов',
        'post' => 'Менеджер'
    ]
]);

// Блокировка пользователя
$client->users->setPermissions('user-key', true);

// Разблокировка пользователя
$client->users->setPermissions('user-key', false);
```

### Работа с ролями

```php
// Получение списка ролей
$roles = $client->roles->getAll();

// Получение информации о роли
$role = $client->roles->get('admin', true);

// Создание роли
$newRole = $client->roles->create('Тестировщик', 'Роль для тестировщиков', [
    [
        'productId' => 'talk',
        'permissionId' => 'remoteControl'
    ]
]);

// Управление ролями пользователя
$client->roles->manageUserRoles('user-key', ['admin'], ['kioskAdmin']);
```

### Работа с комнатами

```php
// Получение информации о комнате
$room = $client->rooms->get('room-key');

// Создание или обновление комнаты
$room = $client->rooms->createOrUpdate(
    'room-key',
    'Тестовая комната',
    'Описание комнаты',
    ['moderator-key-1', 'moderator-key-2'],
    new DateTime('2023-12-31T23:59:59Z'),
    true,
    false,
    'none',
    'none',
    'none',
    0
);

// Добавление модератора
$client->rooms->addModerator('room-key', 'user-key');

// Установка PIN-кода
$client->rooms->setPinCode('room-key', '123456');

// Удаление PIN-кода
$client->rooms->setPinCode('room-key', '');

// Принудительное завершение конференции
$client->rooms->endConference('room-key');
```

### Исходящие звонки

```php
// Выполнение исходящего звонка
$result = $client->rooms->notifyCall(
    'room-key',
    'Тестовая комната',
    'caller-user-key',
    [
        [
            'userKey' => 'callee-user-key',
            'userCallMethod' => 'talk'
        ],
        [
            'phoneNumber' => '+7 (999) 123-45-67'
        ],
        [
            'email' => 'user@example.com'
        ]
    ]
);

// Отмена звонка
$client->rooms->cancelCall('room-key');
```

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
    $users = $client->users->get();
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

## Требования

- PHP 8.2 или выше
- Guzzle HTTP 7.0 или выше
- Расширение JSON