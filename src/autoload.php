<?php
spl_autoload_register(function ($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = [
            'Xicrow\\PhpSimpleDb\\Connection\\Adapter\\MySQL'                     => '/Connection/Adapter/MySQL.php',
            'Xicrow\\PhpSimpleDb\\Connection\\Exception\\UnknownAdapterException' => '/Connection/Exception/UnknownAdapterException.php',
            'Xicrow\\PhpSimpleDb\\Connection\\Exception\\UnknownAliasException'   => '/Connection/Exception/UnknownAliasException.php',
            'Xicrow\\PhpSimpleDb\\Connection\\ConnectionBase'                     => '/Connection/ConnectionBase.php',
            'Xicrow\\PhpSimpleDb\\Connection\\ConnectionInterface'                => '/Connection/ConnectionInterface.php',
            'Xicrow\\PhpSimpleDb\\Connection\\Manager'                            => '/Connection/Manager.php',

            'Xicrow\\PhpSimpleDb\\QueryBuilder\\Adapter\\MySQL'                     => '/QueryBuilder/Adapter/MySQL.php',
            'Xicrow\\PhpSimpleDb\\QueryBuilder\\Exception\\UnknownAdapterException' => '/QueryBuilder/Exception/UnknownAdapterException.php',
            'Xicrow\\PhpSimpleDb\\QueryBuilder\\Exception\\UnknownAliasException'   => '/QueryBuilder/Exception/UnknownAliasException.php',
            'Xicrow\\PhpSimpleDb\\QueryBuilder\\QueryBuilderBase'                   => '/QueryBuilder/QueryBuilderBase.php',
            'Xicrow\\PhpSimpleDb\\QueryBuilder\\QueryBuilderInterface'              => '/QueryBuilder/QueryBuilderInterface.php',
            'Xicrow\\PhpSimpleDb\\QueryBuilder\\Manager'                            => '/QueryBuilder/Manager.php',
        ];
    }
    if (isset($classes[$class])) {
        require __DIR__ . $classes[$class];
    }
}, true, false);
