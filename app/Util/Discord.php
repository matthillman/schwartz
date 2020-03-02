<?php

namespace App\Util;

use Exception;
use WebSocket\Client;
use Illuminate\Support\Arr;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use NotificationChannels\Discord\Exceptions\CouldNotSendNotification;

class Discord {

    /**
     * Discord API base URL.
     *
     * @var string
     */
    protected $baseUrl = 'https://discordapp.com/api';

    /**
     * @var string
     */
    protected $gateway = 'wss://gateway.discord.gg';

    /**
     * API HTTP client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    protected $socket;

    /**
     * Discord API token.
     *
     * @var string
     */
    protected $token;

    /**
     * @param \GuzzleHttp\Client $http
     * @param string $token
     */
    public function __construct(HttpClient $http, $token)
    {
        $this->httpClient = $http;
        $this->token = $token;
    }

    public function getGuild($guildID)
    {
        return $this->request('GET', "guilds/$guildID");
    }

    public function getGuildMember($guildID, $memberID = '')
    {
        // return $this->request('GET', "guilds/$guildId/members");
        $this->identify();
        $client = $this->getSocket();
        $client->send(json_encode([
            'op' => 8,
            'd' => [
                'guild_id' => $guildID,
                'limit' => 0,
                (empty($memberID) ? 'query' : 'user_ids') => $memberID,
            ],
        ]));

        $response = $client->receive();
        \Log::debug("Response 2", [$response]);
        return collect(json_decode($response, true));
    }

    /**
     * Perform an HTTP request with the Discord API.
     *
     * @param string $verb
     * @param string $endpoint
     * @param array $data
     *
     * @return array
     *
     * @throws \NotificationChannels\Discord\Exceptions\CouldNotSendNotification
     */
    protected function request($verb, $endpoint, array $data = [])
    {
        $url = rtrim($this->baseUrl, '/').'/'.ltrim($endpoint, '/');

        try {
            $options = [
                'headers' => [
                    'Authorization' => 'Bot '.$this->token,
                ],
            ];

            if ($verb !== 'GET') {
                $options['json'] = $data;
            }
            $response = $this->httpClient->request($verb, $url, $options);
        } catch (RequestException $exception) {
            if ($response = $exception->getResponse()) {
                throw CouldNotSendNotification::serviceRespondedWithAnHttpError($response);
            }

            throw CouldNotSendNotification::serviceCommunicationError($exception);
        } catch (Exception $exception) {
            throw CouldNotSendNotification::serviceCommunicationError($exception);
        }

        $body = json_decode($response->getBody(), true);

        if (Arr::get($body, 'code', 0) > 0) {
            throw CouldNotSendNotification::serviceRespondedWithAnApiError($body);
        }

        return collect($body);
    }

    /**
     * Get a websocket client for the given gateway.
     *
     * @param string $gateway
     *
     * @return \WebSocket\Client
     */
    public function getSocket()
    {
        if (is_null($this->socket)) {
            $this->gateway = $this->getGateway();
            $this->socket = new Client($this->gateway);
        }

        return $this->socket;
    }

    protected function identify() {
        $client = $this->getSocket();
        $client->send(json_encode([
            'op' => 2,
            'd' => [
                'token' => $this->token,
                'properties' => [
                    '$os' => PHP_OS,
                    '$browser' => 'laravel-discord-query',
                    '$device' => 'laravel-discord-query',
                ],
            ],
        ]));
        $response = $client->receive();
        $identified = Arr::get(json_decode($response, true), 'op') === 10;

        \Log::debug("Response", [$response, $identified]);

        if (! $identified) {
            throw new Exception("Discord responded with an error while trying to identify the bot: $response");
        }
    }

    /**
     * Get the URL of the gateway that the socket should connect to.
     *
     * @return string
     */
    public function getGateway()
    {
        $gateway = $this->gateway;

        try {
            $response = $this->guzzle->get('https://discordapp.com/api/gateway');

            $gateway = Arr::get(json_decode($response->getBody(), true), 'url', $gateway);
        } catch (Exception $e) {
            \Log::warning("Could not get a websocket gateway address, defaulting to '{$gateway}'.");
        }

        return $gateway;
    }
}
