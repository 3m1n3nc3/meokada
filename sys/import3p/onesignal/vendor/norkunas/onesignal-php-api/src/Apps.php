<?php

declare(strict_types=1);

namespace OneSignal;

use OneSignal\Resolver\ResolverFactory;

class Apps extends AbstractApi
{
    private $resolverFactory;

    public function __construct(OneSignal $client, ResolverFactory $resolverFactory)
    {
        parent::__construct($client);

        $this->resolverFactory = $resolverFactory;
    }

    /**
     * Get information about application with provided ID.
     *
     * User authentication key must be set.
     *
     * @param string $id ID of your application
     */
    public function getOne(string $id): array
    {
        $request = $this->createRequest('GET', "/apps/$id");
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getUserAuthKey()}");

        return $this->client->sendRequest($request);
    }

    /**
     * Get information about all your created applications.
     *
     * User authentication key must be set.
     */
    public function getAll(): array
    {
        $request = $this->createRequest('GET', '/apps');
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getUserAuthKey()}");

        return $this->client->sendRequest($request);
    }

    /**
     * Create a new application with provided data.
     *
     * User authentication key must be set.
     *
     * @param array $data Application data
     */
    public function add(array $data): array
    {
        $resolvedData = $this->resolverFactory->createAppResolver()->resolve($data);

        $request = $this->createRequest('POST', '/apps');
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getUserAuthKey()}");
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($resolvedData));

        return $this->client->sendRequest($request);
    }

    /**
     * Update application with provided data.
     *
     * User authentication key must be set.
     *
     * @param string $id   ID of your application
     * @param array  $data New application data
     */
    public function update(string $id, array $data): array
    {
        $resolvedData = $this->resolverFactory->createAppResolver()->resolve($data);

        $request = $this->createRequest('PUT', "/apps/$id");
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getUserAuthKey()}");
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($resolvedData));

        return $this->client->sendRequest($request);
    }

    /**
     * Create a new segment for application with provided data.
     *
     * @param string $appId ID of your application
     * @param array  $data  Segment Data
     */
    public function createSegment($appId, array $data): array
    {
        $resolvedData = $this->resolverFactory->createSegmentResolver()->resolve($data);

        $request = $this->createRequest('POST', "/apps/$appId/segments");
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->createStream($resolvedData));

        return $this->client->sendRequest($request);
    }

    /**
     * Delete existing segment from your application.
     *
     * Application auth key must be set.
     *
     * @param string $appId     Application ID
     * @param string $segmentId Segment ID
     */
    public function deleteSegment(string $appId, string $segmentId): array
    {
        $request = $this->createRequest('DELETE', "/apps/$appId/segments/$segmentId");
        $request = $request->withHeader('Authorization', "Basic {$this->client->getConfig()->getApplicationAuthKey()}");

        return $this->client->sendRequest($request);
    }
}
