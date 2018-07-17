<?php
use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\HelperConstants;
use App\Api\Constants\LogConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Models\ComponentOption;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\PageItemOption;
use App\Api\Models\Site;
use App\Api\Models\TemplateItemOption;
use App\Api\Tools\Query\QueryFactory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

if ( ! function_exists('get_cms_application')) {
    /**
     * Return a cms application name
     *
     * @return mixed
     */
    function get_cms_application()
    {
        return session(HelperConstants::APPLICATION_NAME);
    }
}

if ( ! function_exists('set_cms_application')) {
    /**
     * Set a cms application name in session
     *
     * @param $application
     */
    function set_cms_application($application)
    {
        session([HelperConstants::APPLICATION_NAME => $application]);
    }
}

if ( ! function_exists('to_boolean')) {
    /**
     * Convert a string or a number to a boolean
     *
     * @param $var
     * @param bool $includeNumber
     * @return bool
     */
    function to_boolean($var, $includeNumber = true)
    {
        if ( ! is_string($var)) return (bool) $var;
        if ($includeNumber) {
            switch (strtolower($var)) {
                case '1':
                case 'true':
                case 'on':
                case 'yes':
                case 'y':
                    return true;
                default:
                    return false;
            }
        } else {
            switch (strtolower($var)) {
                case 'true':
                case 'on':
                case 'yes':
                case 'y':
                    return true;
                default:
                    return false;
            }
        }
    }
}

if ( ! function_exists('is_boolean')) {
    /**
     * Check if the value is a boolean or not
     *
     * @param $value
     * @param bool $includeNumber
     * @return bool
     */
    function is_boolean($value, $includeNumber = true)
    {
        if ( ! is_string($value)) {
            return is_bool($value);
        }
        if ($includeNumber) {
            switch (strtolower($value)) {
                case '1':
                case '0':
                case 'true':
                case 'false':
                case 'on':
                case 'off':
                case 'yes':
                case 'no':
                case 'y':
                case 'n':
                    return true;
                default:
                    return false;
            }
        } else {
            switch (strtolower($value)) {
                case 'true':
                case 'false':
                case 'on':
                case 'off':
                case 'yes':
                case 'no':
                case 'y':
                case 'n':
                    return true;
                default:
                    return false;
            }
        }
    }
}

if ( ! function_exists('get_upload_path_structure')) {
    /**
     * Return a file structure of cms uploads folder
     *
     * @param null $directory
     * @param array $exceptions
     * @return array
     */
    function get_upload_path_structure($directory = null, $exceptions = [])
    {
        $uploadsPath = config('cms.' . get_cms_application() . '.uploads_path');
        $root = trim_uploads_path($uploadsPath);

        $directory = $root . '/' . preg_replace('/^(\/|\\\)?' . preg_quote($root, '/') . '/', '', $directory);

        /** @noinspection PhpUndefinedMethodInspection */
        $directories = Storage::directories($directory);
        /** @noinspection PhpUndefinedMethodInspection */
        $files = Storage::files($directory);

        $directoryData = [];
        $fileData = [];

        if ( ! empty($directories)) {
            foreach ($directories as $key => $value) {
                $value = trim_uploads_path($value);
                if ( ! in_array($value, $exceptions)) {
                    $items = get_upload_path_structure($value);
                    array_push($directoryData, [
                        "name" => pathinfo($value, PATHINFO_BASENAME),
                        "type" => LogConstants::DIRECTORY,
                        "items" => $items
                    ]);
                }
            }
        }

        if ( ! empty($files)) {
            foreach ($files as $key => $value) {
                $value = trim_uploads_path($value);
                if ( ! in_array($value, $exceptions)) {
                    array_push($fileData, [
                        "name" => pathinfo($value, PATHINFO_BASENAME),
                        "type" => LogConstants::FILE
                    ]);
                }
            }
        }

        return array_merge($directoryData, $fileData);
    }
}

if ( ! function_exists('str_is_valid_path')) {
    /**
     * Return true if a file path is valid or not
     *
     * @param $path
     * @return bool
     */
    function str_is_valid_path($path)
    {
        $dirName = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return ! empty($dirName) && ! empty($filename) && ! empty($extension);
    }
}

