<?php
namespace Xicrow\PhpSimpleDb\QueryBuilder;

/**
 * Class QueryBuilderBase
 *
 * @package Xicrow\PhpSimpleDb\QueryBuilder
 */
abstract class QueryBuilderBase implements QueryBuilderInterface
{
	/**
	 * @var array
	 */
	protected $parts = [
		'select' => [],
		'from'   => [],
		'join'   => [],
		'where'  => [],
		'group'  => [],
		'having' => [],
		'order'  => [],
		'offset' => null,
		'limit'  => null,
	];

	/**
	 * @var string
	 */
	protected $sql = '';

	/**
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * @inheritdoc
	 */
	public function __construct(array $parts = [])
	{
		$this->parts = array_merge($this->parts, $parts);
	}

	/**
	 * @inheritdoc
	 */
	public function getSql(): string
	{
		return $this->sql;
	}

	/**
	 * @inheritdoc
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @inheritdoc
	 */
	public function select(array $selects)
	{
		$this->parts['select'] = array_merge($this->parts['select'], $selects);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function from(array $froms)
	{
		$this->parts['from'] = array_merge($this->parts['from'], $froms);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function join(array $joins)
	{
		$this->parts['join'] = array_merge($this->parts['join'], $joins);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function where(array $wheres)
	{
		$this->parts['where'] = array_merge($this->parts['where'], $wheres);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function group(array $groups)
	{
		$this->parts['group'] = array_merge($this->parts['group'], $groups);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function order(array $orders)
	{
		$this->parts['order'] = array_merge($this->parts['order'], $orders);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function offset(int $offset)
	{
		$this->parts['offset'] = $offset;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function limit(int $limit)
	{
		$this->parts['limit'] = $limit;

		return $this;
	}
}
