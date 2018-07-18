<?php

namespace App\Auth;

use Log;

use Laravel\Socialite\Two\User;
use GuzzleHttp\Exception\ClientException;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;

class DiscordProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [
        'identify',
        'email'
    ];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://discordapp.com/api/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://discordapp.com/api/oauth2/token';
    }

    /**
     * Get a Social User instance from a known access token.
     *
     * @param  string  $token
     * @return \Laravel\Socialite\Two\User
     */
    public function userFromToken($token)
    {
        $user_map = $this->getUserByToken($token);
        if (is_null($user_map)) {
            return null;
        }
        $user = $this->mapUserToObject($user_map);

        return $user->setToken($token);
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $userUrl = 'https://discordapp.com/api/users/@me';

        try {
            $response = $this->getHttpClient()->get(
                $userUrl,
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$token,
                    ]
                ]
            );
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            Log::info("Discord user lookup by token failed");
            Log::info(\GuzzleHttp\Psr7\str($e->getRequest()));
            Log::info(\GuzzleHttp\Psr7\str($e->getResponse()));
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        if (count($user) == 0) { return null; }

        return (new User)->setRaw($user)->map([
            'id'       => $user['id'],
            'nickname' => sprintf('%s#%s', $user['username'], $user['discriminator']),
            'name'     => $user['username'],
            'email'    => (array_key_exists('email', $user)) ? $user['email'] : null,
            'avatar'   => (is_null($user['avatar'])) ? null : sprintf('https://cdn.discordapp.com/avatars/%s/%s.jpg', $user['id'], $user['avatar']),
        ]);
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return parent::getTokenFields($code) + ['grant_type' => 'authorization_code'];
    }
}
