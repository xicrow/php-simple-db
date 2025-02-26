<?php
declare(strict_types=1);

namespace Xicrow\PhpSimpleDb\QueryBuilder\Adapter;

use Xicrow\PhpSimpleDb\QueryBuilder\QueryBuilderBase;

class MySQL extends QueryBuilderBase
{
	public function execute(): static
	{
		$this->sql        = '';
		$this->parameters = [];

		$this->sql .= 'SELECT ';
		if (!empty($this->parts['select'])) {
			$i = 0;
			foreach ($this->parts['select'] as $alias => $field) {
				if ($i > 0) {
					$this->sql .= ',';
				}
				$this->sql .= "\n";
				$this->sql .= "\t";
				if (is_numeric($alias)) {
					$this->sql .= $this->escapeField($field);
				} else {
					$this->sql .= '(' . $field . ') AS ' . $this->escapeAlias($alias);
				}

				$i++;
			}
		} else {
			$this->sql .= '*';
		}
		if (!empty($this->parts['from'])) {
			$this->sql .= "\n";
			$this->sql .= 'FROM ';
			$i         = 0;
			foreach ($this->parts['from'] as $alias => $table) {
				if ($i > 0) {
					$this->sql .= ', ';
				}
				$this->sql .= "\n";
				$this->sql .= "\t";
				if (is_numeric($alias)) {
					$this->sql .= $this->escapeTable($table);
				} else {
					$this->sql .= $this->escapeTable($table) . ' AS ' . $this->escapeAlias($alias);
				}

				$i++;
			}
		}
		if (!empty($this->parts['join'])) {
			foreach ($this->parts['join'] as $alias => $join) {
				$join = array_merge([
					'alias' => null,
					'type'  => 'INNER',
					'table' => '',
					'where' => [],
				], $join);

				if (!in_array(strtolower($join['type']), ['inner', 'left', 'right', 'outer', 'full outer'])) {
					$join['type'] = 'inner';
				}

				$prefix = strtoupper($join['type']) . ' JOIN ' . $this->escapeTable($join['table']);
				if (!empty($join['alias']) || (!empty($alias) && !is_numeric($alias))) {
					$alias = (!empty($join['alias']) ? $join['alias'] : $alias);
					if ($alias !== $join['table']) {
						$prefix .= ' AS ' . $this->escapeTable($alias);
					}
				}

				$this->sql .= "\n";
				if (!empty($join['where'])) {
					$this->sql .= self::getWhereArrayAsSql($prefix . ' ON ', $join['where']);
				} else {
					$this->sql .= $prefix;
				}
			}
		}
		if (!empty($this->parts['where'])) {
			$this->sql .= "\n";
			$this->sql .= self::getWhereArrayAsSql('WHERE ', $this->parts['where']);
		}
		if (!empty($this->parts['group'])) {
			$this->sql .= "\n";
			$this->sql .= 'GROUP BY';
			$i         = 0;
			foreach ($this->parts['group'] as $field) {
				if ($i > 0) {
					$this->sql .= ',';
				}
				$this->sql .= "\n";
				$this->sql .= "\t";
				$this->sql .= $this->escapeField($field);

				$i++;
			}
		}
		if (!empty($this->parts['having'])) {
			$this->sql .= "\n";
			$this->sql .= self::getWhereArrayAsSql('HAVING ', $this->parts['having']);
		}
		if (!empty($this->parts['order'])) {
			$this->sql .= "\n";
			$this->sql .= 'ORDER BY ';
			$i         = 0;
			foreach ($this->parts['order'] as $field => $direction) {
				if ($i > 0) {
					$this->sql .= ',';
				}
				$this->sql .= "\n";
				$this->sql .= "\t";
				if (is_numeric($field)) {
					$this->sql .= $this->escapeField($direction);
				} else {
					if (!in_array(strtolower($direction), ['asc', 'desc'])) {
						$direction = 'asc';
					}
					$this->sql .= $this->escapeField($field) . ' ' . strtoupper($direction);
				}

				$i++;
			}
		}
		if (array_key_exists('limit', $this->parts) && is_numeric($this->parts['limit'])) {
			$this->sql .= "\n";
			$this->sql .= 'LIMIT ';
			if (array_key_exists('offset', $this->parts) && is_numeric($this->parts['offset'])) {
				$this->sql .= intval($this->parts['offset']);
				$this->sql .= ', ';
			}
			$this->sql .= intval($this->parts['limit']);
		}

		return $this;
	}

