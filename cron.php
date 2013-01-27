<?php
/**
 * Cron application init
 * cron.php
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Date: 16.07.12
 */

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', dirname(__FILE__) . '/application');

// Define path to public directory
defined('PUBLIC_PATH')
    || define('PUBLIC_PATH', dirname(__FILE__) . '/public');

// Define short alias for DIRECTORY_SEPARATOR
defined('DS')
    || define('DS', DIRECTORY_SEPARATOR);

// Define short alias for DIRECTORY_SEPARATOR
defined('START_TIMER')
    || define('START_TIMER', microtime(true));

// Ensure library/ is on include_path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            realpath(APPLICATION_PATH . '/../vendor/zendframework/zendframework1/library'), 
            realpath(APPLICATION_PATH . '/../library'),
            get_include_path(),
        )
    )
);

/** Zend_Application */
require_once 'Zend/Application.php';

try {
    $config = APPLICATION_PATH . '/configs/application.yaml';

    require_once 'Zend/Config/Yaml.php';
    require_once 'Core/Config/Yaml.php';

    /**
     * Get ENV
     */
    if (isset($argv[2]) && !empty($argv[2])) {
        define('APPLICATION_ENV', $argv[2]);
    } else {
        define('APPLICATION_ENV', 'development');
    }

    $result = new Core_Config_Yaml($config, APPLICATION_ENV);
    $result = $result->toArray();
    $config = $result;

    // Create application, bootstrap, and run
    $application = new Zend_Application(
        APPLICATION_ENV,
        $config
    );

    /**
     * Run application by CLI
     */
    if (!isset($argv[1])) {
        throw new Core_Exception("You must provide request string as second parameter!");
    }

    $requestComponents = explode('/', $argv[1]);

    $module = (!empty($requestComponents[0])) ? $requestComponents[0] : 'cron';
    $controller = (!empty($requestComponents[1])) ? $requestComponents[1] : 'index';
    $action = (!empty($requestComponents[2])) ? $requestComponents[2] : 'index';

    /*
    * Create simple request
    */
    $request = new Core_Controller_Request_Cli($action, $controller, $module);
    $bootstrap = $application->getBootstrap();
    $frontController = $bootstrap->bootstrap('frontController')
        ->getResource('frontController');

    $frontController->setRequest($request)
        ->setResponse(new Zend_Controller_Response_Cli())
        ->setRouter(new Core_Controller_Router_Cli())
        ->throwExceptions(true);

    /**
     * Emulate Auth
     */
    require_once 'Core/Session/SaveHandler/Memory.php';
    Zend_Session::setSaveHandler(new Core_Session_SaveHandler_Memory());

    Zend_Session::start();
    $systemUser = new StdClass();
    $systemUser->role = 'system';

    $_SESSION['Auth'] = array(
        'storage' => $systemUser
    );

    /**
     * Run app
     */
    $application->bootstrap()
        ->run();

} catch (Exception $exception) {
    echo $exception->getMessage();
}