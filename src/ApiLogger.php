<?php

namespace Encore\Admin\ApiTester;

use Illuminate\Support\Facades\File;

/**
 * Class ApiLogger
 * @package Encore\Admin\ApiTester
 *
 * @deprecated
 */
class ApiLogger
{
    protected static $path = 'api-tester/api-tester.json';

    public static function log($method, $uri, $parameters = [], $user = null)
    {
        $parameters = get_defined_vars();

        $logPath = storage_path(static::$path);

        if (!file_exists($logPath)) {
            File::makeDirectory(dirname($logPath));
        }

        File::append($logPath, json_encode($parameters).',');
    }

    public static function load()
    {
        $logPath = storage_path(static::$path);

        $data = File::get($logPath);

        $json = '['.trim($data, ','). ']';

        $history = array_reverse(json_decode($json, true));

        foreach ($history as &$item) {
            $item['parameters'] = static::formatParameters($item['parameters']);
        }

        return $history;
    }

    public static function formatParameters($parameters = [])
    {
        if (empty($parameters)) {
            return '[]';
        }

        $retval = [];

        foreach ($parameters as $name => $value) {
            $retval[] = [
                'name' => $name,
                'defaultValue' => $value,
            ];
        }

        return json_encode($retval);
    }
}