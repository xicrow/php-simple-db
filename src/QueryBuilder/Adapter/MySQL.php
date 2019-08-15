<?php
namespace Xicrow\PhpSimpleDb\QueryBuilder\Adapter;

use Xicrow\PhpSimpleDb\QueryBuilder\QueryBuilderBase;

/**
 * Class MySQL
 *
 * @package Xicrow\PhpSimpleDb\QueryBuilder\Adapter
 */
class MySQL extends QueryBuilderBase
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->sql        = '';
        $this->parameters = [];

        $this->sql .= 'SELECT ';
        if (!empty($this->parts['select'])) {
            $i = 0;
            foreach ($this->parts['select'] as $alias => $field) {
                if ($i > 0) {
                    $this->sql .= ',';
                    $this->sql .= "\n";
                    $this->sql .= "\t";
                }
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
            $this->sql .= 'GROUP BY ';
            $i         = 0;
            foreach ($this->parts['group'] as $field) {
                if ($i > 0) {
                    $this->sql .= ',';
                    $this->sql .= "\n";
                    $this->sql .= "\t";
                }
                $this->sql .= $this->escapeField($field);

                $i++;
            }
        }
        if (!empty($this->parts['order'])) {
            $this->sql .= "\n";
            $this->sql .= 'ORDER BY ';
            $i         = 0;
            foreach ($this->parts['order'] as $field => $direction) {
                if ($i > 0) {
                    $this->sql .= ',';
                    $this->sql .= "\n";
                    $this->sql .= "\t";
                }
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

    /**
     * @inheritdoc
     */
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

    /**
     * @param string $prefix
     * @param array  $where
     *
     * @return string
     */
    private function getWhereArrayAsSql(string $prefix, array $where): string
    {
        $sql = '';
        if (!empty($where)) {
            $sql = $prefix;

            $i = 0;
            foreach ($where as $field => $value) {
                if (is_callable($value)) {
                    $result = $value();
                    if (empty($result) || !is_array($result)) {
                        continue;
                    }
                    $field = key($result);
                    $value = current($result);
                }

                if ($i > 0) {
                    $sql .= "\n";
                    $sql .= "\t";
                    $sql .= 'AND ';
                }

                if (is_numeric($field)) {
                    $sql .= $this->escapeField($value);
                } elseif (is_array($value)) {
                    $comparator = 'IN';
                    if (strpos($field, ' ') !== false) {
                        $tmp        = explode(' ', $field);
                        $field      = array_shift($tmp);
                        $comparator = implode(' ', $tmp);
                        $comparator = strtoupper($comparator);
                        unset($tmp);
                    }

                    $placeholders = [];
                    foreach ($value as $data) {
                        $placeholder = ':p' . count($this->parameters);

                        $placeholders[] = $placeholder;

                        $this->parameters[$placeholder] = $data;
                    }

                    $sql .= $this->escapeField($field) . ' ' . $comparator . ' (' . implode(', ', $placeholders) . ')';
                } else {
                    $comparator = '=';
                    if (strpos($field, ' ') !== false) {
                        $tmp        = explode(' ', $field);
                        $field      = array_shift($tmp);
                        $comparator = implode(' ', $tmp);
                        $comparator = strtoupper($comparator);
                        unset($tmp);
                    }

                    $placeholder = ':p' . count($this->parameters);

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

    /**
     * @param string $alias
     *
     * @return string
     */
    private function escapeAlias(string $alias): string
    {
        if (strpos($alias, '`') === false) {
            $alias = '`' . $alias . '`';
        }

        return $alias;
    }

    /**
     * @param string $field
     *
     * @return string
     */
    private function escapeField(string $field): string
    {
        if (strpos($field, '`') === false) {
            if (strpos($field, '.') === false && strpos($field, ' ') === false) {
                $field = '`' . $field . '`';
            } elseif (strpos($field, '.') !== false) {
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

    /**
     * @param string $table
     *
     * @return string
     */
    private function escapeTable(string $table): string
    {
        if (strpos($table, '`') === false) {
            $table = '`' . $table . '`';
        }

        return $table;
    }
}
