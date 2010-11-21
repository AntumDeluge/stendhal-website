<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2010  Stendhal

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class APIPage extends Page {

	public function writeHttpHeader() {
		header("Content-Type: text/javascript", true);
		if ($_REQUEST['method'] == 'isNameAvailable') {
			$this->isNameAvailable($_REQUEST['param']);
		} else {
			$this->unknown($_REQUEST['param']);
		}
		return false;
	}

	/**
	 * checks if a name is available for account or character creation
	 *
	 * @param $name account/character name to check
	 */
	public function isNameAvailable($name) {
		$res = array();
		$res['name'] = $name;
		$res['result'] = Account::isNameAvailable($name);
		echo json_encode($res);
	}

	/**
	 * returns an error response because the method is not known
	 *
	 * @param $param ignored
	 */
	public function unknown($param) {
		header('HTTP/1.1', true, 400); // TODO
		echo 'throw new Exception("Unknown method")';
	}
}
$page = new APIPage();
?>