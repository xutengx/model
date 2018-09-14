<?php

declare(strict_types = 1);
namespace Xutengx\Model;

use PDOStatement;
use RuntimeException;
use Xutengx\Cache\Manager as Cache;
use Xutengx\Model\Component\QueryBuilder;
use Xutengx\Model\Connection\AbstractConnection;
use Xutengx\Model\Traits\{Debug, ObjectRelationalMapping, Transaction};

/**
 * Class Model
 * @package Xutengx\Model
 */
abstract class Model {

	use Debug, ObjectRelationalMapping, Transaction;

	/**
	 * 所有数据库连接类
	 * @var AbstractConnection[]
	 */
	protected static $connections;

	/**
	 * 已经完成类的初始化
	 * @var bool
	 */
	protected static $alreadyInitialized = false;

	/**
	 * 缓存对象
	 * @var Cache
	 */
	protected static $cache;

	/**
	 * 默认数据库连接
	 * @var string
	 */
	protected static $defaultConnection;
	/**
	 * 连接名以及对应的信息
	 * @var array
	 */
	protected static $connectionInfo;
	/**
	 * 表名
	 * @var string
	 */
	protected $table;
	/**
	 * 当前model的数据库连接类
	 * @var AbstractConnection
	 */
	protected $db;
	/**
	 * 主键的字段
	 * @var string
	 */
	protected $primaryKey;
	/**
	 * 表信息
	 * @var array
	 */
	protected $fields = [];
	/**
	 * 链式操作 sql
	 * @var string
	 */
	protected $lastSql;
	/**
	 * PDOStatement
	 * @var PDOStatement
	 */
	protected $PDOStatement;
	/**
	 * 主动指定数据库连接
	 * @var string
	 */
	protected $connection;

	/**
	 * Model constructor.
	 */
	final public function __construct() {
		if (self::$alreadyInitialized === false) {
			throw new RuntimeException(__CLASS__ . ' has not been initialized.');
		}
		// 确定数据表名
		$this->getModelCorrespondingTable();
		$this->getConnection();
		$this->getTableInfo();
	}

	/**
	 * 初始化类
	 * @param Cache $cache
	 * @param string $defaultConnection
	 */
	public static function init(Cache $cache, string $defaultConnection) {
		self::$cache              = $cache;
		self::$defaultConnection  = $defaultConnection;
		self::$alreadyInitialized = true;
	}

	/**
	 * 增加一个连接信息
	 * @param string $connectionName
	 * @param AbstractConnection $connection
	 */
	public static function addConnection(string $connectionName, AbstractConnection $connection) {
		self::$connections[$connectionName] = $connection;
	}

	/**
	 * 静态链式操作
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public static function __callStatic(string $method, array $parameters = []) {
		return (new static)->newQuery()->$method(...$parameters);
	}

	/**
	 * 对象链式操作
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call(string $method, array $parameters = []) {
		return $this->newQuery()->$method(...$parameters);
	}

	/**
	 * 返回一个查询构造器
	 * @param string $table 绑定表名
	 * @param string $primaryKey 主键名
	 * @return QueryBuilder
	 */
	public function newQuery(string $table = null, string $primaryKey = null): QueryBuilder {
		$queryTable      = $table ?? $this->table;
		$queryPrimaryKey = $primaryKey ?? $this->primaryKey;
		return new QueryBuilder($queryTable, $queryPrimaryKey, $this->db, $this);
	}

	/**
	 * 在 Model 中为 QueryBuilder 注册自定义链式方法
	 * 重载此方法
	 * @return array
	 */
	public function registerMethodForQueryBuilder(): array {
		return [];
	}

	/**
	 * 原生sql支持, 普通执行
	 * @param string $sql
	 * @param string $type 使用的数据库链接类型
	 * @return PDOStatement
	 */
	public function query(string $sql, string $type = 'update'): PDOStatement {
		$PDOStatement = $this->db->prepare($sql, $type);
		$PDOStatement->execute();
		return $PDOStatement;
	}

	/**
	 * 原生sql支持, 返回`PDOStatement`对象可用PDOStatement::execute($pars)重复调用
	 * @param string $sql
	 * @param string $type 使用的数据库链接类型
	 * @return PDOStatement
	 */
	public function prepare(string $sql, string $type = 'update'): PDOStatement {
		return $this->db->prepare($sql, $type);
	}

	/**
	 * 得到当前模型对应的数据表名
	 * @return void
	 */
	protected function getModelCorrespondingTable(): void {
		// 驼峰转下划线
		$humpToLine = function($camelCaps, $separator = '_') {
			return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
		};
		if (empty($this->table)) {
			$classNameArray = explode('\\', get_class($this));
			$this->table    = $humpToLine(strtr(end($classNameArray), ['Model' => '']));
		}
	}

	/**
	 * 获取数据库链接对象 $this->db
	 * @return void
	 */
	protected function getConnection(): void {
		if (empty(self::$connections))
			throw new RuntimeException("The connections is empty, please use `static::addConnection()` at first.");
		$this->connection = $this->connection ?? self::$defaultConnection;
		if (isset(self::$connections[$this->connection]))
			$this->db = self::$connections[$this->connection];
		else throw new RuntimeException("The connection[$this->connection] is not defined.");
	}

	/**
	 * 获取表字段信息, 填充主键
	 * @return void
	 */
	protected function getTableInfo(): void {
		$this->fields = self::$cache->remember(function() {
			return $this->db->getAll('SHOW COLUMNS FROM `' . $this->table . '`');
		}, 60);
		foreach ($this->fields as $v) {
			if ($v['extra'] === 'auto_increment') {
				$this->primaryKey = $v['field'];
				break;
			}
		}
	}

}
