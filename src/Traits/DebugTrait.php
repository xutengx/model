<?php

declare(strict_types = 1);
namespace Xutengx\Model\Traits;

/**
 * Trait DebugTrait
 * @package Xutengx\Model\Traits
 */
trait DebugTrait {

	/**
	 * 返回完整sql, 已执行sql
	 * @return string
	 */
	public function getLastSql(): string {
		return $this->lastSql;
	}

	/**
	 * 记录最近次执行的sql
	 * @param string $sql
	 * @return string
	 */
	public function setLastSql(string $sql): string {
		return $this->lastSql = $sql;
	}

}
