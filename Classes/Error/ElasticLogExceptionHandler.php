<?php

namespace Noerdisch\ElasticLog\Error;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Error\ProductionExceptionHandler;
use Neos\Flow\ObjectManagement\DependencyInjection\DependencyProxy;
use Noerdisch\ElasticLog\Service\ElasticSearchService;


/**
 * Production Exception handler that reports exceptions to a elastic search server.
 *
 * @package Noerdisch\ElasticLog\Error
 */
class ElasticLogExceptionHandler extends ProductionExceptionHandler
{

    /**
     * @Flow\Inject
     * @var ElasticSearchService
     */
    protected $elasticSearchService;

    /**
     * @param \Exception|\Throwable $exception
     * @return void
     */
    protected function echoExceptionWeb($exception)
    {
        if (!empty($this->renderingOptions['logException'])) {
            $this->elasticSearchService()->logException($exception);
        }

        parent::echoExceptionWeb($exception);
    }

    /**
     * @param \Exception|\Throwable $exception The exception
     * @return void
     */
    protected function echoExceptionCli($exception)
    {
        if (isset($this->renderingOptions['logException']) && $this->renderingOptions['logException']) {
            $this->getGraylogService()->logException($exception);
        }

        parent::echoExceptionCli($exception);
    }

    /**
     * Returns an instance of the injected GraylogService (including a fallback to a manually instantiated instance
     * if Dependency Injection is not (yet) available)
     *
     * @return GraylogService
     */
    private function getGraylogService()
    {
        if ($this->graylogService instanceof GraylogService) {
            return $this->graylogService;
        } elseif ($this->graylogService instanceof DependencyProxy) {
            return $this->graylogService->_activateDependency();
        } else {
            return new GraylogService();
        }
    }
}
