<?php

namespace Encore\Admin\ApiTester;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;

class ApiTesterController extends Controller
{
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Api tester');

            $tester = new ApiTester();

            $content->body(view('api-tester::index', [
                'routes'    => $tester->getRoutes(),
                // 'logs'    => ApiLogger::load(),
            ]));
        });
    }

    public function handle(Request $request)
    {
        $method = $request->get('method');
        $uri = $request->get('uri');
        $user = $request->get('user');
        $all = $request->all();

        $keys = Arr::get($all, 'key', []);
        $vals = Arr::get($all, 'val', []);

        ksort($keys);
        ksort($vals);

        $parameters = [];

        foreach ($keys as $index => $key) {
            $parameters[$key] = Arr::get($vals, $index);
        }

        $parameters = array_filter($parameters, function ($key) {
            return $key !== '';
        }, ARRAY_FILTER_USE_KEY);

        $tester = new ApiTester();

        $response = $tester->call($method, $uri, $parameters, $user);

        return [
            'status'    => true,
            'message'   => 'success',
            'data'      => $tester->parseResponse($response),
        ];
    }
}