if ( ! function_exists('str_slash_to_forward_slash')) {
    /**
     * Replace any slashed to a forward slash
     *
     * @param $string
     * @return mixed
     */
    function str_slash_to_forward_slash($string)
    {
        return preg_replace('/\\\/', '/', $string);
    }
}

if ( ! function_exists('sha1_is_same_file')) {
    /**
     * Return true if the two files are the same
     *
     * @param $file1
     * @param $file2
     * @param bool $rawOutput
     * @return bool
     */
    function sha1_is_same_file($file1, $file2, $rawOutput = false)
    {
        return sha1_file($file1, $rawOutput) === sha1_file($file2, $rawOutput);
    }
}

if ( ! function_exists('set_env')) {
    /**
     * Set env and config file a new key and value
     *
     * @param $environmentName
     * @param $configKey
     * @param $newValue
     */
    function set_env($environmentName, $configKey, $newValue)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $envPath = App::environmentFilePath();
        $envContent = file_get_contents($envPath);

        /** @noinspection PhpUndefinedMethodInspection */
        $newEnvContent = str_ireplace(
            $environmentName . '=' . Config::get($configKey),
            $environmentName . '=' . $newValue,
            $envContent,
            $count
        );

        if ( ! $count) {
            $newEnvContent .= "\r\n" . $environmentName . '=' . $newValue;
        }

        file_put_contents($envPath, $newEnvContent);

        /** @noinspection PhpUndefinedMethodInspection */
        Config::set($configKey, $newValue);

        //Reload
        /** @noinspection PhpUndefinedMethodInspection */
        if (file_exists(App::getCachedConfigPath())) {
            Artisan::call('config:cache');
        }
    }
}

