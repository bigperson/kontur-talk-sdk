<?php

// Убедитесь, что вы установили пакет через composer:
// composer require bigperson/kontur-talk-sdk

require_once __DIR__ . '/../vendor/autoload.php';

use Kontur\Talk\TalkClient;
use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkRateLimitException;
use Kontur\Talk\Exception\TalkNotFoundException;

// Замените значения параметров на ваши
$space = 'your-space';
$apiKey = 'your-api-key';
$organizerEmail = 'manager@example.com';

try {
    // Инициализация клиента
    $client = new TalkClient($space, $apiKey);

    // Проверка API-ключа: срок действия и разрешённые области
    $accessInfo = $client->applications->accessInfo();
    echo "Ключ действителен до: {$accessInfo['expiredAt']}\n";

    // Создание комнаты для встречи
    $room = $client->rooms->createOrUpdate('demo-room', [
        'title' => 'Демо для клиента',
        'enableLobby' => true,
    ]);
    echo "\nКомната создана: {$room['roomName']}\n";

    // Создание встречи в календаре организатора — от её имени будет звонок
    $event = $client->calendar->createEvent($organizerEmail, [
        'start' => '2026-09-01T10:00:00Z',
        'end' => '2026-09-01T11:00:00Z',
        'subject' => 'Демо для клиента',
        'roomName' => $room['roomName'],
        'enableAutoRecording' => true,
    ]);
    echo "Встреча создана: {$event['onlineMeetingUrl']}\n";

    // Опрос истории конференций по комнате — так находят запись после звонка
    $history = $client->conferencesHistory->list(roomNames: [$room['roomName']]);
    echo "\nКонференций в истории: " . count($history['conferences']) . "\n";

    // Поиск пользователей
    $users = $client->users->search(['top' => 10]);
    echo "\nПользователи:\n";
    foreach ($users['users'] as $user) {
        echo "- {$user['firstname']} {$user['surname']} ({$user['email']})\n";
    }
} catch (TalkNotFoundException $e) {
    // Ресурс не найден
    echo "Ошибка 404: " . $e->getMessage() . "\n";
} catch (TalkRateLimitException $e) {
    // Превышен лимит запросов
    echo "Ошибка 429: " . $e->getMessage() . "\n";
} catch (TalkApiException $e) {
    // Ошибка API
    echo "Ошибка API: " . $e->getMessage() . "\n";
} catch (TalkClientException $e) {
    // Общая ошибка клиента
    echo "Ошибка клиента: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    // Другие исключения
    echo "Ошибка: " . $e->getMessage() . "\n";
}
