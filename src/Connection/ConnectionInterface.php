<?php
declare(strict_types=1);

namespace Xicrow\PhpSimpleDb\Connection;

use PDO;

interface ConnectionInterface
{
	public function initialize(array $config): ConnectionInterface;

	public function config(): array;

	public function pdo(): PDO;
}
