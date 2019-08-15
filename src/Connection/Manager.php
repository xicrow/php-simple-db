<?php
namespace Xicrow\PhpSimpleDb\Connection;

use Xicrow\PhpSimpleDb\Connection\Adapter\MySQL;
use Xicrow\PhpSimpleDb\Connection\Exception\UnknownAdapterException;
use Xicrow\PhpSimpleDb\Connection\Exception\UnknownAliasException;

/**
 * Class Manager
 *
 * @package Xicrow\PhpSimpleDb\Connection
 */
class Manager
{
	/**
	 * @var ConnectionInterface[]
	 */
	private $connections = [];

	/**
	 * @param string $alias
	 * @param array  $config
	 *
	 * @return Manager
	 * @throws UnknownAdapterException
	 */
	public function add(string $alias, array $config): Manager
	{
		$config = array_merge([
			'adapter' => null,
		], $config);

		$connection = false;
		switch (strtolower($config['adapter'])) {
			case 'mysql':
				$connection = new MySQL($config);
			break;
		}

		if (!$connection) {
			throw new UnknownAdapterException('The given adapter "' . $config['adapter'] . '" is invalid');
		}

		$this->connections[$alias] = $connection;

		return $this;
	}

	/**
	 * @param string $alias
	 *
	 * @return ConnectionInterface
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
	 * @param string $alias
	 *
	 * @return Manager
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
