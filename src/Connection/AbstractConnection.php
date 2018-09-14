<?php

declare(strict_types = 1);
namespace Xutengx\Model\Connection;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Class AbstractConnection
 * @package Xutengx\Model
 */
abstract class AbstractConnection {

	/**
	 * 当前进程标识
	 * @var string
	 */
	protected $identification;
	/**
	 * 是否主从数据库
	 * @var bool
	 */
	protected $masterSlave = false;
	/**
	 * 数据库链接名称, 当抛出异常时帮助定位数据库链接
	 * @var string
	 */
	protected $connection;
	/**
	 * 数据库 读 连接集合
	 * @var array
	 */
	protected $dbRead = [];
	/**
	 * 数据库 读 权重
	 * @var array
	 */
	protected $dbReadWeight = [];
	/**
	 * 数据库 写 连接集合
	 * @var array
	 */
	protected $dbWrite = [];
	/**
	 * 数据库 写 权重
	 * @var array
	 */
	protected $dbWriteWeight = [];
	/**
	 * 当前操作类型 select update delate insert
	 * @var string
	 */
	protected $type = 'select';
	/**
	 * 是否事务过程中 不进行数据库更换
	 * @var bool
	 */
	protected $transaction = false;
	/**
	 * pdo初始化属性
	 * @var array
	 */
	protected $pdoAttr;
	/**
	 * 连接初始化sql
	 * @var array
	 */
	protected $initSql;

	/**
	 * AbstractConnection constructor.
	 * @param array $writeConnectionInfo 写连接信息二维数组 eg:[
	 * ['weight'=>5,'type'=>'mysql','host'=>'10.4.17.228','port'=>3306,'user'=>'root','pwd'=>'Huawei$123#_','db'=>'hk'],
	 * ['weight'=>10,'type'=>'mysql','host'=>'10.4.17.219','port'=>3306,'user'=>'root','pwd'=>'Huawei$123#_','db'=>'hk']
	 * ]
	 * @param array $readConnectionInfo 读连接信息二维数组
	 * @param array $pdoAttr
	 * @param array $initSql
	 */
	public function __construct(array $writeConnectionInfo, array $readConnectionInfo = [], array $pdoAttr = [
		PDO::MYSQL_ATTR_INIT_COMMAND       => "SET SESSION SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'",
		PDO::ATTR_TIMEOUT                  => 60,
		PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES         => false,
		PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
		PDO::ATTR_CASE                     => PDO::CASE_LOWER,
		PDO::ATTR_ORACLE_NULLS             => PDO::NULL_TO_STRING,
		PDO::ATTR_STRINGIFY_FETCHES        => false,
		PDO::ATTR_AUTOCOMMIT               => true,
	], array $initSql = [
		'SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ',
		'SET NAMES UTF8',
	]) {
		$this->confFormat($writeConnectionInfo, $this->dbWriteWeight, $this->dbWrite);
		if (!empty($readConnectionInfo)) {
			$this->masterSlave = true;
			$this->confFormat($readConnectionInfo, $this->dbReadWeight, $this->dbRead);
		}
		$this->pdoAttr = $pdoAttr;
		$this->initSql = $initSql;
	}

	/**
	 * 格式化配置文件, 引用赋值
	 * @param array $theConf 待格式化的配置数组
	 * @param array &$theDbWeight 权重数组
	 * @param array &$theDb 配置数组
	 */
	protected static function confFormat(array $theConf, array &$theDbWeight, array &$theDb): void {
		foreach ($theConf as $v) {
			$key         = md5(serialize($v));
			$theDb[$key] = $v;
			if (empty($theDbWeight))
				$theDbWeight[$v['weight']] = md5(serialize($v));
			else {
				$weight                                   = array_keys($theDbWeight);
				$theDbWeight[$v['weight'] + end($weight)] = $key;
			}
		}
	}

	/**
	 * 执行
	 * @param PDOStatement $PDOStatement
	 * @param array $bindings
	 * @return void
	 * @throws PDOException
	 */
	public function execute(PDOStatement $PDOStatement, array $bindings): void {
		$sql = $PDOStatement->queryString;
		try {
			// 执行一条预处理语句
			$PDOStatement->execute($bindings);
			// 普通 sql 记录
			$this->logInfo($sql, $bindings, true);
		} catch (PDOException $pdoException) {
			// 错误 sql 记录
			$this->logError($pdoException->getMessage(), $sql, $bindings, true);
			// 异常抛出
			throw $pdoException;
		}
	}

	/**
	 * 返回PDOStatement, 可做分块解析
	 * @param string $sql
	 * @param array $pars
	 * @return PDOStatement
	 */
	public function getChunk(string $sql, array $pars = []): PDOStatement {
		$this->type = 'select';
		return $this->prepareExecute($sql, $pars);
	}

	/**
	 * 查询一行
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @return array 一维数组
	 */
	public function getRow(string $sql, array $pars = []): array {
		$this->type = 'select';
		$re         = $this->prepareExecute($sql, $pars)->fetch(PDO::FETCH_ASSOC);
		return $re ? $re : [];
	}

	/**
	 * 查询多行
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @return array 二维数组
	 */
	public function getAll(string $sql, array $pars = []): array {
		$this->type = 'select';
		return $this->prepareExecute($sql, $pars)->fetchall(PDO::FETCH_ASSOC);
	}

	/**
	 * 更新数据, 返回受影响的行数
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @return int 受影响的行数
	 */
	public function update(string $sql, array $pars = []): int {
		$this->type = 'update';
		return $this->prepareExecute($sql, $pars)->rowCount();
	}

