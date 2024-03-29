<?php
// +----------------------------------------------------------------------
// | LovelyPHP [ DO AND BECOME LOVELY ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024 https://lovelyphp.mojy.xyz All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MoeCinnamo <abcd2890000456@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

//----------------------------
// LovelyPHP helper function
//----------------------------

use lovely\App;
use lovely\Container;
//use lovely\exception\HttpException;
//use lovely\exception\HttpResponseException;
//use lovely\facade\Cache;
//use lovely\facade\Config;
//use lovely\facade\Cookie;
//use lovely\facade\Env;
//use lovely\facade\Event;
//use lovely\facade\Lang;
//use lovely\facade\Log;
//use lovely\facade\Request;
//use lovely\facade\Route;
//use lovely\facade\Session;
//use lovely\Response;
//use lovely\response\File;
//use lovely\response\Json;
//use lovely\response\Jsonp;
//use lovely\response\Redirect;
//use lovely\response\View;
//use lovely\response\Xml;
//use lovely\route\Url as UrlBuild;
//use lovely\Validate;
use lovely\Collection;
use lovely\helper\Arr;

if (!function_exists('abort')) {
    /**
     * Throw HTTP exception
     * @param integer|Response $code    Status code or Response object instance
     * @param string           $message error message
     * @param array            $header  parameter
     */
    function abort($code, $message = '', $header = [])
    {
        if ($code instanceof Response) {
            throw new HttpResponseException($code);
        } else {
            throw new HttpException($code, $message, null, $header);
        }
    }
}

if (!function_exists('app')) {
    /**
     * 快速获取容器中的实例 支持依赖注入
     * @param string $name        类名或标识 默认获取当前应用实例
     * @param array  $args        参数
     * @param bool   $newInstance 是否每次创建新的实例
     * @return object|App
     */
    function app($name = '', $args = [], $newInstance = false)
    {
        return Container::getInstance()->make($name ?: 'App', $args, $newInstance);
    }
}

if (!function_exists('bind')) {
    /**
     * 绑定一个类到容器
     * @param string|array $abstract 类标识、接口（支持批量绑定）
     * @param mixed        $concrete 要绑定的类、闭包或者实例
     * @return Container
     */
    function bind($abstract, $concrete = null)
    {
        return Container::getInstance()->bind($abstract, $concrete);
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存管理
     * @param string $name    缓存名称
     * @param mixed  $value   缓存值
     * @param mixed  $options 缓存参数
     * @param string $tag     缓存标签
     * @return mixed
     */
    function cache($name = null, $value = '', $options = null, $tag = null)
    {
        if (is_null($name)) {
            return app('cache');
        }

        if ('' === $value) {
            // 获取缓存
            return strpos($name, '?') === 0 ? Cache::has(substr($name, 1)) : Cache::get($name);
        } elseif (is_null($value)) {
            // 删除缓存
            return Cache::delete($name);
        }

        // 缓存数据
        if (is_array($options)) {
            $expire = isset($options['expire']) ? $options['expire'] : null; //修复查询缓存无法设置过期时间
        } else {
            $expire = $options;
        }

        if (is_null($tag)) {
            return Cache::set($name, $value, $expire);
        } else {
            return Cache::tag($tag)->set($name, $value, $expire);
        }
    }
}

if (!function_exists('config')) {
    /**
     * 获取和设置配置参数
     * @param string|array $name  参数名
     * @param mixed        $value 参数值
     * @return mixed
     */
    function config($name = '', $value = null)
    {
        if (is_array($name)) {
            return Config::set($name, $value);
        }

        return strpos($name, '?') === 0 ? Config::has(substr($name, 1)) : Config::get($name, $value);
    }
}

if (!function_exists('cookie')) {
    /**
     * Cookie管理
     * @param string $name   cookie名称
     * @param mixed  $value  cookie值
     * @param mixed  $option 参数
     * @return mixed
     */
    function cookie($name, $value = '', $option = null)
    {
        if (is_null($value)) {
            // 删除
            Cookie::delete($name, $option ?: []);
        } elseif ('' === $value) {
            // 获取
            return strpos($name, '?') === 0 ? Cookie::has(substr($name, 1)) : Cookie::get($name);
        } else {
            // 设置
            return Cookie::set($name, $value, $option);
        }
    }
}

if (!function_exists('download')) {
    /**
     * 获取\lovely\response\Download对象实例
     * @param string $filename 要下载的文件
     * @param string $name     显示文件名
     * @param bool   $content  是否为内容
     * @param int    $expire   有效期（秒）
     * @return \lovely\response\File
     */
    function download($filename, $name = '', $content = false, $expire = 180)
    {
        return Response::create($filename, 'file')->name($name)->isContent($content)->expire($expire);
    }
}

if (!function_exists('dump')) {
    /**
     * 浏览器友好的变量输出
     * @param mixed $vars 要输出的变量
     * @return void
     */
    function dump()
    {
        ob_start();
        call_user_func_array('var_dump', func_get_args());

        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $output . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_SUBSTITUTE);
            }
            $output = '<pre>' . $output . '</pre>';
        }

        echo $output;
    }
}

