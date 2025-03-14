<?php

namespace Kontur\Talk\Api;

use DateTime;
use Kontur\Talk\TalkClient;

class Reports extends ApiClient
{
    /**
     * Получить список отчетов
     *
     * @param int|null $top Максимальное количество возвращаемых записей
     * @param string|null $offset Токен для постраничной загрузки
     * @param DateTime|null $startTime Начальное время для фильтрации
     * @param DateTime|null $endTime Конечное время для фильтрации
     * @return array
     */
    public function getAll(
        ?int $top = 100,
        ?string $offset = null,
        ?DateTime $startTime = null,
        ?DateTime $endTime = null
    ): array {
        $params = [
            'top' => $top
        ];

        if ($offset !== null) {
            $params['offset'] = $offset;
        }

        if ($startTime !== null) {
            $params['startTime'] = $startTime->format('Y-m-d\TH:i:s.v\Z');
        }

        if ($endTime !== null) {
            $params['endTime'] = $endTime->format('Y-m-d\TH:i:s.v\Z');
        }

        return $this->client->get('reports', $params);
    }

    /**
     * Получить отчет по ID
     *
     * @param string $reportId ID отчета
     * @return array
     */
    public function getById(string $reportId): array
    {
        return $this->client->get("reports/{$reportId}");
    }

    /**
     * Создать новый отчет
     *
     * @param string $type Тип отчета
     * @param array $parameters Параметры отчета
     * @return array
     */
    public function create(string $type, array $parameters): array
    {
        $data = [
            'type' => $type,
            'parameters' => $parameters
        ];

        return $this->client->post('reports', $data);
    }

    /**
     * Удалить отчет
     *
     * @param string $reportId ID отчета
     * @return array
     */
    public function delete(string $reportId): array
    {
        return $this->client->delete("reports/{$reportId}");
    }

    /**
     * Получить ссылку для скачивания отчета
     *
     * @param string $reportId ID отчета
     * @return array
     */
    public function getDownloadLink(string $reportId): array
    {
        return $this->client->get("reports/{$reportId}/downloadLink");
    }
}
