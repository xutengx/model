<?php

declare(strict_types = 1);
namespace Xutengx\Model\QueryBuilder;

use Xutengx\Model\Component\QueryBuilder;

/**
 * Trait Support
 * @package Xutengx\Model\QueryBuilder
 */
trait Support {

	/**
	 * 值加上括号
	 * @param string $value 字段 eg:1765595948
	 * @return string   eg:(1765595948)
	 */
	protected static function bracketFormat(string $value): string {
		return '(' . $value . ')';
	}

	/**
	 * 生成sql 包含占位符
	 * @param array $parameters 参数绑定, 在此处, 仅作记录sql作用
	 * @return string sql
	 */
	public function toSql(array $parameters = []): string {
		$remember = is_null($this->sqlType) ? false : true;
		$sql      = $this->makeSql();
		if ($remember)
			$this->rememberSql($sql, $parameters);
		return $sql;
	}

	/**
	 * 生成完成sql
	 * @param array $parameters
	 * @return string
	 */
	public function toCompleteSql(array $parameters = []): string{
		$sql      = $this->makeSql();
		foreach ($parameters as $k => $v) {
			$parameters[$k] = '\'' . $v . '\'';
		}
		return strtr($sql, $parameters);
	}

	/**
	 * 建立sql
	 * @return string
	 */
	protected function makeSql(): string {
		$sql = '';
		switch ($this->sqlType) {
			case 'select':
				$sql = 'select ' . $this->dealSelect() . $this->dealFromSelect();
				break;
			case 'update':
				$sql = 'update ' . $this->dealFrom() . ' set' . $this->dealData();
				break;
			case 'insert':
				$sql = 'insert into ' . $this->dealFrom() . $this->dealColumn() . ' values' . $this->dealValue();
				break;
			case 'replace':
				$sql = 'replace into ' . $this->dealFrom() . ' set' . $this->dealData();
				break;
			case 'delete':
				$sql = 'delete from ' . $this->dealFrom();
				break;
		}
		$sql .= $this->dealJoin() . $this->dealWhere() . $this->dealGroup() . $this->dealHaving() . $this->dealOrder() .
		        $this->dealLimit() . $this->dealLock();
		if (!empty($this->union)) {
			$sql = $this->bracketFormat($sql);
			foreach ($this->union as $type => $clauseArray) {
				foreach ($clauseArray as $clause)
					$sql .= $type . $this->bracketFormat($clause);
			}
		}
		return $sql;
	}
	/**
	 * 返回insert时,使用的字段名
	 * @return string
	 */
	protected function dealColumn(): string {
		return is_null($this->column) ? '' : ('(' . $this->column . ')');
	}

	/**
	 * 返回insert时,使用的值
	 * @return string
	 */
	protected function dealValue(): string {
		return '(' . implode('),(', $this->value) . ')';
	}

	/**
	 * 返回select部分
	 * @return string
	 */
	protected function dealSelect(): string {
		return is_null($this->select) ? '*' : $this->select;
	}

	/**
	 * 返回from部分 select 专用
	 * @return string
	 */
	protected function dealFromSelect(): string {
		if ($this->noFrom === true)
			return '';
		if (is_null($this->from)) {
			return ' from `' . $this->table . '`';
		}
		else {
			return ' from ' . $this->from;
		}
	}

	/**
	 * 返回from部分
	 * @return string
	 */
	protected function dealFrom(): string {
		return is_null($this->from) ? ('`' . $this->table . '`') : $this->from;
	}

	/**
	 * data
	 * @return string
	 */
	protected function dealData(): string {
		return is_null($this->data) ? '' : (' ' . $this->data);
	}

	/**
	 * join
	 * @return string
	 */
	protected function dealJoin(): string {
		return is_null($this->join) ? '' : (' ' . $this->join);
	}

	/**
	 * 返回where部分
	 * @return string
	 */
	protected function dealWhere(): string {
		if (is_null($this->where)) {
			return '';
		}
		else {
			// 子语句
			if (is_null($this->sqlType)) {
				return $this->where;
			}
			else {
				return ' where ' . $this->where;
			}
		}
	}

