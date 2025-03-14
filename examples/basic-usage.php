<?php

// Убедитесь, что вы установили пакет через composer:
// composer require kontur/talk-sdk

require_once __DIR__ . '/../vendor/autoload.php';

use Kontur\Talk\TalkClient;
use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkRateLimitException;
use Kontur\Talk\Exception\TalkNotFoundException;

// Замените значения параметров на ваши
$space = 'your-space';
$apiKey = 'your-api-key';

try {
    // Инициализация клиента
    $client = new TalkClient($space, $apiKey);
    
    // Получение списка пользователей
    $users = $client->users->get(10);
    echo "Пользователи:\n";
    foreach ($users['users'] as $user) {
        echo "- {$user['firstname']} {$user['surname']} ({$user['email']})\n";
    }
    
    // Получение списка ролей
    $roles = $client->roles->getAll();
    echo "\nРоли:\n";
    foreach ($roles as $role) {
        echo "- {$role['title']} ({$role['roleId']})\n";
    }
    
    // Получение статистики по онлайну
    $stats = $client->statistics->getOnline();
    echo "\nСтатистика онлайн:\n";
    echo "Пользователей: {$stats['usersCount']}\n";
    echo "Конференций: {$stats['conferencesCount']}\n";
    
    // Создание или обновление комнаты
    $room = $client->rooms->createOrUpdate(
        'test-room',
        'Тестовая комната',
        'Описание тестовой комнаты'
    );
    echo "\nКомната создана/обновлена: {$room['title']}\n";
    
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