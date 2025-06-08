<?php

namespace App\Notifications;

use App\Models\Tenants\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DomainCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(User $notifiable)
    {
        return ['mail'];
    }

    public function toMail(User $notifiable)
    {
        return (new MailMessage)
        ->line('Welcome to point-ofsale')
        ->line('Thank you for using our application!')
        ->line('We have created a domain for you. Please register the domain '.tenant()->domains->first()->domain.' in the application via the domain menu')
        ->line('and your domain will be activated within 30 days for the trial period')
        ->salutation('point-ofsale');
    }

    public function toArray(User $notifiable)
    {
        return [
            //
        ];
    }
}
