<?php
declare(strict_types = 1);

use Xutengx\Cache\Driver\{Redis};
use Xutengx\Cache\Manager;
use Xutengx\Contracts\Cache\Driver;
use Xutengx\Model\Connection\AbstractConnection;
use Xutengx\Model\Connection\Connection;
use Xutengx\Model\Model;

require_once __DIR__ . '/Models.php';
require_once __DIR__ . '/GenericTestsDatabaseTestCase.php';

final class MakeModelTest extends GenericTestsDatabaseTestCase {

	/**
	 * 实例化Redis缓存驱动
	 * @return Redis
	 */
	public function testMakeRedisDriver(): Redis {
		$host                 = $GLOBALS['REDIS_HOST'];
		$port                 = (int)$GLOBALS['REDIS_PORT'];
		$password             = $GLOBALS['REDIS_PASSWD'];
		$database             = (int)$GLOBALS['REDIS_DATABASE'];
		$persistentConnection = false;
		$this->assertInstanceOf(Redis::class,
			$driver = new Redis($host, $port, $password, $database, $persistentConnection));
		return $driver;
	}

	/**
	 * 实例化Manager
	 * @depends testMakeRedisDriver
	 * @param Driver $Redis
	 * @return Manager
	 */
	public function testCacheManagerWithRedisDriver(Driver $Redis): Manager {
		$this->assertInstanceOf(Manager::class, $Manager = new Manager($Redis));
		$this->assertEquals('redis', $Manager->getDriverName(), '当前缓存类型');
		return $Manager;
	}

	/**
	 * @depends testCacheManagerWithRedisDriver
	 * @param Manager $cache
	 */
	public function testObject(Manager $cache) {
		$init = false;
		try {
			$this->assertInstanceOf(Model::class, new RelationshipStudentTeacher);
			$init = true;
		} catch (RuntimeException $exception) {

		} finally {
			$this->assertFalse($init);
		}

		$defaultConnection = 'defaultConnection';
		Model::init($cache, $defaultConnection);

		$initConnection = false;
		try {
			$this->assertInstanceOf(Model::class, new RelationshipStudentTeacher);
			$initConnection = true;
		} catch (RuntimeException $exception) {

		} finally {
			$this->assertFalse($initConnection);
		}

		Model::addConnection('defaultConnection', $this->GetConnections());

		$this->assertInstanceOf(Model::class, $TestModel = new TestModel);
		$this->assertInstanceOf(Model::class, $Student = new Student);
		$this->assertInstanceOf(Model::class, $Teacher = new Teacher);
		$this->assertInstanceOf(Model::class, $Relationship = new RelationshipStudentTeacher);

		$this->assertFalse($Student === $Teacher);

		$this->assertEquals($Relationship->getTable(), 'relationship_student_teacher');
		$this->assertEquals($Student->getTable(), 'student');
		$this->assertEquals($Teacher->getTable(), 'teacher');
		$this->assertEquals($TestModel->getTable(), 'test', '无主键');

	}

	/**
	 * @return Connection
	 */
	protected function GetConnections(): Connection {
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

}



