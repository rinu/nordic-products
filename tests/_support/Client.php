<?php

namespace tests;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;

class Client extends \GuzzleHttp\Client {
    /** @var Promise[] */
    private $promises = [
        'product' => [],
        'stock' => []
    ];

    public function __construct(array $config = []) {}

    public function getAsync($uri, array $options = []): PromiseInterface {
        $promiseIndex = count($this->promises[$uri]);
        $this->promises[$uri][$promiseIndex] = new Promise(function () use ($uri, $promiseIndex) {
            $this->promises[$uri][$promiseIndex]->resolve(
                new Response(200, [], file_get_contents(__DIR__ . '/mockResponses/' . $uri . '/' . ($promiseIndex + 1) . '.json'))
            );
        });

        return $this->promises[$uri][$promiseIndex];
    }
}
