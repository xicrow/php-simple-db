<?php
namespace Xicrow\PhpSimpleDb\Connection;

use PDO;

/**
 * Interface ConnectionInterface
 *
 * @package Xicrow\PhpSimpleDb\Connection
 */
interface ConnectionInterface
{
    /**
     * @param array $config
     *
     * @return mixed
     */
    public function initialize(array $config): ConnectionInterface;

    /**
     * @return array
     */
    public function config(): array;

    /**
     * @return PDO
     */
    public function pdo(): PDO;
}
