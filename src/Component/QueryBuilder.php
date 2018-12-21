<?php

declare(strict_types = 1);
namespace Xutengx\Model\Component;

use Closure;
use InvalidArgumentException;
use Xutengx\Model\Connection\AbstractConnection;
use Xutengx\Model\Model;
use Xutengx\Model\QueryBuilder\{Aggregates, Column, Data, Debug, Execute, From, Group, Having, Index, Join, Limit, Lock,
	Order, Prepare, Select, Special, Support, Union, Value, Where};

/**
 * Class QueryBuilder 查询构造器
 * @package Xutengx\Model\Component
 */
class QueryBuilder {

	use Support, Where, Select, Data, From, Join, Group, Order, Limit, Lock, Having, Index, Union, Prepare, Execute, Debug, Aggregates, Special, Value, Column;
	/**
	 * 自动绑定计数器
	 * @var int
	 */
	protected static $bindingCounter = 0;
	/**
	 * 绑定的表名
	 * @var string
	 */
	protected $table;
	/**
	 * 主键
	 * @var string
	 */
	protected $primaryKey;
	/**
	 * 当前语句类别
	 * @var string
	 */
	protected $sqlType;
	/**
	 * 数据库链接
	 * @var AbstractConnection
	 */
	protected $db;
	/**
	 * 所属模型
	 * @var Model
	 */
	protected $model;
	/**
	 * 最近次执行的sql
	 * @var string
	 */
	protected $lastSql;
	protected $select;
	protected $data;
	protected $column;
	protected $value = [];
	protected $from;
	protected $where;
	protected $join;
	protected $group;
	protected $having;
	protected $order;
	protected $limit;
	protected $lock;
	protected $union = [];
	/**
	 * 自动绑定数组
	 * @var array
	 */
	protected $bindings = [];
	/**
	 * 预期的查询2维数组的索引
	 * @var string
	 */
	protected $index;
	/**
	 * Model 中为 QueryBuilder 注册de自定义链式方法
	 * @var array
	 */
	protected $registerMethodFromModel = [];

	/**
	 * QueryBuilder constructor.
	 * @param string $table
	 * @param string $primaryKey
	 * @param AbstractConnection $db
	 * @param Model $model
	 */
	public function __construct(string $table, ?string $primaryKey, AbstractConnection $db, Model $model) {
		$this->table      = $table;
		$this->primaryKey = $primaryKey;
		$this->db         = $db;
		$this->model      = $model;

		$this->registerMethod();
	}

	/**
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function column(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				switch (gettype($params[0])) {
					case 'array':
						return $this->columnArray(...$params);
					case 'string':
						return $this->columnString(...$params);
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 插入数据
	 * @param array $arr
	 * @return QueryBuilder
	 */
	public function value(array $arr): QueryBuilder {
		return ((count($arr) !== count($arr, 1)) || is_array(reset($arr))) ? $this->valueTwoDimensionalArray($arr) :
			$this->valueOneDimensionalArray($arr);
	}

	/**
	 * 查询条件
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function where(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				switch (gettype($params[0])) {
					case 'object':
						return $this->andWhere(...$params);
					case 'array':
						return $this->whereArray(...$params);
					default :
						return $this->whereRaw((string)$params[0]);
				}
			case 2:
				return $this->whereValue((string)$params[0], '=', (string)$params[1]);
			case 3:
				return $this->whereValue((string)$params[0], (string)$params[1], (string)$params[2]);
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 字段值在范围内
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function whereIn(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 2:
				switch (gettype($params[1])) {
					case 'array':
						return $this->whereInArray(...$params);
					default :
						return $this->whereInString(...$params);
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 字段值不在范围内
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function whereNotIn(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 2:
				switch (gettype($params[1])) {
					case 'array':
						return $this->whereNotInArray(...$params);
					default :
						return $this->whereNotInString(...$params);
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 字段值在2值之间
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function whereBetween(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 2:
				return $this->whereBetweenArray(...$params);
			case 3:
				return $this->whereBetweenString((string)$params[0], (string)$params[1], (string)$params[2]);
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 字段值不在2值之间
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function whereNotBetween(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 2:
				return $this->whereNotBetweenArray(...$params);
			case 3:
				return $this->whereNotBetweenString((string)$params[0], (string)$params[1], (string)$params[2]);
		}
		throw new InvalidArgumentException;
	}

	/**
	 * where exists
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function whereExists(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				switch (gettype($obj = reset($params))) {
					case 'object':
						return $this->whereExistsClosure($obj);
					case 'string':
						return $this->whereExistsRaw($obj);
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * where not exists
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function whereNotExists(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				switch (gettype($obj = reset($params))) {
					case 'object':
						return $this->whereNotExistsClosure($obj);
					case 'string':
						return $this->whereNotExistsRaw($obj);
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * where 子查询
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function whereSubQuery(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 3:
				switch (gettype($obj = end($params))) {
					case 'object':
						if ($obj instanceof Closure) {
							return $this->whereSubQueryClosure(...$params);
						}
						elseif ($obj instanceof QueryBuilder) {
							return $this->whereSubQueryQueryBuilder(...$params);
						}
						throw new InvalidArgumentException;
					case 'string':
						return $this->whereSubQueryRaw(...$params);
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * having条件
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function having(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				switch (gettype($params[0])) {
					case 'object':
						return $this->andHaving(...$params);
					case 'array':
						return $this->havingArray(...$params);
					default :
						return $this->havingRaw('1');
				}
			case 2:
				return $this->havingValue((string)$params[0], '=', (string)$params[1]);
			case 3:
				return $this->havingValue((string)$params[0], (string)$params[1], (string)$params[2]);
		}
		throw new InvalidArgumentException;
	}

	/**
	 * having字段值在范围内
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function havingIn(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 2:
				switch (gettype($params[1])) {
					case 'array':
						return $this->havingInArray(...$params);
					default :
						return $this->havingInString(...$params);
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * having字段值不在范围内
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function havingNotIn(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 2:
				switch (gettype($params[1])) {
					case 'array':
						return $this->havingNotInArray(...$params);
					default :
						return $this->havingNotInString(...$params);
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * having字段值在2值之间
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function havingBetween(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 2:
				return $this->havingBetweenArray(...$params);
			case 3:
				return $this->havingBetweenString((string)$params[0], (string)$params[1], (string)$params[2]);
		}
		throw new InvalidArgumentException;
	}

	/**
	 * having字段值不在2值之间
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function havingNotBetween(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 2:
				return $this->havingNotBetweenArray(...$params);
			case 3:
				return $this->havingNotBetweenString((string)$params[0], (string)$params[1], (string)$params[2]);
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 左链接
	 * @param string $table
	 * @param string $fieldOne
	 * @param string $symbol
	 * @param string $fieldTwo
	 * @return QueryBuilder
	 */
	public function leftJoin(string $table, string $fieldOne, string $symbol, string $fieldTwo): QueryBuilder {
		return $this->joinString($table, $fieldOne, $symbol, $fieldTwo, 'left join');
	}

