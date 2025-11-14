<?php

namespace LaravelDynamicApi\Traits;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

/**
 * Common Trait.
 * 
 * Common functions used arround the code.
 * 
 * Used by Controller.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
trait CommonTrait
{
    use ValidatesRequests;

    /**
     * Get valid request fields.
     * 
     * Return only the fields in the rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param array $rules
     * @return array
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function getRequestData(Request $request, array $rules)
    {
        $keys = [];

        // remove array validation rules
        foreach (array_keys($rules) as $rule) {
            if (strpos($rule, '.')) {
                continue;
            } else {
                $keys[] = $rule;
            }
        }
        $request->validate($rules);

        return $request->only($keys);
    }

    /**
     * Convert booleans in the requests.
     *
     * @return mixed $value True or False, or return the value.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function convertBooleans(string $value)
    {
        if ($value === 'true' || $value === 'TRUE' || $value === '1' || $value === 'on' || $value === 'ON')
            return true;

        if ($value === 'false' || $value === 'FALSE' || $value === '0' || $value === 'off' || $value === 'OFF')
            return false;

        return $value;
    }

    /**
     * Call api.
     *
     * @param string $uri Request uri.
     * @param string $method Request method.
     * @param array $request Request parameters.
     * @param boolean $abort If abort when not found.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     * 
     */
    public static function callApi(string $uri, string $method, array $request = [], $abort = true)
    {
        $response = app()->handle(
            Request::create($uri, $method, $request)
        );

        if ($response->isNotFound() && $abort === true) {
            // Return abort
            abort(404, 'Not found');
        };

        return $response;
    }

    /**
     * Call get api.
     *
     * @param $uri Request uri.
     * @param $request Request parameters.
     * @param $abort If abort when not found.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function callGetApi(string $uri, array $request = [], $abort = true)
    {
        return CommonTrait::callApi($uri, 'GET', $request, $abort);
    }

    /**
     * Get the ids betwwen the first and the second $ids value from the $idsList.
     * 
     * - If $ids is empty return all
     * - If $ids size is one it returns the ids first element of $idsList and the id sent in $ids
     * - If $ids size is two it returns the ids between the ids in the $idsList sent in $ids.
     * - If $ids size bigger than two, it returns null.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function getIdsBetween($idsList, $ids)
    {
        // If empty return all
        if (empty($ids)) {
            return array_column($idsList, 'id');
        }

        $between = [];

        // If the array size is 1, send all ids below that.
        if (count($ids) === 1) {
            foreach ($idsList as $id) {
                array_push($between, $id);

                if ($ids[0] === $id) {
                    break;
                }
            }

            return $between;
        }

        // If the array size is 2, send all ids between that.
        if (count($ids) === 2) {
            $start = false;
            foreach ($idsList as $id) {
                if ($id === $ids[0]) {
                    $start = true;
                }
                if ($start === true) {
                    array_push($between, $id);
                }
                if ($id === $ids[1]) {
                    break;
                }
            }
            return $between;
        }

        return null;
    }

    /**
     * Return data with pagination style.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function returnPaginatedDetails(string $urlParams, $data = [], int $total = 0): object
    {
        $lastPage = $total === 0 ? null : ceil($total / $this->perPage);
        $nextPage = $lastPage === null || $lastPage == $this->page ? null : $this->page + 1;
        $prevPage = $lastPage === null || $this->page == 1 ? null : $this->page - 1;
        $from = $total === 0 ? 1 : $this->page * $this->perPage - $this->perPage + 1;
        $to = $total === 0 ? 1 : $this->page * $this->perPage;

        return collect([
            "current_page" => $this->page,
            "data" => $data,
            "first_page_url" => $total !== 0 ? $this->request->root() . '/' . $this->request->path() . '?paginated=true&page=1' . $urlParams : null,
            "from" => $from,
            "last_page" => $lastPage,
            "last_page_url" => $lastPage ? $this->request->root() . '/' . $this->request->path() . '?paginated=true&page=' . $lastPage . $urlParams : null,
            "next_page_url" => $nextPage ? $this->request->root() . '/' . $this->request->path() . '?paginated=true&page=' . $nextPage . $urlParams : null,
            "path" => $this->request->root() . '/' . $this->request->path(),
            "per_page" => $this->perPage,
            "prev_page_url" => $prevPage ? $this->request->root() . '/' . $this->request->path() . '?paginated=true&page=' . $prevPage . $urlParams : null,
            "to" => $to,
            "total" => $total,
        ]);
    }

    /**
     * Where like ignore case.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function whereLikeIgnoreCase($model, string $collumn, string $term)
    {
        return $model::whereRaw("UPPER({$collumn}) LIKE '%" . strtoupper($term) . "%'");
    }

    /**
     * Where like soundex.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function whereLikeSoundex($model, string $collumn, string $term)
    {
        return $model::whereRaw("soundex({$collumn}) LIKE soundex('" . $term . "') OR UPPER({$collumn}) LIKE '%" . strtoupper($term) . "%'");
    }

    /** Synch Has Many Relations.
     * 
     * @param $modelFunction Model function, for example $course->cities()
     * @param array $data The request data array.
     * @param string $field The field name.
     * 
     * @since 12.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected function syncHasMany($modelFunction, array $data, string $field)
    {
        $modelFunction->delete();
        foreach ($data[$field] as $f) {
            $modelFunction->create($f);
        }
    }
}