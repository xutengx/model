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

final class ORMTest extends GenericTestsDatabaseTestCase {

	/**
	 * @var Model
	 */
	private static $model;

	public function testCreate() {
		$model     = static::$model;
		$model->orm = [
			'name'       => '盖伦',
			'age'        => '32',
			'sex'        => '1',
			'teacher_id' => '0',
		];
		$table     = $model->getTable();
		$this->assertEquals(20, $model->create(true), 'ORM新增一行, 返回主键');
		$this->assertEquals("insert into `$table`(`name`,`age`,`sex`,`teacher_id`) values( '盖伦' , '32' , '1' , '0' )", $model->getLastSql());

		$model->orm = [
			'name'       => '盖伦',
			'age'        => '32',
			'sex'        => '1',
			'teacher_id' => '0',
		];
		$table     = $model->getTable();
		$this->assertEquals(1, $model->create(), 'ORM新增一行');
		$this->assertEquals("insert into `$table`(`name`,`age`,`sex`,`teacher_id`) values( '盖伦' , '32' , '1' , '0' )", $model->getLastSql());
	}

	public function testSave() {
		$model     = static::$model;
		$model->orm = [
			'id'         => 10,
			'name'       => '盖伦',
			'age'        => '32',
			'sex'        => '1',
			'teacher_id' => '0',
		];
		$table     = $model->getTable();
		$this->assertEquals(1, $model->save(), 'ORM修改一行');
		$this->assertEquals("update `$table` set `id`= '10' ,`name`= '盖伦' ,`age`= '32' ,`sex`= '1' ,`teacher_id`= '0'  where `id`= '10' ", $model->getLastSql());


		$model     = static::$model;
		$model->orm = [
			'name'       => '盖伦',
			'age'        => '39',
			'sex'        => '1',
			'teacher_id' => '0',
		];
		$table     = $model->getTable();
		$this->assertEquals(1, $model->save(2), 'ORM修改一行');
		$this->assertEquals("update `$table` set `name`= '盖伦' ,`age`= '39' ,`sex`= '1' ,`teacher_id`= '0'  where `id`= '2' ", $model->getLastSql());
	}


	protected function setUp() {
		parent::setUp();
		if (is_null(static::$model)) {
			$redis         = $this->makeRedisDriver();
			$cache         = $this->cacheManagerWithRedisDriver($redis);
			static::$model = $this->getModel($cache);
		}
	}

	/**
	 * 实例化Redis缓存驱动
	 * @return Redis
	 */
	protected function makeRedisDriver(): Redis {
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
	protected function cacheManagerWithRedisDriver(Driver $Redis): Manager {
		$this->assertInstanceOf(Manager::class, $Manager = new Manager($Redis));
		$this->assertEquals('redis', $Manager->getDriverName(), '当前缓存类型');
		return $Manager;
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

	/**
	 * @depends testCacheManagerWithRedisDriver
	 * @param Manager $cache
	 * @return Model
	 */
	protected function getModel(Manager $cache): Model {
		$defaultConnection = 'defaultConnection';
		Model::init($cache, $defaultConnection);
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
		return $Student;
	}
}