	/**
	 * 右链接
	 * @param string $table
	 * @param string $fieldOne
	 * @param string $symbol
	 * @param string $fieldTwo
	 * @return QueryBuilder
	 */
	public function rightJoin(string $table, string $fieldOne, string $symbol, string $fieldTwo): QueryBuilder {
		return $this->joinString($table, $fieldOne, $symbol, $fieldTwo, 'right join');
	}

	/**
	 * 内链接
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function join(...$params): QueryBuilder {
		return $this->joinString(...$params);
	}

	/**
	 * 自定义二维数组的键
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function index(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				switch (gettype(reset($params))) {
					case 'string':
						return $this->indexString(...$params);
					case 'object':
						return $this->indexClosure(...$params);
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 查询字段
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function select(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				switch (gettype(reset($params))) {
					case 'array':
						return $this->selectArray(...$params);
					case 'string':
						return $this->selectString(...$params);
				}
				throw new InvalidArgumentException;
			case 2:
				return $this->selectFunction(...$params);
			case 3:
				return $this->selectFunction(...$params);
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 更新字段
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function data(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				return $this->dataArray(...$params);
			case 2:
				return $this->dataString(...$params);
		}
		throw new InvalidArgumentException;
	}

	/**
	 * from数据表
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function from(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				return $this->fromString(...$params);
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 设置数据表
	 * @param string $table
	 * @return QueryBuilder
	 */
	public function table(string $table): QueryBuilder {
		$this->table = $table;
		return $this;
	}

	/**
	 * 分组
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function group(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				switch (gettype(reset($params))) {
					case 'array':
						return $this->groupArray(...$params);
					case 'string':
						return $this->groupString(...$params);
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 排序
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function order(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				return $this->orderString(...$params);
			case 2:
				return $this->orderString(...$params);
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 限制
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function limit(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				return $this->limitTake(...$params);
			case 2:
				return $this->limitOffsetTake(...$params);
		}
		throw new InvalidArgumentException;
	}

	/**
	 * union 联合查询
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function union(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				switch (gettype($obj = reset($params))) {
					case 'object':
						return $this->unionClosure($obj);
					case 'string':
						return $this->unionRaw(...$params);
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * union all 联合查询
	 * @param mixed ...$params
	 * @return QueryBuilder
	 */
	public function unionAll(...$params): QueryBuilder {
		switch (func_num_args()) {
			case 1:
				switch (gettype($obj = reset($params))) {
					case 'object':
						if ($obj instanceof Closure) {
							return $this->unionClosure($obj, 'union all');
						}
						elseif ($obj instanceof QueryBuilder) {
							return $this->unionQueryBuilder($obj, 'union all');
						}
						throw new InvalidArgumentException;
					case 'string':
						return $this->unionRaw($obj, 'union all');
				}
		}
		throw new InvalidArgumentException;
	}

	/**
	 * 排他锁
	 * @return QueryBuilder
	 */
	public function lock(): QueryBuilder {
		return $this->lockForUpdate();
	}

	/**
	 * 执行自定义方法
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call(string $method, array $args = []) {
		if (isset($this->registerMethodFromModel[$method])) {
			return $this->registerMethodFromModel[$method](...$args);
		}
		else
			throw new InvalidArgumentException('Undefined method [ ' . $method . ' ].');
	}

	/**
	 * 在 Model 中为 QueryBuilder 注册自定义链式方法
	 * @return void
	 */
	protected function registerMethod(): void {
		foreach ($this->model->registerMethodForQueryBuilder() as $methodName => $func) {
			if (isset($this->$methodName) || isset($this->registerMethodFromModel[$methodName]) ||
			    method_exists($this, $methodName))
				throw new InvalidArgumentException('The method name [ ' . $methodName . ' ] is already used .');
			elseif ($func instanceof Closure) {
				$this->registerMethodFromModel[$methodName] = function(...$params) use ($func) {
					return $func($this, ...$params);
				};
			}
			else
				throw new InvalidArgumentException('The method [ ' . $methodName . ' ] mast instanceof Closure .');
		}
	}

}
