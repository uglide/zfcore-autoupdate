<?php
/**
 * Core_Controller_Router_Cli.php
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Date: 16.07.12
 */
class Core_Controller_Router_Cli extends Zend_Controller_Router_Rewrite
{
    /**
     * Find a matching route to the current PATH_INFO and inject
     * returning values to the Request object.
     * @param Zend_Controller_Request_Abstract $request
     * @return Zend_Controller_Request_Abstract
     * @throws Zend_Controller_Router_Exception
     */
    public function route(Zend_Controller_Request_Abstract $request)
    {
        if ($this->_useDefaultRoutes) {
            $this->addDefaultRoutes();
        }

        // Find the matching route
        $routeMatched = false;

        foreach (array_reverse($this->_routes, true) as $name => $route) {
            if (method_exists($route, 'isAbstract') && $route->isAbstract()) {
                continue;
            }

            if (!method_exists($route, 'getVersion') || $route->getVersion() == 1) {
                $match = $request->getPathInfo();
            } else {
                $match = $request;
            }

            if ($params = $route->match($match)) {
                $this->_setRequestParams($request, $params);
                $this->_currentRoute = $name;
                $routeMatched        = true;
                break;
            }
        }

        if (!$routeMatched) {
            require_once 'Zend/Controller/Router/Exception.php';
            throw new Zend_Controller_Router_Exception('No route matched the request', 404);
        }

        if($this->_useCurrentParamsAsGlobal) {
            $params = $request->getParams();
            foreach($params as $param => $value) {
                $this->setGlobalParam($param, $value);
            }
        }

        return $request;
    }
}

