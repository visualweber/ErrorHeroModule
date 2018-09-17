<?php

namespace ErrorHeroModule;

use RuntimeException;

class HeroAutoload
{
    public static function handle($class)
    {
        if (\class_exists($class, false)) {
            return;
        }

        $debugBacktrace = \debug_backtrace();
        if (
            isset($debugBacktrace[1]['function'], $debugBacktrace[2]['function']) &&
            $debugBacktrace[1]['function'] === 'spl_autoload_call' &&
            (
                $debugBacktrace[2]['function'] === 'class_exists' || $debugBacktrace[2]['function'] === 'interface_exists'
            )
        ) {
            return;
        }

        throw new RuntimeException(sprintf(
            'class %s not found',
            $class
        ));
    }
}