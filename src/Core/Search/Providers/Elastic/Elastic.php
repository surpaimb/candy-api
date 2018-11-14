<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Search\Providers\Elastic\Types\ProductType;

class Elastic implements SearchContract
{
    protected $client;

    protected $indexer;

    public function __construct(Search $client, Indexer $indexer)
    {
        $this->client = $client;
        $this->indexer = $indexer;
    }

    /**
     *
     * @return Indexer
     */
    public function indexer()
    {
        return $this->indexer;
    }

    /**
     *
     * @return Search
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * @return ProductType|mixed
     */
    public function products()
    {
        return app()->make(ProductType::class);
    }
}
