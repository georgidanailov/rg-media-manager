<?php

namespace App\Service;

use App\Service\ElasticsearchClient;

class ActivityLogger
{
    private $elasticsearch;

    public function __construct(ElasticsearchClient $elasticsearchClient)
    {
        $this->elasticsearch = $elasticsearchClient->getClient();
    }

    public function logActivity(string $type, array $data): void
    {
        $this->elasticsearch->index([
            'index' => 'activity_logs',
            'body' => [
                'type' => $type,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
                'data' => $data,
            ],
        ]);
    }
}
