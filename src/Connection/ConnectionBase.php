<?php
declare(strict_types=1);

namespace Xicrow\PhpSimpleDb\Connection;

use PDO;

abstract class ConnectionBase implements ConnectionInterface
{
	protected array $config = [
		'host'        => null,
		'port'        => null,
		'unix_socket' => null,
		'database'    => null,
		'username'    => null,
		'password'    => null,
		'options'     => [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		],
		'charset'     => null,
	];

	protected PDO|null $pdo = null;

	public function __construct(array $config = [])
	{
		if (!empty($config)) {
			$this->initialize($config);
		}
	}

	public function initialize(array $config): ConnectionInterface
	{
		$this->config = array_merge($this->config, $config);

		return $this;
	}

	public function config(): array
	{
		return $this->config;
	}

	public function pdo(): PDO
	{
		return $this->pdo;
	}
}
