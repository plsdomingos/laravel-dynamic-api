<?php

namespace LaravelDynamicApi\Models;

use LaravelDynamicApi\Common\Constants;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Parent Model.
 * 
 * All models must extend to this to use the generic functions.
 * 
 * @since 01.04.2022
 * @author Pedro Domingos <pedro@panttera.com>
 */
class Model extends BaseModel
{
    // Use this constant to define if the model is a reference data.
    const IS_REFERENCE_DATA = false;

    // Use this constant to hidde the fields by default.
    // The visible fields must be defined inside the const ALWAYS_VISIBLE
    // This variable can also be overwritten in backend-engine.php on the field hidden_by_default.
    // If defined in backend-engine.php, this const will be ignored throughout the code.
    const HIDDEN_BY_DEFAULT = true;

    /**
     * The output is hidden by default, constant HIDDEN_BY_DEFAULT. If false the constant VISIBLE_FIELDS is ignored.
     * When the HIDDEN_BY_DEFAULT is true, the visible fields can be configured in this variable.
     * 
     * @var array
     */
    const VISIBLE_FIELDS = [];

    /**
     * Allowed resources and options.
     * 
     * Options:
     * * auth
     * * mandatory_rules
     * * paginated
     * * profiles
     * 
     * @var array EXECUTION_TYPES
     */
    const EXECUTION_TYPES = [];

    /**
     * Block resources.
     * 
     * @var array BLOCK_EXECUTION_TYPES
     */
    const BLOCK_EXECUTION_TYPES = [];

    /**
     * Model functions.
     * 
     * @var array FUNCTIONS
     */
    const FUNCTIONS = [];

    /**
     * All Fields.
     * 
     * All model fields including the translated fields except the translated fields 'model_id' and 'locale'.
     * 
     * @var array FIELDS All model fields including the translated fields except the translated fields 'model_id' and 'locale'.
     */
    const FIELDS = ['id'];

    /**
     * All Translated Fields.
     * 
     * All model translated fields except the fields 'model_id' and 'locale'.
     * 
     * @var array
     */
    const TRANSLATED_FIELDS = [];

    /**
     * All append fields.
     * 
     * @var array
     */
    const APPEND_FIELDS = [];

    /**
     * All relation fields.
     * 
     * @var array
     */
    const WITH_FIELDS = [];

    /**
     * Fields to hide when the model is returned as a relation.
     * 
     * The outputs ignore this fields.
     * 
     * @var array
     */
    const RELATION_HIDDEN_FIELDS = [];

    /**
     * Model casts.
     * 
     * @var array
     */
    const CAST = [];

    /**
     * Excel header to export.
     * 
     * @var array
     */
    const EXCEL_EXPORT_HEADER = [];

    /********************** SIMPLIFIED **********************/
    /**
     * Fields to return.
     * 
     * By default only id.
     * 
     * @output simplified
     * @var array
     */
    const SIMPLIFIED_FIELDS = ['id'];
    /**
     * Translations fields and appends to return.
     * 
     * @output simplified
     * @var array
     */
    const SIMPLIFIED_VISIBLE_FIELDS = [];
    /**
     * Relations to return.
     * 
     * @output simplified
     * @var array
     */
    const SIMPLIFIED_WITH_FIELDS = [];
    /**
     * Return relations count.
     * 
     * @output simplified
     * @var array
     */
    const SIMPLIFIED_WITH_COUNT_FIELDS = [];

    /*********************** COMPLETE **********************
     * 
     * By default all fields, translations and appends are visible.
     * All relations count are also returned.
     * 
     */
    /**
     * Relations to return.
     * 
     * @output complete
     * @var array
     */
    const COMPLETE_WITH_FIELDS = [];
    /**
     * All kind of fields to hide.
     * 
     * @output complete
     * @var array
     */
    const COMPLETE_HIDDEN_FIELDS = [];

