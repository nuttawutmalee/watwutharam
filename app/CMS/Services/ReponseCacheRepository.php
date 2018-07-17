<?php

namespace App\CMS\Services;

use Illuminate\Cache\Repository;
use Spatie\ResponseCache\ResponseSerializer;

class ResponseCacheRepository
{
    /** @var \Illuminate\Cache\Repository */
    protected $cache;

    /** @var \Spatie\ResponseCache\ResponseSerializer */
    protected $responseSerializer;

    public function __construct(ResponseSerializer $responseSerializer, Repository $cache)
    {
        $this->cache = $cache;
        $this->responseSerializer = $responseSerializer;
    }

    /**
     * @param string $key
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param \DateTime|int $minutes
     */
    public function put($key, $response, $minutes)
    {
        $this->cache->put($key, $this->responseSerializer->serialize($response), $minutes);
    }

    public function putData($key, $data, $minutes)
    {
        $this->cache->put($key, $data, $minutes);
    }

    public function has($key)
    {
        return $this->cache->has($key);
    }

    public function get($key)
    {
        return $this->responseSerializer->unserialize($this->cache->get($key));
    }

    public function getData($key)
    {
        return $this->cache->get($key);
    }

    public function flush()
    {
        $this->cache->flush();
    }
}