<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\User;
use App\Notifications\DiscordMessage;

class SendDiscordMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:message {user} {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends the given text as a discord DM to the given user';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userID = $this->argument('user');
        $message = $this->argument('message');

        $user = User::where('id', is_int($userID) ? $userID : null)
            ->orWhere('discord_id', $userID)
            ->orWhere('discord', 'ilike', $userID)
            ->orWhere('email', 'ilike', $userID)
            ->firstOrFail();

        $this->info("Sending discord DM to user $user->name");
        $this->info("  ↦ $message");

        $user->notify(new DiscordMessage($message));

    }
}
