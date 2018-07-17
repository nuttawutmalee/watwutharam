<?php
if ( ! function_exists('isset_not_empty')) {
    /**
     * Return a value only if it's not empty
     *
     * @param $value
     * @param null $default
     * @return null
     */
    function isset_not_empty(&$value, $default = null)
    {
        try {
            return isset($value) && !empty($value) ? $value : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if ( ! function_exists('remove_leading_slashes')) {
    /**
     * Return removed-leading-slashes string
     *
     * @param $string
     * @param $replace
     * @return mixed
     */
    function remove_leading_slashes($string, $replace = '')
    {
        $expression = '/^(\/|\\\)+/';
        if (!preg_match($expression, $string)) return $replace . $string;
        return preg_replace($expression, $replace, $string);
    }
}

if ( ! function_exists('remove_trailing_slashes')) {
    /**
     * Return removed-trailing-slashes string
     *
     * @param $string
     * @param string $replace
     * @return mixed
     */
    function remove_trailing_slashes($string, $replace = '')
    {
        $expression = '/(\/|\\\)+$/';
        if (!preg_match($expression, $string)) return $string . $replace;
        return preg_replace($expression, $replace, $string);
    }
}

if ( ! function_exists('json_diff_inner')) {
    /**
     * Return a difference between two json objects
     *
     * @param $a
     * @param $b
     * @return array
     */
    function json_diff_inner($a, $b) {
        $patch = array();
        if (is_object($a)) {
            if (!is_object($b)) {
                $patch[] = (object)array(
                    "op" => "replace",
                    "path" => "",
                    "value" => $b,
                    "oldValue" => $a
                );
                return $patch;
            }
            $totalKeys = 0;
            $addedKeys = 0;
            $deletedKeys = 0;
            $changedKeys = 0;
            $subChanges = array();
            foreach ($b as $key => $value) {
                $totalKeys++;
                $path = "/".str_replace("/", "~1", str_replace("~", "~0", $key));
                if (!property_exists($a, $key)) {
                    $addedKeys++;
                    $subChanges[] = (object)array(
                        "op" => "add",
                        "path" => $path,
                        "value" => $value
                    );
                } else {
                    $subPatch = json_diff_inner($a->$key, $value);
                    if (count($subPatch)) {
                        $changedKeys++;
                    }
                    foreach ($subPatch as $change) {
                        $change->path = $path.$change->path;
                        $subChanges[] = $change;
                    }
                }
            }
            foreach ($a as $key => $value) {
                $totalKeys++;
                $path = "/".str_replace("/", "~1", str_replace("~", "~0", $key));
                if (!property_exists($b, $key)) {
                    $deletedKeys++;
                    $subChanges[] = (object)array(
                        "op" => "remove",
                        "path" => $path,
                        "oldValue" => $value
                    );
                }
            }
            if ($addedKeys + $deletedKeys + $changedKeys > $totalKeys*0.5) {
                $patch[] = (object)array(
                    "op" => "replace",
                    "path" => "",
                    "value" => $b,
                    "oldValue" => $a
                );
            } else {
                $patch = array_merge($patch, $subChanges);
            }
        } elseif (is_array($a)) {
            if (!is_array($b)) {
                $patch[] = (object)array(
                    "op" => "replace",
                    "path" => "",
                    "value" => $b,
                    "oldValue" => $a
                );
                return $patch;
            }
            $arrayChanges = array();
            $indexA = 0;
            $copyA = $a;
            while (TRUE) {
                $matchA = $matchB = NULL;
                $distanceA = $distanceB = 0;
                while ((isset($distanceA) || isset($distanceB)) && !isset($matchA)) {
                    if (isset($distanceA)) {
                        if ($indexA + $distanceA < count($copyA)) {
                            $matchIndex = $indexA;
                            while ($matchIndex <= $indexA + $distanceA) {
                                if ($b[$matchIndex] == $copyA[$indexA + $distanceA]) {
                                    $matchA = $indexA + $distanceA;
                                    $matchB = $matchIndex;
                                    break;
                                }
                                $matchIndex++;
                            }
                            $distanceA++;
                        } else {
                            $distanceA = NULL;
                        }
                    }
                    if (isset($distanceB)) {
                        if ($indexA + $distanceB < count($b)) {
                            $matchIndex = $indexA;
                            while ($matchIndex <= $indexA + $distanceB) {
                                if ($copyA[$matchIndex] == $b[$indexA + $distanceB]) {
                                    $matchA = $matchIndex;
                                    $matchB = $indexA + $distanceB;
                                    break;
                                }
                                $matchIndex++;
                            }
                            $distanceB++;
                        } else {
                            $distanceB = NULL;
                        }
                    }
                }
                if (!isset($matchA)) {
                    $matchA = count($copyA);
                    $matchB = count($b);
                }
                for ($index = $indexA; $index < $matchA && $index < $matchB; $index++) {
                    $arrayChanges[] = (object)array(
                        "op" => "replace",
                        "path" => "/$index",
                        "value" => $b[$index],
                        "oldValue" => $a[$index]
                    );
                }
                for ($index = $matchA; $index < $matchB; $index++) {
                    $arrayChanges[] = (object)array(
                        "op" => "add",
                        "path" => "/$index",
                        "value" => $b[$index]
                    );
                }
                for ($index = $matchB; $index < $matchA; $index++) {
                    $arrayChanges[] = (object)array(
                        "op" => "remove",
                        "path" => "/$index",
                        "oldValue" => $copyA[$index]
                    );
                }
                if ($matchB > 0) {
                    $copyA = array_merge(array_fill(0, $matchB, NULL), array_slice($copyA, $matchA));
                } else {
                    $copyA = array_slice($copyA, $matchA);
                }
                $indexA = $matchB + 1;
                if ($matchA >= count($copyA) && $matchB >= count($b)) {
                    break;
                }
            }
            if (count($arrayChanges) > (count($a) + count($b))/4) {
                $patch[] = (object)array(
                    "op" => "replace",
                    "path" => "",
                    "value" => $b,
                    "oldValue" => $a
                );
            } else {
                foreach ($arrayChanges as $change) {
                    if ($change->op == "replace") {
                        $subPatch = json_diff_inner($change->oldValue, $change->value);
                        foreach ($subPatch as $subChange) {
                            $subChange->path = $change->path.$subChange->path;
                            $patch[] = $subChange;
                        }
                    } else {
                        $patch[] = $change;
                    }
                }
            }
        } else {
            if ($a != $b) {
                $patch[] = (object)array(
                    "op" => "replace",
                    "path" => "",
                    "value" => $b,
                    "oldValue" => $a
                );
            }
        }
        return $patch;
    }
}

if ( ! function_exists('json_diff')) {
    /**
     * Return a difference between two json objects
     *
     * @param $a
     * @param $b
     * @return array
     */
    function json_diff($a, $b) {
        $patch = json_diff_inner($a, $b);
        return $patch;
    }
}

if ( ! function_exists('datelo')) {
    /**
     * Return date string according to locale
     *
     * @param $str
     * @param null $time
     * @param null $locale
     * @return false|string
     */
    function datelo($str, $time = null, $locale = null){

        if($time === null) $time = time();

        if (preg_match("/[DlFM]/", $str) && ! preg_match("/[nz]/",  $str)){
            if ( ! is_null($locale)) {
                setlocale(LC_TIME, $locale);
            }

            $dict = array();
            $dict['d'] = '%d';
            $dict['D'] = '%a';
            $dict['j'] = '%e';
            $dict['l'] = '%A';
            $dict['N'] = '%u';
            $dict['w'] = '%w';
            $dict['F'] = '%B';
            $dict['m'] = '%m';
            $dict['M'] = '%b';
            $dict['Y'] = '%G';
            $dict['g'] = '%l';
            $dict['G'] = '%k';
            $dict['h'] = '%I';
            $dict['H'] = '%H';
            $dict['i'] = '%M';
            $dict['s'] = '%S';
            $dict['S'] = ' '; //removes English sufixes th rd etc.
            $dict[' '] = ' ';
            $dict['-'] = '-';
            $dict['/'] = '/';
            $dict[':'] = ':';
            $dict[','] = ',';

            $chars = preg_split("//", $str);
            $nstr = '';

            foreach ($chars as $c){
                if ($c){ //skip empties
                    $nstr .= $dict[$c];
                }
            }

            $str = strftime($nstr, $time);

            return iconv('iso-8859-1', mb_detect_encoding($str), $str);

        }else{ // not localized

            return date($str, $time);

        }
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

if ( ! function_exists('url_exists')) {
    /**
     * Return true if url exists
     *
     * @param null $url
     * @return bool
     */
    function url_exists($url = null)
    {
        if (is_null($url)) return false;
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $code >= 200 && $code < 300;
        } catch (\Exception $exception) { return false; }
    }
}

if (  ! function_exists('file_path_encoded')) {
    /**
     * Return file path encoded string
     *
     * @param $string
     * @return mixed
     */
    function file_path_encoded($string)
    {
        return preg_replace_callback('#://([^/]+)/([^?]+)#', function ($match) {
            return '://' . $match[1] . '/' . join('/', array_map('rawurlencode', explode('/', $match[2])));
        }, $string);
    }
}