	/**
	 * group
	 * @return string
	 */
	protected function dealGroup(): string {
		return is_null($this->group) ? '' : (' group by ' . $this->group);
	}

	/**
	 * having
	 * @return string
	 */
	protected function dealHaving(): string {
		return is_null($this->having) ? '' : (' having ' . $this->having);
	}

	/**
	 * order
	 * @return string
	 */
	protected function dealOrder(): string {
		return is_null($this->order) ? '' : (' order by ' . $this->order);
	}

	/**
	 * limit
	 * @return string
	 */
	protected function dealLimit(): string {
		return is_null($this->limit) ? '' : (' limit ' . $this->limit);
	}

	/**
	 * lock
	 * @return string
	 */
	protected function dealLock(): string {
		return is_null($this->lock) ? '' : (' ' . $this->lock);
	}

	/**
	 * 记录最近次的sql, 完成参数绑定的填充
	 * 重载此方法可用作sql日志
	 * @param string $sql 拼接完成的sql
	 * @param array $pars 参数绑定数组
	 * @return void
	 */
	protected function rememberSql(string $sql, array $pars = []): void {
		foreach ($pars as $k => $v) {
			$pars[$k] = '\'' . $v . '\'';
		}
		$this->lastSql = strtr($sql, $pars);
		$this->model->setLastSql($this->lastSql);
	}

	/**
	 * 获取一个与自己主属性相同的全新实例, 不同于clone
	 * @return QueryBuilder
	 */
	protected function getSelf(): QueryBuilder {
		return new QueryBuilder($this->table, $this->primaryKey, $this->db, $this->model);
	}

	/**
	 * 给与sql片段两端空格
	 * @param string $part sql片段
	 * @return string
	 */
	protected function partFormat(string $part): string {
		return ' ' . trim($part) . ' ';
	}

	/**
	 * 给字段加上反引号
	 * @param string $field 字段 eg: sum(order.amount) as sum_price
	 * @return string eg: sum(`order`.`amount`) as `sum_price`
	 */
	protected function fieldFormat(string $field): string {
		if ($has_as = stristr($field, ' as ')) {
			$as        = substr($has_as, 0, 4);
			$info      = explode($as, $field);
			$alias     = ' as `' . end($info) . '`';
			$mayBeFunc = reset($info);
		}
		else {
			$alias     = '';
			$mayBeFunc = $field;    // eg: sum(order.amount)
		}
		if (($a = strstr($mayBeFunc, '('))) {   // eg: (order.amount)
			$action = str_replace($a, '', $mayBeFunc);  // eg: sum
			$a      = ltrim($a, '(');
			$a      = rtrim($a, ')');
			if (strstr($a, '.')) {
				$arr  = explode('.', $a);
				$temp = '`' . reset($arr) . '`.`' . end($arr) . '`';
			}
			else
				$temp = '`' . $a . '`';
			$temp = $action . '(' . $temp . ')';
		}
		else {
			if (strpos($mayBeFunc, '.') === false) {
				$temp = '`' . $mayBeFunc . '`';
			}
			else {
				$arr  = explode('.', $mayBeFunc);
				$temp = '`' . reset($arr) . '`.`' . end($arr) . '`';
			}
		}
		return $temp . $alias;
	}

	/**
	 * 将值转化为`绑定参数键`
	 * @param string $value
	 * @return string
	 */
	protected function valueFormat(string $value): string {
		$key                  = ':' . (string)self::$bindingCounter++;
		$this->bindings[$key] = $value;
		return ' ' . $key . ' ';
	}

	/**
	 * 将值转化为`绑定参数键`
	 * @param array $valueArray
	 * @return string
	 */
	protected function valueArrayFormat(array $valueArray): string {
		$str = '';
		foreach ($valueArray as $value) {
			$str .= $this->valueFormat((string)$value) . ',';
		}
		return rtrim($str, ',');
	}

}
