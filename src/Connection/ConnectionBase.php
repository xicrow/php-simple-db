<?php
namespace Xicrow\PhpSimpleDb\Connection;

use PDO;

/**
 * Class ConnectionBase
 *
 * @package Xicrow\PhpSimpleDb\Connection
 */
abstract class ConnectionBase implements ConnectionInterface
{
	/**
	 * @var array
	 */
	protected $config = [
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

	/**
	 * @var PDO
	 */
	protected $pdo = null;

	/**
	 * @param array $config
	 */
	public function __construct(array $config = [])
	{
		if (!empty($config)) {
			$this->initialize($config);
		}
	}

	/**
	 * @param array $config
	 *
	 * @return ConnectionInterface
	 */
	public function initialize(array $config): ConnectionInterface
	{
		$this->config = array_merge($this->config, $config);

		return $this;
	}

	/**
	 * @return array
	 */
	public function config(): array
	{
		return $this->config;
	}

	/**
	 * @return PDO
	 */
	public function pdo(): PDO
	{
		return $this->pdo;
	}
}
