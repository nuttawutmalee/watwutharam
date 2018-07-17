<?php

namespace App\Api\Events;

use App\Api\Models\CmsLog;
use App\Api\Models\Site;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use /** @noinspection PhpUnusedAliasInspection */
    Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use /** @noinspection PhpUnusedAliasInspection */
    Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FormEmailSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Current site
     *
     * @var $site
     */
    public $site;

    /**
     * @var CmsLog
     */
    public $log;

    /**
     * Form properties
     *
     * @var $properties
     */
    public $properties;

    /**
     * Form submission data
     *
     * @var $submissionData
     */
    public $submissionData;

    /**
     * Form's type
     *
     * @var $formTypeValue
     */
    public $formTypeValue;

    /**
     * Overrides
     *
     * @var $overrides
     */
    public $overrides;

    /**
     * Create a new event instance.
     *
     * @param Site $site
     * @param CmsLog $log
     * @param $properties
     * @param $submissionData
     * @param $formTypeValue
     * @param $overrides
     */
    public function __construct(Site $site, CmsLog $log, $properties, $submissionData, $formTypeValue, $overrides = [])
    {
        $this->site = $site;
        $this->log = $log;
        $this->properties = $properties;
        $this->submissionData = $submissionData;
        $this->formTypeValue = $formTypeValue;
        $this->overrides = $overrides;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('form-email-sent');
    }
}
