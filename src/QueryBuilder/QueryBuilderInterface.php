<?php
declare(strict_types=1);

namespace Xicrow\PhpSimpleDb\QueryBuilder;

interface QueryBuilderInterface
{
	public function __construct(array $parts = []);

	public function execute(): static;

	public function render(): string;

	public function getSql(): string;

	public function getParameters(): array;

	public function select(array $selects): static;

	public function from(array $froms): static;

	public function join(array $joins): static;

	public function where(array $wheres): static;

	public function group(array $groups): static;

	public function order(array $orders): static;

	public function offset(int $offset): static;

	public function limit(int $limit): static;
}
