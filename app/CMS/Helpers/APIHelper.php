<?php

namespace App\CMS\Helpers;

use App\CMS\Constants\CMSConstants;
use Illuminate\Http\Response as BaseResponse;

class APIHelper
{
    /**
     * APIHelper constructor.
     */
    function __construct()
    {
        //
    }

    /**
     * Return a host url
     *
     * @return mixed
     */
    public static function getHostUrl()
    {
        if (session(CMSConstants::CMS_PAGE_PREVIEW_ACTIVE, false)) {
            return self::prependSchemeToUrl(parse_url(url()->current(), PHP_URL_HOST));
        } else {
            return preg_replace('/(?:^http:\/\/|https:\/\/)?(?:www(?:.)?\.)?([^.]*)(?:\..+)/', '$1', parse_url(url()->current(), PHP_URL_HOST));
        }
    }

    /**
     * Return a current url path
     *
     * @return mixed
     */
    public static function getCurrentUrlPath()
    {
        if (session(CMSConstants::CMS_PAGE_PREVIEW_ACTIVE, false)) {
            return '';
        } else {
            return preg_replace('/^\//', '', parse_url(url()->current(), PHP_URL_PATH));
        }
    }

    /**
     * Return a prepend url with http/https scheme
     *
     * @param $url
     * @return string
     */
    public static function prependSchemeToUrl($url)
    {
        if (CMSHelper::isUrlExternal($url)) {
            $scheme = parse_url($url, PHP_URL_SCHEME);
            if (empty($scheme)) return 'http://' . $url;
        }
        return $url;
    }


    /**
     * Return a current friendly url
     *
     * @return string
     */
    public static function getCurrentFriendlyUrl()
    {
        $path = self::getCurrentUrlPath();

        if (is_null($path)) {
            $path = config('cms-client.homepage_path');
        }

        //Remove initial slashes
        return preg_replace('/(\/+)$/i', '/', config('cms-client.friendly_url_prefix')) .  preg_replace('/^(\/+)/i', '', $path);
    }

    /**
     * Return a site by its domain name
     *
     * @param null $domainName
     * @return array|mixed|null
     */
    public static function getSiteByDomainName($domainName = null)
    {
        $data = null;
        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms-client.api.urls.helpers.get_site');
        $applicationName = config('cms-client.application_name');

        if (is_null($domainName)) {
            $domainName = self::getHostUrl();
        }

        if ( ! $applicationName || ! $domainName || ! $client || ! $url) return $data;

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $domainName
        ]);

        //try {
        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $client->getAsync($url, [
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $promise->wait();

        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
            $data = self::extractDataFromBody($data);
        }
        //} catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return a redirect url by its source url
     *
     * @param $sourceUrl
     * @param null $domainName
     * @return array|mixed|null
     */
    public static function getRedirectUrlBySourceUrl($sourceUrl, $domainName = null)
    {
        $data = null;
        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms-client.api.urls.helpers.post_get_redirect_url');
        $applicationName = config('cms-client.application_name');

        if (is_null($sourceUrl)) {
            $sourceUrl = self::getCurrentFriendlyUrl();
        }

        if (is_null($domainName)) {
            $site = session(CMSConstants::SITE);
            $domainName = $site->domain_name;
        }

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $domainName
        ]);

        if ( ! $applicationName || ! $domainName || ! $sourceUrl || ! $url || ! $client) return $data;

