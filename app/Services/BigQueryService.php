<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;

class BigQueryService
{
    protected BigQueryClient $client;

    public function __construct()
    {
        $this->client = new BigQueryClient([
            'projectId' => config('services.bigquery.project_id'),
            'keyFilePath' => config('services.bigquery.key_file'),
        ]);
    }

    public function runQuery(string $sql): array
    {
        $queryJob = $this->client->query($sql);
        $results = $this->client->runQuery($queryJob);

        if (!$results->isComplete()) {
            throw new \Exception('BigQuery job did not complete.');
        }

        return iterator_to_array($results->rows());
    }
}
