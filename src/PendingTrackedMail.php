<?php

namespace MichaelGrimshaw\MailTracker;

use http\Exception\InvalidArgumentException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\PendingMail;

/**
 * Class PendingTrackedMail
 *
 * @package MichaelGrimshaw\MailTracker
 */
class PendingTrackedMail extends PendingMail
{

    /**
     * Create a new mailable mailer instance.
     *
     * @param  \Illuminate\Mail\Mailer  $mailer
     * @return void
     */
    public function __construct(Mailer $mailer)
    {
        parent::__construct($mailer);
    }

    /**
     * Set the recipients of the message.
     *
     * @param  mixed  $users
     * @return $this
     */
    public function to($users)
    {
        $this->setRecipientInfo($users, 'to');

        return parent::to($users);
    }

    /**
     * Set the recipients of the message.
     *
     * @param  mixed  $users
     * @return $this
     */
    public function cc($users)
    {
        $this->setRecipientInfo($users, 'cc');

        return parent::cc($users);
    }

    /**
     * Set the recipients of the message.
     *
     * @param  mixed  $users
     * @return $this
     */
    public function bcc($users)
    {
        $this->setRecipientInfo($users, 'bcc');

        return parent::bcc($users);
    }

    /*
     * @todo
     */
    public function category($category)
    {
        $this->mailer->tracker->category = $category;

        return $this;
    }

    /*
     * @todo
     */
    public function linkedTo($link)
    {
        if (! $link instanceof Model) {
            throw new InvalidArgumentException('Only a Model instance can be linked to.');
        }

        $this->mailer->tracker->linked_to_id = $link->getKey();
        $this->mailer->tracker->linked_to_type = $link->getMorphClass();

        return $this;
    }

    public function tracked($canTrack = true)
    {
        $this->mailer->tracker->enabled = $canTrack;

        return $this;
    }

    /**
     * Send a new mailable message instance.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return mixed
     */
    public function send(Mailable $mailable)
    {
        $this->mailer->tracker->mail_class = class_basename($mailable);

        return parent::send($mailable);
    }

    /**
     * Send a mailable message immediately.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return mixed
     */
    public function sendNow(Mailable $mailable)
    {
        $this->mailer->tracker->mail_class = class_basename($mailable);

        return parent::sendNow($mailable);
    }

    /**
     * Push the given mailable onto the queue.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return mixed
     */
    public function queue(Mailable $mailable)
    {
        $this->mailer->tracker->mail_class = class_basename($mailable);

        return parent::queue($mailable);
    }

    /**
     * Deliver the queued message after the given delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return mixed
     */
    public function later($delay, Mailable $mailable)
    {
        $this->mailer->tracker->mail_class = class_basename($mailable);

        return parent::later($delay, $mailable);
    }

    /**
     * Extracts information to save to the tracker object.
     *
     * @param collection|object|array|string $users
     * @param string                         $type
     *
     * @return void
     */
    protected function setRecipientInfo($users, $type = 'to')
    {
        if (config('mailtracker.track_distribution_type.'. $type)) {
            if ($users instanceof Collection) {
                foreach ($users as $user) {
                    $this->storeInTracker($type, $user->email, $user->getKey(), $user->getMorphClass());
                }
            } elseif ($users instanceof Model) {
                $this->storeInTracker($type, $users->email, $users->getKey(), $users->getMorphClass());
            } elseif (is_array($users)) {
                foreach ($users as $user) {
                    if (is_array($user) && array_has($user, 'email')) {
                        $this->storeInTracker($type, $user['email']);
                    }
                }
            } elseif (is_string($users)) {
                $this->storeInTracker($type, $users);
            }
        }
    }

    /**
     * Stores recipient information to the tracker object.
     *
     * @param string      $distributionType
     * @param string      $email
     * @param int|null    $recipientId
     * @param string|null $recipientType
     *
     * @return void
     */
    protected function storeInTracker($distributionType, $email, $recipientId = null, $recipientType = null)
    {
        $this->mailer->tracker->recipients[] = (object) [
            'distribution_type' => $distributionType,
            'email'             => $email,
            'recipient_id'      => $recipientId,
            'recipient_type'    => $recipientType,
        ];
    }
}
