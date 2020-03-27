<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage as Message;

class DiscordMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;
    public $embed;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($message, $embed = [])
    {
        $this->message = $message;
        $this->embed = $embed;
        $this->queue = 'notifications';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        return Message::create($this->message, $this->embed);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'embed' => $this->embed,
        ];
    }
}
