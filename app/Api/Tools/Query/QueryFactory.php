<?php
namespace App\Api\Tools\Query;

use App\Api\Constants\HelperConstants;
use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\ValidationRuleConstants;

class QueryFactory
{
    /**
     * Create a new control-list paginator object
     *
     * @param $itemOptionId
     * @param $items
     * @param $elementType
     * @param $elementValue
     * @param \App\Api\Models\Site $site
     * @param $languageCode
     * @param int $page
     * @param string|null $filter
     *
     * @return Query|null
     */
    public static function make($itemOptionId, $items, $elementType, $elementValue, $site, $languageCode, $page = 1, $filter = null)
    {
        if ($elementType === OptionElementTypeConstants::CONTROL_LIST) {
            if ($elementValue = json_recursive_decode($elementValue)) {
                $paginationEnabled = isset($elementValue->{OptionElementTypeConstants::PAGINATION_ENABLED})
                    ? $elementValue->{OptionElementTypeConstants::PAGINATION_ENABLED}
                    : false;

                $filterEnabled = isset($elementValue->{OptionElementTypeConstants::FILTER_ENABLED})
                    ? $elementValue->{OptionElementTypeConstants::FILTER_ENABLED}
                    : false;

                $perPage = isset($elementValue->{OptionElementTypeConstants::PAGINATION_PER_PAGE})
                    ? $elementValue->{OptionElementTypeConstants::PAGINATION_PER_PAGE}
                    : 0;

                $templateName = isset($elementValue->{OptionElementTypeConstants::PAGINATION_TEMPLATE_NAME})
                    ? $elementValue->{OptionElementTypeConstants::PAGINATION_TEMPLATE_NAME}
                    : '';

                if ($perPage <= 0) $perPage = count($items);

                /** @noinspection PhpUndefinedMethodInspection */
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $path = app('Dingo\Api\Routing\UrlGenerator')->version('v1')->route('helpers.query', [
                    'item_option_id' => $itemOptionId
                ]);
                $query = http_build_query([
                    ValidationRuleConstants::CMS_APPLICATION_NAME_FIELD => get_cms_application(),
                    ValidationRuleConstants::FORM_TOKEN_BODY => config('cms.' . get_cms_application() . '.form_token'),
                    HelperConstants::LANGUAGE_CODE => $languageCode,
                    HelperConstants::DOMAIN_NAME => $site->domain_name
                ], '', '&');
                $path .= '?' . $query;

                if ($paginationEnabled) {
                    $queryObject = new Query($items, $perPage, $page, [
                        'paginationEnabled' => $paginationEnabled,
                        'filterEnabled' => $filterEnabled,
                        'filter' => $filter
                    ]);
                    $queryObject->setPageName(OptionElementTypeConstants::PAGINATION_PAGE_NAME);
                    $queryObject->setPath($path);
                    $queryObject->setTemplateView($templateName);
                    $queryObject->setDomainName($site->domain_name);

                    return $queryObject;
                }

                if ($filterEnabled) {
                    $queryObject = new Query($items, $perPage, $page, [
                        'paginationEnabled' => $paginationEnabled,
                        'filterEnabled' => $filterEnabled,
                        'filter' => $filter
                    ]);
                    $queryObject->setPageName(OptionElementTypeConstants::PAGINATION_PAGE_NAME);
                    $queryObject->setPath($path);
                    $queryObject->setDomainName($site->domain_name);

                    return $queryObject;
                }
            }
        }

        return null;
    }
}