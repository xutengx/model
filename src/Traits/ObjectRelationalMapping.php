<?php

declare(strict_types = 1);
namespace Xutengx\Model\Traits;

use InvalidArgumentException;

/**
 * Trait ObjectRelationalMapping ORM
 * @package Xutengx\Model\Traits
 */
trait ObjectRelationalMapping {

	/**
	 * orm属性集合
	 * @var array
	 */
	public $orm = [];

	/**
	 * orm属性设置
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function __set(string $key, string $value): void {
		$this->orm[$key] = $value;
	}

	/**
	 * orm属性保存更新
	 * @param int $key 主键
	 * @return int 受影响的行数
	 */
	public function save(int $key = null): int {
		$param = [];
		foreach ($this->fields as $v) {
			if (array_key_exists($v['field'], $this->orm)) {
				$param[$v['field']] = $this->orm[$v['field']];
			}
		}
		if (is_null($key) && isset($this->orm[$this->primaryKey])) {
			$key = $this->orm[$this->primaryKey];
		}
		elseif (is_null($key))
			throw new InvalidArgumentException('model ORM save without the key');
		return $this->newQuery()->data($param)->where($this->primaryKey, $key)->update();
	}

	/**
	 * orm属性新增
	 * @param bool $insertGetId 返回上次插入的id
	 * @return int 受影响的行数or上次插入的id
	 */
	public function create(bool $insertGetId = false): int {
		$param = [];
		foreach ($this->fields as $v) {
			if (array_key_exists($v['field'], $this->orm)) {
				$param[$v['field']] = $this->orm[$v['field']];
			}
		}
		return $insertGetId ? $this->newQuery()->value($param)->insertGetId() :
			$this->newQuery()->value($param)->insert();
	}

}
