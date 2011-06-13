<?php

/**
 * Keeps a change history of any Model object.
 *
 * Usage:
 *
 *   // saving a new version
 *   Versions::add ($obj);
 *
 *   // getting the history of an object
 *   $history = Versions::history ($obj);
 *
 *   // get recent changes by current user
 *   global $user;
 *   $recent = Versions::recent ($user);
 *
 *   // compare current version of a web page to previous:
 *
 *   // 1. fetch the page
 *   $w = new Webpage ('index');
 *
 *   // 2. get the previous version (limit=1, offset=1)
 *   $history = Versions::history ($w, 1, 1);
 *
 *   // 3. restore the first result's object
 *   $w2 = Versions::restore ($history[0]);
 *
 *   // 4. compare the two
 *   $modified_fields = Versions::diff ($w, $w2);
 *
 * Fields:
 *
 * id - versions id
 * class - class name of the object
 * pkey - object's 'key' field value
 * user - user id or 0 if no user saved
 * ts - date/time of the change
 * serialized - serialized version of the object
 */
class Versions extends Model {
	/**
	 * Add a version to the store.
	 */
	function add ($obj) {
		global $user;
		$v = new Versions (array (
			'class' => get_class ($obj),
			'pkey' => $obj->{$obj->key},
			'user' => (! $user) ? 0 : $user->id,
			'ts' => gmdate ('Y-m-d H:i:s'),
			'serialized' => json_encode ($obj->data)
		));
		$v->put ();
		return $v;
	}

	/**
	 * Recreate an object from the stored version. Takes any
	 * result from recent() or history().
	 */
	function restore ($vobj = false) {
		if (! $vobj) {
			$vobj = $this;
		}
		$class = $vobj->class;
		$obj = new $class (json_decode ($vobj->serialized), false);
		return $obj;
	}

	/**
	 * Get recent versions by a user or everyone.
	 */
	function recent ($user = false, $limit = 10, $offset = 0) {
		$v = Versions::query ();
		if ($user) {
			$v->where ('user', $user);
		}
		return $v->order ('ts desc')
			->group ('class, pkey')
			->fetch_orig ($limit, $offset);
	}

	/**
	 * Get recent versions of an object, or of objects of a specific
	 * class.
	 */
	function history ($obj, $limit = 10, $offset = 0) {
		if ($limit === true) {
			if (is_string ($obj)) {
				return count (Versions::query ()
					->where ('class', $obj)
					->group ('pkey')
					->fetch_field ('pkey'));
			}
			return count (Versions::query ()
				->where ('class', get_class ($obj))
				->where ('pkey', $obj->{$obj->key})
				->fetch_field ('pkey'));
		}
		if (is_string ($obj)) {
			return Versions::query ()
				->where ('class', $obj)
				->order ('ts desc')
				->group ('pkey')
				->fetch_orig ($limit, $offset);
		}
		return Versions::query ()
			->where ('class', get_class ($obj))
			->where ('pkey', $obj->{$obj->key})
			->order ('ts desc')
			->fetch_orig ($limit, $offset);
	}

	/**
	 * Compare two versions of a Model object. Returns an array of properties
	 * that have changed between the two versions, but does no comparison
	 * of the changes themselves. Note that this looks at the data array
	 * of the Model objects, not object properties, so it will not work
	 * on ordinary objects, only Model-based objects and objects returned
	 * by the recent() and history() methods.
	 */
	function diff ($obj1, $obj2) {
		if (get_class ($obj1) == 'stdClass') {
			$obj1 = Versions::restore ($obj1);
		}
		if (get_class ($obj2) == 'stdClass') {
			$obj2 = Versions::restore ($obj2);
		}

		$changed = array ();
		foreach ($obj1->data as $key => $value) {
			if ($value !== $obj2->data[$key]) {
				$changed[] = $key;
			}
		}
		return $changed;
	}

	/**
	 * Get a list of classes that have objects stored.
	 */
	function get_classes () {
		return db_shift_array (
			'select distinct class from versions order by class asc'
		);
	}
}

?>