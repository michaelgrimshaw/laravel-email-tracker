<?php

namespace MichaelGrimshaw\MailTracker;

use Illuminate\Mail\Mailer;
use stdClass;
use Swift_Mailer;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Mail\Mailable as MailableContract;

/**
 * Class MailTracker
 *
 * @package MichaelGrimshaw\MailTracker
 */
class MailTracker extends Mailer
{

    /**
     * @var stdClass
     */
    public $tracker;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Factory $views, Swift_Mailer $swift, Dispatcher $events = null)
    {
        parent::__construct($views, $swift, $events);
    }
    
    /**
     * @return void
     */
    protected function setTracker()
    {
        $this->tracker = new stdClass;

        $this->tracker->enabled        = true;
        $this->tracker->queue          = null;
        $this->tracker->category       = null;
        $this->tracker->linked_to_id   = null;
        $this->tracker->linked_to_type = null;
        $this->tracker->mail_class     = null;
        $this->tracker->recipients     = [];
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     * @return \Illuminate\Mail\PendingMail
     */
    public function to($users)
    {
        $this->setTracker();
        return (new PendingTrackedMail($this))->to($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     * @return \Illuminate\Mail\PendingMail
     */
    public function bcc($users)
    {
        $this->setTracker();
        return (new PendingTrackedMail($this))->bcc($users);
    }

    /**
     * Send a new message using a view.
     *
     * @param  string|array|MailableContract  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @return void
     */
    public function send($view, array $data = [], $callback = null)
    {
        if ($view instanceof MailableContract) {
            return $this->sendMailable($view);
        }

        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
        list($view, $plain, $raw) = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        $this->addContent($message, $view, $plain, $raw, $data);

        call_user_func($callback, $message);

        // If a global "to" address has been set, we will set that address on the mail
        // message. This is primarily useful during local development in which each
        // message should be delivered into a single mail address for inspection.
        if (isset($this->to['address'])) {
            $this->setGlobalTo($message);
        }

        // Next we will determine if the message should be sent. We give the developer
        // one final chance to stop this message and then we will send it to all of
        // its recipients. We will then fire the sent event for the sent message.
        $swiftMessage = $message->getSwiftMessage();

        if ($this->tracker->enabled) {
            $headerData = $this->getHeaderData();

            $body = str_replace(config('mailtracker.tracker_id_replacement'), $headerData['unique_args']['tracker_id'], $swiftMessage->getBody());

            $swiftMessage->setBody($body);

            $header = $this->asString($headerData);

            $swiftMessage
                ->getHeaders()
                ->addTextHeader('X-SMTPAPI', $header);

            if (config('mailtracker.tracking_cleaner.enabled')) {
                TrackedMail::clean();
            }
        }

        if ($this->shouldSendMessage($swiftMessage)) {
            $this->sendSwiftMessage($swiftMessage);

            $this->dispatchSentEvent($message);
        }
    }

    /**
     * Queue a new e-mail message for sending.
     *
     * @param  string|array|MailableContract  $view
     * @param  string|null  $queue
     * @return mixed
     */
    public function queue($view, $queue = null)
    {
        $this->tracker->queue = is_null($queue) ? $this->queue : $queue;

        return parent::queue($view, $queue);
    }

    /**
     * Gets header data to add to mail.
     *
     * @return mixed
     */
    private function getHeaderData()
    {
        $trackerId = $this->storeTracker();

        $header['unique_args'] = [
            'tracker_id' => $trackerId
        ];

        if (isset($this->tracker->category)) {
            $header['category'] = $this->tracker->category;
        }

        return $header;
    }

    /**
     * Stores tracking data for each recipient.
     *
     * @return int
     */
    private function storeTracker()
    {
        $trackerId = config('mailtracker.models.email_tracker')::max('tracker_id') + 1;

        foreach ($this->tracker->recipients as $recipient) {
            $data = [
                'tracker_id'        => $trackerId,
                'recipient_id'      => $recipient->recipient_id,
                'recipient_type'    => $recipient->recipient_type,
                'linked_to_id'      => $this->tracker->linked_to_id,
                'linked_to_type'    => $this->tracker->linked_to_type,
                'distribution_type' => $recipient->distribution_type,
                'email'             => $recipient->email,
                'category'          => $this->tracker->category,
                'queue'             => $this->tracker->queue,
                'mail_class'        => $this->tracker->mail_class,
            ];

            config('mailtracker.models.email_tracker')::create($data);
        }

        return $trackerId;
    }

    /**
     * Converts array to json.
     *
     * @param array $data
     *
     * @return string
     */
    private function asJSON($data)
    {
        $json = json_encode($data);
        $json = preg_replace('/(["\]}])([,:])(["\[{])/', '$1$2 $3', $json);

        return $json;
    }

    /**
     * Converts Array to String for mail header.
     *
     * @param array $data
     *
     * @return string
     */
    private function asString($data)
    {
        $json = $this->asJSON($data);

        return wordwrap($json, 76, "\n   ");
    }
}
