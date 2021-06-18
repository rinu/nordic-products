<?php

namespace tests;

class Products extends \NordicProducts\Products {
    protected function getClient(): \GuzzleHttp\Client {
        if (!$this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }
}
