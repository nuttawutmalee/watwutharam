<?php
namespace App\Api\Tools\Query;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use IteratorAggregate;
use JsonSerializable;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;

class Query extends AbstractPaginator implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Jsonable, PaginatorContract
{
    /**
     * Determine if there are more items in the data source.
     *
     * @var bool
     */
    protected $hasMore;

    /**
     * Total amount of items.
     *
     * @var int
     */
    protected $total;

    /**
     * Total amount of pages.
     *
     * @var int
     */
    protected $totalPages;

    /**
     * Paginator template name
     *
     * @var string
     */
    protected $templateName = '';

    /**
     * Site domain name
     *
     * @var string
     */
    protected $domainName = '';

    /**
     * @var string
     */
    protected $filterName = 'filter';

    /**
     * @var object|null
     */
    protected $filter = null;

    /**
     * @var bool
     */
    protected $paginationEnabled = false;

    /**
     * @var bool
     */
    protected $filterEnabled = false;

    /**
     * @var bool
     */
    protected $includeData = false;

    /**
     * Create a new paginator instance.
     *
     * @param  mixed  $items
     * @param  int  $perPage
     * @param  int|null  $currentPage
     * @param  array  $options (path, query, fragment, pageName, filter, filterEnabled, paginationEnabled)
     */
    public function __construct($items, $perPage, $currentPage = null, array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }

        $this->perPage = $perPage;
        $this->currentPage = $this->setCurrentPage($currentPage);
        $this->path = $this->path != '/' ? rtrim($this->path, '/') : $this->path;

        $this->filter = json_decode(json_encode($this->filter));

        if ($this->filterEnabled && !is_null($this->filter)) {
            $where = isset($this->filter->where) ? $this->filter->where : null;
            $order = isset($this->filter->order) ? $this->filter->order : null;

            $items = $this->filterItems($items, $where);
            $items = $this->sortItems($items, $order);
        }

        $this->total = count($items);

        if ($this->perPage === 0) {
            $this->perPage = $this->total;
        }

        $this->totalPages = (int) ceil($this->total / $this->perPage);

