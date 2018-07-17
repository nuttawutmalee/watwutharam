<?php

namespace App\Api\Observers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\Page;
use App\Api\Models\CmsLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class PageObserver
{
    /**
     * Listen to the Pages creating event.
     *
     * @param Page $page
     * @throws \Exception
     */
    public function creating(Page $page)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        if (Schema::hasColumn('pages', 'published_at') && is_null($page->published_at)) {
            $page->published_at = Carbon::today('UTC');
        }

        if ($template = $page->template) {
            if ($site = $template->site) {
                $duplicateQuery = Page::where('template_id', $template->getKey())
                    ->whereHas('template', function ($query) use ($site) {
                        /** @var \Illuminate\Database\Eloquent\Builder $query */
                        $query->where('site_id', $site->getKey());
                    })
                    ->where(function ($query) use ($page) {
                        /** @var \Illuminate\Database\Eloquent\Builder $query */
                        $query->where('variable_name', $page->variable_name)
                            ->orWhere('friendly_url', $page->friendly_url);
                    });

                $count = $duplicateQuery->count();

                if ($count > 0) {
                    throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME . ', ' . ErrorMessageConstants::DUPLICATE_FRIENDLY_URL, 500);
                }
            } else {
                throw new \Exception('Site not found');
            }
        } else {
            throw new \Exception('Template not found');
        }
    }
    
    /**
     * Listen to the Pages created event.
     *
     * @param  Page  $page
     * @return void
     */
    public function created(Page $page)
    {
        if ($template = $page->template) {
            $page->inheritTemplate();
        }

        CmsLog::log($page, LogConstants::PAGE_CREATED);
    }

    /**
     * Listen to the Pages updating event.
     *
     * @param  Page  $page
     * @throws \Exception
     * @return void
     */
    public function updating(Page $page)
    {
        if ($template = $page->template) {
            if ($site = $template->site) {
                if ( ! $page->wasRecentlyCreated) {
                    $duplicateQuery = Page::where('template_id', $template->getKey())
                        ->whereHas('template', function ($query) use ($site) {
                            /** @var \Illuminate\Database\Eloquent\Builder $query */
                            $query->where('site_id', $site->getKey());
                        })
                        ->where($page->getKeyName(), '!=', $page->getKey())
                        ->where(function ($query) use ($page) {
                            /** @var \Illuminate\Database\Eloquent\Builder $query */
                            $query->where('variable_name', $page->variable_name)
                                ->orWhere('friendly_url', $page->friendly_url);
                        });

                    $count = $duplicateQuery->count();

                    if ($count > 0) {
                        throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME . ', ' . ErrorMessageConstants::DUPLICATE_FRIENDLY_URL, 500);
                    }
                }

                CmsLog::log($page->getOriginal(), LogConstants::PAGE_BEFORE_UPDATED);
            } else {
                throw new \Exception('Site not found');
            }
        } else {
            throw new \Exception('Template not found');
        }
    }

    /**
     * Listen to the Pages updated event.
     *
     * @param  Page  $page
     * @return void
     */
    public function updated(Page $page)
    {
        CmsLog::log($page, LogConstants::PAGE_UPDATED);
    }

    /**
     * Listen to the Pages saved event.
     *
     * @param  Page  $page
     * @return void
     */
    public function saved(Page $page)
    {
        CmsLog::log($page, LogConstants::PAGE_SAVED);
    }

    /**
     * Listen to the Pages deleting event.
     *
     * @param  Page  $page
     * @return void
     */
    public function deleting(Page $page)
    {
        CmsLog::log($page, LogConstants::PAGE_BEFORE_DELETED);
    }

    /**
     * Listen to the Pages deleted event.
     *
     * @param  Page  $page
     * @return void
     */
    public function deleted(Page $page)
    {
        CmsLog::log($page, LogConstants::PAGE_DELETED);
    }
}