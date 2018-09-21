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

final class ModelTest extends GenericTestsDatabaseTestCase {

	/**
	 * @var Model
	 */
	private static $model;

	protected function setUp() {
		parent::setUp();
		if(is_null(static::$model)){
			$redis = $this->makeRedisDriver();
			$cache = $this->cacheManagerWithRedisDriver($redis);
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


	public function testiInsert() {
		$model = static::$model;
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

	public function testWhere() {
		$Student = static::$model;
		$this->assertEquals($Student->select(['id', 'name', 'age'])->where('id', '3')->getRow(),
			['id' => 3, 'name' => '小腾', 'age' => 16]);

		$this->assertEquals($Student->select('id,name,age')
		                            ->where('id', '<=', '3')
		                            ->order('age', 'desc')
		                            ->index('name')
		                            ->getAll(), [
			'小腾' => ['id' => 3, 'name' => '小腾', 'age' => 16],
			'小张' => ['id' => 2, 'name' => '小张', 'age' => 11],
			'小明' => ['id' => 1, 'name' => '小明', 'age' => 6],
		]);

		$this->assertEquals($Student->newQuery()
		                            ->select('id,name,age')
		                            ->where('sex', 1)
		                            ->order('id')
		                            ->limit(2)
		                            ->getAll(), [
			['id' => 3, 'name' => '小腾', 'age' => 16],
			['id' => 6, 'name' => '非卡', 'age' => 16]
		]);

		$this->assertEquals($Student->newQuery()
		                            ->select('id,name,age')
		                            ->where('sex', 1)
		                            ->where('age', '>', 16)
		                            ->order('id')
		                            ->limit(2)
		                            ->getAll(), [
			['id' => 7, 'name' => '狄龙', 'age' => 17],
			['id' => 8, 'name' => '金庸', 'age' => 17]
		]);

		$this->assertEquals($Student->newQuery()
		                            ->select('id,name,age')
		                            ->where('sex', 1)
		                            ->where('id', '&', 1)
		                            ->order('id')
		                            ->limit(2)
		                            ->getAll(), [
			['id' => 3, 'name' => '小腾', 'age' => 16],
			['id' => 7, 'name' => '狄龙', 'age' => 17]
		]);

		$this->assertEquals($Student->newQuery()
		                            ->select(['count(id)', 'sum(id) as sum'])
		                            ->where('id', '&', 1)
		                            ->where('name', 'like', '%小%')
		                            ->group(['sex'])
		                            ->getAll(), [
			['count(`id`)' => 1, 'sum' => '3'],
			['count(`id`)' => 2, 'sum' => '6']
		]);

	}

	public function test自增减() {
		$Student = static::$model;
		$this->assertEquals(1, $Student->data(['name' => ''])
		                               ->dataIncrement('age', 1)
		                               ->where('id<=2')
		                               ->order('id', 'asc')
		                               ->limit(1)
		                               ->update());

		$this->assertEquals($Student->select(['age','name'])->where('id', 1)->getRow(), [
			'age' => 7,
			'name' => '',
		]);

		$this->assertEquals(1, $Student->data(['name' => '小明'])
		                               ->dataDecrement('age', 1)
		                               ->where('id<=2')
		                               ->order('id', 'asc')
		                               ->limit(1)
		                               ->update());

		$this->assertEquals($Student->select(['age','name'])->where('id', 1)->getRow(), [
			'age' => 6,
			'name' => '小明',
		]);
	}

	public function testOrWhere() {
		$Student = static::$model;
		$this->assertEquals($Student->newQuery()->select(['id', 'name', 'age'])
		                            ->whereIn('id', ['1', 2, '3', '4'])
		                            ->andWhere(function($queryBuilder) {
			                            $queryBuilder->where('id', '2')
			                                         ->orWhere(function($re) {
				                                         $re->where('id', '3')->whereNotNull('id');
			                                         });
		                            })
		                            ->getAll(), [
			['id' => 2, 'name' => '小张', 'age' => 11],
			['id' => 3, 'name' => '小腾', 'age' => 16]
		]);
		$this->assertEquals($Student->getLastSql(), "select `id`,`name`,`age` from `student` where `id`in( '1' , '2' , '3' , '4' ) and (`id`= '2'  or (`id`= '3'  and `id`is not null))");

		$this->assertEquals($Student->newQuery()->select(['id', 'name', 'age'])
		                            ->whereIn('id', '1,2,3,4')
		                            ->andWhere(function($queryBuilder) {
			                            $queryBuilder->where('id', '2')
			                                         ->orWhere(function($re) {
				                                         $re->where('id', '3')->whereNotNull('id');
			                                         });
		                            })
		                            ->getAll(), [
			['id' => 2, 'name' => '小张', 'age' => 11],
			['id' => 3, 'name' => '小腾', 'age' => 16]
		]);
		$this->assertEquals($Student->getLastSql(), "select `id`,`name`,`age` from `student` where `id`in( '1' , '2' , '3' , '4' ) and (`id`= '2'  or (`id`= '3'  and `id`is not null))");


	}

	public function testSelect() {
		$model = static::$model;
		$arr = $model->newQuery()->where('sex', 1)->order('id')->limit(2)->getAll();
		$this->assertEquals(2, count($arr));
		$this->assertEquals(reset($arr)['sex'], 1, '注意类型');
		$this->assertLessThanOrEqual(reset($arr)['sex'], end($arr)['sex']);

		$arr = $model->where('age', '>', '999')->order('id', 'desc')->limit(1, 5)->getAll();
		$this->assertEquals(0, count($arr));
	}

	public function testHaving() {
		$this->assertEquals(static::$model->select(['id'])
		                                  ->whereBetween('id', ['1', '9'])
		                                  ->havingIn('id', ['1', '3'])
		                                  ->group('id')
		                                  ->getAllToSql(),
			"select `id` from `student` where `id`between '1' and '9'  group by `id` having `id`in( '1' , '3' )");

		$this->assertEquals(static::$model->select(['id'])
		                                  ->whereBetween('id', ['1', '9'])
		                                  ->havingIn('id', ['1', '3'])
		                                  ->group('id')
		                                  ->getAll(), [
			['id' => 1],
			['id' => 3]
		]);

		$this->assertEquals(static::$model->select(['id'])
		                                  ->whereBetween('id', ['1', '9'])
		                                  ->havingBetween('id', ['1', '3'])
		                                  ->group('id')
		                                  ->getAll(), [
			['id' => 1],
			['id' => 2],
			['id' => 3]
		]);

	}

	public function testWhereRaw(){
		$this->assertEquals(static::$model->newQuery()->select(['id'])
		                                  ->whereRaw('id between 1 and 9')
		                                  ->havingBetween('id', ['1', '3'])
		                                  ->group('id')
		                                  ->getAll(), [
			['id' => 1],
			['id' => 2],
			['id' => 3]
		]);
	}

	public function testTransaction() {
		$this->assertFalse(static::$model->transaction(function($obj) {
			$obj->where('id', '>=', "1")
			    ->where('ids', '<=', "256")
			    ->having('id', '<>', '256')
			    ->order('id', 'desc')
			    ->select('id')
			    ->group('id')
			    ->lock()
			    ->getRow();
		}, 3));

		$this->assertTrue(static::$model->transaction(function($obj) {
			$obj->where('id', '>=', "1")
			    ->where('id', '<=', "256")
			    ->having('id', '<>', '256')
			    ->order('id', 'desc')
			    ->select('id')
			    ->group('id')
			    ->lock()
			    ->getRow();
		}, 3));
	}

	public function testUnion() {
		$first = static::$model->select(['id', 'name', 'age'])->whereBetween('id', '1', '4');

		$res = static::$model::select(['id', 'name', 'age'])->whereBetween('id', '1', '2')->union(function($obj) {
			$obj->select(['id', 'name', 'age'])->whereBetween('id', '2', '3');
		})->unionAll($first->getAllToSql())->getAll();

		var_dump(static::$model->getLastSql());
		var_dump($res);exit;
	}

}



