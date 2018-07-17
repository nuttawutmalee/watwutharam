<?php
namespace App\CMS\Tools;

use ArrayAccess;
use Countable;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Pagination\AbstractPaginator;
use IteratorAggregate;
use JsonSerializable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use App\CMS\Constants\CMSConstants;
use App\CMS\Helpers\CMSHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class Query extends AbstractPaginator implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Jsonable
{
    /**
     * @var null|int
     */
    protected $currentPage;

    /**
     * @var null|int
     */
    protected $perPage;

    /**
     * @var null|int
     */
    protected $total;

    /**
     * @var null|int
     */
    protected $totalPages;

    /**
     * @var null|int
     */
    protected $firstItemIndex;

    /**
     * @var null|int
     */
    protected $lastItemIndex;

    /**
     * @var bool
     */
    protected $hasMore = false;

    /**
     * @var null|string
     */
    protected $method;

    /**
     * @var null|string
     */
    protected $path;

    /**
     * @var null|string
     */
    protected $nextPageUrl;

    /**
     * @var null|string
     */
    protected $prevPageUrl;

    /**
     * @var Collection
     */
    protected $items;

    /**
     * @var object
     */
    protected $filter;

    /**
     * @var string
     */
    protected $templateName = '';

    /**
     * @var string
     */
    protected $domainName = '';

    /**
     * @var string
     */
    protected $filterName = 'filter';

    /**
     * @var bool
     */
    protected $paginationEnabled = false;

    /**
     * @var bool
     */
    protected $filterEnabled = false;

    /**
     * @param $query
     */
    function __construct($query)
    {
        if (isset($query->page_name)) {
            $this->paginationEnabled = true;
            $this->currentPage = isset_not_empty($query->current_page);
            $this->perPage = isset_not_empty($query->per_page);
            $this->total = isset_not_empty($query->total);
            $this->totalPages = isset_not_empty($query->total_pages);
            $this->firstItemIndex = isset_not_empty($query->from);
            $this->lastItemIndex = isset_not_empty($query->to);
            $this->hasMore = isset($query->has_more) ? $query->has_more : false;
            $this->nextPageUrl = isset_not_empty($query->next_page_url);
            $this->prevPageUrl = isset_not_empty($query->prev_page_url);
            $this->templateName = isset_not_empty($query->template_name);
            $this->pageName = isset_not_empty($query->page_name);
        }

        if (isset($query->filter_name)) {
            $this->filterEnabled = true;
            $this->total = isset_not_empty($query->total);
            $this->filterName = isset_not_empty($query->filter_name);
            $this->filter = isset_not_empty($query->filter, []);
        }

        $this->method = isset_not_empty($query->method, 'GET');
        $this->path = isset_not_empty($query->path);
        $this->domainName = isset_not_empty($query->domain_name);

        $this->setItems(isset_not_empty($query->data));
    }

    /**
     * @return array|null
     */
    private function getInitialData()
    {
        $json = json_encode($this->filter) ?: json_encode([]);

        $path = preg_match('/\?/', $this->path())
            ? $this->path() . '&' . $this->filterName . '=' . $json
            : $this->path() . '?' . $this->filterName . '=' . $json;

        $client = new Client();
        $response = $client->request($this->method(), $path, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $body = $response->getBody();
            $json = (string) $body;
            $query = json_decode($json);
            return isset_not_empty($query->data);
        }

        return [];
    }

    /**
     * @param $data
     */
    private function setItems($data)
    {
        if (is_null($data)) {
            $data = $this->getInitialData();
        }
        $this->items = $data instanceof Collection ? $data : Collection::make($data);
    }

    /**
     * @return int|null
     */
    public function currentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return int|null
     */
    public function perPage()
    {
        return $this->perPage;
    }

    /**
     * @return int|null
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * @return int|null
     */
    public function totalPages()
    {
        return $this->totalPages;
    }

    /**
     * @return int|null
     */
    public function firstItemIndex()
    {
        return $this->firstItemIndex;
    }

    /**
     * @return int|null
     */
    public function lastItemIndex()
    {
        return $this->lastItemIndex;
    }

    /**
     * @return bool
     */
    public function hasMore()
    {
        return $this->hasMore;
    }

    /**
     * @return null|string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * @return null|string
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * @param object|string|null
     * @return null|string
     */
    public function nextPageUrl($filter = null)
    {
        if ( ! $this->hasMore()) return null;
        if ( ! $this->filterEnabled) return $this->nextPageUrl;
        if (empty($this->nextPageUrl)) return $this->nextPageUrl;

        if (is_null($filter)) {
            $filter = $this->filter;
        }

        $json = json_encode($filter);
        if (empty($json)) return $this->nextPageUrl;

        return preg_match('/\?/', $this->nextPageUrl)
            ? $this->nextPageUrl . '&' . $this->filterName . '=' . $json
            : $this->nextPageUrl . '?' . $this->filterName . '=' . $json;
    }

    /**
     * @param object|string|null
     * @return null|string
     */
    public function previousPageUrl($filter = null)
    {
        if ( ! $this->filterEnabled) return $this->prevPageUrl;
        if (empty($this->prevPageUrl)) return $this->prevPageUrl;

        if (is_null($filter)) {
            $filter = $this->filter;
        }

        $json = json_encode($filter);
        if (empty($json)) return $this->prevPageUrl;

        return preg_match('/\?/', $this->prevPageUrl)
            ? $this->prevPageUrl . '&' . $this->filterName . '=' . $json
            : $this->prevPageUrl . '?' . $this->filterName . '=' . $json;
    }

    /**
     * @return null|string
     */
    public function items()
    {
        return $this->items;
    }

    /**
     * @return mixed
     */
    public function firstItem()
    {
        return collect($this->items)->first();
    }

    /**
     * @return mixed
     */
    public function lastItem()
    {
        return collect($this->items)->last();
    }

    /**
     * @param bool $fullPath
     * @param string $separator
     * @return null|string
     */
    public function templateName($fullPath = true, $separator = '.')
    {
        if ( ! $fullPath) {
            return $this->templateName;
        }

        if (CMSHelper::getSite()) {
            return CMSHelper::getTemplatePath(CMSConstants::QUERY_PAGINATION_VIEW_PATH . $separator . $this->templateName, $separator);
        } else {
            return CMSConstants::TEMPLATE_VIEW_PATH . $separator . $this->domainName . $separator . CMSConstants::QUERY_PAGINATION_VIEW_PATH . $separator . $this->templateName;
        }
    }


    /**
     * @param array $data
     * @return HtmlString
     */
    public function render($data = [])
    {
        $templateName = $this->templateName(true);

        if (view()->exists($templateName)) {
            return new HtmlString(
                view()->make($templateName, array_merge($data, [
                    'query' => $this
                ]))->render()
            );
        }

        return null;
    }

    /**
     * @param string|null $filter
     * @return Query|null
     */
    public function nextPaginator($filter = null)
    {
        if ( ! $this->paginationEnabled) return null;
        if ( ! $this->nextPageUrl($filter)) return null;

        $client = new Client();
        $response = $client->request($this->method(), $this->nextPageUrl($filter), [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $body = $response->getBody();
            $json = (string) $body;
            return CMSHelper::createControlListQueryFromJSON($json);
        }

        return null;
    }

    /**
     * @param string|null $filter
     * @return HtmlString|null
     */
    public function renderNextPaginator($filter = null)
    {
        if ( ! $this->paginationEnabled) return null;
        if ( ! $this->nextPageUrl($filter)) return null;

        $client = new Client();
        $response = $client->request($this->method(), $this->nextPageUrl($filter));

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $body = $response->getBody();
            $html = (string) $body;
            return new HtmlString($html);
        }

        return null;
    }

    /**
     * @param string|null $filter
     * @return Query|null
     */
    public function previousPaginator($filter = null)
    {
        if ( ! $this->paginationEnabled) return null;
        if ( ! $this->previousPageUrl($filter)) return null;

        $client = new Client();
        $response = $client->request($this->method(), $this->previousPageUrl($filter), [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $body = $response->getBody();
            $json = (string) $body;
            return CMSHelper::createControlListQueryFromJSON($json);
        }

        return null;
    }

    /**
     * @param string|null $filter
     * @return HtmlString|null
     */
    public function renderPreviousPaginator($filter = null)
    {
        if ( ! $this->paginationEnabled) return null;
        if ( ! $this->previousPageUrl($filter)) return null;

        $client = new Client();
        $response = $client->request($this->method(), $this->previousPageUrl($filter));

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $body = $response->getBody();
            $html = (string) $body;
            return new HtmlString($html);
        }

        return null;
    }

    /**
     * @param $page
     * @param string|null $filter
     * @return Query|null
     */
    public function paginatorOfPage($page, $filter = null)
    {
        if ( ! $this->paginationEnabled)  return null;

        $page = $this->isValidPageNumber($page) ? (int) $page : 1;

        $client = new Client();

        $path = preg_match('/\?/', $this->path())
            ? $this->path() . '&' . $this->pageName . '=' . $page
            : $this->path() . '?' . $this->pageName . '=' . $page;

        if (!is_null($filter)) {
            $path .= '&' . $this->filterName . '=' . $filter;
        }

        $response = $client->request($this->method(), $path, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $body = $response->getBody();
            $json = (string) $body;
            return CMSHelper::createControlListQueryFromJSON($json);
        }

        return null;
    }

    /**
     * @param $page
     * @param string|null $filter
     * @return HtmlString|string
     */
    public function renderPaginatorOfPage($page, $filter = null)
    {
        if ( ! $this->paginationEnabled) return null;

        $page = $this->isValidPageNumber($page) ? (int) $page : 1;

        $client = new Client();

        $path = preg_match('/\?/', $this->path())
            ? $this->path() . '&' . $this->pageName . '=' . $page
            : $this->path() . '?' . $this->pageName . '=' . $page;

        if (!is_null($filter)) {
            $path .= '&' . $this->filterName . '=' . $filter;
        }

        $response = $client->request($this->method(), $path);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $body = $response->getBody();
            $html = (string) $body;
            return new HtmlString($html);
        }

        return '';
    }

    /**
     * @param null $filter
     * @return null|string
     */
    public function getFilterUrl($filter = null)
    {
        if ( ! $this->filterEnabled) return null;

        $json = json_encode($filter);

        return preg_match('/\?/', $this->path())
            ? $this->path() . '&' . $this->filterName . '=' . $json
            : $this->path() . '?' . $this->filterName . '=' . $json;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->paginationEnabled) {
            $return = [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'total_pages' => $this->totalPages(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
                'has_more' => $this->hasMore(),
                'template_name' => $this->templateName,
                'domain_name' => $this->domainName,
                'method' => 'GET',
                'path' => $this->path,
                'page_name' => $this->pageName,
                'next_page_url' => $this->nextPageUrl(),
                'prev_page_url' => $this->previousPageUrl(),
                'first_item' => $this->firstItem(),
                'last_item' => $this->lastItem(),
                'data' => $this->items->toArray(),
            ];

            if ($this->filterEnabled) {
                $return['filter_name'] = $this->filterName;
            }

            return $return;
        }

        if ($this->filterEnabled) {
            return [
                'domain_name' => $this->domainName,
                'method' => 'GET',
                'path' => $this->path,
                'filter_name' => $this->filterName,
                'data' => $this->items->toArray(),
            ];
        }

        return [];
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @return bool
     */
    public function isPagination()
    {
        return $this->paginationEnabled;
    }

    /**
     * @return bool
     */
    public function isFilter()
    {
        return $this->filterEnabled;
    }

    /**
     * @return null|object
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param $filter
     * @return Query|null
     */
    public function filterBy($filter)
    {
        $json = json_encode($filter) ?: json_encode([]);

        $path = preg_match('/\?/', $this->path())
            ? $this->path() . '&' . $this->filterName . '=' . $json
            : $this->path() . '?' . $this->filterName . '=' . $json;

        $client = new Client();
        $response = $client->request($this->method(), $path, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $body = $response->getBody();
            $json = (string) $body;
            return CMSHelper::createControlListQueryFromJSON($json);
        }

        return null;
    }
}