	public function render(): string
	{
		$sql = $this->getSql();
		foreach ($this->getParameters() as $placeholder => $value) {
			if (is_null($value)) {
				$value = 'NULL';
			} elseif (is_string($value)) {
				$value = '"' . addslashes($value) . '"';
			}

			$sql = str_replace($placeholder, $value, $sql);
		}

		return $sql;
	}

	protected function getWhereArrayAsSql(string $prefix, array $where, string $type = 'AND', int $indent = 0): string
	{
		$sql = '';
		if (!empty($where)) {
			$sql = $prefix;

			$i = 0;
			foreach ($where as $field => $value) {
				if (!is_scalar($value) && is_callable($value)) {
					$result = $value();
					if (empty($result) || !is_array($result)) {
						continue;
					}
					$field = key($result);
					$value = current($result);
				}

				if (is_array($value) && count($value) === 0) {
					continue;
				}

				$sql .= "\n";
				$sql .= str_repeat("\t", ($indent + 1));
				if ($i > 0) {
					$sql .= $type . ' ';
				}

				if (is_numeric($field) && is_array($value)) {
					$field = 'AND';
				}
				if (in_array($field, ['AND', 'OR']) && is_array($value) && count($value) > 0) {
					$sql .= "\n";
					$sql .= str_repeat("\t", ($indent + 1));
					$sql .= '(';
					$sql .= $this->getWhereArrayAsSql('', $value, $field, ($indent + 1));
					$sql .= "\n";
					$sql .= str_repeat("\t", ($indent + 1));
					$sql .= ')';

					$i++;

					continue;
				}

				if (is_numeric($field)) {
					$sql .= $this->escapeField($value);
				} elseif (is_array($value)) {
					$comparator = 'IN';
					if (str_contains($field, ' ')) {
						$tmp        = explode(' ', $field);
						$field      = array_shift($tmp);
						$comparator = implode(' ', $tmp);
						$comparator = strtoupper($comparator);
						unset($tmp);
					}

					$placeholders = [];
					foreach ($value as $data) {
						$placeholder = $this->getPlaceholder($data);

						$placeholders[] = $placeholder;

						$this->parameters[$placeholder] = $data;
					}

					$sql .= $this->escapeField($field) . ' ' . $comparator . ' (' . implode(', ', $placeholders) . ')';
				} else {
					$comparator = '=';
					if (str_contains($field, ' ')) {
						$tmp        = explode(' ', $field);
						$field      = array_shift($tmp);
						$comparator = implode(' ', $tmp);
						$comparator = strtoupper($comparator);
						unset($tmp);
					}

					$placeholder = $this->getPlaceholder($value);

					$sql .= $this->escapeField($field) . ' ' . $comparator . ' ' . $placeholder;

					if (is_bool($value)) {
						$value = intval($value);
					}

					$this->parameters[$placeholder] = $value;
				}

				$i++;
			}
		}

		return $sql;
	}

	protected function getPlaceholder(mixed $value): string
	{
		$placeholder = ':';
		$placeholder .= substr(gettype($value), 0, 3);
		$placeholder .= str_pad((string)count($this->parameters), 4, '0', STR_PAD_LEFT);

		return $placeholder;
	}

	protected function escapeAlias(string $alias): string
	{
		if (!str_contains($alias, '`')) {
			$alias = '`' . $alias . '`';
		}

		return $alias;
	}

	protected function escapeField(string $field): string
	{
		if (!str_contains($field, '`')) {
			if (!str_contains($field, '.') && !str_contains($field, ' ')) {
				$field = '`' . $field . '`';
			} elseif (str_contains($field, '.')) {
				if (preg_match_all('#[a-zA-Z0-9_]+\.[a-zA-Z0-9_]+#', $field, $matches)) {
					foreach ($matches[0] as $match) {
						$search  = $match;
						$replace = '`' . str_replace('.', '`.`', $match) . '`';
						$field   = str_replace($search, $replace, $field);
					}
				}
			}
		}

		return $field;
	}

	protected function escapeTable(string $table): string
	{
		if (!str_contains($table, '`')) {
			$table = '`' . $table . '`';
		}

		return $table;
	}
}
