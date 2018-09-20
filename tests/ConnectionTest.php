<?php
declare(strict_types = 1);

use Xutengx\Model\Connection\AbstractConnection;
use Xutengx\Model\Connection\Connection;

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
				'db'     => 'model_test_master_0'
			]
		];
		$this->assertInstanceOf(AbstractConnection::class, $conn = new Connection($writeArray));
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
				'db'     => 'model_test_master_0'
			],
			[
				'weight' => 5,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'model_test_master_1'
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
				'db'     => 'model_test_slave_0'
			],
			[
				'weight' => 1,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'model_test_slave_1'
			],
			[
				'weight' => 1,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'model_test_slave_2'
			],
			[
				'weight' => 0,
				'type'   => 'mysql',
				'host'   => '127.0.0.1',
				'port'   => 3306,
				'user'   => 'root',
				'pwd'    => 'root',
				'db'     => 'model_test_slave_3'
			]
		];
		$this->assertInstanceOf(AbstractConnection::class, $conn = new Connection($writeArray, $readyArray));
		return $conn;
	}

	public function test数据库connection读写() {
		$conn = $this->testSimpleGetConnection();
		$res  = $conn->getAll('select * from student where sex=:sex', [':sex' => 1]);
		$this->assertTrue(is_array($res));
		$this->assertEquals(count($res), 6);

		$id = reset($res)['id'];
		$conn->update('update student set name="王力宏" where id=:id', [':id' => $id]);

		$res = $conn->getRow('select * from student where id=:id', [':id' => $id]);
		$this->assertEquals('王力宏', $res['name']);
	}

	public function test数据库connection读写切换() {
		$conn = $this->testGetConnection();
		$res  = $conn->getAll('select * from student where sex=:sex', [':sex' => 1]);
		$this->assertTrue(is_array($res));
		$this->assertEquals(count($res), 6);

		$id = reset($res)['id'];
		$conn->update('update student set name="王力宏" where id=:id', [':id' => $id]);

		$res = $conn->getRow('select * from student where id=:id', [':id' => $id]);
		$this->assertNotEquals('王力宏', $res['name']);

	}

	public function test从库保持一个连接() {
		$conn = $this->testGetConnection();
		$arr  = [];
		for ($i = 0; $i < 1000; $i++)
			$arr[] = $conn->getRow('select database()')['database()']; // 当前的数据库名称
		$this->assertEquals(1, count($dbArr = array_unique($arr)));
		$this->assertTrue(!in_array('model_test_slave_3', $dbArr), '权重测试');
	}

	public function test事务中不切换到从库(){
		$conn = $this->testGetConnection();
		$this->assertTrue($conn->begin());
		$conn->getRow('select database()')['database()'];
		$writeTable = $conn->getRow('select database()')['database()'];
		$this->assertTrue(in_array($writeTable, ['model_test_master_0', 'model_test_master_1']));
		$this->assertTrue($conn->rollBack());
		$this->assertTrue($conn->begin());

		$arr  = [];
		for ($i = 0; $i < 1000; $i++)
			$arr[] = $conn->getRow('select database()')['database()']; // 当前的数据库名称
		$this->assertEquals(1, count($dbArr = array_unique($arr)));
		$this->assertEquals(reset($dbArr), $writeTable);
		$conn->rollBack();
	}

}


