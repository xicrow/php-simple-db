<?php
declare(strict_types=1);

namespace Xicrow\PhpSimpleDb\QueryBuilder;

use Xicrow\PhpSimpleDb\QueryBuilder\Adapter\MySQL;
use Xicrow\PhpSimpleDb\QueryBuilder\Exception\UnknownAdapterException;
use Xicrow\PhpSimpleDb\QueryBuilder\Exception\UnknownAliasException;

class Manager
{
	/** @phpstan-var QueryBuilderInterface[] */
	private array $queryBuilders = [];

	/**
	 * @throws UnknownAdapterException
	 */
	public function add(string $alias, array $config): Manager
	{
		$config = array_merge([
			'adapter' => null,
		], $config);

		$queryBuilder = false;
		if (strtolower($config['adapter']) == 'mysql') {
			$queryBuilder = new MySQL();
		}

		if (!$queryBuilder) {
			throw new UnknownAdapterException('The given adapter "' . $config['adapter'] . '" is invalid');
		}

		$this->queryBuilders[$alias] = $queryBuilder;

		return $this;
	}

	/**
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
