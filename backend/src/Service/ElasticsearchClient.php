<?php

namespace App\Service;

use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchClient
{
    private $client;

    public function __construct(string $host = 'http://elasticsearch:9200')
    {
        $this->client = ClientBuilder::create()
            ->setHosts([$host])
            ->build();
    }

    public function getClient()
    {
        return $this->client;
    }

}