if ( ! function_exists('is_json')) {
    /**
     * Return true if the string is a json string
     *
     * @param $string
     * @return bool
     */
    function is_json($string)
    {
        try {
            return is_string($string)
                && is_array(json_decode($string, true))
                && (json_last_error() == JSON_ERROR_NONE) ? true : false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if ( ! function_exists('json_recursive_decode')) {
    /**
     * Return decoded json recursively
     *
     * @param $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     */
    function json_recursive_decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        if (is_json($json)) {
            $json = json_decode($json, $assoc, $depth, $options);
            return json_recursive_decode($json, $assoc, $depth, $options);
        }
        return $json;
    }
}

if ( ! function_exists('get_class_by_name')) {
    /**
     * Return a class object from a class name
     *
     * @param $className
     * @param string $namespace
     * @return null
     */
    function get_class_by_name($className, $namespace = '')
    {
        $className = $namespace . '\\' .  $className;

        if ( ! class_exists($className)) return null;

        return new $className;
    }
}

if ( ! function_exists('get_default_option_null_value')) {
    /**
     * Return a default value from an option type if the option value is empty or null
     *
     * @param $value
     * @param string $type
     * @return float|int|string
     * @throws Exception
     */
    function get_default_option_null_value($value, $type = OptionValueConstants::STRING)
    {
        if ( ! is_null($value)) return $value;

        switch (strtoupper($type)) {
            case OptionValueConstants::STRING:
                $value = '';
                break;
            case OptionValueConstants::INTEGER:
                $value = 0;
                break;
            case OptionValueConstants::DECIMAL:
                $value = 0.0;
                break;
            case OptionValueConstants::DATE:
                $value = Carbon::now();
                break;
            default:
                throw new \Exception(ErrorMessageConstants::INVALID_OPTION_TYPE);
        }

        return $value;
    }
}

if ( ! function_exists('get_item_option_by_any_item_option_id')) {
    /**
     * Return a item option from any type of item option id
     *
     * @param $itemOptionId
     * @return bool|null|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|PageItemOption|GlobalItemOption|TemplateItemOption|ComponentOption
     */
    function get_item_option_by_any_item_option_id($itemOptionId)
    {
       if ( ! $itemOptionId) return null;

       $pageItemOption = PageItemOption::find($itemOptionId);
       $globalItemOption = GlobalItemOption::find($itemOptionId);
       $templateItemOption = TemplateItemOption::find($itemOptionId);
       $componentOption = ComponentOption::find($itemOptionId);

       return $pageItemOption ?: $globalItemOption ?: $templateItemOption ?: $componentOption ?: null;
    }
}

if ( ! function_exists('extract_control_list_data')) {
    /**
     * Return an object from a control list data
     *
     * @param Site $site
     * @param $controlListData
     * @param $itemOptionId
     * @param $languageCode
     * @param $onlyId
     * @param array $pages
     * @return array
     */
    function extract_control_list_data($site, $controlListData, $itemOptionId, $languageCode = null, $onlyId = null, $pages = [])
    {
        $return = [];

        if ( ! empty($controlListData) && ! is_null($controlListData)) {
            usort($controlListData, function ($first, $second) {
                if (isset($first->order) && isset($second->order)) {
                    return $first->order > $second->order;
                }
                return 0;
            });

            foreach ($controlListData as $key => $controlList) {
                $data = [];

                $id = isset($controlList->id) ? $controlList->id : $key;
                $globalItem = null;
                $globalItemId = isset($controlList->global_item_id) ? $controlList->global_item_id : null;
                $attributes = isset($controlList->props) ? $controlList->props : null;
                $queryId = $itemOptionId . '.' . $id;
                $active = isset($controlList->active) ? $controlList->active : true;

                if (!$active) continue;

                if ( ! is_null($globalItemId)) {
                    /** @var GlobalItem $globalItem */
                    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                    $globalItem = GlobalItem::where('id', $globalItemId)
                        ->where('is_active', true)
                        ->first();
                }

                if (is_null($globalItem)) {
                    if ( ! empty($attributes)) {
                        foreach ($attributes as $attribute) {
                            $optionValue = $attribute->option_value;
                            $queryJSON = null;

                            try {
                                if ($attribute->element_type === OptionElementTypeConstants::CONTROL_LIST) {
                                    if ( ! empty($onlyId)) {
                                        if ($onlyId === $queryId) {
                                            $optionValue = extract_control_list_data($site, json_recursive_decode($optionValue), $queryId, $languageCode, null, $pages);

                                            return [
                                                'query_id' => $queryId,
                                                'option_value' => $optionValue ?: null,
                                                'option_type' => $attribute->option_type,
                                                'element_type' => $attribute->element_type,
                                                'element_value' => json_recursive_decode($attribute->element_value)
                                            ];
                                        }
                                    }

                                    $optionValue = extract_control_list_data($site, json_recursive_decode($optionValue), $queryId, $languageCode, $onlyId, $pages);

                                    if ($query = QueryFactory::make(
                                        $queryId,
                                        $optionValue,
                                        $attribute->element_type,
                                        $attribute->element_value,
                                        $site,
                                        $languageCode
                                    )
                                    ) {
                                        $queryJSON = $query->jsonSerialize();
                                        $optionValue = [];
                                    }
                                }
                            } catch (\Exception $exception) {}

                            switch ($attribute->option_type) {
                                case OptionValueConstants::INTEGER:
                                $optionValue = intval($optionValue);
                                    break;
                                case OptionValueConstants::DECIMAL:
                                $optionValue = floatval($optionValue);
                                    break;
                                default:
                                    break;
                            }

                            $elementValue = json_recursive_decode($attribute->element_value);

                            if (isset($elementValue->propStructure) && is_array($elementValue->propStructure)) {
                                $structures = array_map(function ($structure) {
                                    if (isset($structure->element_value) && is_string($structure->element_value)) {
                                        $structure->element_value = json_recursive_decode($structure->element_value);
                                    }
                                    return $structure;
                                }, $elementValue->propStructure);

                                $elementValue->propStruture = $structures;
                            }

                            switch ($attribute->element_type) {
                                case OptionElementTypeConstants::CHECKBOX:
                                    $optionValue = to_boolean($optionValue);
                                    break;
                                case OptionElementTypeConstants::TEXTBOX:
                                    if (isset($elementValue->helper) && $elementValue->helper === 'url') {
                                        if (is_uuid($optionValue)) {
                                            $pageSelectedId = $optionValue;
                                        } else if (isset($elementValue->selectedPageId) && is_uuid($elementValue->selectedPageId)) {
                                            $pageSelectedId = $elementValue->selectedPageId;
                                        } else {
                                            $pageSelectedId = null;
                                        }

                                        if ($pageSelectedId) {
                                        /** @var \App\Api\Models\Page $targetPage */
                                            if ($targetPage = collect($pages)->where('id', $pageSelectedId)->first()) {
                                            $optionValue = $targetPage->friendly_url;
                                            }
                                        }
                                    }
                                    break;
                                default:
                                    break;
                            }

                            $returnData = [
                                HelperConstants::HELPER_ID => $id,
                                $attribute->variable_name => $optionValue ?: null,
                                $attribute->variable_name . HelperConstants::HELPER_ID => $attribute->prop_id,
                                $attribute->variable_name . HelperConstants::HELPER_OPTION_TYPE => $attribute->option_type,
                                $attribute->variable_name . HelperConstants::HELPER_ELEMENT_TYPE => $attribute->element_type,
                                $attribute->variable_name . HelperConstants::HELPER_ELEMENT_VALUE => $elementValue
                            ];

                            if ( ! is_null($queryJSON)) {
                                $returnData[$attribute->variable_name . HelperConstants::HELPER_QUERY] = $queryJSON;
                            }

                            $data = array_merge($returnData, $data);
                        }
                    }
                } else {
                    /** @var [] $globalItemOptions */
                    $globalItemOptions = $globalItem->globalItemOptions;

                    if ( ! empty($globalItemOptions)) {
                        foreach ($globalItemOptions as $globalItemOption) {
                            $globalItemOptionData = [];
                            $queryJSON = null;

                            $optionArray = $globalItemOption->withNecessaryData([], $languageCode)->all();

                            if ($optionArray['element_type'] === OptionElementTypeConstants::CONTROL_LIST) {
                                $controlListData = array_key_exists('translated_text', $optionArray)
                                    ? json_recursive_decode($optionArray['translated_text'])
                                    : json_recursive_decode($optionArray['option_value']);

                                $value = extract_control_list_data($site, $controlListData, $globalItemOption->getKey(), $languageCode, null, $pages);

                                if ($query = QueryFactory::make(
                                    $globalItemOption->getKey(),
                                    $value,
                                    $optionArray['element_type'],
                                    $optionArray['element_value'],
                                    $site,
                                    $languageCode
                                )
                                ) {
                                    $queryJSON = $query->jsonSerialize();
                                    $value = [];
                                }
                            } else {
                                $value = array_key_exists('translated_text', $optionArray) ? $optionArray['translated_text'] : $optionArray['option_value'];
                            }

                            switch ($optionArray['option_type']) {
                                case OptionValueConstants::INTEGER:
                                $value = intval($value);
                                    break;
                                case OptionValueConstants::DECIMAL:
                                $value = floatval($value);
                                    break;
                                default:
                                    break;
                            }

                            $elementValue = json_recursive_decode($optionArray['element_value']);

                            if (isset($elementValue->propStructure) && is_array($elementValue->propStructure)) {
                                $structures = array_map(function ($structure) {
                                    if (isset($structure->element_value) && is_string($structure->element_value)) {
                                        $structure->element_value = json_recursive_decode($structure->element_value);
                                    }
                                    return $structure;
                                }, $elementValue->propStructure);

                                $elementValue->propStruture = $structures;
                            }

                            switch ($optionArray['element_type']) {
                                case OptionElementTypeConstants::CHECKBOX:
                                    $value = to_boolean($value);
                                    break;
                                case OptionElementTypeConstants::TEXTBOX:
                                    if (isset($elementValue->helper) && $elementValue->helper === 'url') {
                                        if (is_uuid($value)) {
                                            $pageSelectedId = $value;
                                        } else if (isset($elementValue->selectedPageId) && is_uuid($elementValue->selectedPageId)) {
                                            $pageSelectedId = $elementValue->selectedPageId;
                                        } else {
                                            $pageSelectedId = null;
                                        }

                                        if ($pageSelectedId) {
                                        /** @var \App\Api\Models\Page $targetPage */
                                            if ($targetPage = collect($pages)->where('id', $pageSelectedId)->first()) {
                                            $value = $targetPage->friendly_url;
                                            }
                                        }
                                    }
                                    break;
                                default:
                                    break;
                            }

                            $globalItemOptionData[HelperConstants::HELPER_ID] = $id;
                            $globalItemOptionData[$optionArray['variable_name']] = $value;
                            $globalItemOptionData[$optionArray['variable_name'] . HelperConstants::HELPER_ID] = $optionArray['prop_id'];
                            $globalItemOptionData[$optionArray['variable_name'] . HelperConstants::HELPER_OPTION_TYPE] = $optionArray['option_type'];
                            $globalItemOptionData[$optionArray['variable_name'] . HelperConstants::HELPER_ELEMENT_TYPE] = $optionArray['element_type'];
                            $globalItemOptionData[$optionArray['variable_name'] . HelperConstants::HELPER_ELEMENT_VALUE] = $elementValue;

                            if (!is_null($queryJSON)) {
                                $globalItemOptionData[$optionArray['variable_name'] . HelperConstants::HELPER_QUERY] = $queryJSON;
                            }

                            if ( ! empty($globalItemOptionData)) {
                                $data = array_merge($globalItemOptionData, $data);
                            }
                        }
                    }
                }

                if ( ! empty($data)) {
                    $return[] = $data;
                }
            }
        }

        return $return;
    }
}

if ( ! function_exists('trim_uploads_path')) {
    /**
     * Return a trimmed file path
     *
     * @param $path
     * @param string $uploads
     * @return mixed
     */
    function trim_uploads_path($path, $uploads = HelperConstants::UPLOADS_FOLDER)
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        if (app()->environment('testing')) {
            $uploads = HelperConstants::UPLOADS_FOLDER_TESTING;
        }

        return preg_replace('/^' . preg_quote($uploads, '/') . '(\/)?/i', '', $path);
    }

}

if ( ! function_exists('is_valid_md5')) {
    /**
     * Return true if the given string is a valid md5 string
     *
     * @param string $md5
     * @return bool
     */
    function is_valid_md5($md5 = '')
    {
        return strlen($md5) == 32 && ctype_xdigit($md5);
    }
}

if ( ! function_exists('is_404')) {
    /**
     * Return true if url is 404
     *
     * @param $url
     * @return bool
     */
    function is_404($url)
    {
        try {
            $handle = curl_init($url);
            curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

            curl_exec($handle);

            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);

            if ($httpCode >= 200 && $httpCode < 300) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            return true;
        }
    }
}

