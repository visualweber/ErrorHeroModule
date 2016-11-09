<?php

namespace ErrorHeroModule\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\Mvc\MvcEvent;

class Mvc extends AbstractListenerAggregate
{
    /**
     * @var array
     */
    private $errorHeroModuleConfig;

    private $errorType = [
        E_ERROR,
        E_WARNING,
        E_PARSE,
        E_NOTICE,
        E_CORE_ERROR,
        E_CORE_WARNING,
        E_COMPILE_ERROR,
        E_CORE_WARNING,
        E_USER_ERROR,
        E_USER_WARNING,
        E_USER_NOTICE,
        E_STRICT,
        E_RECOVERABLE_ERROR,
        E_DEPRECATED,
        E_USER_DEPRECATED,
    ];

    /**
     * @param array $errorHeroModuleConfig
     */
    public function __construct(array $errorHeroModuleConfig)
    {
        $this->errorHeroModuleConfig = $errorHeroModuleConfig;
    }

    /**
     * @param  EventManagerInterface $events
     * @param  int                   $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        // exceptions
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, [$this, 'renderError']);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'dispatchError'], 100);

        // php errors
        $this->listeners[] = $events->attach('*', [$this, 'phpError']);
    }

    private function handleException($e)
    {

    }

    public function renderError($e)
    {
        $this->handleException($e);
    }

    public function dispatchError($e)
    {
        $this->handleException($e);
    }

    public function phpError($e)
    {
        if ($this->errorHeroModuleConfig['options']['display_errors'] === 0) {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors',0);
        }

        register_shutdown_function([$this, 'execOnShutdown']);
        set_error_handler([$this, 'phpErrorHandler']);
    }

    public function execOnShutdown()
    {
        $error = error_get_last();
        if ($error && ($error['type'] & E_FATAL)) {
            $this->phpErrorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    public function phpErrorHandler()
    {

    }
}