if (!function_exists('env')) {
    /**
     * 获取环境变量值
     * @access public
     * @param string $name    环境变量名（支持二级 .号分割）
     * @param string $default 默认值
     * @return mixed
     */
    function env($name = null, $default = null)
    {
        return Env::get($name, $default);
    }
}

if (!function_exists('event')) {
    /**
     * 触发事件
     * @param mixed $event 事件名（或者类名）
     * @param mixed $args  参数
     * @return mixed
     */
    function event($event, $args = null)
    {
        return Event::trigger($event, $args);
    }
}

if (!function_exists('halt')) {
    /**
     * 调试变量并且中断输出
     * @param mixed $vars 调试变量或者信息
     */
    function halt()
    {
        call_user_func_array('dump', func_get_args());

        throw new HttpResponseException(Response::create());
    }
}

if (!function_exists('input')) {
    /**
     * 获取输入数据 支持默认值和过滤
     * @param string $key 获取的变量名
     * @param mixed  $default 默认值
     * @param string|array|null $filter 过滤方法
     * @return mixed
     */
    function input($key = '', $default = null, $filter = '')
    {
        if (str_starts_with($key, '?')) {
            $key = substr($key, 1);
            $has = true;
        }

        if ($pos = strpos($key, '.')) {
            // 指定参数来源
            $method = substr($key, 0, $pos);
            if (in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'route', 'param', 'request', 'session', 'cookie', 'server', 'env', 'path', 'file'])) {
                $key = substr($key, $pos + 1);
                if ('server' == $method && is_null($default)) {
                    $default = '';
                }
            } else {
                $method = 'param';
            }
        } else {
            // 默认为自动判断
            $method = 'param';
        }

        return isset($has) ?
            Request::has($key, $method) :
            Request::$method($key, $default, $filter);
    }
}

if (!function_exists('invoke')) {
    /**
     * 调用反射实例化对象或者执行方法 支持依赖注入
     * @param mixed $call 类名或者callable
     * @param array $args 参数
     * @return mixed
     */
    function invoke($call, array $args = [])
    {
        $container = Container::getInstance();

        if (is_callable($call)) {
            return $container->invoke($call, $args);
        }

        return $container->invokeClass($call, $args);
    }
}

if (!function_exists('json')) {
    /**
     * 获取\lovely\response\Json对象实例
     * @param mixed $data    返回的数据
     * @param int   $code    状态码
     * @param array $header  头部
     * @param array $options 参数
     * @return \lovely\response\Json
     */
    function json($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'json', $code)->header($header)->options($options);
    }
}

if (!function_exists('jsonp')) {
    /**
     * 获取\lovely\response\Jsonp对象实例
     * @param mixed $data    返回的数据
     * @param int   $code    状态码
     * @param array $header  头部
     * @param array $options 参数
     * @return \lovely\response\Jsonp
     */
    function jsonp($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'jsonp', $code)->header($header)->options($options);
    }
}

if (!function_exists('lang')) {
    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function lang($name, array $vars = [], $lang = '')
    {
        return Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('parse_name')) {
    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name    字符串
     * @param int    $type    转换类型
     * @param bool   $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    function parse_name($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);

            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
    }
}

if (!function_exists('redirect')) {
    /**
     * 获取\lovely\response\Redirect对象实例
     * @param string $url  重定向地址
     * @param int    $code 状态码
     * @return \lovely\response\Redirect
     */
    function redirect($url = '', $code = 302)
    {
        return Response::create($url, 'redirect', $code);
    }
}

if (!function_exists('request')) {
    /**
     * 获取当前Request对象实例
     * @return Request
     */
    function request()
    {
        return app('request');
    }
}

