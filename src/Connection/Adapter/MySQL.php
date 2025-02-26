<?php
declare(strict_types=1);

namespace Xicrow\PhpSimpleDb\Connection\Adapter;

use Pdo;
use Xicrow\PhpSimpleDb\Connection\ConnectionBase;
use Xicrow\PhpSimpleDb\Connection\ConnectionInterface;

class MySQL extends ConnectionBase
{
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
}
