<?php

namespace Kontur\Talk\Api;

use Kontur\Talk\TalkClient;

/**
 * Абстрактный базовый класс для всех клиентов API
 */
abstract class ApiClient
{
    /**
     * @var TalkClient Клиент API Kontur Talk
     */
    protected TalkClient $client;

    /**
     * Конструктор
     *
     * @param TalkClient $client Клиент API Kontur Talk
     */
    public function __construct(TalkClient $client)
    {
        $this->client = $client;
    }
}