//        try {
        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $client->postAsync($url, [
            'form_params' => [
                'source_url' => remove_leading_slashes(remove_trailing_slashes($sourceUrl), '/')
            ],
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $promise->wait();

        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
            $data = self::extractDataFromBody($data);
        }
//        } catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return page data by its friendly url and language code
     *
     * @param null $friendlyUrl
     * @param null $languageCode
     * @param array $filterItems
     * @return array|mixed|null
     */
    public static function getPageData($friendlyUrl = null, $languageCode = null, $filterItems = [])
    {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        if (is_null($languageCode)) {
            if ($currentLanguageCode = CMSHelper::getCurrentLanguageCode()) {
                $languageCode = $currentLanguageCode;
            } else {
                if ($language = CMSHelper::getSiteMainLanguage()) {
                    $languageCode = $language->code;
                }
            }
        }

        if (is_null($friendlyUrl)) {
            $friendlyUrl = self::getCurrentFriendlyUrl();
        }

        $friendlyUrl = self::removeLanguageCodeFromUrl($languageCode, $friendlyUrl);

        if (empty($friendlyUrl) || $friendlyUrl === $languageCode) {
            $friendlyUrl = config('cms-client.homepage_path');
        }

        $data = null;

        if ( ! $friendlyUrl) return $data;

        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms-client.api.urls.helpers.post_get_page');
        $applicationName = config('cms-client.application_name');

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $site->domain_name,
        ]);

        if ( ! $applicationName || ! $url || ! $client) return $data;

        //try {
        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $client->postAsync($url, [
            'form_params' => [
                'friendly_url' => $friendlyUrl,
                'language_code' => $languageCode,
                'filter_items' => $filterItems
            ],
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $promise->wait();

        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
            $data = self::extractDataFromBody($data);
        }
//        } catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return parent pages data
     *
     * @param null $friendlyUrl
     * @param null $languageCode
     * @param array $filterPages
     * @param array $filterItems
     * @param $orderBy
     * @param $orderDirection
     * @return array|mixed|null|string
     */
    public static function getParentPagesData(
        $friendlyUrl = null,
        $languageCode = null,
        $filterPages = [],
        $filterItems = [],
        $orderBy = CMSConstants::ORDER_BY_UPDATED_AT,
        $orderDirection = CMSConstants::ORDER_DESC
    ) {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        if (is_null($languageCode)) {
            if ($currentLanguageCode = CMSHelper::getCurrentLanguageCode()) {
                $languageCode = $currentLanguageCode;
            } else {
                if ($language = CMSHelper::getSiteMainLanguage()) {
                    $languageCode = $language->code;
                }
            }
        }

        if (is_null($friendlyUrl)) {
            $friendlyUrl = self::getCurrentFriendlyUrl();
        }

        $friendlyUrl = self::removeLanguageCodeFromUrl($languageCode, $friendlyUrl);

        if (empty($friendlyUrl) || $friendlyUrl === $languageCode) {
            $friendlyUrl = config('cms-client.homepage_path');
        }

        $data = null;

        if ( ! $friendlyUrl) return $data;

        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms-client.api.urls.helpers.post_get_parent_pages_data');
        $applicationName = config('cms-client.application_name');

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $site->domain_name,
        ]);

        if ( ! $applicationName || ! $url || ! $client) return $data;

        //try {
        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $client->postAsync($url, [
            'form_params' => [
                'friendly_url' => $friendlyUrl,
                'language_code' => $languageCode,
                'filter_pages' => $filterPages,
                'filter_items' => $filterItems,
                'order_by' => $orderBy,
                'order_direction' => $orderDirection
            ],
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $promise->wait();

        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
            $data = self::extractDataFromBody($data);
        }
        //} catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return child pages data
     *
     * @param null $friendlyUrl
     * @param null $languageCode
     * @param array $filterPages
     * @param array $filterItems
     * @param $orderBy
     * @param $orderDirection
     * @return array|mixed|null|string
     */
    public static function getChildPagesData(
        $friendlyUrl = null,
        $languageCode = null,
        $filterPages = [],
        $filterItems = [],
        $orderBy = CMSConstants::ORDER_BY_UPDATED_AT,
        $orderDirection = CMSConstants::ORDER_DESC
    ) {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        if (is_null($languageCode)) {
            if ($currentLanguageCode = CMSHelper::getCurrentLanguageCode()) {
                $languageCode = $currentLanguageCode;
            } else {
                if ($language = CMSHelper::getSiteMainLanguage()) {
                    $languageCode = $language->code;
                }
            }
        }

        if (is_null($friendlyUrl)) {
            $friendlyUrl = self::getCurrentFriendlyUrl();
        }

        $friendlyUrl = self::removeLanguageCodeFromUrl($languageCode, $friendlyUrl);

        if (empty($friendlyUrl) || $friendlyUrl === $languageCode) {
            $friendlyUrl = config('cms-client.homepage_path');
        }

        $data = null;

        if ( ! $friendlyUrl) return $data;

        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms-client.api.urls.helpers.post_get_child_pages_data');
        $applicationName = config('cms-client.application_name');

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $site->domain_name,
        ]);

        if ( ! $applicationName || ! $url || ! $client) return $data;

        //try {
        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $client->postAsync($url, [
            'form_params' => [
                'friendly_url' => $friendlyUrl,
                'language_code' => $languageCode,
                'filter_pages' => $filterPages,
                'filter_items' => $filterItems,
                'order_by' => $orderBy,
                'order_direction' => $orderDirection
            ],
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $promise->wait();

        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
            $data = self::extractDataFromBody($data);
        }
        //} catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return global item data by its language code
     *
     * @param null $languageCode
     * @return array|mixed|null
     */
    public static function getGlobalItemData($languageCode = null)
    {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        if (is_null($languageCode)) {
            if ($currentLanguageCode = CMSHelper::getCurrentLanguageCode()) {
                $languageCode = $currentLanguageCode;
            } else {
                if ($language = CMSHelper::getSiteMainLanguage()) {
                    $languageCode = $language->code;
                }
            }
        }

        $data = null;
        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms-client.api.urls.helpers.get_global_items');
        $applicationName = config('cms-client.application_name');

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $site->domain_name,
            'language_code' => $languageCode
        ]);

        if ( ! $applicationName || ! $url || ! $client) return $data;

        //try {
        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $client->getAsync($url, [
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $promise->wait();

        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
            $data = self::extractDataFromBody($data);
        }
        //} catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return pages by categories
     *
     * @param $categories
     * @param $amount
     * @param $orderBy
     * @param $orderDirection
     * @param null $languageCode
     * @param array $filterItems
     * @return array|mixed|null
     */
    public static function getPagesByCategories(
        $categories,
        $amount = null,
        $orderBy = CMSConstants::ORDER_BY_UPDATED_AT,
        $orderDirection = CMSConstants::ORDER_DESC,
        $languageCode = null,
        $filterItems = []
    ) {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        if (is_null($languageCode)) {
            if ($currentLanguageCode = CMSHelper::getCurrentLanguageCode()) {
                $languageCode = $currentLanguageCode;
            } else {
                if ($language = CMSHelper::getSiteMainLanguage()) {
                    $languageCode = $language->code;
                }
            }
        }

        if (is_null($categories)) return null;

        if ( ! is_array($categories)) $categories = array($categories);

        $data = null;
        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms.api.urls.helpers.post_get_pages_search_by_categories');
        $applicationName = config('cms.application_name');

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $site->domain_name
        ]);

        if ( ! $applicationName || ! $categories || ! $url || ! $client) return $data;

        //try {
        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $client->postAsync($url, [
            'form_params' => [
                'categories' => $categories,
                'amount' => $amount,
                'order_by' => $orderBy,
                'order_direction' => $orderDirection,
                'language_code' => $languageCode,
                'filter_items' => $filterItems
            ],
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $promise->wait();

        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
            $data = self::extractDataFromBody($data);
        }
        //} catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return sibling pages by categories
     *
     * @param $categories
     * @param $pageId
     * @param $amount
     * @param $direction
     * @param $orderBy
     * @param $orderDirection
     * @param null $languageCode
     * @param array $filterItems
     * @return array|mixed|null
     */
    public static function getSiblingPagesByCategories(
        $categories,
        $pageId = null,
        $amount = null,
        $direction = CMSConstants::PREVIOUS_DIRECTION,
        $orderBy = CMSConstants::ORDER_BY_UPDATED_AT,
        $orderDirection = CMSConstants::ORDER_DESC,
        $languageCode = null,
        $filterItems = []
    ) {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        if (is_null($languageCode)) {
            if ($currentLanguageCode = CMSHelper::getCurrentLanguageCode()) {
                $languageCode = $currentLanguageCode;
            } else {
                if ($language = CMSHelper::getSiteMainLanguage()) {
                    $languageCode = $language->code;
                }
            }
        }

        if (is_null($categories)) return null;

        if ( ! is_array($categories)) $categories = array($categories);

        $data = null;
        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms.api.urls.helpers.post_get_pages_search_by_categories');
        $applicationName = config('cms.application_name');

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $site->domain_name
        ]);

        if ( ! $applicationName || ! $categories || ! $url || ! $client) return $data;

        //try {
        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $client->postAsync($url, [
            'form_params' => [
                'categories' => $categories,
                'page_id' => $pageId,
                'amount' => $amount,
                'direction' => $direction,
                'order_by' => $orderBy,
                'order_direction' => $orderDirection,
                'language_code' => $languageCode,
                'filter_items' => $filterItems
            ],
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $promise->wait();

        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
            $data = self::extractDataFromBody($data);
        }
        //} catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return pages by template variable name
     *
     * @param $templateName
     * @param $amount
     * @param $orderBy
     * @param $orderDirection
     * @param null $languageCode
     * @param array $filterItems
     * @return array|mixed|null|string
     */
    public static function getPagesByTemplateName(
        $templateName,
        $amount = null,
        $orderBy = CMSConstants::ORDER_BY_UPDATED_AT,
        $orderDirection = CMSConstants::ORDER_DESC,
        $languageCode = null,
        $filterItems = []
    ) {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        if (is_null($languageCode)) {
            if ($currentLanguageCode = CMSHelper::getCurrentLanguageCode()) {
                $languageCode = $currentLanguageCode;
            } else {
                if ($language = CMSHelper::getSiteMainLanguage()) {
                    $languageCode = $language->code;
                }
            }
        }

        if (is_null($templateName)) return null;

        if ( ! is_array($templateName)) $templateName = array($templateName);

        $data = null;
        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms-client.api.urls.helpers.post_get_pages_search_by_template');
        $applicationName = config('cms-client.application_name');

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $site->domain_name
        ]);

        if ( ! $applicationName || ! $templateName || ! $url || ! $client) return $data;

        //try {
        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $client->postAsync($url, [
            'form_params' => [
                'template_name' => $templateName,
                'amount' => $amount,
                'order_by' => $orderBy,
                'order_direction' => $orderDirection,
                'language_code' => $languageCode,
                'filter_items' => $filterItems
            ],
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $promise->wait();

        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
            $data = self::extractDataFromBody($data);
        }
        //} catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return pages by template variable name
     *
     * @param $templateName
     * @param $pageId
     * @param $amount
     * @param $direction
     * @param $orderBy
     * @param $orderDirection
     * @param null $languageCode
     * @param array $filterItems
     * @return array|mixed|null|string
     */
    public static function getSiblingPagesByTemplateName(
        $templateName,
        $pageId,
        $amount = null,
        $direction = CMSConstants::PREVIOUS_DIRECTION,
        $orderBy = CMSConstants::ORDER_BY_UPDATED_AT,
        $orderDirection = CMSConstants::ORDER_DESC,
        $languageCode = null,
        $filterItems = []
    ) {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        if (is_null($languageCode)) {
            if ($currentLanguageCode = CMSHelper::getCurrentLanguageCode()) {
                $languageCode = $currentLanguageCode;
            } else {
                if ($language = CMSHelper::getSiteMainLanguage()) {
                    $languageCode = $language->code;
                }
            }
        }

        if (is_null($templateName)) return null;

        if ( ! is_array($templateName)) $templateName = array($templateName);

        $data = null;
        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms.api.urls.helpers.post_get_pages_search_by_template');
        $applicationName = config('cms.application_name');

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $site->domain_name
        ]);

        if ( ! $applicationName || ! $templateName || ! $url || ! $client) return $data;

        //try {
        /** @var \GuzzleHttp\Promise\Promise $promise */
        $promise = $client->postAsync($url, [
            'form_params' => [
                'template_name' => $templateName,
                'page_id' => $pageId,
                'amount' => $amount,
                'order_by' => $orderBy,
                'order_direction' => $orderDirection,
                'direction' => $direction,
                'language_code' => $languageCode,
                'filter_items' => $filterItems
            ],
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $promise->wait();

        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
            $data = self::extractDataFromBody($data);
        }
        //} catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return site map data
     *
     * @param array $exceptions
     * @return array|mixed|null|string
     */
    public static function getSiteMapData($exceptions = [])
    {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        $data = null;
        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms-client.api.urls.helpers.get_site_map_data');
        $applicationName = config('cms-client.application_name');

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $site->domain_name
        ]);

        if ( ! $applicationName || ! $url || ! $client) return $data;

        //try {
        $response = $client->post($url, [
            'form_params' => [
                'exceptions' => $exceptions
            ],
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
            $data = self::extractDataFromBody($data);
        }
        //} catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return site map as an xml file
     *
     * @param $languageCode
     * @return array|mixed|null|string
     */
    public static function getSiteMapXML($languageCode = null)
    {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        if (is_null($languageCode)) {
            if ($currentLanguageCode = CMSHelper::getCurrentLanguageCode()) {
                $languageCode = $currentLanguageCode;
            } else {
                if ($language = CMSHelper::getSiteMainLanguage()) {
                    $languageCode = $language->code;
                }
            }
        }

        $data = null;
        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms-client.api.urls.helpers.get_site_map_xml');
        $applicationName = config('cms-client.application_name');

        $url = self::replaceUrlParameters($url, [
            'domain_name' => $site->domain_name,
            'language_code' => $languageCode
        ]);

        if ( ! $applicationName || ! $url || ! $client) return $data;

        //try {
        $response = $client->get($url, [
            'headers' => [
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
        }
        //} catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return form data
     *
     * @param $variableName
     * @param null $languageCode
     * @return null|string
     */
    public static function getFormPropertyData($variableName, $languageCode = null)
    {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        if (is_null($languageCode)) {
            if ($currentLanguageCode = CMSHelper::getCurrentLanguageCode()) {
                $languageCode = $currentLanguageCode;
            } else {
                if ($language = CMSHelper::getSiteMainLanguage()) {
                    $languageCode = $language->code;
                }
            }
        }

        $data = null;
        $client = resolve(CMSConstants::CMS_API);
        $url = config('cms-client.api.urls.helpers.get_form_property_data');
        $token = config('cms-client.api.form_token');
        $applicationName = config('cms-client.application_name');

        $url = APIHelper::replaceUrlParameters($url, [
            'domain_name' => $site->domain_name,
            'variable_name' => $variableName,
            'language_code' => $languageCode
        ]);

        if ( ! $applicationName || ! $url || ! $client || ! $token) return $data;

        //try {
        $response = $client->get($url, [
            'headers' => [
                CMSConstants::FORM_TOKEN_HEADER => $token,
                CMSConstants::CMS_APPLICATION_NAME_HEADER => $applicationName
            ],
            'stream' => true
        ]);

        /** @var \GuzzleHttp\Psr7\Response $response */
        if ($response->getStatusCode() == BaseResponse::HTTP_OK) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }

            $data = self::extractDataFromBody($data);
        }
        //} catch (\Exception $e) {}

        return $data;
    }

    /**
     * Return replaced url with parameters
     *
     * @param $url
     * @param array $params
     * @param string $token
     * @return mixed
     */
    public static function replaceUrlParameters($url, array $params = [], $token = ':')
    {
        if ($url) {
            $params = is_array($params) ? $params : [];
            foreach ($params as $key => $value) {
                $url = preg_replace('/' . $token . $key . '/', $value, $url);
                $url = preg_replace('/\/{2,}/', '/', $url);
            }
        }

        return $url;
    }

    /**
     * Return data from http body
     *
     * @param $body
     * @param bool $shift
     * @return array|mixed|null
     */
    private static function extractDataFromBody($body, $shift = false) {
        $body = json_decode($body, false);
        $data = $body;
        if (isset($body->result) && $body->result === true) {
            if (isset($body->data) && ! is_null($body->data)) {
                $data = $body->data;
            } else {
                $data = null;
            }
            if ($shift) {
                if (is_array($data) && count($data) == 1) {
                    $data = array_shift($data);
                }
            }
        }
        return $data;
    }

    /**
     * Return removed-language-code url
     *
     * @param $languageCode
     * @param $url
     * @return mixed
     */
    private static function removeLanguageCodeFromUrl($languageCode, $url)
    {
        $newUrl = preg_replace('/^(\/+)/', '', $url);
        $newUrl = preg_replace('/^(' . preg_quote($languageCode, '/') . '\/)/', '', $newUrl);
        return $newUrl;
    }
}