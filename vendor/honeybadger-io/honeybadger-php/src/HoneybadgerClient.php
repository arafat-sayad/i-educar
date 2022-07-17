<?php

namespace Honeybadger;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Honeybadger\Exceptions\ServiceException;
use Honeybadger\Exceptions\ServiceExceptionFactory;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class HoneybadgerClient
{
    /**
     * @var \Honeybadger\Config
     */
    protected $config;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @param  \Honeybadger\Config  $config
     * @param  \GuzzleHttp\Client|null  $client
     */
    public function __construct(Config $config, Client $client = null)
    {
        $this->config = $config;
        $this->client = $client ?? $this->makeClient();
    }

    /**
     * @param  array  $notification
     * @return array
     */
    public function notification(array $notification): array
    {
        try {
            $response = $this->client->post(
                'v1/notices',
                ['body' => json_encode($notification, JSON_PARTIAL_OUTPUT_ON_ERROR)]
            );
        } catch (Throwable $e) {
            $this->handleServiceException(ServiceException::generic($e));

            return [];
        }

        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            $this->handleServiceException((new ServiceExceptionFactory($response))->make());

            return [];
        }

        return (string) $response->getBody()
            ? json_decode($response->getBody(), true)
            : [];
    }

    /**
     * @param  string  $key
     * @return void
     */
    public function checkin(string $key): void
    {
        try {
            $response = $this->client->head(sprintf('v1/check_in/%s', $key));
        } catch (Throwable $e) {
            $this->handleServiceException(ServiceException::generic($e));

            return;
        }

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $this->handleServiceException((new ServiceExceptionFactory($response))->make());

            return;
        }
    }

    private function handleServiceException(ServiceException $e): void
    {
        $serviceExceptionHandler = $this->config['service_exception_handler'];
        call_user_func_array($serviceExceptionHandler, [$e]);
    }

    /**
     * @return \GuzzleHttp\Client
     */
    private function makeClient(): Client
    {
        return new Client([
            'base_uri' => $this->config['endpoint'],
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::HEADERS => [
                'X-API-Key' => $this->config['api_key'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            RequestOptions::TIMEOUT => $this->config['client']['timeout'],
            RequestOptions::PROXY => $this->config['client']['proxy'],
            RequestOptions::VERIFY => $this->config['client']['verify'] ?? true,
        ]);
    }
}
