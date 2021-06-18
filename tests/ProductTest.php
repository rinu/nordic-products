<?php

use Codeception\Test\Unit;

class ProductTest extends Unit {
    /** @var \tests\Products */
    private $products;

    protected function _before() {
        $this->products = new \tests\Products();
        $this->products->apiKey = 'test';
    }

    public function testGettingProducts() {
        $products = $this->products->getProducts();

        self::assertEquals([
            1 => ['id' => 1],
            2 => ['id' => 2],
            3 => ['id' => 3]
        ], $products);
    }

    public function testGettingProductsWithPrices() {
        $products = $this->products->getProducts(true);

        self::assertEquals([
            1 => ['id' => 1, 'price' => 5],
            2 => ['id' => 2, 'price' => 6],
            3 => ['id' => 3, 'price' => 7]
        ], $products);
    }
}
