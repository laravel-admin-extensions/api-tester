<?php

namespace Encore\Admin\ApiTester;

use Encore\Admin\Extension;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;

class ApiTester extends Extension
{
    use BootExtension;

    /**
     * The Illuminate application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var array
     */
    public static $methodColors = [
        'GET'    => 'green',
        'HEAD'   => 'gray',
        'POST'   => 'blue',
        'PUT'    => 'yellow',
        'DELETE' => 'red',
        'PATCH'  => 'aqua',
    ];

    /**
     * ApiTester constructor.
     * @param \Illuminate\Foundation\Application|null $app
     */
    public function __construct(\Illuminate\Foundation\Application $app = null)
    {
        $this->app = $app ?: app();
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param string $userId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function call($method, $uri, $parameters = [], $user = null)
    {
//        ApiLogger::log(...func_get_args());

        if ($user) {
            $this->loginUsing($user);
        }

        $kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');

        $uri = $this->prepareUrlForRequest($uri);

        $files = [];

        foreach ($parameters as $key => $val) {
            if ($val instanceof UploadedFile) {
                $files[$key] = $val;
                unset($parameters[$key]);
            }
        }

        $symfonyRequest = SymfonyRequest::create(
            $uri, $method, $parameters,
            [], $files, ['HTTP_ACCEPT' => 'application/json']
        );

        $request = Request::createFromBase($symfonyRequest);

        try {
            $response = $kernel->handle($request);
        } catch (\Exception $e) {
            $response = app('Illuminate\Contracts\Debug\ExceptionHandler')->render($request, $e);
        }

        $kernel->terminate($request, $response);

        return $response;
    }

    /**
     * Login a user by giving userid.
     *
     * @param $userId
     */
    protected function loginUsing($userId)
    {
        $guard = static::config('guard', 'api');

        if ($method = static::config('user_retriever')) {
            $user = call_user_func($method, $userId);
        } else {
            $user = app('auth')->guard($guard)->getProvider()->retrieveById($userId);
        }

        $this->app['auth']->guard($guard)->setUser($user);
    }

    /**
     * @param Response $response
     * @return array
     */
    public function parseResponse(Response $response)
    {
        $content = $response->getContent();

        $jsoned = json_decode($content);

        if (json_last_error() == JSON_ERROR_NONE) {
            $content = json_encode($jsoned, JSON_PRETTY_PRINT);
        }

        $lang = 'json';

        $contentType = $response->headers->get('content-type');
        if (Str::contains($contentType, 'html')) {
            $lang = 'html';
        }

        return [
            'headers'    => json_encode($response->headers->all(), JSON_PRETTY_PRINT),
            'cookies'    => json_encode($response->headers->getCookies(), JSON_PRETTY_PRINT),
            'content'    => $content,
            'language'   => $lang,
            'status'     => [
                'code'  => $response->getStatusCode(),
                'text'  => $this->getStatusText($response),
            ],
        ];
    }

    /**
     * @param Response $response
     * @return string
     */
    protected function getStatusText(Response $response)
    {
        $statusText = new \ReflectionProperty($response, 'statusText');

        $statusText->setAccessible(true);

        return $statusText->getValue($response);
    }

    /**
     * Filter the given array of files, removing any empty values.
     *
     * @param  array  $files
     * @return mixed
     */
    protected function filterFiles($files)
    {
        foreach ($files as $key => $file) {
            if ($file instanceof UploadedFile) {
                continue;
            }

            if (is_array($file)) {
                if (! isset($file['name'])) {
                    $files[$key] = $this->filterFiles($files[$key]);
                } elseif (isset($files[$key]['error']) && $files[$key]['error'] !== 0) {
                    unset($files[$key]);
                }

                continue;
            }

            unset($files[$key]);
        }

        return $files;
    }

    /**
     * Turn the given URI into a fully qualified URL.
     *
     * @param  string  $uri
     * @return string
     */
    protected function prepareUrlForRequest($uri)
    {
        if (Str::startsWith($uri, '/')) {
            $uri = substr($uri, 1);
        }

        if (! Str::startsWith($uri, 'http')) {
            $uri = config('app.url').'/'.$uri;
        }

        return trim($uri, '/');
    }

    /**
     * Get all api routes.
     *
     * @return array
     */
    public function getRoutes()
    {
        $routes = app('router')->getRoutes();

        $prefix = static::config('prefix');

        $routes = collect($routes)->filter(function ($route) use ($prefix) {

            return Str::startsWith($route->uri, static::config('prefix'));

        })->map(function ($route) {
            return $this->getRouteInformation($route);
        })->all();

        if ($sort = request('_sort')) {
            $routes = $this->sortRoutes($sort, $routes);
        }

        $routes = collect($routes)->filter()->map(function ($route) {
            $route['parameters'] = json_encode($this->getRouteParameters($route['action']));

            unset($route['middleware'], $route['host'], $route['name'], $route['action']);

            return $route;
        })->toArray();

        return array_filter($routes);
    }

    /**
     * Get parameters info of route.
     *
     * @param $action
     * @return array
     */
    protected function getRouteParameters($action)
    {
        if (is_callable($action) || $action === 'Closure') {
            return [];
        }

        if (is_string($action) && ! Str::contains($action, '@')) {
            list($class, $method) = static::makeInvokable($action);
        } else {
            list($class, $method) = explode('@', $action);
        }
        
        $classReflector = new \ReflectionClass($class);

        $comment = $classReflector->getMethod($method)->getDocComment();

        if ($comment) {
            $parameters = [];
            preg_match_all('/\@SWG\\\Parameter\(\n(.*?)\)\n/s', $comment, $matches);
            foreach (array_get($matches, 1, []) as $item) {
                preg_match_all('/(\w+)=[\'"]?([^\r\n"]+)[\'"]?,?\n/s', $item, $match);
                if (count($match) == 3) {

                    $match[2] = array_map(function ($val) {
                        return trim($val, ',');
                    }, $match[2]);

                    $parameters[] = array_combine($match[1], $match[2]);
                }
            }

            return $parameters;
        }

        return [];
    }

    /**
     * @param $action
     * @return array
     */
    protected static function makeInvokable($action)
    {
        if (! method_exists($action, '__invoke')) {
            throw new \UnexpectedValueException("Invalid route action: [{$action}].");
        }

        return [$action, '__invoke'];
    }

    /**
     * Get the route information for a given route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        return [
            'host'       => $route->domain(),
            'method'     => $route->methods()[0],
            'uri'        => $route->uri(),
            'name'       => $route->getName(),
            'action'     => $route->getActionName(),
            'middleware' => $this->getRouteMiddleware($route),
        ];
    }

    /**
     * Sort the routes by a given element.
     *
     * @param  string  $sort
     * @param  array  $routes
     * @return array
     */
    protected function sortRoutes($sort, $routes)
    {
        return Arr::sort($routes, function ($route) use ($sort) {
            return $route[$sort];
        });
    }

    /**
     * Get before filters.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return string
     */
    protected function getRouteMiddleware($route)
    {
        return collect($route->gatherMiddleware())->map(function ($middleware) {
            return $middleware instanceof \Closure ? 'Closure' : $middleware;
        });
    }
}