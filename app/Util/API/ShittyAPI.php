<?php

namespace App\Util\API;

use GuzzleHttp\Client;
use JsonStreamingParser\Parser;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\StreamWrapper;
use JsonStreamingParser\ParsingError;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use JsonStreamingParser\Listener\InMemoryListener;

class ShittyAPI {

    const URL_BASE = 'https://swgoh.shittybots.me/api';

    const API_PLAYER = 'player';
    const API_GUILD = 'guild';
    const API_RAW = 'query';

    /**
     * The HTTP Client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    public function getPlayer($allyCode) {
        return $this->callAPI(static::API_PLAYER, $allyCode);
    }

    public function getGuild($allyCode, Callable $memberCallback = null) {
        return $this->callAPI(static::API_GUILD, $allyCode, '', $memberCallback);
    }

    public function callAPI($api, $payload, $query = '', Callable $memberCallback = null) {
        $URL = $this->buildAPIUrl($api, $payload, $query);
        try {
            $response = $this->getHttpClient()->get($URL, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'shittybot' => config('services.shitty_bot.token'),
                ],
                'json' => [],
            ]);
            $raw = $response->getBody();
            $response = null;
            unset($response);
        } catch (ClientException | ServerException $e) {
            // $response = $e->getResponse();
            // $body = json_decode($response->getBody(), true);

            // if ($response->getStatusCode() == 401 || $body['code'] == 401 || $response->getStatusCode() == 503) {
            //     $this->setToken(null);
            //     $args = func_get_args();
            //     return call_user_func_array([$this, __METHOD__], $args);
            // }

            throw $e;
        }
        if (is_null($memberCallback)) {
            $listener = new InMemoryListener;
        } else {
            $listener = new GuildListener($memberCallback);
        }

        try {
            $parser = new Parser(StreamWrapper::getResource($raw), $listener);
            $parser->parse();
        } catch (ParsingError $e) {
            throw new APIException((string)$raw . " -> using [$URL]", 0, $e);
        }

        return collect($listener->getJson());
    }

    protected function buildAPIUrl($path, $payload, $query = '') {
        $query = strlen($query) === 0 ? '' : str_start($query, '?');
        return static::URL_BASE . str_start($path, '/') . '/' . $payload . $query;
    }

    /**
     * Get a instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }

    /**
     * Set the Guzzle HTTP client instance.
     *
     * @param  \GuzzleHttp\Client  $client
     * @return $this
     */
    public function setHttpClient(Client $client)
    {
        $this->httpClient = $client;

        return $this;
    }
}