        $this->setItems($items);
    }

    /**
     * @param array $array1
     * @param array $array2
     * @param string $key
     * @return array
     */
    private function control_list_intersect(array $array1, array $array2, $key = '_id')
    {
        $intersect = [];
        foreach ($array1 as $value) {
            if (isset($value[$key])) {
                $results = collect($array2)->where($key, $value[$key])->toArray();
                $intersect = collect($intersect)->merge($results)->toArray();
            }
        }
        return collect($intersect)->unique()->toArray();
    }

    /**
     * @param $items
     * @param $property
     * @param $options
     * @return array
     * @throws \Exception
     */
    private function getFilterResult($items, $property, $options)
    {
        if (is_object($options)) {
            $keys = collect($options)->keys()->toArray();
            if (count($keys) > 0) {
                $operator = $keys[0];
                switch ($operator) {
                    case 'lt':
                        if ($value = isset($options->{$operator}) ? $options->{$operator} : null) {
                            $items = collect($items)
                                ->filter(function ($item) use ($property, $value) {
                                    if (isset($item[$property])) {
                                        return $item[$property] < $value;
                                    }
                                    return true;
                                })
                                ->toArray();
                        } else {
                            throw new \Exception('lt operator is invalid');
                        }
                        break;
                    case 'lte':
                        if ($value = isset($options->{$operator}) ? $options->{$operator} : null) {
                            $items = collect($items)
                                ->filter(function ($item) use ($property, $value) {
                                    if (isset($item[$property])) {
                                        return $item[$property] <= $value;
                                    }
                                    return true;
                                })
                                ->toArray();
                        } else {
                            throw new \Exception('lte operator is invalid');
                        }
                        break;
                    case 'gt':
                        if ($value = isset($options->{$operator}) ? $options->{$operator} : null) {
                            $items = collect($items)
                                ->filter(function ($item) use ($property, $value) {
                                    if (isset($item[$property])) {
                                        return $item[$property] > $value;
                                    }
                                    return true;
                                })
                                ->toArray();
                        } else {
                            throw new \Exception('gt operator is invalid');
                        }
                        break;
                    case 'gte':
                        if ($value = isset($options->{$operator}) ? $options->{$operator} : null) {
                            $items = collect($items)
                                ->filter(function ($item) use ($property, $value) {
                                    if (isset($item[$property])) {
                                        return $item[$property] >= $value;
                                    }
                                    return true;
                                })
                                ->toArray();
                        } else {
                            throw new \Exception('gte operator is invalid');
                        }
                        break;
                    case 'between':
                        $value = isset($options->{$operator}) ? $options->{$operator} : [];
                        if (!empty($value) && count($value) === 2) {
                            $start = $value[0];
                            $end = $value[1];
                            $items = collect($items)
                                ->filter(function ($item) use ($property, $start, $end) {
                                    if (isset($item[$property])) {
                                        return $item[$property] >= $start && $item[$property] <= $end;
                                    }
                                    return true;
                                })
                                ->toArray();
                        } else {
                            throw new \Exception('between operator is invalid');
                        }
                        break;
                    case 'like':
                        if ($value = isset($options->{$operator}) ? $options->{$operator} : null) {
                            $flags = isset($options->options) ? $options->options : '';
                            $re = '/' . $value . '/' . $flags;
                            $items = collect($items)
                                ->filter(function ($item) use ($property, $re) {
                                    if (isset($item[$property])) {
                                        return !!preg_match($re, $item[$property]);
                                    }
                                    return true;
                                })
                                ->toArray();
                        } else {
                            throw new \Exception('like operator is invalid');
                        }
                        break;
                    case 'nlike':
                        if ($value = isset($options->{$operator}) ? $options->{$operator} : null) {
                            $flags = isset($options->options) ? $options->options : '';
                            $re = '/' . $value . '/' . $flags;
                            $items = collect($items)
                                ->filter(function ($item) use ($property, $re) {
                                    if (isset($item[$property])) {
                                        return !preg_match($re, $item[$property]);
                                    }
                                    return true;
                                })
                                ->toArray();
                        } else {
                            throw new \Exception('nlike operator is invalid');
                        }
                        break;
                    case 'ilike':
                        if ($value = isset($options->{$operator}) ? $options->{$operator} : null) {
                            $re = '/' . $value . '/i';
                            $items = collect($items)
                                ->filter(function ($item) use ($property, $re) {
                                    if (isset($item[$property])) {
                                        return !!preg_match($re, $item[$property]);
                                    }
                                    return true;
                                })
                                ->toArray();
                        } else {
                            throw new \Exception('ilike operator is invalid');
                        }
                        break;
                    case 'nilike':
                        if ($value = isset($options->{$operator}) ? $options->{$operator} : null) {
                            $re = '/' . $value . '/i';
                            $items = collect($items)
                                ->filter(function ($item) use ($property, $re) {
                                    if (isset($item[$property])) {
                                        return !preg_match($re, $item[$property]);
                                    }
                                    return true;
                                })
                                ->toArray();
                        } else {
                            throw new \Exception('nilike operator is invalid');
                        }
                        break;
                    case 'inq':
                        $value = isset($options->{$operator}) ? $options->{$operator} : [];
                        if (!empty($value)) {
                            $items = collect($items)->whereIn($property, $value)->toArray();
                        } else {
                            throw new \Exception('inq operator is invalid');
                        }
                        break;
                    case 'nin':
                        $value = isset($options->{$operator}) ? $options->{$operator} : [];
                        if (!empty($value)) {
                            $items = collect($items)->whereNotIn($property, $value)->toArray();
                        } else {
                            throw new \Exception('nin operator is invalid');
                        }
                        break;
                        break;
                    case 'eq':
                        $value = isset($options->${$operator}) ? $options->${$operator} : '';
                        $items = collect($items)
                            ->filter(function ($item) use ($property, $value) {
                                if (isset($item[$property])) {
                                    return $item[$property] === $value;
                                }
                                return true;
                            })
                            ->toArray();
                        break;
                    case 'neq':
                        $value = isset($options->${$operator}) ? $options->${$operator} : '';
                        $items = collect($items)
                            ->filter(function ($item) use ($property, $value) {
                                if (isset($item[$property])) {
                                    return $item[$property] !== $value;
                                }
                                return true;
                            })
                            ->toArray();
                        break;
                    case 'regexp':
                        if ($value = isset($options->{$operator}) ? $options->{$operator} : null) {
                            $re = '/' . $value . '/';
                            $items = collect($items)
                                ->filter(function ($item) use ($property, $re) {
                                    if (isset($item[$property])) {
                                        return !!preg_match($re, $item[$property]);
                                    }
                                    return true;
                                })
                                ->toArray();
                        } else {
                            throw new \Exception('regexp operator is invalid');
                        }
                        break;
                    default:
                        break;
                }
            }

            return $items;
        }

        return collect($items)->where($property, $options)->toArray();
    }

    /**
     * @param $items
     * @param $where
     * @return array
     * @throws \Exception
     */
    protected function filterItems($items, $where)
    {
        if (empty($items) || empty($where)) return $items;

        $filteredItems = [];

        foreach ($where as $key => $value) {
            switch ($key) {
                case 'and':
                    $results = [];
                    if (!is_array($value)) {
                        throw new \Exception('and operation is invalid');
                    }
                    foreach ($value as $cond) {
                        $result = $this->filterItems($items, $cond);
                        if (empty($results)) {
                            $results = $result;
                        } else {
                            $results = $this->control_list_intersect($results, $result);
                        }
                        $items = $results;
                    }
                    $filteredItems = $results;
                    break;
                case 'or';
                    $results = [];
                    if (!is_array($value)) {
                        throw new \Exception('or operation is invalid');
                    }
                    foreach ($value as $cond) {
                        $result = $this->filterItems($items, $cond);
                        $results = collect($results)->merge($result)->toArray();
                    }
                    $filteredItems = $results;
                    break;
                default:
                    $result = $this->getFilterResult($items, $key, $value);
                    if (empty($filteredItems)) {
                        $filteredItems = $result;
                    } else {
                        $filteredItems = $this->control_list_intersect($filteredItems, $result);
                    }
                    break;
            }

            $filteredItems = collect($filteredItems)->unique()->values()->toArray();
        }

        return $filteredItems;
    }

    /**
     * @param $items
     * @param $order
     * @return array
     */
    protected function sortItems($items, $order)
    {
       if (empty($items) || empty($order)) return $items;

       $lookup = [];
       $direction = [];

       if (is_string($order)) {
           $order = [$order];
       }

       if (is_array($order) && count($order) > 0) {
           foreach ($order as $item) {
               if (preg_match('/^([a-zA-Z]+)(?:\s+)?(ASC|DESC|asc|desc)?$/', $item, $matches)) {
                   array_push($lookup, $matches[1]);
                   array_push($direction, isset($matches[2]) ? strtoupper($matches[2]) : 'ASC');
               }
           }
       }

       if (!empty($lookup) && !empty($direction)) {
           return collect($items)
               ->sort(function ($a, $b) use ($lookup, $direction) {
                   foreach ($lookup as $index => $value) {
                       $dir = isset($direction[$index]) ? $direction[$index] : 'ASC';
                       if (isset($a[$value]) && isset($b[$value])) {
                           if ($a[$value] === $b[$value]) {
                               continue;
                           }
                           if ($dir === 'ASC') {
                               return ($a[$value] < $b[$value]) ? -1 : 1;
                           } else if ($dir === 'DESC') {
                               return ($a[$value] > $b[$value]) ? -1 : 1;
                           }
                       }
                   }
                   return 0;
               })
               ->values()
               ->toArray();
       }

       return $items;
    }

    /**
     * Get the current page for the request.
     *
     * @param  int  $currentPage
     * @return int
     */
    protected function setCurrentPage($currentPage)
    {
        $currentPage = $currentPage ?: static::resolveCurrentPage();

        return $this->isValidPageNumber($currentPage) ? (int) $currentPage : 1;
    }

    /**
     * Set the items for the paginator.
     *
     * @param  mixed  $items
     * @return void
     */
    protected function setItems($items)
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        if ($this->paginationEnabled) {
            $this->hasMore = count($this->items) > ($this->perPage * $this->currentPage());
            $offset = ($this->currentPage() - 1) * $this->perPage;
            if ($offset < 0) $offset = 0;
            $this->items = $this->items->slice($offset, $this->perPage);
        } else {
            $this->hasMore = false;
        }
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
     * Return the amount of items.
     *
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * Return the amount of pages.
     *
     * @return int
     */
    public function totalPages()
    {
        return $this->totalPages;
    }

    /**
     * Get the URL for the next page.
     *
     * @return string|null
     */
    public function nextPageUrl()
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage() + 1);
        }

        return null;
    }

    /**
     * Get the URL for the previous page.
     *
     * @return string|null
     */
    public function previousPageUrl()
    {
        if ($this->currentPage() > 1) {
            return $this->url($this->currentPage() - 1);
        }

        return null;
    }

    /**
     * Render the paginator using the given view.
     *
     * @param  string|null  $view
     * @param  array  $data
     * @return string
     */
    public function links($view = null, $data = [])
    {
        return $this->render($view, $data);
    }

    /**
     * Render the paginator using the given view.
     *
     * @param  string|null  $view
     * @param  array  $data
     * @return string
     */
    public function render($view = null, $data = [])
    {
        return new HtmlString(
            static::viewFactory()->make($view, array_merge($data, [
                'query' => $this,
            ]))->render()
        );
    }

    /**
     * Manually indicate that the paginator does have more pages.
     *
     * @param  bool  $value
     * @return $this
     */
    public function hasMorePagesWhen($value = true)
    {
        $this->hasMore = $value;

        return $this;
    }

    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    public function hasMorePages()
    {
        return $this->hasMore;
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
                'has_more' => $this->hasMorePages(),
                'template_name' => $this->templateName(),
                'domain_name' => $this->domainName(),
                'method' => 'GET',
                'path' => $this->path,
                'page_name' => $this->pageName,
                'next_page_url' => $this->nextPageUrl(),
                'prev_page_url' => $this->previousPageUrl(),
            ];

            if ($this->filterEnabled) {
                $return['filter_name'] = $this->filterName;
                $return['filter'] = $this->filter;
            }

            if ($this->includeData) {
                $return['data'] = $this->items->toArray();
            }

            return $return;
        }

        if ($this->filterEnabled) {
            $return = [
                'total' => $this->total(),
                'domain_name' => $this->domainName(),
                'method' => 'GET',
                'path' => $this->path,
                'filter_name' => $this->filterName,
                'filter' => $this->filter
            ];

            if ($this->includeData) {
                $return['data'] = $this->items->toArray();
            }

            return $return;
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
     * Get paginator template name
     *
     * @return string
     */
    public function templateName()
    {
        return $this->templateName;
    }

    /**
     * Set paginator template name
     *
     * @param $templateName
     */
    public function setTemplateView($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * @param $filterName
     */
    public function setFilterName($filterName)
    {
        $this->filterName = $filterName;
    }

    /**
     * Get paginator domain name
     *
     * @return string
     */
    public function domainName()
    {
        return $this->domainName;
    }

    /**
     * Set paginator domain name
     *
     * @param $domainName
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }

    /**
     * @param $includeData
     */
    public function setIncludeData($includeData)
    {
        $this->includeData = $includeData;
    }
}