    /******************** EXTENSIVE *************************
     * 
     * By default all fields, appends and translations are visible.
     * All relations and relations count are also returned.
     * 
     * !!!Use extensive output can cause performance problems!!!
     */
    /**
     * All kind of fields to hide.
     * 
     * @output extensive
     * @var array
     */
    const EXTENSIVE_HIDDEN_FIELDS = [];

    /*********************** RELATIONS ********************
     * TODO: Use simplified fields by default
     * TODO: It's not working
     * 
     * Make visible or hidde specific fields in model relations.
     * The array must contain the relation name and the field name.
     * 
     */
    /**
     * Extra visible fields in model relation.
     * 
     * @example - ['study_fields' => ['background_image']]
     * @var array<relationName-string, array<fieldName-string>>
     */
    const RELATION_MAKE_VISIBLE_FIELDS = [];
    /**
     * Hidden fields in model relation.
     * 
     * @example - ['study_fields' => ['media']]
     * @var array<relationName-string, array<fieldName-string>>
     */
    const RELATION_MAKE_HIDDEN_FIELDS = [];

    /*********************** VALIDATION ********************
     * 
     * Validation rules per method and user rule.
     * 
     */
    /**
     * Model validation rules.
     * 
     * @example - ['store' => ['school_admin'] => ['full_name' => 'required|string']]]
     * @var array<method-string, array<userRole, array<fieldName-string, rule-string>>>
     */
    const VALIDATION_RULES = [];

    /*************** ALWAYS VISIBLE OR HIDDEN *************
     * 
     * It's important to have some fields always visible or always hide.
     * For example we should always hide the user emails, phone number and address.
     * 
     */

    /**
     * Always hidden fields.
     * This constant is only used if the const HIDDEN_BY_DEFAULT is false
     * 
     * Not applied if the user is super admin.
     * 
     * @var array
     */
    const ALWAYS_HIDDEN = [];

    /**
     * Fields to transform in CDATA.
     * 
     * @var array
     */
    const CADATA_FIELDS = [];

    /**
     * Filter to ignore in the requestFilter funtion.
     * 
     * @var array
     */
    const IGNORE_FILTERS = [];

    /**
     * Term filters.
     * 
     * @var array
     */
    const TERM_FILTERS = [];

    /**
     * Relation term filters.
     * 
     * @var array
     */
    const RELATION_TERM_FILTERS = [];

    /**
     * Sort to ignore in the requestFilter funtion.
     * 
     * @var array
     */
    const IGNORE_SORT = [];

