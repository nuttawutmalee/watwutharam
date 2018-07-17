<?php

namespace App\Api\Observers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\RedirectUrl;
use App\Api\Models\CmsLog;

class RedirectUrlObserver
{
    /**
     * Listen to the RedirectUrls creating event.
     *
     * @param RedirectUrl $redirectUrl
     * @throws \Exception
     */
    public function creating(RedirectUrl $redirectUrl)
    {
        $this->guardAgainstDuplicateCombinationOfSourceAndDestination($redirectUrl->source_url, $redirectUrl->destination_url);
    }
    
    /**
     * Listen to the RedirectUrls created event.
     *
     * @param  RedirectUrl  $redirectUrl
     * @return void
     */
    public function created(RedirectUrl $redirectUrl)
    {
        CmsLog::log($redirectUrl, LogConstants::REDIRECT_URL_CREATED);
    }

    /**
     * Listen to the RedirectUrls updating event.
     *
     * @param  RedirectUrl  $redirectUrl
     * @return void
     */
    public function updating(RedirectUrl $redirectUrl)
    {
        if ( ! $redirectUrl->wasRecentlyCreated) {
            $this->guardAgainstDuplicateCombinationOfSourceAndDestination($redirectUrl->source_url, $redirectUrl->destination_url, $redirectUrl);
        }

        CmsLog::log($redirectUrl->getOriginal(), LogConstants::REDIRECT_URL_BEFORE_UPDATED);
    }

    /**
     * Listen to the RedirectUrls updated event.
     *
     * @param  RedirectUrl  $redirectUrl
     * @return void
     */
    public function updated(RedirectUrl $redirectUrl)
    {
        CmsLog::log($redirectUrl, LogConstants::REDIRECT_URL_UPDATED);
    }

    /**
     * Listen to the RedirectUrls saved event.
     *
     * @param  RedirectUrl  $redirectUrl
     * @return void
     */
    public function saved(RedirectUrl $redirectUrl)
    {
        CmsLog::log($redirectUrl, LogConstants::REDIRECT_URL_SAVED);
    }

    /**
     * Listen to the RedirectUrls deleting event.
     *
     * @param  RedirectUrl  $redirectUrl
     * @return void
     */
    public function deleting(RedirectUrl $redirectUrl)
    {
        CmsLog::log($redirectUrl, LogConstants::REDIRECT_URL_BEFORE_DELETED);
    }
    
    /**
     * @param $sourceUrl
     * @param $destinationUrl
     * @param null $redirectUrl
     * @throws \Exception
     */
    private function guardAgainstDuplicateCombinationOfSourceAndDestination($sourceUrl, $destinationUrl, $redirectUrl = null)
    {
        $sourceToDestinationQuery = RedirectUrl::where('source_url', $sourceUrl)->where('destination_url', $destinationUrl);
        $destinationToSourceQuery = RedirectUrl::where('source_url', $destinationUrl)->where('destination_url', $sourceUrl);

        if (is_null($redirectUrl)) {
            $sourceToDestinationCount = $sourceToDestinationQuery->count();
            $destinationToSourceCount = $destinationToSourceQuery->count();

            if ($sourceToDestinationCount + $destinationToSourceCount > 0) {
                throw new \Exception(ErrorMessageConstants::DUPLICATE_SOURCE_AND_DESTINATION_URL, 500);
            }
        } else {
            $duplicateSourceToDestination = $sourceToDestinationQuery->get();
            $duplicateDestinationToSource = $destinationToSourceQuery->get();

            $isDuplicateSourceToDestination = false;
            $isDuplicateDestinationToSource = false;

            if ( ! empty($duplicateSourceToDestination->all())) {
                $isDuplicateSourceToDestination = $duplicateSourceToDestination->every(function ($value) use ($redirectUrl) {
                    /**
                     * @var RedirectUrl $redirectUrl
                     * @var RedirectUrl $value
                     */
                    return $redirectUrl->getKey() !== $value->getKey();
                });
            }

            if ( ! empty($duplicateDestinationToSource->all())) {
                $isDuplicateDestinationToSource = $duplicateDestinationToSource->every(function ($value) use ($redirectUrl) {
                    /**
                     * @var RedirectUrl $redirectUrl
                     * @var RedirectUrl $value
                     */
                    return $redirectUrl->getKey() !== $value->getKey();
                });
            }

            if ($isDuplicateSourceToDestination && $isDuplicateDestinationToSource) {
                throw new \Exception(ErrorMessageConstants::DUPLICATE_SOURCE_AND_DESTINATION_URL, 500);
            }
        }
    }
}