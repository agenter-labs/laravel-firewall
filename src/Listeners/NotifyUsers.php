<?php

namespace AgenterLab\Firewall\Listeners;

use AgenterLab\Firewall\Events\AttackDetected as Event;
use AgenterLab\Firewall\Notifications\AttackDetected;
use AgenterLab\Firewall\Notifications\Notifiable;
use Throwable;

class NotifyUsers
{
    /**
     * Handle the event.
     *
     * @param Event $event
     *
     * @return void
     */
    public function handle(Event $event)
    {
        try {
            (new Notifiable)->notify(new AttackDetected($event->log));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