    /**
     * Return default hidden fields.
     * 
     * @return Simplified All fileds except SIMPLIFIED_VISIBLE_FIELDS and SIMPLIFIED_FIELDS.
     * @return Complete cons COMPLETE_HIDDEN_FIELDS.
     * @return Extensive cons EXTENSIVE_HIDDEN_FIELDS.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function getHiddenFields(string $output = Constants::OUTPUT_SIMPLIFIED): array
    {
        switch ($output) {
            case Constants::OUTPUT_EXTENSIVE:
                return static::EXTENSIVE_HIDDEN_FIELDS;
            case Constants::OUTPUT_COMPLETE:
                return array_values(
                    array_diff(
                        array_merge(static::COMPLETE_HIDDEN_FIELDS, static::WITH_FIELDS),
                        static::COMPLETE_WITH_FIELDS,
                    )
                );
            case Constants::OUTPUT_SIMPLIFIED:
            default:
                return array_values(
                    array_diff(
                        array_merge(
                            static::RELATION_HIDDEN_FIELDS,
                            static::APPEND_FIELDS,
                            static::FIELDS,
                            static::WITH_FIELDS,
                            array_map(function ($withCount) {
                                return $withCount . '_count';
                            }, array_merge(static::WITH_FIELDS))
                        ),
                        static::SIMPLIFIED_FIELDS,
                        static::SIMPLIFIED_VISIBLE_FIELDS,
                        static::SIMPLIFIED_WITH_FIELDS,
                        array_map(function ($withCount) {
                            return $withCount . '_count';
                        }, array_merge(static::SIMPLIFIED_WITH_COUNT_FIELDS))
                    )
                );
        }
    }

    /**
     * Return default visible fields.
     * 
     * @return Simplified SIMPLIFIED_VISIBLE_FIELDS and SIMPLIFIED_FIELDS.
     * @return Complete All APPEND_FIELDS, FIELDS and count WITH_FIELDS, except the field inside COMPLETE_HIDDEN_FIELDS and WITH_FIELDS
     * @return Extensive All appends and translated except the appends in EXTENSIVE_HIDDEN_FIELDS
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function getVisibleFields(string $output = Constants::OUTPUT_SIMPLIFIED): array
    {
        $hiddenByDefault = config('laravel-dynamic-api.hidden_by_default', static::HIDDEN_BY_DEFAULT);
        $alwaysHidden = $hiddenByDefault ?
            array_diff(static::getAllFields(), static::VISIBLE_FIELDS) :
            static::ALWAYS_HIDDEN;

        // Remove the appends with the same name as the relations.
        $whithFields = array_diff(static::WITH_FIELDS, static::APPEND_FIELDS);
        switch ($output) {
            case Constants::OUTPUT_EXTENSIVE:
                return array_values(
                    array_unique(
                        array_diff(
                            array_diff(
                                array_merge(
                                    array_merge(
                                        static::FIELDS,
                                        static::APPEND_FIELDS,
                                        static::WITH_FIELDS,
                                    ),
                                    array_map(function ($withCount) {
                                        return $withCount . '_count';
                                    }, array_merge(static::WITH_FIELDS))
                                ),
                                static::EXTENSIVE_HIDDEN_FIELDS
                            ),
                            $alwaysHidden
                        )
                    )
                );
            case Constants::OUTPUT_COMPLETE:
                return array_values(
                    array_unique(
                        array_diff(
                            array_diff(
                                array_merge(
                                    static::FIELDS,
                                    static::APPEND_FIELDS,
                                    array_map(function ($withCount) {
                                        return $withCount . '_count';
                                    }, array_merge(static::WITH_FIELDS)),
                                    static::COMPLETE_WITH_FIELDS
                                ),
                                static::COMPLETE_HIDDEN_FIELDS,
                                array_diff(
                                    $whithFields,
                                    static::COMPLETE_WITH_FIELDS
                                )
                            ),
                            $alwaysHidden
                        )
                    )
                );
            case Constants::OUTPUT_SIMPLIFIED:
            default:
                return array_values(
                    array_unique(
                        array_diff(
                            array_merge(
                                static::SIMPLIFIED_VISIBLE_FIELDS,
                                static::SIMPLIFIED_FIELDS,
                                array_map(
                                    function ($withCount) {
                                        return $withCount . '_count';
                                    },
                                    array_merge(static::SIMPLIFIED_WITH_COUNT_FIELDS)
                                ),
                                static::SIMPLIFIED_WITH_FIELDS
                            ),
                            $alwaysHidden
                        )
                    )
                );
        }
    }

    /**
     * Get append fields.
     *
     * @return array static::APPEND_FIELDS
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function getAppendFields(string $output = Constants::OUTPUT_SIMPLIFIED): array
    {
        return static::APPEND_FIELDS;
    }

    /**
     * Return default with fields.
     * 
     * @return Simplified WITH_FIELDS and SIMPLIFIED_WITH_FIELDS.
     * @return Complete WITH_FIELDS, SIMPLIFIED_WITH_FIELDS and COMPLETE_WITH_FIELDS
     * @return Extensive WITH_FIELDS, SIMPLIFIED_WITH_FIELDS, COMPLETE_WITH_FIELDS and EXTENSIVE_WITH_FIELDS
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function getWithFields(string $output = Constants::OUTPUT_SIMPLIFIED): array
    {
        switch ($output) {
            case Constants::OUTPUT_EXTENSIVE:
                return array_diff(static::WITH_FIELDS, static::EXTENSIVE_HIDDEN_FIELDS);
            case Constants::OUTPUT_COMPLETE:
                return static::COMPLETE_WITH_FIELDS;
            case Constants::OUTPUT_SIMPLIFIED:
            default:
                return static::SIMPLIFIED_WITH_FIELDS;
        }
    }

    /**
     * Return default with count fields.
     * 
     * @return Simplified SIMPLIFIED_WITH_COUNT_FIELDS.
     * @return Complete all with fields
     * @return Extensive all with fields
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function getWithCountFields(string $output = Constants::OUTPUT_SIMPLIFIED): array
    {
        switch ($output) {
            case Constants::OUTPUT_EXTENSIVE:
                return array_diff(
                    static::WITH_FIELDS,
                    static::EXTENSIVE_HIDDEN_FIELDS
                );
            case Constants::OUTPUT_COMPLETE:
                // TODO: Add COMPLETE_HIDDEN_COUNT_FIELDS, to hidde some counts if necessary
                return static::WITH_FIELDS;
            case Constants::OUTPUT_SIMPLIFIED:
            default:
                return static::SIMPLIFIED_WITH_COUNT_FIELDS;
        }
    }

    /**
     * Return default fields.
     * 
     * @return Simplified SIMPLIFIED_FIELDS.
     * @return Completed FIELDS
     * @return Extensive FIELDS
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function getFields(string $output = Constants::OUTPUT_SIMPLIFIED): array
    {
        switch ($output) {
            case Constants::OUTPUT_EXTENSIVE:
            case Constants::OUTPUT_COMPLETE:
                return array_diff(static::FIELDS, static::TRANSLATED_FIELDS);
            case Constants::OUTPUT_SIMPLIFIED:
            default:
                return array_diff(static::SIMPLIFIED_FIELDS, static::TRANSLATED_FIELDS);
        }
    }

    /**
     * Get all fields.
     * 
     * @return array Unique fields of RELATION_HIDDEN_FIELDS, APPEND_FIELDS, TRANSLATED_FIELDS, FIELDS
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function getAllFields(string $output = Constants::OUTPUT_SIMPLIFIED): array
    {
        return
            array_merge(
                static::RELATION_HIDDEN_FIELDS,
                static::APPEND_FIELDS,
                static::TRANSLATED_FIELDS,
                static::FIELDS,
                static::WITH_FIELDS,
                array_map(function ($withCount) {
                    return $withCount . '_count';
                }, array_merge(static::WITH_FIELDS))
            );
    }

    /**
     * Get relation visible fields.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function getRelationVisibleFields(
        string $relation,
        string $relationClass,
        string $output = Constants::OUTPUT_SIMPLIFIED
    ): array {
        if (array_key_exists(
            $relation,
            static::RELATION_MAKE_VISIBLE_FIELDS
        )) {
            if (array_key_exists(
                $output,
                static::RELATION_MAKE_VISIBLE_FIELDS[$relation]
            )) {
                if (is_array(static::RELATION_MAKE_VISIBLE_FIELDS[$relation][$output])) {
                    return static::RELATION_MAKE_VISIBLE_FIELDS[$relation][$output];
                }
            }
        }
        return $relationClass::getVisibleFields($output);
    }

    /**
     * Get relation hidden fields.
     * All fields except the visible ones.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function getRelationHiddenFields(
        string $relation,
        string $relationClass,
        string $output = Constants::OUTPUT_SIMPLIFIED,
        array $makeVisible = []
    ): array {
        if (array_key_exists(
            $relation,
            static::RELATION_MAKE_HIDDEN_FIELDS
        )) {
            if (is_array(static::RELATION_MAKE_HIDDEN_FIELDS[$relation])) {
                return static::RELATION_MAKE_HIDDEN_FIELDS[$relation];
            }
        }

        return array_values(
            array_diff(
                array_merge(
                    $relationClass::getHiddenFields($output),
                    $relationClass::getAllFields($output)
                ),
                $makeVisible
            )
        );
    }

    /**
     * Validate visible and hidden fields.
     * 
     * @return array ['makeVisible' => $makeVisible, 'makeHidden' => $makeHidden]
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function validateVisibleAndHiddenFields(
        object | null $user,
        array $makeVisible,
        array $makeHidden
    ): array {
        $hiddenByDefault = config('laravel-dynamic-api.hidden_by_default', static::HIDDEN_BY_DEFAULT);
        $alwaysHidden = $hiddenByDefault ?
            array_diff(static::getAllFields(), static::VISIBLE_FIELDS) :
            static::ALWAYS_HIDDEN;

        if ($user !== null) {
            if ($user->isSuperAdmin()) {
                return ['makeVisible' => $makeVisible, 'makeHidden' => $makeHidden];
            }
        }
        $makeHidden = array_values(array_merge($makeHidden, $alwaysHidden));
        $makeVisible = array_values(array_diff($makeVisible, $alwaysHidden));

        return ['makeVisible' => $makeVisible, 'makeHidden' => $makeHidden];
    }

    /**
     * Update fields to CDATA to return in a XML.
     * 
     * @todo Accept internal array inside the object with more then 2 steps.
     * 
     * @since 21.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function updateCDATAFeilds(string $output, bool $error = false): object
    {
        if (!$error) {
            $singleObject = false;
            if (!($output instanceof Collection)) {
                $output = collect($output);
                if (!is_array($output->take(1))) {
                    $singleObject = true;
                }
            }
            foreach (static::CADATA_FIELDS as $cdataField) {
                if ($singleObject) {
                    if (Str::contains($cdataField, '.')) {
                        $arrayFields = explode('.', $cdataField);
                        $field1 = $arrayFields[0];
                        if ($output[$field1]) {
                            $updatedElement = [];
                            foreach ($output[$field1] as $arrayElement) {
                                $field2 = $arrayFields[1];
                                $arrayElement[$field2] = ['_cdata' => $arrayElement[$field2]];
                                array_push($updatedElement, $arrayElement);
                            }
                            $output->$field1 = $updatedElement;
                        }
                    } else {
                        if ($output[$cdataField]) {
                            $output[$cdataField] = ['_cdata' => $output[$cdataField]];
                        }
                    }
                    continue;
                }

                foreach ($output as $outputElement) {
                    if (Str::contains($cdataField, '.')) {
                        $arrayFields = explode('.', $cdataField);
                        $field1 = $arrayFields[0];
                        if ($outputElement->$field1) {
                            $updatedElement = [];
                            foreach ($outputElement->$field1 as $arrayElement) {
                                $field2 = $arrayFields[1];
                                $arrayElement[$field2] = ['_cdata' => $arrayElement[$field2]];
                                array_push($updatedElement, $arrayElement);
                            }
                            $outputElement->$field1 = $updatedElement;
                        }
                    } else {
                        if ($outputElement->$cdataField) {
                            $outputElement->$cdataField = ['_cdata' => $outputElement->$cdataField];
                        }
                    }
                }
            }
            return $output;
        }
        return $output;
    }

    /**
     * Validate if the resource is allowed.
     * 
     * By default: index and show true, others false.
     * 
     * @param string $type index | show | store | update | bulkDestroy | bulkRestore
     * 
     * @since 31.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function isResourceAllowed(string $type): bool
    {
        // Check if the resource is blocked.
        if (in_array($type, static::BLOCK_EXECUTION_TYPES)) {
            return false;
        }
        // Check model available resources.
        // If not empty all executions types need to be here.
        if (!empty(static::EXECUTION_TYPES)) {
            if (array_key_exists($type, static::EXECUTION_TYPES) || in_array($type, static::EXECUTION_TYPES)) {
                return true;
            } else {
                return false;
            }
        }

        // Check model available resources.
        // If not empty all executions types need to be here.
        $resources = config('laravel-dynamic-api.execution_types', []);
        if (!empty($resources)) {
            if (array_key_exists($type, $resources) || in_array($type, $resources)) {
                return true;
            } else {
                return false;
            }
        }

        // Return the default values
        switch ($type) {
            case 'show':
            case 'relationIndex':
            case 'relationShow':
                return true;
            default:
                return false;
        }
    }

    /**
     * Validate if the resource needs authentication.
     * 
     * By default: index and show false, others true.
     * 
     * @param string $type index | show | store | update | bulkDestroy | bulkRestore
     * 
     * @since 31.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function isAuthRequired(string $type): bool
    {
        $value = Model::getExecutionTypeValueByKey($type, static::EXECUTION_TYPES, 'authentication');
        if ($value !== null) {
            return $value == true;
        }

        // Return default
        switch ($type) {
            case 'index':
            case 'show':
            case 'relationIndex':
            case 'relationShow':
                return false;
            default:
                return true;
        }
    }

    /**
     * Validate if the resource needs rules.
     * 
     * By default: index, show and delete false ; store and update true
     * 
     * @param string $type index | show | store | update | bulkDestroy | bulkRestore
     * 
     * @since 31.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function isRulesRequired(string $type): bool
    {
        $value = Model::getExecutionTypeValueByKey($type, static::EXECUTION_TYPES, 'mandatory_rules');
        if ($value !== null) {
            return $value == true;
        }

        // Return default valyes
        switch ($type) {
            case 'index':
            case 'show':
            case 'destroy':
            case 'relationIndex':
            case 'relationShow':
                return false;
            default:
                return true;
        }
    }

    /**
     * Get if index output is paginated.
     * 
     * By default false
     * 
     * @since 31.05.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function isPaginated(string $type, string $output): bool
    {
        $value = Model::getExecutionTypeValueByKey($type, static::EXECUTION_TYPES, 'paginated');
        if ($value !== null) {
            return $value == true;
        }

        // Return default values.
        switch ($output) {
            case Constants::OUTPUT_SIMPLIFIED:
                return false;
            case Constants::OUTPUT_COMPLETE:
            case Constants::OUTPUT_EXTENSIVE:
            default:
                return true;
        }
    }

    /**
     * Normalize dates before create and update.
     * 
     * By default this function returns the $data without any logic.
     * 
     * Overwride this funtion inside the controller, if neessary.
     * 
     * @since 01.04.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    protected static function normalizeDates(Request $request, array $data): array
    {
        return $data;
    }

    /**
     * Function to run before the show, update and destroy functions.
     * 
     * By default this function doesn't have any logic inside.
     * 
     * Overwride this funtion inside the model, if neessary.
     * 
     * @param object $model The model.
     * @param string $type show | update | destroy
     * @param $authUser Authenticated user
     * 
     */
    public static function beforeFunction(
        string $type,
        Request $request,
        array $data,
        string $modelClass,
        string $modelName,
        object | null $model,
        string | null $relationClass,
        string | null $relationName,
        object | null $relationModel,
        string $locale,
        object | null $authUser,
    ): void {
        // To be overwritten in the model.
    }
    /**
     * Function to run before the show, update and destroy functions.
     * 
     * By default this function doesn't have any logic inside.
     * 
     * Overwride this funtion inside the model, if neessary.
     * 
     * @param object $model The model.
     * @param string $type show | update | destroy
     * @param $authUser Authenticated user
     * 
     */
    public function beforeModelFunction(
        string $type,
        Request $request,
        array $data,
        string $modelClass,
        string $modelName,
        object | null $model,
        string | null $relationClass,
        string | null $relationName,
        object | null $relationModel,
        string $locale,
        object | null $authUser,
    ) {
        // To be overwritten in the model.
        return $this;
    }

