<?php
namespace Xicrow\PhpSimpleDb\Connection\Adapter;

use Pdo;
use Xicrow\PhpSimpleDb\Connection\ConnectionBase;
use Xicrow\PhpSimpleDb\Connection\ConnectionInterface;

/**
 * Class MySQL
 *
 * @package Xicrow\PhpSimpleDb\Connection\Adapter
 */
class MySQL extends ConnectionBase
{
	/**
	 * @param array $config
	 *
	 * @return ConnectionInterface
	 */
	public function initialize(array $config): ConnectionInterface
	{
		parent::initialize($config);

		$dsn = 'mysql:dbname=' . $this->config['database'];
		if (isset($this->config['host'])) {
			$dsn .= '; host=' . $this->config['host'];
		}
		if (isset($this->config['port'])) {
			$dsn .= '; port=' . $this->config['port'];
		}
		if (isset($this->config['unix_socket'])) {
			$dsn .= '; unix_socket=' . $this->config['unix_socket'];
		}

		$this->pdo = new Pdo($dsn, $this->config['username'], $this->config['password'], $this->config['options']);
		if (isset($this->config['charset'])) {
			$this->pdo->prepare('SET NAMES "' . $this->config['charset'] . '"')->execute();
		}

		return $this;
	}

	public function buildSql(array $parts): string
	{
		$sql = '';
		$sql .= 'SELECT ';
		if (!empty($parts['select'])) {
			$i = 0;
			foreach ($parts['select'] as $alias => $field) {
				if ($i > 0) {
					$sql .= ',';
					$sql .= "\n";
					$sql .= str_repeat(' ', strlen('SELECT '));
				}
				if (is_numeric($alias)) {
					$sql .= $this->escapeTableAndFieldName($field);
				} else {
					$sql .= $this->escapeTableAndFieldName($field) . ' AS ' . $this->escapeTableAndFieldName($alias);
				}

				$i++;
			}
		} else {
			$sql .= '*';
		}
		if (!empty($parts['from'])) {
			$sql .= "\n";
			$sql .= 'FROM ' . $this->escapeTableAndFieldName($parts['from']);
		}
		if (!empty($parts['join'])) {
			foreach ($parts['join'] as $join) {
				$join = array_merge([
					'type'  => 'INNER',
					'table' => '',
					'where' => [],
				], $join);

				if (!in_array(strtolower($join['type']), ['inner', 'left', 'right', 'outer', 'full outer'])) {
					$join['type'] = 'inner';
				}

				$prefix = strtoupper($join['type']) . ' JOIN ' . $this->escapeTableAndFieldName($join['table']);

				$sql .= "\n";
				if (!empty($join['where'])) {
					$sql .= self::getWhereArrayAsSql($prefix . ' ON ', $join['where']);
				} else {
					$sql .= $prefix;
				}
			}
		}
		if (!empty($parts['where'])) {
			$sql .= "\n";
			$sql .= self::getWhereArrayAsSql('WHERE ', $parts['where']);
		}
		if (!empty($parts['group'])) {
			$sql .= "\n";
			$sql .= 'GROUP BY';
			$i   = 0;
			foreach ($parts['group'] as $field) {
				if ($i > 0) {
					$sql .= ',';
				}
				$sql .= ' ' . $this->escapeTableAndFieldName($field);

				$i++;
			}
		}
		if (!empty($parts['order'])) {
			$sql .= "\n";
			$sql .= 'ORDER BY';
			$i   = 0;
			foreach ($parts['order'] as $field => $direction) {
				if ($i > 0) {
					$sql .= ', ';
				}
				if (is_numeric($field)) {
					$sql .= $this->escapeTableAndFieldName($direction);
				} else {
					if (!in_array(strtolower($direction), ['asc', 'desc'])) {
						$direction = 'asc';
					}
					$sql .= ' ' . $this->escapeTableAndFieldName($field) . ' ' . strtoupper($direction);
				}

				$i++;
			}
		}
		if (array_key_exists('limit', $parts) && is_numeric($parts['limit'])) {
			$sql .= "\n";
			$sql .= 'LIMIT ';
			if (array_key_exists('offset', $parts) && is_numeric($parts['offset'])) {
				$sql .= intval($parts['offset']);
				$sql .= ', ';
			}
			$sql .= intval($parts['limit']);
		}

		return $sql;
	}

	private function getWhereArrayAsSql(string $prefix, array $where): string
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

				if ($i > 0) {
					$sql .= "\n";
					$sql .= str_repeat(' ', strlen($prefix));
					$sql .= 'AND ';
				}

				if (is_numeric($field)) {
					$sql .= $this->escapeTableAndFieldName($value);
				} elseif (is_array($value)) {
					$comparator = 'IN';
					if (strpos($field, ' ') !== false) {
						$tmp        = explode(' ', $field);
						$field      = array_shift($tmp);
						$comparator = implode(' ', $tmp);
						$comparator = strtoupper($comparator);
						unset($tmp);
					}

					$sql .= $this->escapeTableAndFieldName($field) . ' ' . $comparator . ' (\'' . implode('\', \'', $value) . '\')';
				} else {
					$comparator = '=';
					if (strpos($field, ' ') !== false) {
						$tmp        = explode(' ', $field);
						$field      = array_shift($tmp);
						$comparator = implode(' ', $tmp);
						$comparator = strtoupper($comparator);
						unset($tmp);
					}

					if (is_null($value)) {
						$value = 'NULL';
					} elseif (is_string($value)) {
						$value = '"' . $value . '"';
					}

					$sql .= $this->escapeTableAndFieldName($field) . ' ' . $comparator . ' ' . $value;
				}

				$i++;
			}
		}

		return $sql;
	}

	private function escapeTableAndFieldName(string $data): string
	{
		if (strpos($data, '`') !== false) {
			return $data;
		}

		if (strpos($data, '.') === false && strpos($data, ' ') === false) {
			$data = '`' . $data . '`';
		} elseif (strpos($data, '.') !== false) {
			if (preg_match_all('#[a-z0-9_]+\.[a-z0-9_]+#', $data, $matches)) {
				foreach ($matches[0] as $match) {
					$search  = $match;
					$replace = '`' . str_replace('.', '`.`', $match) . '`';
					$data    = str_replace($search, $replace, $data);
				}
			}
		}

		return $data;
	}
}
