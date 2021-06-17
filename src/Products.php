<?php

namespace NordicProducts;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use Psr\Http\Message\ResponseInterface;

class Products {
    private const BASE_URI = 'https://api.nordic-digital.com/v1/';

    public $apiKey = '';

    private $client;

    private $totalProductPages = 1;

    private $products = [];

    private $totalStockPages = 1;

    private $stocks = [];

    public function getProducts(bool $withPriceStock = false): ?array {
        $currentProductPage = 1;
        $currentStockPage = 1;
        $firstRequestPromises = [];
        $promises = [];

        $firstProductRequest = $this->startProductRequest($currentProductPage);
        $firstRequestPromises[] = $firstProductRequest;
        $firstProductRequest->then(function () use (&$currentProductPage, &$promises) {
            while ($currentProductPage < $this->totalProductPages) {
                $currentProductPage++;

                $promises[] = $this->startProductRequest($currentProductPage);
            }
        });

        if ($withPriceStock) {
            $firstStockRequest = $this->startStockRequest($currentStockPage);
            $firstRequestPromises[] = $firstStockRequest;
            $firstStockRequest->then(function () use (&$currentStockPage, &$promises) {
                while ($currentStockPage < $this->totalStockPages) {
                    $currentStockPage++;

                    $promises[] = $this->startStockRequest($currentStockPage);
                }
            });
        }

        Utils::all($firstRequestPromises)->wait();
        Utils::all($promises)->wait();

        if ($withPriceStock) {
            foreach ($this->products as $productId => $product) {
                $this->products[$productId] = array_merge($product, $this->stocks[$productId] ?? []);
            }
        }

        return $this->products;
    }

    private function getClient(): Client {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => self::BASE_URI,
                'headers' => ['Authorization' => 'Basic ' . \base64_encode($this->apiKey)]
            ]);
        }

        return $this->client;
    }

    private function startProductRequest(int $page): PromiseInterface {
        return $this->getClient()->getAsync('product', [
            'query' => [
                'page' => $page,
                'per_page' => 1000
            ]
        ])->then(function (ResponseInterface $response) {
            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['resource']['paging']['totalPages'])) {
                $this->totalProductPages = $responseData['resource']['paging']['totalPages'];
            }

            if (isset($responseData['products'])) {
                foreach ($responseData['products'] as $product) {
                    $this->products[$product['id']] = $product;
                }
            }
        });
    }

    private function startStockRequest(int $page): PromiseInterface {
        return $this->getClient()->getAsync('stock', [
            'query' => [
                'page' => $page,
                'per_page' => 1000
            ]
        ])->then(function (ResponseInterface $response) {
            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['resource']['paging']['totalPages'])) {
                $this->totalStockPages = $responseData['resource']['paging']['totalPages'];
            }

            if (isset($responseData['stock'])) {
                foreach ($responseData['stock'] as $stock) {
                    $this->stocks[$stock['id']] = $stock;
                }
            }
        });
    }
}
