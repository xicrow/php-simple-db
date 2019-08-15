<?php
namespace Xicrow\PhpSimpleDb\QueryBuilder;

/**
 * Interface QueryBuilderInterface
 *
 * @package Xicrow\PhpSimpleDb\QueryBuilder
 */
interface QueryBuilderInterface
{
	/**
	 * @param array $parts
	 */
	public function __construct(array $parts = []);

	/**
	 * @return static
	 */
	public function execute();

	/**
	 * @return string
	 */
	public function render(): string;

	/**
	 * @return string
	 */
	public function getSql(): string;

	/**
	 * @return array
	 */
	public function getParameters(): array;

	/**
	 * @param array $selects
	 *
	 * @return static
	 */
	public function select(array $selects);

	/**
	 * @param array $froms
	 *
	 * @return static
	 */
	public function from(array $froms);

	/**
	 * @param array $joins
	 *
	 * @return static
	 */
	public function join(array $joins);

	/**
	 * @param array $wheres
	 *
	 * @return static
	 */
	public function where(array $wheres);

	/**
	 * @param array $groups
	 *
	 * @return static
	 */
	public function group(array $groups);

	/**
	 * @param array $orders
	 *
	 * @return static
	 */
	public function order(array $orders);

	/**
	 * @param int $offset
	 *
	 * @return static
	 */
	public function offset(int $offset);

	/**
	 * @param int $limit
	 *
	 * @return static
	 */
	public function limit(int $limit);
}
