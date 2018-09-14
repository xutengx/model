<?php
declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Xutengx\Cache\Driver\{Redis};
use Xutengx\Cache\Manager;
use Xutengx\Contracts\Cache\Driver;
use Xutengx\Model\Connection\AbstractConnection;
use Xutengx\Model\Connection\Connection;
use Xutengx\Model\Model;

require_once __DIR__ . '/Models.php';

final class SrcTest extends TestCase {

	public function setUp() {

	}

	/**
	 * 实例化Redis缓存驱动
	 * @return Redis
	 */
	public function testMakeRedisDriver(): Redis {
		$host                 = '127.0.0.1';
		$port                 = 6379;
		$password             = '';
		$database             = 0;
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

		Model::addConnection('defaultConnection', $this->GetConnection());

		$this->assertInstanceOf(Model::class, $Student = new Student);
		$this->assertInstanceOf(Model::class, $Teacher = new Teacher);
		$this->assertInstanceOf(Model::class, $Relationship = new RelationshipStudentTeacher);
		$this->assertInstanceOf(Model::class, $TestModel = new TestModel);

		$this->assertFalse($Student === $Teacher);

		$this->assertEquals($Relationship->getTable(), 'relationship_student_teacher');
		$this->assertEquals($Student->getTable(), 'student');
		$this->assertEquals($Teacher->getTable(), 'teacher');
		$this->assertEquals($TestModel->getTable(), 'test', '无主键');

		$this->insert($TestModel);
		$this->insert($Student);
		$this->insert($Teacher);
		$this->insert($Relationship);

	}

	/**
	 * @return Connection
	 */
	protected function GetConnection() {
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

	protected function insert(Model $model) {
		$table     = $model->getTable();
		$timeStamp = time();
		$this->assertEquals(1, $model->insert(), '新增全默认的一行');
		$this->assertEquals('insert into `' . $table . '` values()', $model->insertToSql());
		$this->assertEquals('insert into `' . $table . '` values()', $model->getLastSql());

		$data = [
			[],
			[],
			[]
		];
		$this->assertEquals('insert into `' . $table . '` values(),(),()', $model->value($data)->insertToSql());
		$this->assertEquals(3, $model->value($data)->insert(), '2维数组新增');
		$this->assertEquals('insert into `' . $table . '` values(),(),()', $model->getLastSql());

		$data = [
			[
				$ct = date('Y-m-d H:i:m', $timeStamp),
				$ut = date('Y-m-d H:i:m', $timeStamp)
			],
			[
				$ct = date('Y-m-d H:i:m', $timeStamp),
				$ut = date('Y-m-d H:i:m', $timeStamp)
			],
			[
				$ct = date('Y-m-d H:i:m', $timeStamp),
				$ut = date('Y-m-d H:i:m', $timeStamp)
			]
		];
		$this->assertEquals(3, $model->newQuery()->column(['created_at', 'updated_at'])->value($data)->insert(),
			'2维数组新增,键值分开设置');

		$data = [
			'created_at' => $ct = date('Y-m-d H:i:m', $timeStamp),
			'updated_at' => $ut = date('Y-m-d H:i:m', $timeStamp)
		];
		$this->assertEquals("insert into `$table`(`created_at`,`updated_at`) values( '$ct' , '$ut' )",
			$model->value($data)->insertToSql());
		$this->assertEquals(1, $model->value($data)->insert(), '1维数组新增');
		$this->assertEquals("insert into `$table`(`created_at`,`updated_at`) values( '$ct' , '$ut' )",
			$model->getLastSql());

		try {
			$this->assertInternalType('string', $lastInsertId = $model::value($data)->insertGetId());
			$this->assertEquals($data['created_at'], ($model->where('id', $lastInsertId)->getRow())['created_at']);
		} catch (RuntimeException $exception) {
			$this->assertEquals('The method[InsertGetId] can not be properly executed without primaryKey[AUTO_INCREMENT].',
				$exception->getMessage());
			$this->assertEquals($table, 'test', 'test数据库是没有主键的, 会抛出此异常');
		}
	}

}



