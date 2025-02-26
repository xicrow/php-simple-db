<?php
declare(strict_types=1);

namespace Xicrow\PhpSimpleDb\QueryBuilder;

abstract class QueryBuilderBase implements QueryBuilderInterface
{
	protected array  $parts      = [
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
	protected string $sql        = '';
	protected array  $parameters = [];

	public function __construct(array $parts = [])
	{
		$this->parts = array_merge($this->parts, $parts);
	}

	public function getSql(): string
	{
		return $this->sql;
	}

	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function select(array $selects): static
	{
		$this->parts['select'] = array_merge($this->parts['select'], $selects);

		return $this;
	}

	public function from(array $froms): static
	{
		$this->parts['from'] = array_merge($this->parts['from'], $froms);

		return $this;
	}

	public function join(array $joins): static
	{
		$this->parts['join'] = array_merge($this->parts['join'], $joins);

		return $this;
	}

	public function where(array $wheres): static
	{
		$this->parts['where'] = array_merge($this->parts['where'], $wheres);

		return $this;
	}

	public function group(array $groups): static
	{
		$this->parts['group'] = array_merge($this->parts['group'], $groups);

		return $this;
	}

	public function order(array $orders): static
	{
		$this->parts['order'] = array_merge($this->parts['order'], $orders);

		return $this;
	}

	public function offset(int $offset): static
	{
		$this->parts['offset'] = $offset;

		return $this;
	}

	public function limit(int $limit): static
	{
		$this->parts['limit'] = $limit;

		return $this;
	}
}
