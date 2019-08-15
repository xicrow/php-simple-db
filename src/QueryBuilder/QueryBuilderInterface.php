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
     * @return $this
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
     * @return $this
     */
    public function select(array $selects);

    /**
     * @param string $from
     *
     * @return mixed
     */
    public function from(array $froms);

    /**
     * @param array $joins
     *
     * @return mixed
     */
    public function join(array $joins);

    /**
     * @param array $wheres
     *
     * @return mixed
     */
    public function where(array $wheres);

    /**
     * @param array $groups
     *
     * @return mixed
     */
    public function group(array $groups);

    /**
     * @param array $orders
     *
     * @return mixed
     */
    public function order(array $orders);

    /**
     * @param int $offset
     *
     * @return mixed
     */
    public function offset(int $offset);

    /**
     * @param int $limit
     *
     * @return mixed
     */
    public function limit(int $limit);
}