    /**
     * Function to run after the show, store, update and destroy functions.
     * 
     * By default this function doesn't have any logic inside.
     * 
     * Overwride this funtion inside the model, if neessary.
     * 
     * @param object $model The model.
     * @param string $type show | store | update | destroy
     * @param $authUser Authenticated user
     * 
     */
    public static function afterFunction(
        string $type,
        Request $request,
        array $data,
        mixed $returnObject,
        string $modelClass,
        string $modelName,
        object | null $model,
        string | null $relationClass,
        string | null $relationName,
        object | null $relationModel,
        string $locale,
        object | null $authUser,
    ): mixed {
        // To be overwritten in the model.
        return $returnObject;
    }

    /**
     * Function to run after the show, store, update and destroy functions.
     * 
     * By default this function doesn't have any logic inside.
     * 
     * Overwride this funtion inside the model, if neessary.
     * 
     * @param object $model The model.
     * @param string $type show | store | update | destroy
     * @param $authUser Authenticated user
     * 
     */
    public function afterModelFunction(
        string $type,
        Request $request,
        array $data,
        string $modelClass,
        string $modelName,
        object | null $model,
        string | null $relationClass,
        string | null $relationName,
        object | null $relationModel,
        string $locale,
        object | null $authUser,
    ): object {
        // To be overwritten in the model.
        return $this;
    }

