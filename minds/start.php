<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/19
 * Time: 下午8:59
 */

namespace minds;

use minds\Env;

// 核心常量
define('MINDS_VERSION', '1.0.0');
define('MINDS_START_TIME', microtime(true));
define('MINDS_START_MEM', memory_get_usage());
define('EXT', '.php');
define('DS', DIRECTORY_SEPARATOR);

// 路径常量
defined('MINDS_PATH') or define('MINDS_PATH', __DIR__ . DS);
define('LIB_PATH', MINDS_PATH . 'library' . DS);
define('CORE_PATH', LIB_PATH . 'minds' . DS);
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);
defined('VENDOR_PATH') or define('VENDOR_PATH', ROOT_PATH . 'vendor' . DS);
defined('EXTEND_PATH') or define('EXTEND_PATH', ROOT_PATH . 'extend' . DS);
defined('RUNTIME_PATH') or define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DS);
defined('LOG_PATH') or define('LOG_PATH', RUNTIME_PATH . 'log' . DS);
defined('CACHE_PATH') or define('CACHE_PATH', RUNTIME_PATH . 'cache' . DS);
defined('TEMP_PATH') or define('TEMP_PATH', RUNTIME_PATH . 'temp' . DS);
defined('CONF_PATH') or define('CONF_PATH', APP_PATH); // 配置文件目录
defined('CONF_EXT') or define('CONF_EXT', EXT); // 配置文件后缀
defined('ENV_PREFIX') or define('ENV_PREFIX', 'PHP_'); // 环境变量的配置前缀

// 环境常量
define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);

// 加载 Loader
require(CORE_PATH . 'Loader' . EXT);

// 注册自动加载
\minds\Loader::register();

// 加载环境变量配置文件
\minds\Env::load(ROOT_PATH . '.env');

// 注册错误和异常处理机制
\minds\Error::register();

// 加载通用配置文件
\minds\Config::set(include(MINDS_PATH . 'configuration' . EXT));

// 运行应用程序
App::run();