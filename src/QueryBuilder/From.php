<?php

declare(strict_types = 1);
namespace Xutengx\Model\QueryBuilder;

use Xutengx\Model\Component\QueryBuilder;

/**
 * Trait From
 * @package Xutengx\Model\QueryBuilder
 */
trait From {

	protected $noFrom = false;

	/**
	 * 将一个from加入查询
	 * @param string $str
	 * @return QueryBuilder
	 */
	public function fromString(string $str): QueryBuilder {
		$this->from = '`' . $str . '`';
		return $this;
	}

	/**
	 * 将一个from加入查询
	 * @param string $str
	 * @return QueryBuilder
	 */
	public function fromRaw(string $str): QueryBuilder {
		$this->from = $str;
		return $this;
	}

	/**
	 * 设置不需要from片段
	 * 仅对 select 生效
	 * @return QueryBuilder
	 */
	public function noFrom(): QueryBuilder {
		$this->noFrom = true;
		return $this;
	}

}