    public static function requestFilter(
        string $modelClass,
        mixed $query,
        mixed $filter,
        mixed $sortBy,
        mixed $sortOrder,
        int $page,
        int $perPage,
        object | null $authUser,
    ): mixed {
        // To be overwritten in the model.
        return $query;
    }

    public static function requestSort(
        string $modelClass,
        mixed $query,
        mixed $sortBy,
        mixed $sortOrder,
        object | null $authUser,
    ): mixed {
        // To be overwritten in the model.
        return $query;
    }

    /**
     * Filter the results after get. Usered on get relation index.
     * 
     * @since 27.03.2024
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function collectionFilter(
        string $modelClass,
        mixed $query,
        mixed $filter,
        object | null $authUser,
    ): mixed {
        // To be overwritten in the model.
        return $query;
    }

    /**
     * Validate if the profile is allowed.
     * 
     * @param string $type index | show | store | update | bulkDestroy | bulkRestore
     * 
     * @since 28.12.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function isProfileAllowed(
        string $type,
        object | null $authUser
    ): bool {
        if ($authUser->isSuperAdmin()) {
            return true;
        }

        $value = Model::getExecutionTypeValueByKey($type, static::EXECUTION_TYPES, 'profiles');
        if (is_array($value)) {
            if (in_array('all', $value)) {
                return true;
            }
            foreach ($authUser->roles as $role) {
                if (in_array($role['name'], $value)) {
                    return true;
                }
            }
        }

        // Return the default values
        switch ($type) {
            case 'index':
            case 'show':
                return true;
            default:
                return false;
        }
    }

    /**
     * Validate if the profile is allowed.
     * 
     * @param string $type index | show | store | update | bulkDestroy | bulkRestore
     * 
     * @since 28.12.2022
     * @author Pedro Domingos <pedro@panttera.com>
     */
    public static function isAllowed(
        string $type,
        Request $request,
        string $modelClass,
        string $modelName,
        object | null $model,
        string | null $relationClass,
        string | null $relationName,
        object | null $relationModel,
        string $locale,
        object | null $authUser,
    ): bool {
        // To be overwritten in the model.
        return true;
    }

