<?php
namespace Xicrow\PhpSimpleDb\QueryBuilder;

use Xicrow\PhpSimpleDb\QueryBuilder\Adapter\MySQL;
use Xicrow\PhpSimpleDb\QueryBuilder\Exception\UnknownAdapterException;
use Xicrow\PhpSimpleDb\QueryBuilder\Exception\UnknownAliasException;

/**
 * Class Manager
 *
 * @package Xicrow\PhpSimpleDb\QueryBuilder
 */
class Manager
{
	/**
	 * @var QueryBuilderInterface[]
	 */
	private $queryBuilders = [];

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

		$queryBuilder = false;
		switch (strtolower($config['adapter'])) {
			case 'mysql':
				$queryBuilder = new MySQL();
			break;
		}

		if (!$queryBuilder) {
			throw new UnknownAdapterException('The given adapter "' . $config['adapter'] . '" is invalid');
		}

		$this->queryBuilders[$alias] = $queryBuilder;

		return $this;
	}

	/**
	 * @param string $alias
	 *
	 * @return QueryBuilderInterface
	 * @throws UnknownAliasException
	 */
	public function get(string $alias): QueryBuilderInterface
	{
		if (!array_key_exists($alias, $this->queryBuilders)) {
			throw new UnknownAliasException('No query builder found with alias "' . $alias . '"');
		}

		return $this->queryBuilders[$alias];
	}

	/**
	 * @param string $alias
	 *
	 * @return Manager
	 * @throws UnknownAliasException
	 */
	public function remove(string $alias): Manager
	{
		if (!array_key_exists($alias, $this->queryBuilders)) {
			throw new UnknownAliasException('No query builder found with alias "' . $alias . '"');
		}

		unset($this->queryBuilders[$alias]);

		return $this;
	}
}
