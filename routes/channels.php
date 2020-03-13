<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('permissions.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('mods.{name}', function ($user, $name) {
    return $user->active;
});

Broadcast::channel('guilds', function ($user) {
    return $user->active;
});

Broadcast::channel('bot', function ($user) {

});

Broadcast::channel('plan.{id}', function ($user, $id) {
    $plan = \App\TerritoryWarPlan::find($id);

    if (is_null($plan) || Gate::denies('edit-guild', $plan->guild->id)) {
        return null;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar,
    ];

});