if (!function_exists('response')) {
    /**
     * 创建普通 Response 对象实例
     * @param mixed      $data   输出数据
     * @param int|string $code   状态码
     * @param array      $header 头信息
     * @param string     $type
     * @return Response
     */
    function response($data = '', $code = 200, $header = [], $type = 'html')
    {
        return Response::create($data, $type, $code)->header($header);
    }
}

if (!function_exists('session')) {
    /**
     * Session管理
     * @param string $name  session名称
     * @param mixed  $value session值
     * @return mixed
     */
    function session($name = '', $value = '')
    {
        if (is_null($name)) {
            // 清除
            Session::clear();
        } elseif ('' === $name) {
            return Session::all();
        } elseif (is_null($value)) {
            // 删除
            Session::delete($name);
        } elseif ('' === $value) {
            // 判断或获取
            return str_starts_with($name, '?') ? Session::has(substr($name, 1)) : Session::get($name);
        } else {
            // 设置
            Session::set($name, $value);
        }
    }
}

if (!function_exists('token')) {
    /**
     * 获取Token令牌
     * @param string $name 令牌名称
     * @param mixed  $type 令牌生成方法
     * @return string
     */
    function token($name = '__token__', $type = 'md5')
    {
        return Request::buildToken($name, $type);
    }
}

if (!function_exists('token_field')) {
    /**
     * 生成令牌隐藏表单
     * @param string $name 令牌名称
     * @param mixed  $type 令牌生成方法
     * @return string
     */
    function token_field($name = '__token__', $type = 'md5')
    {
        $token = Request::buildToken($name, $type);

        return '<input type="hidden" name="' . $name . '" value="' . $token . '" />';
    }
}

if (!function_exists('token_meta')) {
    /**
     * 生成令牌meta
     * @param string $name 令牌名称
     * @param mixed  $type 令牌生成方法
     * @return string
     */
    function token_meta($name = '__token__', $type = 'md5')
    {
        $token = Request::buildToken($name, $type);

        return '<meta name="csrf-token" content="' . $token . '">';
    }
}

if (!function_exists('trace')) {
    /**
     * 记录日志信息
     * @param mixed  $log   log信息 支持字符串和数组
     * @param string $level 日志级别
     * @return array|void
     */
    function trace($log = '[lovely]', $level = 'log')
    {
        if ('[lovely]' === $log) {
            return Log::getLog();
        }

        Log::record($log, $level);
    }
}

if (!function_exists('url')) {
    /**
     * Url生成
     * @param string      $url    路由地址
     * @param array       $vars   变量
     * @param bool|string $suffix 生成的URL后缀
     * @param bool|string $domain 域名
     * @return UrlBuild
     */
    function url($url = '', $vars = [], $suffix = true, $domain = false)
    {
        return Route::buildUrl($url, $vars)->suffix($suffix)->domain($domain);
    }
}

if (!function_exists('validate')) {
    /**
     * 生成验证对象
     * @param string|array $validate      验证器类名或者验证规则数组
     * @param array        $message       错误提示信息
     * @param bool         $batch         是否批量验证
     * @param bool         $failException 是否抛出异常
     * @return Validate
     */
    function validate($validate = '', array $message = [], $batch = false, $failException = true)
    {
        if (is_array($validate) || '' === $validate) {
            $v = new Validate();
            if (is_array($validate)) {
                $v->rule($validate);
            }
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }

            $class = strpos($validate, '\\') ? $validate : app()->parseClass('validate', $validate);

            $v = new $class();

            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        return $v->message($message)->batch($batch)->failException($failException);
    }
}

if (!function_exists('view')) {
    /**
     * 渲染模板输出
     * @param string   $template 模板文件
     * @param array    $vars     模板变量
     * @param int      $code     状态码
     * @param callable $filter   内容过滤
     * @return \lovely\response\View
     */
    function view($template = '', $vars = [], $code = 200, $filter = null)
    {
        return Response::create($template, 'view', $code)->assign($vars)->filter($filter);
    }
}

if (!function_exists('display')) {
    /**
     * 渲染模板输出
     * @param string   $content 渲染内容
     * @param array    $vars    模板变量
     * @param int      $code    状态码
     * @param callable $filter  内容过滤
     * @return \lovely\response\View
     */
    function display($content, $vars = [], $code = 200, $filter = null)
    {
        return Response::create($content, 'view', $code)->isContent(true)->assign($vars)->filter($filter);
    }
}

