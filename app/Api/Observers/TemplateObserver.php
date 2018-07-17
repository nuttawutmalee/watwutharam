<?php

namespace App\Api\Observers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\Component;
use App\Api\Models\Template;
use App\Api\Models\CmsLog;

class TemplateObserver
{
    /**
     * Listen to the Templates creating event.
     *
     * @param Template $template
     * @throws \Exception
     */
    public function creating(Template $template)
    {
        if ($site = $template->site) {
            $duplicateQuery = Template::where('site_id', $site->getKey())->where('variable_name', $template->variable_name);
            $count = $duplicateQuery->count();

            if ($count > 0) {
                throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
            }
        } else {
            throw new \Exception(ErrorMessageConstants::SITE_NOT_FOUND);
        }
    }

    /**
     * Listen to the Templates created event.
     *
     * @param  Template  $template
     * @return void
     */
    public function created(Template $template)
    {
        // Seed data
        $components = Component::whereIn('variable_name', [
            'metadata',
            'google_plus_metadata',
            'open_graph_metadata',
            'twitter_card_metadata',
            'twitter_card_metdata' // Typo backward compatibility
        ])->get();

        if (count($components) > 0) {
            collect($components)->each(function ($component) use ($template) {
                /** @var Component $component */
                if ($component->variable_name === 'twitter_card_metdata') {
                    $template->templateItems()->create([
                        'name' => $component->name,
                        'variable_name' => 'twitter_card_metadata',
                        'component_id' => $component->getKey()
                    ]);
                } else {
                    $template->templateItems()->create([
                        'name' => $component->name,
                        'variable_name' => $component->variable_name,
                        'component_id' => $component->getKey()
                    ]);
                }
            });
        }

        CmsLog::log($template, LogConstants::TEMPLATE_CREATED);
    }

    /**
     * Listen to the Templates updating event.
     *
     * @param Template $template
     * @throws \Exception
     */
    public function updating(Template $template)
    {
        if ($site = $template->site) {
            if ( ! $template->wasRecentlyCreated) {
                $duplicateQuery = Template::where('site_id', $site->getKey())
                    ->where($template->getKeyName(), '!=', $template->getKey())
                    ->where('variable_name', $template->variable_name);
                $count = $duplicateQuery->count();

                if ($count > 0) {
                    throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
                }
            }

            CmsLog::log($template->getOriginal(), LogConstants::TEMPLATE_BEFORE_UPDATED);
        } else {
            throw new \Exception(ErrorMessageConstants::SITE_NOT_FOUND);
        }
    }

    /**
     * Listen to the Templates updated event.
     *
     * @param  Template  $template
     * @return void
     */
    public function updated(Template $template)
    {
        CmsLog::log($template, LogConstants::TEMPLATE_UPDATED);
    }

    /**
     * Listen to the Templates saved event.
     *
     * @param  Template  $template
     * @return void
     */
    public function saved(Template $template)
    {
        CmsLog::log($template, LogConstants::TEMPLATE_SAVED);
    }

    /**
     * Listen to the Templates deleting event.
     *
     * @param  Template  $template
     * @return void
     */
    public function deleting(Template $template)
    {
        CmsLog::log($template, LogConstants::TEMPLATE_BEFORE_DELETED);
    }
}