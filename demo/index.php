<?php
require_once('../vendor/autoload.php');

use Xicrow\PhpSimpleDb\Connection\Manager as ConnectionManager;
use Xicrow\PhpSimpleDb\QueryBuilder\Adapter\MySQL as QueryBuilderMySQL;
use Xicrow\PhpSimpleDb\QueryBuilder\Manager as QueryBuilderManager;

/**
 * Add and get a new connection, through the connection manager
 *
 * @var \Xicrow\PhpSimpleDb\Connection\Adapter\MySQL $connection
 */
$connectionManager = new ConnectionManager();
$connectionManager->add('test', [
	'adapter'  => 'mysql',
	'host'     => '127.0.0.1',
	'port'     => '3306',
	'database' => 'information_schema',
	'username' => 'root',
	'password' => '',
	'charset'  => 'utf8',
]);
$connection = $connectionManager->get('test');

/**
 * Add and get a new query builder, through the query builder manager
 *
 * @var QueryBuilderMySQL $queryBuilder
 */
$queryBuilderManager = new QueryBuilderManager();
$queryBuilderManager->add('test', [
	'adapter' => 'mysql',
]);
$queryBuilder = $queryBuilderManager->get('test');

/**
 * Do some tests
 */
$queryBuilder->select([
	'TABLES.TABLE_SCHEMA',
	'TABLES.TABLE_NAME',
	'TABLES.ENGINE',
	'TABLES.TABLE_ROWS',
	'SCHEMATA_JOIN.DEFAULT_CHARACTER_SET_NAME',
	'foo'                         => '"bar"',
	'table_rows'                  => 'TABLES.TABLE_ROWS',
	'character_set_raw'           => 'SELECT `DEFAULT_CHARACTER_SET_NAME` FROM `SCHEMATA` WHERE `SCHEMA_NAME` = `TABLES`.`TABLE_SCHEMA` LIMIT 1',
	'character_set_query_builder' => str_replace("\n", ' ', (new QueryBuilderMySQL([
		'select' => ['DEFAULT_CHARACTER_SET_NAME'],
		'from'   => ['SCHEMATA'],
		'where'  => ['SCHEMA_NAME = TABLES.TABLE_SCHEMA'],
		'limit'  => 1,
	]))->execute()->render()),
])->from([
	'TABLES',
])->join([
	'SCHEMATA_JOIN' => [
		'type'  => 'left',
		'table' => 'SCHEMATA',
		'where' => [
			'SCHEMATA_JOIN.SCHEMA_NAME = TABLES.TABLE_SCHEMA',
		],
	],
])->where([
	'TABLES.TABLE_SCHEMA'  => 'mysql',
	'TABLES.ENGINE'        => ['CSV', 'InnoDB', 'MyISAM'],
	'TABLES.TABLE_ROWS >=' => 0,
])->group([
	'TABLES.TABLE_SCHEMA',
	'TABLES.TABLE_NAME',
])->order([
	'TABLES.TABLE_SCHEMA' => 'ASC',
	'TABLES.TABLE_NAME'   => 'ASC',
])->offset(0)->limit(50)->execute();

try {
	$statement = $connection->pdo()->prepare($queryBuilder->getSql());
	$statement->execute($queryBuilder->getParameters());

	echo '<pre>' . $queryBuilder->render() . '</pre>';
	echo '<pre>' . print_r($statement->fetchAll(), true) . '</pre>';
} catch (PDOException $exception) {
	echo '<pre>' . print_r($exception, true) . '</pre>';
	echo '<pre>' . $queryBuilder->render() . '</pre>';
}
