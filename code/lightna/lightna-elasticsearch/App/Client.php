<?php

declare(strict_types=1);

namespace Lightna\Elasticsearch\App;

use Lightna\Engine\App\Exception\LightnaException;
use Lightna\Engine\App\ObjectA;

class Client extends ObjectA
{
    static public float $timeSpent = 0;

    /** @AppConfig(elasticsearch) */
    protected array $config;
    protected string $endpoint;

    protected function init(array $data = []): void
    {
        $this->endpoint = 'http://' . $this->config['connection']['host'] . ':' . $this->config['connection']['port'];
    }

    public function search(string $indexName, array $query): array
    {
        return $this->request($this->getPrefix() . $indexName . '/document/_search', $query);
    }

    public function getPrefix(): string
    {
        return $this->config['prefix'];
    }

    protected function request(string $action, array $body): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint . '/' . $action);
        $this->setRequestOptions($ch, $body);

        $startTime = microtime(true);
        $response = curl_exec($ch);
        static::$timeSpent += microtime(true) - $startTime;

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($responseCode !== 200) {
            throw new LightnaException('Elasticsearch respond HTTP ' . $responseCode . ':' . $response);
        }

        return json_decode($response, true);
    }

    protected function setRequestOptions($ch, array $body): void
    {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['connection']['timeout'] ?? 15);
    }
}