    /**
     * Generate specific rules inside the model.
     */
    public static function generateSpecificRules(
        string $field,
        string $type,
        Request $request,
        string $modelClass,
        string $modelName,
        object | null $model,
        string | null $relationClass,
        string | null $relationName,
        object | null $relationModel,
        string $locale,
        object | null $authUser,
        array $rule,
        string $modelTable,
        string | null $modelTranslationTable,
    ): array {
        // To be overwritten in the model.
        return [];
    }

    public static function getExecutionTypeValueByKey(string $type, array $modelResource, string $executionTypeKey)
    {
        $resources = $modelResource;
        $value = Model::getExecutionTypeOnRessources($type, $resources, $executionTypeKey);
        if ($value !== null) {
            return $value;
        }

        // If does not exists in the model ressources, check the global ones.
        $resources = config('laravel-dynamic-api.execution_types', []);
        return Model::getExecutionTypeOnRessources($type, $resources, $executionTypeKey);
    }

    private static function getExecutionTypeOnRessources(string $type, array $resources, string $executionTypeKey)
    {
        if (in_array($type, $resources) || array_key_exists($type, $resources)) {
            if (is_array($resources[$type])) {
                foreach ($resources[$type] as $key => $option) {
                    if ($key === $executionTypeKey) {
                        return $option;
                    }
                }
            }
        }
        return null;
    }
}