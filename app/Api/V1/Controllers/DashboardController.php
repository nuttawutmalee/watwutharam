<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\LogConstants;
use App\Api\Models\Site;
use App\Api\Models\User;
use App\Api\Models\CmsLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    /**
     * DashboardController constructor.
     */
    function __construct()
    {
        //
    }

    /**
     * Return a dashboard data
     *
     * @param Request $request
     * @param string $id
     * @return mixed
     */
    public function getDashboardData(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id) {
        /** @var Site $site */
		$site = Site::findOrFail($id);

		/** @var User[]|\Illuminate\Support\Collection $user_data */
		$user_data = User::all();

		$last_log_date = date("Y-m-d", strtotime("-6 months"));

		/** @var CmsLog[]|\Illuminate\Support\Collection $login_stat */
		$login_stat = CmsLog::where([
							['updated_at', '>', $last_log_date],
							['action', '=', LogConstants::USER_LOGIN]
						])
						->orderBy('updated_at', 'desc')
						->get(['id', 'updated_at']);

        $log_size = [
            'size' => 0,
            'type' => 'MB'
        ];

        if ($size = get_db_table_size((new CmsLog)->getTable())) {
            $log_size['size'] = number_format($size/(1024*1024), 2);
        }

		$submissions_count  = CmsLog::where('action', LogConstants::FORM_SUBMIT)
			->where('log_data', 'LIKE', '%"site_id":"' . $site->getKey() . '"%')
			->count();

		$recentActivities = [
			'time_difference' => null,
			'data' => null
		];
		
		$recentActivityList = [
			LogConstants::PAGE_SAVED,
			LogConstants::PAGE_DELETED,
			LogConstants::GLOBAL_ITEM_SAVED,
			LogConstants::GLOBAL_ITEM_DELETED,
            LogConstants::COMPONENT_CASCADE_UPDATE_INHERITANCES
		];

		$activities = null;

		try {
			/** @noinspection PhpUndefinedMethodInspection */
			$activities = DB::table('cms_logs')
				->select(DB::raw('*, count(*) as total_count, SUBSTRING(log_data FROM POSITION(\'"id":"\' in log_data)+6 FOR 36) as item_id'))
				->whereIn('action', $recentActivityList)
				->groupBy(DB::raw('UNIX_TIMESTAMP(created_at) DIV 60, item_id, action'))
				->take(10)
				->orderBy('created_at', 'desc')
				->get();
		} catch (\Exception $exception) {}

		if ( ! empty($activities)) {
			$first = false;
			$deleted = [];
			$recentActivities['data'] = collect($activities)
				->map(function ($activity) use ($site, &$first, &$recentActivities, &$deleted) {
					if ( ! isset($activity->log_data) || ! isset($activity->updated_by)) return null;

					$data = [
						'title' => null,
						'updated_by' => LogConstants::SYSTEM,
						'created_at' => $activity->created_at,
						'item_id' => $activity->item_id,
                        'permissions' => [],
						'item_type' => null,
						'link' => false
					];

					if ($logData = $activity->log_data) {
						$logData = json_decode($logData);
						$name = isset($logData->name) ? $logData->name : null;
						$permissions = isset($logData->permissions) ? $logData->permissions : [];
						$siteId = isset($logData->site_id)
                            ? $logData->site_id
                            : (isset($logData->template->site_id) ? $logData->template->site_id : null);
						$action = $activity->action;

						if ( ! is_null($siteId)) {
						    if ($siteId !== $site->getKey()) return null;
                        }

						switch ($action) {
							case LogConstants::PAGE_SAVED:
								$data['title'] = is_null($name)
									? 'Page updated'
									: 'Page: ' . $name . ' updated';
								$data['item_type'] = 'page';
								$data['permissions'] = $permissions;
								$data['link'] = ! in_array($activity->item_id, $deleted);
								break;
							case LogConstants::PAGE_DELETED:
								$data['title'] = is_null($name)
									? 'Page deleted'
									: 'Page: ' . $name . ' deleted';
								$data['item_type'] = 'page';
								$deleted[] = $activity->item_id;
								break;
							case LogConstants::GLOBAL_ITEM_SAVED:
								$data['title'] = is_null($name)
									? 'Global updated'
									: 'Global: ' . $name . ' updated';
								$data['item_type'] = 'global';
								$data['link'] = ! in_array($activity->item_id, $deleted);
								break;
							case LogConstants::GLOBAL_ITEM_DELETED:
								$data['title'] = is_null($name)
									? 'Global deleted'
									: 'Global: ' . $name . ' deleted';
								$data['item_type'] = 'global';
								$deleted[] = $activity->item_id;
								break;
                            case LogConstants::COMPONENT_CASCADE_UPDATE_INHERITANCES:
                                $data['title'] = is_null($name)
                                    ? 'Component inheritance updated'
                                    : 'Component: ' . $name . ', inheritance updated';
                                $data['item_type'] = 'component';
                                $deleted[] = $activity->item_id;
                                break;
							default:
								break;
						}
					}

					if ($updatedBy = $activity->updated_by) {
						if ($updatedBy !== LogConstants::SYSTEM) {
							$updatedBy = json_decode($updatedBy);
							$data['updated_by'] = isset($updatedBy->name) ? $updatedBy->name : 'SYSTEM';
						}
					}

					if (is_null($data['title'])) return null;

					if ( ! $first) {
						$first = true;
						$createdAt = Carbon::createFromFormat('Y-m-d H:i:s', $activity->created_at);
						$difference = Carbon::now()->diffInMinutes($createdAt);
						$recentActivities['time_difference'] = Carbon::now()->subMinutes($difference)->diffForHumans();
					}

					return $data;
				})
				->filter()
                ->values()
				->all();
		}

		$dashboard_data = [
			"server_time" => date("Y-m-d"),
			"site" => [
				"domain_name" => $site["domain_name"],
				"description" => $site["description"]
			],
			"templates" => count($site["templates"]),
			"pages" => count($site["pages"]),
			"languages" => count($site["languages"]),
			"users" => count($user_data),
			"login_stat" => $login_stat,
            "logs_size" => $log_size,
			"number_of_submissions" => $submissions_count,
			"recent_activities" => $recentActivities
		];

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($dashboard_data);
    }

    /**
     * Return search options
     *
     * @param Request $request
     * @return mixed
     */
	public function searchOptions(Request $request) {
	    /** @var \Illuminate\Support\Collection $global_items_result */
        /** @noinspection PhpUndefinedMethodInspection */
        $global_items_result = DB::table('global_items')
						->select(DB::raw("global_items.id, global_items.name, 'GLOBAL_ITEM' as item_type"))
						->where([
							['global_items.name', 'like', '%' . $request->input('search_key') . '%'],
							['global_items.site_id', '=', $request->input('site_id')]
						])
						->get();

		/** @var \Illuminate\Support\Collection $global_items_opt_result */
        /** @noinspection PhpUndefinedMethodInspection */
        $global_items_opt_result = DB::table('global_item_options')
						->join('global_items', 'global_items.id', '=', 'global_item_options.global_item_id')
						->select(
							DB::raw("global_item_options.id, global_item_options.name, 'GLOBAL_ITEM_OPTION' as item_type, global_item_options.global_item_id as parent_id, global_items.name as parent_name")
						)
						->where([
							['global_item_options.name', 'like', '%' . $request->input('search_key') . '%'],
							['global_items.site_id', '=', $request->input('site_id')]
						])
						->get();

		/** @var \Illuminate\Support\Collection $page_items_opt_result */
        /** @noinspection PhpUndefinedMethodInspection */
        $page_items_opt_result = DB::table('page_item_options')
						->join('page_items', 'page_items.id', '=', 'page_item_options.page_item_id')
						->join('pages', 'pages.id', '=', 'page_items.page_id')
						->join('templates', 'templates.id', '=', 'pages.template_id')
						->select(
							DB::raw("page_item_options.id, page_item_options.name, 'PAGE_ITEM_OPTION' as item_type, page_items.id as parent_id, page_items.name as parent_name, pages.id as page_id, pages.name as page_name")
						)
						->where([
							['page_item_options.name', 'like', '%' . $request->input('search_key') . '%'],
							['templates.site_id', '=', $request->input('site_id')]
						])
						->get();

		/** @var \Illuminate\Support\Collection $page_items_result */
        /** @noinspection PhpUndefinedMethodInspection */
        $page_items_result = DB::table('page_items')
						->join('pages', 'pages.id', '=', 'page_items.page_id')
						->join('templates', 'templates.id', '=', 'pages.template_id')
						->select(
							DB::raw("page_items.id, page_items.name, 'PAGE_ITEM' as item_type, pages.id as page_id, pages.name as page_name")
						)
						->where([
							['page_items.name', 'like', '%' . $request->input('search_key') . '%'],
							['templates.site_id', '=', $request->input('site_id')]
						])
						->get();

		/** @var \Illuminate\Support\Collection $page_result */
        /** @noinspection PhpUndefinedMethodInspection */
        $page_result = DB::table('pages')
						->join('templates', 'templates.id', '=', 'pages.template_id')
						->select('pages.id as page_id',
						'pages.name',
						'pages.template_id')
						->select(
							DB::raw("pages.id, pages.name, 'PAGE' as item_type, pages.template_id")
						)
						->where([
							['pages.name', 'like', '%' . $request->input('search_key') . '%'],
							['templates.site_id', '=', $request->input('site_id')]
						])
						->orWhere([
							['pages.friendly_url', 'like', '%' . $request->input('search_key') . '%'],
							['templates.site_id', '=', $request->input('site_id')]
						])
						->get();

		/** @var \Illuminate\Support\Collection $template_result */
        /** @noinspection PhpUndefinedMethodInspection */
        $template_result = DB::table('templates')
						->select('templates.id', 'templates.name')
						->select(
							DB::raw("templates.id, templates.name, 'TEMPLATE' as item_type")
						)
						->where([
							['templates.name', 'like', '%' . $request->input('search_key') . '%'],
							['templates.site_id', '=', $request->input('site_id')]
						])
						->get();
		
		if (!isset($global_items_result)) {
			$global_items_result = collect([]);
		}
		if (!isset($global_items_opt_result)) {
			$global_items_opt_result = collect([]);
		}
		if (!isset($page_items_opt_result)) {
			$page_items_opt_result = collect([]);
		}
		if (!isset($page_items_result)) {
			$page_items_result = collect([]);
		}
		if (!isset($page_result)) {
			$page_result = collect([]);
		}
		if (!isset($template_result)) {
			$template_result = collect([]);
		}

        /** @noinspection PhpUndefinedMethodInspection */
		$search_result = $global_items_result->merge($global_items_opt_result);
        /** @noinspection PhpUndefinedMethodInspection */
		$search_result = $search_result->merge($page_result);
        /** @noinspection PhpUndefinedMethodInspection */
		$search_result = $search_result->merge($page_items_result);
        /** @noinspection PhpUndefinedMethodInspection */
        $search_result = $search_result->merge($page_items_opt_result);
		/** @noinspection PhpUndefinedMethodInspection */
        $search_result = $search_result->merge($template_result);

		$search_result_data = [
			"search_result" => $search_result
		];

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($search_result_data);
	}
}

