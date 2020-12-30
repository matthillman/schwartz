<?php

namespace App\Providers;

use Horizon;
use App\Database\UpsertBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\LazyCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

use Storage;
use Google_Client;
use Google_Service_Sheets;
use Illuminate\Support\Arr;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Horizon::auth(function ($request) {
            $user = auth()->user();
            return $user && $user->admin;
        });
        Blade::if('user', function ($permission) {
            return auth()->user()->$permission;
        });
        Blade::if('bot', function() {
            return stripos(request()->header('schwartz'), 'bot') !== false;
        });
        Blade::if('person', function() {
            return stripos(request()->header('schwartz'), 'bot') === false;
        });

        Response::macro('jsonStream', function (LazyCollection $value) {
            return Response::stream(function() use ($value) {
                echo "[";
                flush();
                set_time_limit(0);

                $firstRow = true;
                foreach ($value as $data) {
                    if ($firstRow) {
                        $firstRow = false;
                    } else {
                        echo ',';
                    }

                    echo $data->toJson();
                    flush();

                    $jsonData = null;
                    $data = null;
                }

                echo ']';
                flush();
            });
        });

        $token = $this->app->make('config')->get('services.discord.token');

        $this->app->when(\App\Util\Discord::class)
            ->needs('$token')
            ->give($token);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Collection::macro('quoteValues', function() {
            return $this->transform(function($item) {
                return collect($item)->mapWithKeys(function ($value, $key) {
                    if (is_null($value)) {
                        $value = 'NULL';
                    }
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    return [$key => $value];
                });
            });
        });

        Collection::macro('flipWithKeys', function() {
            $results = [];

            foreach ($this->items as $key => $value) {
                if (!isset($results[$value])) {
                    $results[$value] = [];
                }

                $results[$value][] = $key;
            }

            return new static($results);
        });

        $this->app->bind('shitty_bot', function () {
            return new \App\Util\API\ShittyAPI;
        });

        $this->app->bind('google_sheets', function () {
            $config = isset($this->app['config']['google']) ? $this->app['config']['google'] : [];
            $client = $this->getClient($config);

            return new Google_Service_Sheets($client);
        });
    }

    private function getClient($config) {
        $client = new Google_Client();

        $client->setApplicationName(Arr::get($config, 'application_name', ''));
        $client->setScopes(Arr::get($config, 'scopes', [Google_Service_Sheets::SPREADSHEETS_READONLY]));
        $client->setAccessType(Arr::get($config, 'access_type', 'offline'));

        $client->setClientId(Arr::get($config, 'client_id', ''));
        $client->setClientSecret(Arr::get($config, 'client_secret', ''));
        $client->setRedirectUri(Arr::get($config, 'redirect_uri', ''));
        $client->setApprovalPrompt(Arr::get($config, 'approval_prompt', 'auto'));

        $client->setDeveloperKey(Arr::get($config, 'developer_key', ''));

        if (Arr::get($config, 'service.enable', false)) {
            $this->auth($userEmail);
        }

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $existingToken = $this->getToken();
        if (!is_null($existingToken)) {
            $accessToken = json_decode($existingToken, true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }

            $this->setToken(json_encode($client->getAccessToken()));
        }
        return $client;
    }

    protected function getToken() {
        if (/*is_null($this->token) && */Storage::disk('local')->exists('.__google_token')) {
            return /*$this->token = */Storage::disk('local')->get('.__google_token');
        }
         return null; //$this->token;
    }

    protected function setToken($token) {
        // $this->token = $token;
        if (is_null($token)) {
            Storage::disk('local')->delete('.__google_token');
        } else {
            Storage::disk('local')->put('.__google_token', $token);
        }
    }
}
