<?php
/*
 * Stendhal website - a website to manage and ease playing of Stendhal game
 * Copyright (C) 2008-2023  The Arianne Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

interface Cache {
	/**
	 * stores a value in the cache
	 *
	 * @param $key    object key to access the value
	 * @param $value  object value to be stored
	 * @param $ttl    int optional time to live
	 * @return boolean caching successful?
	 */
	function store($key, $value, $ttl = 0);

	/**
	 * fetches a value from the cache
	 *
	 * @param $key     object key to access the value
	 * @param $success true, if the fetch was succesful
	 * @return mixed   value
	 */
	function fetch($key, &$success = false);

	/**
	 * fetches an array value from the cache that was previously converted into an ArrayObject
	 *
	 * @param $key     object key to access the value
	 * @param $success true, if the fetch was succesful
	 * @return mixed   value
	 */
	function fetchAsArray($key, &$success = false);

	/**
	 * clears the cache after an update
	 */
	function clearCacheIfOutdate();

	/**
	 * clears the cache
	 */
	function clear();
}

class APCCacheImpl implements Cache {
	private $keysToPurge = array('stendhal_creatures', 'stendhal_items', 'stendhal_npcs',
								'stendhal_item_classes', 'stendhal_creature_classes',
								'stendhal_pois', 'stendhal_zones', 'stendhal_shops');
	//TODO: stendhal_events_..., $stendhal_query_...

	function store($key, $value, $ttl = 0) {
		return apc_store($key, $value, $ttl);
	}

	function fetch($key, &$success = false) {
		return apc_fetch($key, $success);
	}

	function fetchAsArray($key, &$success = false) {
		$temp = $this->fetch($key, $success);
		if (isset($temp) && $temp !== false) {
			return $temp->getArrayCopy();
		}
		return null;
	}

	function isCacheInvalid() {
		$version = $this->fetch('stendhal_version');
		if (!defined('STENDHAL_VERSION')) {
			error_log('STENDHAL_VERSION undefined: '.$_SERVER['SCRIPT_URI']);
		} else {
			if ($version != STENDHAL_VERSION) {
				return true;
			}
		}
	}

	function clearCacheIfOutdate() {
		if ($this->isCacheInvalid()) {
			$this->clear();
		}
	}

	function clear() {
		foreach($this->keysToPurge as $key) {
			apc_delete($key);
		}
		$this->store('stendhal_version', STENDHAL_VERSION);
	}
}

class NonPersistentCacheImpl implements Cache {
	private $cache = array();

	function store($key, $value, $ttl = 0) {
		$this->cache[$key] = $value;
		return true;
	}

	function fetch($key, &$success = false) {
		if (isset($this->cache[$key])) {
			$success = true;
			return $this->cache[$key];
		} else {
			$success = false;
			return null;
		}
	}

	function fetchAsArray($key, &$success = false) {
		$temp = $this->fetch($key, $success);
		if (isset($temp) && $temp !== false) {
			return $temp->getArrayCopy();
		}
		return null;
	}

	function clearCacheIfOutdate() {
		// do nothing as the cache is not persistent
	}

	function clear() {
		// do nothing as the cache is not persistent
	}
}

if (function_exists('apc_store')) {
	$cache = new APCCacheImpl();
} else {
	$cache = new NonPersistentCacheImpl();
}
$cache->clearCacheIfOutdate();
