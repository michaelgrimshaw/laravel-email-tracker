<?php

namespace MichaelGrimshaw\MailTracker;

use MichaelGrimshaw\MailTracker\Events\MailEvent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class MailTrackerController
 *
 * @package MichaelGrimshaw\MailTracker
 */
class MailTrackerController extends Controller
{

    /**
     * @var mixed
     */
    protected $json;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var TrackedMailEvent
     */
    protected $event;

    /**
     * @var TrackedMail
     */
    protected $mail;

    /**
     * MailTrackerController constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->json = json_decode($request->getContent());
    }

    /**
     * Iterates over Event data.
     *
     * @return void
     */
    public function processEvent()
    {
        foreach ($this->json as $event) {
            $this->data = $event;

            if ($this->verifyEvent()) {
                $this->setMailData();
                $this->storeEvent();
                $this->triggerEvent();
            }
        }
    }

    /**
     * Checks if there is a mail reference.
     *
     * @return bool
     */
    protected function verifyEvent()
    {
        $hasTrackedMail = config('mailtracker.models.email_tracker')::where('tracker_id', $this->getTrackerId())
            ->where('email', $this->getEmail())
            ->exists();

        if ($hasTrackedMail) {
            return true;
        }

        return false;
    }

    /**
     * Stores event data.
     *
     * @return void
     */
    protected function storeEvent()
    {
        $eventData = $this->getEventData();

        $this->event = config('mailtracker.models.email_tracker_event')::create($eventData);
    }

    /**
     * Triggers events based on the event type.
     *
     * @return void
     */
    protected function triggerEvent()
    {
        event(TrackedMailEvent::DEFAULT_EVENT, new MailEvent($this->mail, $this->event, $this->data));

        switch ($this->data->event) {
            case 'processed':
                event(TrackedMailEvent::PROCESSED_EVENT, new MailEvent($this->mail, $this->event, $this->data));
                break;
            case 'dropped':
                event(TrackedMailEvent::DROPPED_EVENT, new MailEvent($this->mail, $this->event, $this->data));
                break;
            case 'delivered':
                event(TrackedMailEvent::DELIVERED_EVENT, new MailEvent($this->mail, $this->event, $this->data));
                break;
            case 'deferred':
                event(TrackedMailEvent::DEFERRED_EVENT, new MailEvent($this->mail, $this->event, $this->data));
                break;
            case 'bounce':
                event(TrackedMailEvent::BOUNCE_EVENT, new MailEvent($this->mail, $this->event, $this->data));
                break;
            case 'open':
                event(TrackedMailEvent::OPEN_EVENT, new MailEvent($this->mail, $this->event, $this->data));
                break;
            case 'click':
                event(TrackedMailEvent::CLICK_EVENT, new MailEvent($this->mail, $this->event, $this->data));
                break;
            case 'spamreport':
                event(TrackedMailEvent::SPAM_REPORT_EVENT, new MailEvent($this->mail, $this->event, $this->data));
                break;
            case 'unsubscribe':
                event(TrackedMailEvent::UNSUBSCRIBE_EVENT, new MailEvent($this->mail, $this->event, $this->data));
                break;
            case 'group_unsubscribe':
                event(TrackedMailEvent::GROUP_UNSUBSCRIBE_EVENT, new MailEvent($this->mail, $this->event, $this->data));
                break;
            case 'group_resubscribe':
                event(TrackedMailEvent::GROUP_RESUBSCRIBE_EVENT, new MailEvent($this->mail, $this->event, $this->data));
                break;
            default:
                break;
        }
    }

    /**
     * Creates array of event data to store.
     *
     * @return array
     */
    protected function getEventData()
    {
        return [
            config('mailtracker.table_names.email_tracker') . '_id' => $this->mail->id,
            'status' => $this->getEvent(),
            'event_data' => $this->getCleanRequest()
        ];
    }

    /**
     * Gets id of the stored mail.
     *
     * @return void
     */
    protected function setMailData()
    {
        $this->mail = config('mailtracker.models.email_tracker')::where('tracker_id', $this->getTrackerId())
            ->where('email', $this->getEmail())
            ->first();
    }

    /**
     * Removes unwanted data from the request.
     *
     * @return array
     */
    protected function getCleanRequest()
    {
        $event = (array) $this->data;

        foreach (config('mailtracker.dont_keep_tracking_data') as $dataName) {
            unset($event[$dataName]);
        }

        return $event;
    }

    /**
     * @return string
     */
    protected function getEmail()
    {
        return $this->data->email;
    }

    /**
     * @return string
     */
    protected function getTrackerId()
    {
        return $this->data->tracker_id;
    }

    /**
     * @return string
     */
    protected function getEvent()
    {
        return $this->data->event;
    }
}
