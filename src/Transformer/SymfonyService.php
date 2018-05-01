<?php

declare(strict_types=1);

namespace ErrorHeroModule\Transformer;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Zend\Db\Adapter\Adapter;
use Zend\Log\Logger;
use Zend\Log\WriterPluginManager;
use Zend\ServiceManager\ServiceManager;

class SymfonyService implements TransformerInterface
{
    public static function transform(ContainerInterface $container, array $configuration) : ContainerInterface
    {
        if ($container instanceof SymfonyContainerBuilder) {
            $adapterName     = Adapter::class;
            $writers         = $configuration['log']['ErrorHeroModuleLogger']['writers'];
            $config          = $configuration['db'];
            $serviceManager  = new ServiceManager();

            foreach ($writers as $key => $writer) {
                if ($writer['name'] === 'db') {
                    $adapterName = $writer['options']['db'];
                    break;
                }
            }

            if (isset($config['adapters'])) {
                foreach ($config['adapters'] as $key => $adapterConfig) {
                    if ($adapterName === $key) {
                        $config = $adapterConfig;
                        break;
                    }
                }
            }
            $serviceManager->setService($adapterName, new Adapter($config));

            foreach ($writers as $key => $writer) {
                if ($writer['name'] === 'db') {
                    $writers[$key]['options']['db'] = $serviceManager->get($adapterName);
                    break;
                }
            }

            $writerPluginManager = new WriterPluginManager($serviceManager);

            $logger = new Logger([
                'writer_plugin_manager' => $writerPluginManager,
                'writers'               => $writers
            ]);
            $container->set('ErrorHeroModuleLogger', $logger);
            unset($serviceManager);
        }

        return $container;
    }
}