	/**
	 * 插入数据, 返回插入的主键
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @return string 插入的主键
	 */
	public function insertGetId(string $sql, array $pars = []): string {
		$this->type = 'insert';
		$res        = $this->prepareExecute($sql, $pars, true, $pdo)->rowCount();
		// mysql_insert_id函数返回的是储存在有AUTO_INCREMENT约束的字段的值.
		// 如果表中的字段不使用AUTO_INCREMENT约束，那么该函数不会返回你所存储的值，而是返回NULL或0
		if ($res)
			return $pdo->lastInsertId();
		throw new PDOException('Insert failed.');
	}

	/**
	 * 插入数据
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @return int 受影响的行数
	 */
	public function insert(string $sql, array $pars = []): int {
		$this->type = 'insert';
		return $this->prepareExecute($sql, $pars)->rowCount();
	}

	/**
	 * 使用PDO->prepare(), 返回的对象可用$res->execute($pars)重复调用
	 * @param string $sql
	 * @param string $type
	 * @return PDOStatement
	 */
	public function prepare(string $sql, string $type = 'update'): PDOStatement {
		if (!in_array($type, ['select', 'update', 'delete', 'insert', 'replace']))
			throw new InvalidArgumentException("The type mast in_array(select update delete insert replace). but [$type] given");
		$this->type = $type;
		return $this->prepareExecute($sql, [], false);
	}

	/**
	 * 开启事务
	 * @return bool
	 */
	public function begin(): bool {
		$this->transaction = true;
		$PDO               = $this->PDO();
		return $PDO->beginTransaction();
	}

	/**
	 * 提交事务
	 * @return bool
	 */
	public function commit(): bool {
		$this->transaction = false;
		$PDO               = $this->PDO();
		return $PDO->commit();
	}

	/**
	 * 是否在事务中
	 * @return bool
	 */
	public function inTransaction(): bool {
		return $this->transaction;
	}

	/**
	 * 回滚事务
	 * @return bool
	 */
	public function rollBack(): bool {
		$this->transaction = false;
		$PDO               = $this->PDO();
		return $PDO->rollBack();
	}

	/**
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call(string $method, array $parameters = []) {
		$this->type = 'update';
		return $this->PDO()->$method(...$parameters);
	}

	/**
	 * 根据权重, 实例化pdo
	 * @param array $theDbWeight 权重数组
	 * @param array $theDb 配置数组->pdo数组
	 * @return PDO
	 */
	protected function weightSelection(array $theDbWeight, array &$theDb): PDO {
		$tmp    = array_keys($theDbWeight);
		$weight = rand(1, end($tmp));
		foreach ($theDbWeight as $k => $v) {
			if ($k - $weight >= 0) {
				$key = $v;
				break;
			}
		}
		if (!is_object($theDb[$key])) {
			$settings    = $theDb[$key];
			$theDb[$key] = $this->newPdo($settings['type'], $settings['db'], $settings['host'],
				(string)$settings['port'], $settings['user'], $settings['pwd']);
		}
		return $theDb[$key];
	}

	/**
	 * pdo初始化属性
	 * 参考文档 https://www.cnblogs.com/Zender/p/8270833.html https://www.cnblogs.com/hf8051/p/4673030.html
	 * @param string $type
	 * @param string $db
	 * @param string $host
	 * @param string $port
	 * @param string $user
	 * @param string $pwd
	 * @return PDO
	 */
	protected function newPdo(string $type, string $db, string $host, string $port, string $user, string $pwd): PDO {
		$dsn = $type . ':dbname=' . $db . ';host=' . $host . ';port=' . $port;
		$pdo = new PDO($dsn, $user, $pwd, $this->pdoAttr);
		foreach ($this->initSql as $ini_sql) {
			$pdo->prepare($ini_sql)->execute();
		}
		return $pdo;
	}

	/**
	 * 普通 sql 记录
	 * @param string $sql
	 * @param array $bindings
	 * @param bool $manual
	 * @return bool
	 */
	abstract protected function logInfo(string $sql, array $bindings = [], bool $manual = false): bool;

	/**
	 * 错误 sql 记录
	 * @param string $msg
	 * @param string $sql
	 * @param array $bindings
	 * @param bool $manual
	 * @return bool
	 */
	abstract protected function logError(string $msg, string $sql, array $bindings = [], bool $manual = false): bool;

	/**
	 * 内部执行, 返回原始数据对象, 触发异常处理
	 * @param string $sql
	 * @param array $pars 参数绑定数组
	 * @param bool $auto 自动执行绑定
	 * @param null $PDO 用作`insertGetId`的return
	 * @return PDOStatement
	 */
	protected function prepareExecute(string $sql, array $pars = [], bool $auto = true, &$PDO = null): PDOStatement {
		try {
			// 链接数据库
			$PDO = $this->PDO();
			// 备要执行的SQL语句并返回一个 PDOStatement 对象
			$PDOStatement = $PDO->prepare($sql);
			if ($auto)
				// 执行一条预处理语句
				$PDOStatement->execute($pars);
			// 普通 sql 记录
			$this->logInfo($sql, $pars, !$auto);
			return $PDOStatement;
		} catch (PDOException $pdoException) {
			// 错误 sql 记录
			$this->logError($pdoException->getMessage(), $sql, $pars, !$auto);
			// 异常抛出
			throw $pdoException;
		}
	}

	/**
	 * 优先返回已存在的PDO对象
	 * @return PDO
	 */
	abstract protected function PDO(): PDO;

	/**
	 * 由操作类型(读/写)和权重(weight), 创建并返回PDO数据库连接
	 * @return PDO
	 */
	protected function connect(): PDO {
		// 查询操作且不属于事务,使用读连接
		return ($this->type === 'select' && !$this->transaction && $this->masterSlave) ?
			$this->weightSelection($this->dbReadWeight, $this->dbRead) :
			$this->weightSelection($this->dbWriteWeight, $this->dbWrite);

	}

}