if (!function_exists('xml')) {
    /**
     * 获取\lovely\response\Xml对象实例
     * @param mixed $data    返回的数据
     * @param int   $code    状态码
     * @param array $header  头部
     * @param array $options 参数
     * @return \lovely\response\Xml
     */
    function xml($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'xml', $code)->header($header)->options($options);
    }
}

if (!function_exists('app_path')) {
    /**
     * 获取当前应用目录
     *
     * @param string $path
     * @return string
     */
    function app_path($path = '')
    {
        return app()->getAppPath() . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
}

if (!function_exists('base_path')) {
    /**
     * 获取应用基础目录
     *
     * @param string $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->getBasePath() . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
}

if (!function_exists('config_path')) {
    /**
     * 获取应用配置目录
     *
     * @param string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->getConfigPath() . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
}

if (!function_exists('assets_path')) {
    /**
     * 获取web资源根目录
     *
     * @param string $path
     * @return string
     */
    function assets_path($path = '')
    {
        return app()->getRootPath() . 'assets' . DIRECTORY_SEPARATOR . ($path ? ltrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : $path);
    }
}

if (!function_exists('runtime_path')) {
    /**
     * 获取应用运行时目录
     *
     * @param string $path
     * @return string
     */
    function runtime_path($path = '')
    {
        return app()->getRuntimePath() . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
}

if (!function_exists('root_path')) {
    /**
     * 获取项目根目录
     *
     * @param string $path
     * @return string
     */
    function root_path($path = '')
    {
        return app()->getRootPath() . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
}

// 助手类函数
if (!function_exists('throw_if')) {
    /**
     * 按条件抛异常
     *
     * @param mixed            $condition
     * @param Throwable|string $exception
     * @return mixed
     *
     * @throws Throwable
     */
    function throw_if($condition, $exception)
    {
        $parameters = array_slice(func_get_args(), 2);

        if ($condition) {
            if (is_string($exception)) {
                $exceptionClass = $exception;
                $exception = new $exceptionClass();
            }

            if (!empty($parameters)) {
                $reflection = new ReflectionMethod($exception, '__construct');
                $exception = $reflection->invokeArgs($exception, $parameters);
            }

            throw $exception;
        }

        return $condition;
    }
}

if (!function_exists('throw_unless')) {
    /**
     * 按条件抛异常
     *
     * @param mixed            $condition
     * @param Throwable|string $exception
     * @return mixed
     * @throws Throwable
     */
    function throw_unless($condition, $exception)
    {
        $parameters = array_slice(func_get_args(), 2);

        if (!$condition) {
            if (is_string($exception)) {
                $exceptionClass = $exception;
                $exception = new $exceptionClass();
            }

            if (!empty($parameters)) {
                $reflection = new ReflectionMethod($exception, '__construct');
                $exception = $reflection->invokeArgs($exception, $parameters);
            }

            throw $exception;
        }

        return $condition;
    }
}

if (!function_exists('tap')) {
    /**
     * 对一个值调用给定的闭包，然后返回该值
     *
     * @param mixed         $value
     * @param callable|null $callback
     * @return mixed
     */
    function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return $value;
        }

        $callback($value);

        return $value;
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param mixed $value
     * @return Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}

if (!function_exists('data_fill')) {
    /**
     * Fill in data where it's missing.
     *
     * @param mixed        $target
     * @param string|array $key
     * @param mixed        $value
     * @return mixed
     */
    function data_fill(&$target, $key, $value)
    {
        return data_set($target, $key, $value, false);
    }
}

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed            $target
     * @param string|array|int $key
     * @param mixed            $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($key))) {
            if ('*' === $segment) {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
                    return value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    function array_dot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, array_dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}

if (!function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param mixed        $target
     * @param string|array $key
     * @param mixed        $value
     * @param bool         $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (!is_array($target) && !($target instanceof ArrayAccess)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (is_array($target) || $target instanceof ArrayAccess) {
            if ($segments) {
                if (!isset($target[$segment])) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target[$segment])) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (!isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }
}

if (!function_exists('trait_uses_recursive')) {
    /**
     * 获取一个trait里所有引用到的trait
     *
     * @param string $trait Trait
     * @return array
     */
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);
        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (!function_exists('class_basename')) {
    /**
     * 获取类名(不包含命名空间)
     *
     * @param mixed $class 类名
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('class_uses_recursive')) {
    /**
     *获取一个类里所有用到的trait，包括父类的
     *
     * @param mixed $class 类名
     * @return array
     */
    function class_uses_recursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];
        $classes = array_merge([$class => $class], class_parents($class));
        foreach ($classes as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}