<?php

declare(strict_types = 1);
namespace Xutengx\Model\QueryBuilder;

use Xutengx\Model\Component\QueryPrepare;

/**
 * Trait Prepare
 * @package Xutengx\Model\QueryBuilder
 */
trait Prepare {

	/**
	 * 按照select预执行sql
	 * @return QueryPrepare
	 */
	public function selectPrepare(): QueryPrepare {
		return $this->forPrepare('select');
	}

	/**
	 * 按照insert预执行sql
	 * @return QueryPrepare
	 */
	public function insertPrepare(): QueryPrepare {
		return $this->forPrepare('insert');
	}

	/**
	 * 按照update预执行sql
	 * @return QueryPrepare
	 */
	public function updatePrepare(): QueryPrepare {
		return $this->forPrepare('update');
	}

	/**
	 * 按照delete预执行sql
	 * @return QueryPrepare
	 */
	public function deletePrepare(): QueryPrepare {
		return $this->forPrepare('delete');
	}

	/**
	 * 按照replace预执行sql
	 * @return QueryPrepare
	 */
	public function replacePrepare(): QueryPrepare {
		return $this->forPrepare('replace');
	}

	/**
	 * 预执行sql
	 * @param string $type
	 * @return QueryPrepare
	 */
	protected function forPrepare(string $type): QueryPrepare {
		$pars = $this->bindings;
		$this->sqlType	 = $type;
		$sql			 = $this->toSql($pars);
		$PDOStatement	 = $this->db->prepare($sql, $type);
		return new QueryPrepare($PDOStatement, $pars, $this->db);
	}

}
