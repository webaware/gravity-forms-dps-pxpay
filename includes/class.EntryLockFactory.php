<?php
namespace webaware\gf_dpspxpay;

if (!defined('ABSPATH')) {
	exit;
}

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
