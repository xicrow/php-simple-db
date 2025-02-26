<?php
declare(strict_types=1);

namespace Xicrow\PhpSimpleDb\Connection;

use Xicrow\PhpSimpleDb\Connection\Adapter\MySQL;
use Xicrow\PhpSimpleDb\Connection\Exception\UnknownAdapterException;
use Xicrow\PhpSimpleDb\Connection\Exception\UnknownAliasException;

class Manager
{
	/** @phpstan-var ConnectionInterface[] */
	private array $connections = [];

	/**
	 * @throws UnknownAdapterException
	 */
	public function add(string $alias, array $config): Manager
	{
		$config = array_merge([
			'adapter' => null,
		], $config);

		$connection = false;
		if (strtolower($config['adapter']) == 'mysql') {
			$connection = new MySQL($config);
		}

		if (!$connection) {
			throw new UnknownAdapterException('The given adapter "' . $config['adapter'] . '" is invalid');
		}

		$this->connections[$alias] = $connection;

		return $this;
	}

	/**
	 * @throws UnknownAliasException
	 */
	public function get(string $alias): ConnectionInterface
	{
		if (!array_key_exists($alias, $this->connections)) {
			throw new UnknownAliasException('No connection found with alias "' . $alias . '"');
		}

		return $this->connections[$alias];
	}

	/**
	 * @throws UnknownAliasException
	 */
	public function remove(string $alias): Manager
	{
		if (!array_key_exists($alias, $this->connections)) {
			throw new UnknownAliasException('No connection found with alias "' . $alias . '"');
		}

		unset($this->connections[$alias]);

		return $this;
	}
}
