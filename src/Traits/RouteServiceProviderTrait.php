<?php

namespace LaravelDynamicApi\Traits;

use LaravelDynamicApi\Common\Constants;
use Exception;

/**
 * Route Service Provider.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
trait RouteServiceProviderTrait
{
    /**
     * Resolve model.
     * 
     * The default output is with the complete fields.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function resolveModelTrait(
        $requestUser,
        string $modelClass,
        $routeKey,
        string $locale,
        string $output = Constants::OUTPUT_COMPLETE,
        array $withCount = [],
        array $with = [],
        bool $translation = true,
        bool $abort = true
    ) {
        // Get model by id if it's numeric
        if (is_numeric($routeKey)) {
            return $this->getModelById(
                $requestUser,
                $modelClass,
                $routeKey,
                $output,
                $with,
                $withCount,
                $abort
            );
        }

        return $this->getModelBySlug(
            $requestUser,
            $modelClass,
            $routeKey,
            $locale,
            $output,
            $with,
            $withCount,
            $translation,
            $abort
        );
    }

    /**
     * Resolve relation model.
     * 
     * The default output is with the complete fields.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public function resolveRelationModelTrait(
        $requestUser,
        string $relationClass,
        $routeKey,
        string $locale,
        bool $translation = true,
        bool $abort = true
    ) {
        // Get model by id if it's numeric
        if (is_numeric($routeKey)) {
            return $this->getRelationModelById(
                $requestUser,
                $relationClass,
                $routeKey,
                $abort
            );
        }

        return $this->getRelationModelBySlug(
            $requestUser,
            $relationClass,
            $routeKey,
            $locale,
            $translation,
            $abort
        );
    }

    /**
     * Get model by id.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    private function getModelById(
        $userRequest,
        string $modelClass,
        string $routeKey,
        string $output,
        array $with,
        array $withCount,
        bool $abort
    ) {
        $model = $modelClass::select($modelClass::getFields($output))
            ->withCount(array_diff(
                array_merge($modelClass::getWithCountFields($output), $withCount),
                [$modelClass::MORPH_RELATION_NAME]
            ))
            ->with(array_merge($modelClass::getWithFields($output), $with))
            ->find($routeKey);

        if (!$model && !$abort) {
            return null;
        }

        if (!$model) {
            if ($userRequest) {
                $userRequest->update([
                    'status' => 404,
                ]);
            }
            abort(404, 'Not found');
        }

        return $model;
    }

    /**
     * Get relation model by id.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    private function getRelationModelById(
        $userRequest,
        string $relationClass,
        string $routeKey,
        bool $abort
    ) {
        $relationModel = $relationClass::where('id', $routeKey)->first();

        if (!$relationModel && !$abort) {
            return null;
        }

        if (!$relationModel) {
            $userRequest->update([
                'status' => 404,
            ]);
            abort(404);
        }

        return $relationModel;
    }

    /**
     * Get model by slug in all languages.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    private function getModelBySlug(
        $userRequest,
        string $modelClass,
        string $routeKey,
        string $locale,
        string $output,
        array $with,
        array $withCount,
        bool $translation,
        bool $abort
    ) {
        try {
            $model = $modelClass::select($modelClass::getFields($output))
                ->withCount(array_merge($modelClass::getWithCountFields($output), $withCount))
                ->with(array_merge($modelClass::getWithFields($output), $with));

            if ($translation) {
                $model = $model->whereTranslation('slug', $routeKey, $locale)->first();
            } else {
                $model = $model->where('slug', $routeKey, $locale)->first();
                if (is_null($model)) {
                    if (!$abort) {
                        return null;
                    }
                    $userRequest->update([
                        'status' => 404,
                    ]);
                    abort(404);
                }
            }

            if (is_null($model) && $translation) {
                foreach (config('translatable.locales') as $localeKey => $label) {

                    $modelInLocale = $modelClass::select($modelClass::getFields($output))
                        ->withCount(array_merge($modelClass::getWithCountFields($output), $withCount))
                        ->with(array_merge($modelClass::getWithFields($output), $with))
                        ->whereTranslation('slug', $routeKey, $localeKey)->first();

                    if ($modelInLocale) {
                        return $modelInLocale;
                    }
                }
                if (!$abort) {
                    return null;
                }
                if ($userRequest) {
                    $userRequest->update([
                        'status' => 404,
                    ]);
                }
                abort(404);
            }
        } catch (Exception $e) {
            if ($userRequest) {
                $userRequest->update([
                    'status' => 404,
                ]);
            }
            abort(404);
        }

        return $model;
    }

    /**
     * Get relation model by slug in all languages.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    private function getRelationModelBySlug(
        $userRequest,
        string $relationClass,
        string $routeKey,
        string $locale,
        bool $translation,
        bool $abort
    ) {
        try {
            if ($translation) {
                $relationModel = $relationClass::whereTranslation('slug', $routeKey, $locale)->first();
            } else {
                $relationModel = $relationClass::where('slug', $routeKey, $locale)->first();
                if (is_null($relationModel)) {
                    if (!$abort) {
                        return null;
                    }
                    $userRequest->update([
                        'status' => 404,
                    ]);
                    abort(404);
                }
            }

            if (is_null($relationModel) && $translation) {
                foreach (config('translatable.locales') as $localeKey => $label) {

                    $relationModelInLocale = $relationClass::whereTranslation('slug', $routeKey, $localeKey)->first();

                    if ($relationModelInLocale) {
                        return $relationModelInLocale;
                    }
                }
                if (!$abort) {
                    return null;
                }
                $userRequest->update([
                    'status' => 404,
                ]);
                abort(404);
            }
        } catch (Exception $e) {
            $userRequest->update([
                'status' => 404,
            ]);
            abort(404);
        }

        return $relationModel;
    }
}
