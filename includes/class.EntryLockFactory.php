<?php
namespace webaware\gf_dpspxpay;

if (!defined('ABSPATH')) {
	exit;
}

const OBJECT_CACHE_LOCK_GROUP = 'gfpxpay_locks';

/**
 * interface for an entry lock object
 */
interface IEntryLock {

	public function acquire() : bool;

	public function is_acquired() : bool;

	public function release();

}

/**
 * creator of an entry lock object, to suit available PHP extensions
 */
class EntryLockFactory {

	public static function create(string $id) : IEntryLock {
		// try to use a persistent object cache first
		if (wp_using_ext_object_cache()) {
			return new EntryLockObjectCache($id);
		}

		// fallback to the database
		return new EntryLockMySQL($id);
	}

}

/**
 * implementation for lock via a MySQL row
 */
class EntryLockMySQL implements IEntryLock {

	private string	$lock_id;
	private bool	$acquired = false;

	public function __construct(string $id) {
		$this->lock_id = 'gfpxpay_elock_' . $id;
	}

	public function acquire() : bool {
		global $wpdb;

		if ($this->acquired) {
			return true;
		}

		$sql = "
			insert ignore into $wpdb->options (option_name, option_value, autoload)
			values (%s, %s, 'no')
		";
		$rowcount = $wpdb->query($wpdb->prepare($sql, [$this->lock_id, time()]));

		$this->acquired = $rowcount > 0;
		return $this->acquired;
	}

	public function is_acquired() : bool {
		return $this->acquired;
	}

	public function release() {
		global $wpdb;

		if ($this->acquired) {
			$sql = "delete from $wpdb->options where option_name = %s";
			$wpdb->query($wpdb->prepare($sql, $this->lock_id));
			$this->acquired = false;
		}
	}

}

/**
 * implementation for lock via persistent object cache
 */
class EntryLockObjectCache implements IEntryLock {

	private string	$lock_id;
	private bool	$acquired = false;

	public function __construct(string $id) {
		$this->lock_id = 'gfpxpay_elock_' . $id;
	}

	public function acquire() : bool {
		if ($this->acquired) {
			return true;
		}

		$this->acquired = wp_cache_add($this->lock_id, time(), OBJECT_CACHE_LOCK_GROUP, MINUTE_IN_SECONDS * 30);

		return $this->acquired;
	}

	public function is_acquired() : bool {
		return $this->acquired;
	}

	public function release() {
		if ($this->acquired) {
			wp_cache_delete($this->lock_id, OBJECT_CACHE_LOCK_GROUP);
			$this->acquired = false;
		}
	}

}
