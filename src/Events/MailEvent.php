<?php

namespace MichaelGrimshaw\MailTracker\Events;

use MichaelGrimshaw\MailTracker\Models\TrackedMail;
use MichaelGrimshaw\MailTracker\Models\TrackedMailEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class MailEvent
 *
 * @package MichaelGrimshaw\MailTracker\Events
 */
class MailEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var TrackedMail
     */
    public $mail;

    /**
     * @var TrackedMailEvent
     */
    public $event;

    /**
     * @var object
     */
    public $response;

    /**
     * MailEvent constructor.
     *
     * @param TrackedMail      $mail
     * @param TrackedMailEvent $event
     * @param object           $response
     *
     * @return void
     */
    public function __construct(TrackedMail $mail, TrackedMailEvent $event, $response)
    {
        $this->mail     = $mail;
        $this->event    = $event;
        $this->response = $response;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('mail.' . $this->mail->id);
    }
}
