<?php
declare(strict_types = 1);

use Xutengx\Model\Connection\AbstractConnection;
use Xutengx\Model\Connection\Connection;
use Xutengx\Model\Connection\PersistentConnection;

require_once __DIR__ . '/Models.php';
require_once __DIR__ . '/GenericTestsDatabaseTestCase.php';

final class ConnectionTest extends GenericTestsDatabaseTestCase {

	/**
	 * @return Connection
	 */
	public function testSimpleGetConnection(): Connection {
		$writeArray = [
			[
				'weight' => 5,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'test'
			]
		];
		$this->assertInstanceOf(AbstractConnection::class, $conn = new Connection($writeArray));
		return $conn;
	}

	public function testSimpleGetPersistentConnection(): PersistentConnection {
		$writeArray = [
			[
				'weight' => 5,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'test'
			]
		];
		$this->assertInstanceOf(AbstractConnection::class, $conn = new PersistentConnection($writeArray));
		return $conn;
	}

	public function testGetConnection(): Connection {
		$writeArray = [
			[
				'weight' => 5,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'test'
			]
		];
		$readyArray = [
			[
				'weight' => 1,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'test'
			],
			[
				'weight' => 1,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'test'
			]
		];
		$this->assertInstanceOf(AbstractConnection::class, $conn = new Connection($writeArray, $readyArray));
		return $conn;
	}

	public function testGetPersistentConnection(): Connection {
		$writeArray = [
			[
				'weight' => 5,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'test'
			]
		];
		$readyArray = [
			[
				'weight' => 1,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'test'
			],
			[
				'weight' => 1,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'test'
			]
		];
		$this->assertInstanceOf(AbstractConnection::class, $conn = new Connection($writeArray, $readyArray));
		return $conn;
	}

	public function test数据库connection读写切换(){
		$conn = $this->testGetConnection();
		$res = $conn->getAll('select * from student where sex=1');
		$this->assertTrue(is_array($res));
	}

}