if ( ! function_exists('is_file_404')) {
    /**
     * Return true if remote file is 404
     *
     * @param $url
     * @return bool
     */
    function is_file_404($url)
    {
        try {
            $handle = curl_init($url);
            curl_setopt($handle,  CURLOPT_NOBODY, TRUE);

            curl_exec($handle);

            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);

            if ($httpCode >= 200 && $httpCode < 300) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            return true;
        }
    }
}

if ( ! function_exists('get_db_table_size')) {
    /**
     * Return table size in bytes
     *
     * @param $tableName
     * @return int|null
     */
    function get_db_table_size($tableName)
    {
        if (is_null($tableName)) return null;

        $table = null;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $table = DB::select('SHOW TABLE STATUS WHERE Name = "' . $tableName . '"');
        } catch (\Exception $exception) {}

        if (empty($table)) return null;

        $size = 0;

        if ($status = collect($table)->first()) {
            $size = $status->Data_length + $status->Index_length;
        }

        return $size;
    }
}

if ( ! function_exists('is_uuid')) {
    /**
     * @param $uuid
     * @return bool
     */
    function is_uuid($uuid)
    {
        return is_string($uuid) && (bool) preg_match('/^[a-f0-9]{8,8}-(?:[a-f0-9]{4,4}-){3,3}[a-f0-9]{12,12}$/i', $uuid);
    }
}
