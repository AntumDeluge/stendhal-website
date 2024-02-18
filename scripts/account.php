<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin
 Copyright (C) 2008-2024 The Arianne Project

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



/**
 * checkLogin - Checks if the user has already previously
 * logged in, and a session with the user has already been
 * established. Returns true if the user has logged in.
 */
function checkLogin(){
	return isset($_SESSION) && isset($_SESSION['account']);
}


function getAdminLevel() {
	if(!checkLogin()) {
		return -1;
	}

	$sql = "select max(admin) As adminlevel FROM character_stats, characters, account "
		. " WHERE character_stats.name=characters.charname AND characters.player_id=account.id "
		. " AND account.username='".mysql_real_escape_string($_SESSION['account']->username)."'";
	return DB::game()->query($sql)->fetchColumn();
}


// Returns user id for username or false
function getUserID($username) {
	$sql = "SELECT id FROM account WHERE username = '".
		mysql_real_escape_string($username)."'";

	$stmt = DB::game()->query($sql);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();
	if (!$row) {
		// Couldn't find the userid or DB failure
		return false;
	}

	return $row['id'];
}




/**
 * verifies that thae specified characer belongs to the specified account
 *
 * @param string $username name of account
 * @param string $charname name of character
 * @return boolean
 */
function verifyCharacterBelongsToUsername($username, $charname) {
	$sql = "SELECT player_id "
	. "FROM account, characters "
	. "WHERE account.username='".mysql_real_escape_string($username)
	. "' AND account.id=characters.player_id "
	. "AND characters.charname='".mysql_real_escape_string($charname)."'";
	return DB::game()->query($sql)->rowCount() > 0;
}

/**
 * checks if a character exist
 *
 * @param string $username name of account
 * @return boolean
 */
function doesCharacterExist($charname) {
	$sql = "SELECT player_id "
	. "FROM characters "
	. "WHERE characters.charname='".mysql_real_escape_string($charname)."'";
	return DB::game()->query($sql)->rowCount() > 0;
}

function storeSeed($username, $ip, $seed, $authenticated) {
	$query = 'INSERT INTO loginseed(player_id, address, seed, complete, used)'
	." SELECT id, '".mysql_real_escape_string($ip)."', '".mysql_real_escape_string($seed)."', '"
	.mysql_real_escape_string($authenticated)."', 0 FROM account WHERE username='".mysql_real_escape_string($username)."'";

	DB::game()->exec($query);
}


/**
 * merges two accounts
 *
 * @param string $oldUsername the account to merge in
 * @param string $newUsername the account to keep
 */
function mergeAccount($oldUsername, $newUsername) {
	$oldAccountId = getUserID($oldUsername);
	$newAccountId = getUserID($newUsername);
	
	if ($oldAccountId == $newAccountId) {
		return;
	}

	DB::game()->exec("UPDATE account SET status='merged' WHERE id='".mysql_real_escape_string($oldAccountId)."'");
	DB::game()->exec("UPDATE account SET username=concat('merged.".time().".', username) WHERE id='".mysql_real_escape_string($oldAccountId)."' AND (password='' OR password IS NULL)");
	$sql = "SELECT charname FROM characters WHERE player_id='".mysql_real_escape_string($oldAccountId)."'";
	$rows = DB::game()->query($sql);
	$chars = array();
	foreach($rows as $row) {
		$chars[] = $row['charname'];
		PlayerLoginEntry::logAccountMerge($row['charname'], $oldAccountId, $oldUsername, $newUsername);
	}
	sendUdpMessage('stendhal', $oldUsername . ' was merged into ' . $newUsername. ' (' . implode(', ', $chars) . ')');
	DB::game()->exec("UPDATE characters SET player_id='".mysql_real_escape_string($newAccountId)."' WHERE player_id='".mysql_real_escape_string($oldAccountId)."'");
	DB::game()->exec("UPDATE accountLink SET player_id='".mysql_real_escape_string($newAccountId)."' WHERE player_id='".mysql_real_escape_string($oldAccountId)."'");
}


/**
 * adds an account link (like an openid)
 *
 * @param $username   string the name of the logged in user to add the link to
 * @param $identifier string the openid identifier
 * @param $type       string "openid" or "connect"
 * @param $email      string email address provided by the identity provider
 * @param $nickname   string nick name
 */
function addAccountLink($username, $type, $identifier, $email, $nickname) {
	$accountId = getUserID($username);
	$sql = "INSERT INTO accountLink (player_id, type, username, nickname, email) VALUES ('"
	. mysql_real_escape_string($accountId)."', '"
	. mysql_real_escape_string($type)."', '"
	. mysql_real_escape_string($identifier)."', '"
	. mysql_real_escape_string($email)."', '"
	. mysql_real_escape_string($nickname)."');";
	DB::game()->exec($sql);
}

/**
 * dels an account link (like an openid)
 *
 * @param $username   string the name of the logged in user to add the link to
 * @param $identifier string the openid identifier
 * @param $type       string "openid" or "connect"
 */
function delAccountLink($username, $type, $identifier) {
	$accountId = getUserID($username);
	$sql = "DELETE FROM accountLink WHERE player_id='"
		. mysql_real_escape_string($accountId)."' AND type='"
		. mysql_real_escape_string($type)."' AND username='"
		. mysql_real_escape_string($identifier)."';";
	DB::game()->exec($sql);
}


/**
 * A class that represent a player history entry
 */
class PlayerLoginEntry {
	/* date and time of event */
	public $timedate;
	/* name of ip-address */
	public $address;
	/* name of service */
	public $service;
	/* type of event */
	public $eventType;
	/* success */
	public $success;

	function __construct($timedate, $address, $service, $eventType, $success) {
		$this->timedate = $timedate;
		$this->address = $address;
		$this->service = $service;
		$this->eventType = $eventType;
		$this->success = $success;
	}


	/**
	 * gets a list of recent login events for that player
	 */
	public static function getLoginHistory($playerId) {
		$sql = "SELECT address, timedate, service, event, result FROM "
		. "(SELECT address, timedate, service, 'login' As event, result FROM loginEvent "
		. "WHERE player_id=".mysql_real_escape_string($playerId)." AND timedate > DATE_SUB(CURDATE(),INTERVAL 7 DAY) "
		. "UNION SELECT address, timedate, service, 'password change' As event, result FROM passwordChange "
		. "WHERE player_id=".mysql_real_escape_string($playerId)." AND timedate > DATE_SUB(CURDATE(),INTERVAL 7 DAY)) As data "
		. "ORDER BY timedate DESC LIMIT 1000;";

		$list=array();
		$rows = DB::game()->query($sql);
		foreach($rows as $row) {
			$list[] = new PlayerLoginEntry($row['timedate'],
			$row['address'], $row['service'], $row['event'],$row['result']);
		}
		return $list;
	}

	/**
	 * log password changes for user from ip
	 * returns boolean successfully logged
	 */
	public static function logUserPasswordChange($user, $ip, $oldpass, $result) {
		$userid = getUserID($user);

		if ( $userid === false) {
			return $userid = -1;
		}

		$q = "INSERT INTO passwordChange (player_id, address, oldpassword, service, result)".
			" values (".$userid.", '".mysql_real_escape_string(trim($ip))."', '".mysql_real_escape_string($oldpass)."', 'website', ".intval($result).")";
		return DB::game()->exec($q);
	}

	/**
	 * log logins for user from ip
	 * returns boolean successfully logged
	 */
	public static function logUserLogin($service, $user, $ip, $accountLink, $success) {
		$userid = getUserID($user);

		if ( $userid === false ) {
			$userid = -1;
		}

		$q = "INSERT INTO loginEvent (player_id, address, result, service";
		if ($accountLink) {
			$q = $q .', account_link_id';
		}
		$q = $q . ") values (".$userid.", '".mysql_real_escape_string(trim($ip))." ',".intval($success).", '"
			. mysql_real_escape_string($service)."'";
		if ($accountLink) {
			$q = $q . ", '".mysql_real_escape_string($accountLink)."'";
		}
		$q = $q . ")";

		$res = DB::game()->exec($q);
		return $res;
	}

	public static function logAccountMerge($character, $oldAccountId, $oldUsername, $newUsername) {
		$q = "INSERT INTO gameEvents (source, event, param1, param2) values ".
			"('".mysql_real_escape_string($character)."', 'accountmerge', '".mysql_real_escape_string($oldAccountId)."', '"
			.mysql_real_escape_string($oldUsername). "-->". mysql_real_escape_string($newUsername) ."')";
		return DB::game()->exec($q);
	}
}


/**
 * A class that represents a StoredMessage
 */
class StoredMessage {
	/* id */
	public $id;
	/* source of message (who sent it) */
	public $source;
	/* target of message (who it was sent to) */
	public $target;
	/* date and time of event */
	public $timedate;
	/* content of message */
	public $message;
	/* type of message: S (Support); P (player); N (NPC)  */
	public $messageType;
	/* whether it was delivered */
	public $delivered;

	function __construct($id, $source, $target, $timedate, $message, $messageType, $delivered) {
		$this->id = $id;
		$this->source = $source;
		$this->target = $target;
		$this->timedate = $timedate;
		$this->message = $message;
		$this->messageType = $messageType;
		$this->delivered = $delivered;
	}

	/**
	 * gets a list of recent messages for that player
	 */
	public static function getCountUndeliveredMessages($playerId, $where) {
		$sql = "SELECT count(*) as count "
		. " FROM postman , characters "
		. " WHERE " . $where
		. " AND characters.player_id=".mysql_real_escape_string($playerId)
		. " AND delivered = 0;";
		return DB::game()->query($sql)->fetchColumn();
	}

	/**
	 * gets a list of recent messages for that player
	 */
	public static function getStoredMessages($playerId, $where) {
		$sql = "SELECT postman.id, postman.source, postman.target, postman.timedate, postman.message, postman.messageType, postman.delivered "
		. " FROM postman , characters "
		. " WHERE " . $where
		. " AND  characters.player_id=".mysql_real_escape_string($playerId)
		. " AND postman.timedate > DATE_SUB(CURDATE(),INTERVAL 3 MONTH) "
		. " ORDER BY postman.timedate DESC LIMIT 100;";
		// echo $sql;
		$rows = DB::game()->query($sql);
		$list=array();

		foreach($rows as $row) {
			$list[] = new StoredMessage($row['id'], $row['source'], $row['target'], $row['timedate'],
			$row['message'], $row['messageType'], $row['delivered']);
		}
		return $list;
	}

	/**
	 * deleted messages sent by the player
	 *
	 * @param $playerId int   id of player
	 * @param $ids      array id of messages to delete (need to be sql escaped)
	 */
	public static function deleteSentMessages($playerId, $ids) {
		$sql = "DELETE FROM postman USING postman, characters WHERE characters.player_id='".mysql_real_escape_string($playerId)
			."' AND characters.charname=postman.source AND (postman.deleted='R') AND postman.id IN (".$ids.")";
		DB::game()->exec($sql);

		$sql = "UPDATE postman, characters SET postman.deleted='S' WHERE characters.player_id='".mysql_real_escape_string($playerId)
			."' AND characters.charname=postman.source AND postman.id IN (".$ids.")";
		DB::game()->exec($sql);
	}


	/**
	 * deleted messages received by the player
	 *
	 * @param $playerId int   id of player
	 * @param $ids      array id of messages to delete (need to be sql escaped)
	 */
		public static function deleteReceivedMessages($playerId, $ids) {
			$sql = "DELETE FROM postman USING postman, characters WHERE characters.player_id='".mysql_real_escape_string($playerId)
				."' AND characters.charname=postman.target AND (postman.deleted='S' OR postman.messagetype='N') AND postman.id IN (".$ids.")";
			DB::game()->exec($sql);

			$sql = "UPDATE postman, characters SET postman.deleted='R' WHERE characters.player_id='".mysql_real_escape_string($playerId)
				."' AND characters.charname=postman.target AND postman.id IN (".$ids.")";
			DB::game()->exec($sql);
	}
}



/**
 * Account
 *
 * @author hendrik
 */
class Account {
	public $id;
	public $username;
	public $password;
	public $email;
	public $emailTrusted;
	public $timedate;
	public $status;
	public $banMessage;
	public $banExpire;
	public $links;
	public $usedAccountLink;
	public $usedLoginseed;
	
	public function __construct($id, $username, $password, $email, $emailTrusted, $timedate, $status) {
		$this->id = $id;
		$this->username = $username;
		$this->password = $password;
		$this->email = $email;
		$this->emailTrusted = $emailTrusted;
		$this->timedate = $timedate;
		$this->status = $status;
	}

	public static function tryLogin($type, $username, $password, $accountLink) {
		$service = 'website';
		$usedAccountLink = null;

		if (!Account::checkIpBan()) {
			return "Your IP Address has been banned.";
		}

		// ask database
		if ($type == 'password' || $type == 'passwordchange') {
			$banMessage = Account::checkBlocked($username, $_SERVER['REMOTE_ADDR']);
			if ($banMessage != null) {
				$success = 4;
			} else {
				$account = Account::readAccountByName($username);

				if ($account->status === "deleted") {
					return "This account name is reserved. Accounts names will be reserved if they are missleading (e. g. the name might indicate an admin status) or if an account with that name existed in the past.";
				}

				if (isset($account)) {
					$success = $account->checkPassword($password);
					if ($success == 0) {
						$banMessage = "Invalid username or password";
					}
				} else {
					$success = 0;
					$banMessage = "Invalid username or password";
				}
			}
		} else if ($type === 'loginseed') {
			$account = Account::readAccountByLoginseed($password);
			if ($account) {
				Account::useUpLoginseed($account->usedLoginseed);
				$success = 1;
			}
			$usedAccountLink = -1;
			$service = 'Stendhal App';
		} else {
			$account = Account::readAccountByLink($type, $username, $password);
			if (!$account || is_string($account)) {
				$account = $accountLink->createAccount();
			}
			$success = 1;
		}

		if ($success == 1) {
			$account->readAccountBan();
			$banMessage = $account->getAccountStatusMessage();
			$success = $account->getAccountStatusCode();

			$username = $account->username;
			$passhash = $account->password;
			// accountLink-parameter might not have an id
			$usedAccountLink = $account->usedAccountLink;
		}
		// Log loginEvent or passwordChange
		if ($type != 'passwordchange') {
		    PlayerLoginEntry::logUserLogin($service, $username, $_SERVER['REMOTE_ADDR'], $usedAccountLink, $success);
		} else {
			PlayerLoginEntry::logUserPasswordChange($username, $_SERVER['REMOTE_ADDR'], $passhash, $success);
		}

		// if the account is banned
		if (isset($banMessage)) {
			return $banMessage;
		}
		return $account;
	}

	private static function checkIpBan() {
		// TODO: implement me
		return true;
	}

	/**
	 * reads an account object based on the account id.
	 *
	 * @param string $username username
	 */
	public static function readAccountById($id) {
		$sql = "SELECT account.id, username, password, email.email, account.timedate, account.status "
		. " FROM account LEFT JOIN email ON email.player_id=account.id "
		. " WHERE id=".((int) $id);
		$stmt = DB::game()->query($sql);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		if ($row) {
			$res = new Account($row['id'], $row['username'], $row['password'], $row['email'], false, $row['timedate'], $row['status']);
		}
		return $res;
	}

	/**
	 * reads an account object based on the username.
	 *
	 * @param string $username username
	 */
	public static function readAccountByName($username) {
		$sql = "SELECT account.id, username, password, email.email, account.timedate, account.status "
			. " FROM account LEFT JOIN email ON email.player_id=account.id "
			. " WHERE username='".mysql_real_escape_string($username)."'";
		$stmt = DB::game()->query($sql);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		if ($row) {
			$res = new Account($row['id'], $row['username'], $row['password'], $row['email'], false, $row['timedate'], $row['status']);
		return $res;
		}
		return null;
	}

	/**
	 * reads an account object from the database based on an account link.
	 *
	 * @param string $type "openid", "facebook"
	 * @param string $username identifier (e. g. openid url)
	 * @param string $password an optional secret
	 */
	public static function readAccountByLink($type, $username, $password) {
		$sql = "SELECT account.id As id, account.username As username, "
			. " account.password As password, email.email As email, "
			. " account.timedate As timedate, account.status As status, "
			. " accountLink.id As usedAccountLink"
			. " FROM accountLink INNER JOIN account ON (account.id = accountLink.player_id) "
			. " LEFT JOIN email ON (account.id = email.player_id) "
			. " WHERE accountLink.type='".mysql_real_escape_string($type)."'"
			. " AND accountLink.username='".mysql_real_escape_string($username)."'";
		if (isset($password)) {
			$sql = $sql . " AND accountLink.secret='".mysql_real_escape_string($password)."'";
		} else {
			$sql = $sql . " AND accountLink.secret IS NULL";
		}

		$stmt = DB::game()->query($sql);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$res = NULL;
		if ($row) {
			$res = new Account($row['id'], $row['username'], $row['password'], $row['email'], false, $row['timedate'], $row['status']);
			$res->usedAccountLink = $row['usedAccountLink'];
		}
		return $res;
	}

	/**
	 * reads an account object from the database based on a loginseed.
	 *
	 * @param string $loginseed loginseed
	 */
	public static function readAccountByLoginseed($loginseed) {
		$sql = "SELECT account.id As id, account.username As username, "
			. " account.password As password, email.email As email, "
			. " account.timedate As timedate, account.status As status, "
			. " loginseed.id As usedLoginseed"
			. " FROM loginseed INNER JOIN account ON (account.id = loginseed.player_id) "
			. " LEFT JOIN email ON (account.id = email.player_id) "
			. " WHERE loginseed.seed='".mysql_real_escape_string($loginseed)."'"
			. " AND loginseed.used=0"
			. " AND loginseed.timedate > date_sub(current_timestamp, interval 1 day)";

			$stmt = DB::game()->query($sql);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			$res = NULL;
			if ($row) {
				$res = new Account($row['id'], $row['username'], $row['password'], $row['email'], false, $row['timedate'], $row['status']);
				$res->usedAccountLink = -1;
				$res->usedLoginseed = $row['usedLoginseed'];
			}
			return $res;
	}

	/**
	 * marks an loginseed as used
	 * 
	 * @param string $loginseedId id of loginseed
	 */
	public static function useUpLoginseed($loginseedId) {
		$sql = "UPDATE loginseed SET used = used+1"
			. " WHERE loginseed.id=".intval($loginseedId);
		DB::game()->exec($sql);
	}
	

	/**
	 * checks that the password is correct
	 *
	 * @param string password
	 */
	private function checkPassword($password) {

		if (strpos($this->password, '$') === 0) {
			$cryptpass = crypt(STENDHAL_PASSWORD_PEPPER . strtoupper(md5($password)), $this->password);
			if ($cryptpass == $this->password) {
				return 1;
			}

			// pre-Marauroa 2.0 passwords
			$cryptpass = crypt(STENDHAL_PASSWORD_PEPPER . strtoupper(md5(md5($password, true))), $this->password);
			if ($cryptpass == $this->$password) {
				return 1;
			}
			return 0;
		}

		$md5pass = strtoupper(md5($password));
		if ($md5pass == $this->password) {
			return 1;
		}

		// We need to check the pre-Marauroa 2.0 passwords
		$md5pass = strtoupper(md5(md5($password, true)));
		if ($md5pass == $this->password) {
			return 1;
		}
		return 0;
	}

	public static function checkBlocked($username, $ip) {
		$sql = "SELECT count(*) as amount FROM loginEvent"
			. " WHERE address='" . mysql_real_escape_string($ip) . "'"
			. " AND result != 1 and timedate > date_sub(CURRENT_TIMESTAMP(), INTERVAL 10 MINUTE)";

		$count = queryFirstCell($sql, DB::game());
		if ($count > 3) {
			return "There have been too many failed login attempts from your network. Please wait a couple of minutes or contact support.";
		}

		$sql = "SELECT count(*) as amount FROM loginEvent, account"
			. " WHERE loginEvent.player_id=account.id"
			. " AND username='" . mysql_real_escape_string($username) . "'"
			. " AND loginEvent.result != 1 and loginEvent.timedate > date_sub(CURRENT_TIMESTAMP(), INTERVAL 10 MINUTE)";

		$count = queryFirstCell($sql, DB::game());
		if ($count > 10) {
			return "There have been too many failed login attempts for your account. Please wait a couple of minutes or contact support.";
		}

		return null;
	}

	private function readAccountBan() {
		$sql = "SELECT reason, expire FROM accountban "
			." WHERE accountban.player_id='".mysql_real_escape_string($this->id)."'"
			." AND (accountban.expire > CURRENT_TIMESTAMP OR accountban.expire IS NULL) ORDER BY ifnull(expire,'9999-12-31') desc limit 1 ";
		$stmt = DB::game()->query($sql);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		if ($row) {
			$this->banMessage = $row['reason'];
			$this->banExpire = $row['expire'];
		}
	}

	/**
	 * get a message telling the player why the account is not active
	 *
	 * @return string message or <code>null</code> if the account is active
	 */
	public function getAccountStatusMessage() {
		if (isset($this->banMessage)) {
			if (isset($this->banExpire)) {
				$res = "Your account is temporarily banned until " . $this->banExpire . " server time.\n";
			} else {
				$res = "Your account is banned.\n";
			}
			$res = $res . "The reason given was: " . $this->banMessage;
		} else if ($this->status == "banned") {
			$res = "Your account has been banned. Please contact support.";
		} else if ($this->status == "inactive") {
			$res = "Your account has been flagged as inactive. Please contact support.";
		} else if ($this->status == "merged") {
			$res = "Your account has been merged into another account. Please login with that account or contact support.";
		} else if ($this->status == "deleted") {
		    $res = "This account name is reserved. Accounts names will be reserved if they are missleading (e. g. the name might indicate an admin status) or if an account with that name existed in the past.";
		}
		if (isset($res)) {
			return $res;
		}
		return null;
	}

	/**
	 * get a status telling the player why the account is not active
	 *
	 * @return int message or <code>null</code> if the account is active
	 */
	public function getAccountStatusCode() {
	    $res = 1;
		if (isset($this->banMessage)) {
			$res = 2;
		} else if ($this->status == "banned") {
			$res = 2;
		} else if ($this->status == "inactive") {
			$res = 3;
		} else if ($this->status == "deleted") {
		    $res = 3;
		} else if ($this->status == "merged") {
			$res = 5;
		}
		return $res;
	}

	/**
	 * tries to convert a proposed username into a valid one
	 *
	 * @param string $username proposed username
	 * @return string valid username or <code>null</code>.
	 */
	public static function convertToValidUsername($username) {

	    // only allow letters
		$temp = preg_replace('/[^a-z]/', '', strtolower($username));

		// limit length
		$temp = substr($temp, 0, 19);

		// ensure minimum length
		if (strlen($temp) > 0) {
			$org = $temp;
			while (strlen($temp) < 6) {
				$temp = $temp . $org;
			}
			return substr($temp, 0, 20);
		} else {
			return null;
		}
	}

	/**
	 * Creates a sha512crypt hash of the password hash, unless it is disabled in configuration
	 *
	 * @param string $passwordHash password hash
	 * @return string sha512crypt hash
	 */
	public static function sha512crypt($passwordHash) {
		if (STENDHAL_PASSWORD_HASH == 'md5') {
			return $passwordHash;
		}
		$alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
		$salt = '$6$';
		for($i = 0; $i < 16; $i++) {
			$salt .= $alphabet[rand(0, 63)];
		}
		return crypt(STENDHAL_PASSWORD_PEPPER . $passwordHash, $salt);
	}

	/**
	 * inserst a record in the account table.
	 */
	public function insert() {
		$sql = "INSERT INTO account(username, status";
		$sql2 = ") VALUES ('".mysql_real_escape_string($this->username)
			."', '".mysql_real_escape_string($this->status)."'";
		if ($this->password) {
			$sql .= ", password";
			$sql2 .= ", '".mysql_real_escape_string(Account::sha512crypt($this->password))."'";
		}
		$sql = $sql.$sql2.');';
		DB::game()->exec($sql);
		$this->id = DB::game()->lastInsertId();
		$this->insertEMail($this->email, $this->emailTrusted);
	}

	/**
	 * gets the email history
	 */
	public static function getEmailHistory($playerId) {
		$sql = "SELECT email, token, address, timedate, confirmed "
		. " FROM email WHERE player_id=" . intval($playerId)
		. " ORDER BY id DESC";

		return fetchToArray($sql, DB::game());
	}

	/**
	 * changes the email-address
	 *
	 * @param email new email-address
	 */
	public function insertEMail($email, $trusted) {
		if ($trusted) {
			$sql = "insert into email(player_id, email, address, confirmed) values ('".mysql_real_escape_string($this->id)
			."', '".mysql_real_escape_string($email)."', '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."', NOW())";
			DB::game()->exec($sql);
		} else {
			$data = Account::getEmailHistory($this->id);
			if (count($data) > 0) {
				$row = $data[0];
				if ($row['email'] == $email) {
					if (isset($row['confirmed']) && ($row['confirmed'] != '')&& (strpos($row['confirmed'], '0000') === false)) {
						return;
					} else {
						$token = $row['token'];
					}
				}
			}

			if (!isset($token)) {
				$token = createRandomString();
				$sql = "insert into email(player_id, email, token) values ('".mysql_real_escape_string($this->id)
					."', '".mysql_real_escape_string($email)."', '".mysql_real_escape_string($token)."')";
				DB::game()->exec($sql);
			}
			require_once('scripts/cmd/mail.php');
			sendRegistrationMail($this->id, $this->username, $token, $email);
		}
	}

	/**
	 * checks if a name is available for account/character creation
	 *
	 * @param $name          string  name to check
	 * @param $ignoreAccount boolean ignore this account on the character check (to allow someone to create a character with his own account name)
	 */
	public static function isNameAvailable($name, $ignoreAccount) {
		$sql = '';
		if (!$ignoreAccount || trim($name) != trim($ignoreAccount)) {
			$sql = "SELECT username FROM account WHERE username = '".mysql_real_escape_string($name)."' UNION ";
		}
		$sql .= "SELECT charname FROM characters WHERE charname = '".mysql_real_escape_string($name)."';";
		$stmt = DB::game()->query($sql);
		return $stmt->rowCount() == 0;
	}

	/**
	 * reads the permissions for an account
	 *
	 * @param string $accountId accountId
	 */
	public static function readPermissions($accountId) {
		try {
			$stmt = DB::web()->prepare('SELECT * FROM permission WHERE account_id=:id');
			$stmt->execute(array(
				':id' => $accountId
			));
			$tmp = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			return $tmp;
		} catch(PDOException $e) {
			error_log('ERROR addNews: ' . $e->getMessage());
			return null;
		}
	}

	/**
	 * creates the return url
	 */
	public static function createReturnUrl() {
		if (isset($_SERVER['SCRIPT_URI'])) {
			$res = $_SERVER['SCRIPT_URI'];
		} else {
			// SCRIPT_URI seems to be set by mod_redirect only
			if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == "on")) {
				$res = 'https';
			} else {
				$res = 'http';
			}
			$res = $res.'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		}

		$res = $res.'?id='.urlencode($_REQUEST['id']);
		if (isset($_REQUEST['url'])) {
			$res .= '&url='.urlencode($_REQUEST['url']);
		}
		if (isset($_REQUEST['social'])) {
			$res .= '&social='.urlencode($_REQUEST['social']);
		}
		if (isset($_REQUEST['tab'])) {
			$res .= '&tab='.urlencode($_REQUEST['tab']);
		}
		return str_replace('%2F', '/', $res);
	}

	/**
	 * logs the user in using the providing account link, automatically
	 * creates the account, if it does not exist, yet.
	 *
	 * @param AccountLink $accountLink
	 */
	public static function loginOrCreateByAccountLink($accountLink) {
		unset($_SESSION['account']);
		$account = Account::tryLogin($accountLink->type, $accountLink->username, null, $accountLink);
		$_SESSION['account'] = $account;
		$_SESSION['csrf'] = createRandomString();
		$_SESSION['marauroa_authenticated_username'] = $account->username;
		fixSessionPermission();
	}

	/**
	 * verifies an email
	 * @param string $token
	 * @return boolean
	 */
	public static function verifyEMail($token) {
		$sql = "SELECT count(*) FROM email WHERE token='".mysql_real_escape_string($token)."'";
		$temp = queryFirstCell($sql, DB::game());
		if ($temp == 0) {
			return false;
		}

		$sql = "UPDATE email SET address='".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])
			. "', confirmed=NOW() WHERE token='".mysql_real_escape_string($token)
			. "' AND confirmed IS NULL";
		DB::game()->exec($sql);
		return true;
	}


	public static $SELFBAN_RESULT_OK = 0;
	public static $SELFBAN_RESULT_NOT_CONFIGURED = 1;
	public static $SELFBAN_INVALID_TOKEN = 2;
	public static $SELFBAN_ACCOUNT_ALREADY_BANNED = 3;
	public static $SELFBAN_ACCOUNT_NOT_ACTIVE = 4;

	/**
	 * self bans an account
	 *
	 * @param int $accountid
	 * @param string $timestamp
	 * @param string $signature
	 * @return int enum value
	 */
	public function selfban($accountid, $timestamp, $signature) {
		if (!defined('STENDHAL_SECRET')) {
			return Account::SELFBAN_RESULT_NOT_CONFIGURED;
		}

		$s = hash_hmac("sha512", $accountid.'/'.$timestamp, STENDHAL_SECRET);
		if ($signature != $s) {
			return Account::SELFBAN_INVALID_TOKEN;
		}

		$account = Account::readAccountById($accountid);
		$account->readAccountBan();

		if (isset($account->banMessage) || $account->status == 'banned') {
			return Account::SELFBAN_ACCOUNT_ALREADY_BANNED;
		}

		if ($account->status != 'active') {
			return Account::SELFBAN_ACCOUNT_NOT_ACTIVE;
		}

		//TODO


		return Account::SELFBAN_RESULT_OK;
	}
}


/**
 * Account Link
 *
 * @author hendrik
 */
class AccountLink {
	public $id;
	public $playerId;
	public $type;
	public $username;
	public $nickname;
	public $email;
	public $secret;
	public $emailVerified;

	/**
	 * creates a new AccountLink
	 */
	public function __construct($id, $playerId, $type, $username, $nickname, $email, $secret, $emailVerified) {
		$this->id = $id;
		$this->playerId = $playerId;
		$this->type = $type;
		$this->username = $username;
		$this->nickname = $nickname;
		$this->email = $email;
		$this->secret = $secret;
		$this->emailVerified = $emailVerified;
	}

	public static function getAccountLinks($playerId) {
		$links = array();
		$sql = "SELECT id, player_id, type, username, nickname, email, secret "
		. "FROM accountLink "
		. "WHERE player_id ='".mysql_real_escape_string($playerId)."'";
		$rows = DB::game()->query($sql);
		foreach($rows as $row) {
			$links[] = new AccountLink($row['id'], $row['player_id'],
			$row['type'], $row['username'], $row['nickname'],
			$row['email'], $row['secret'], false);
		}
		return $links;
	}

	public static function findAccountLinksForUsername($type, $accountId, $username) {
		$sql = "SELECT id, player_id, type, username, nickname, email, secret "
				. "FROM accountLink "
				. "WHERE username ='".mysql_real_escape_string($username)."'"
				. " AND type = '".mysql_real_escape_string($type)."'"
				. " AND player_id=".intval($accountId);
		$links = array();
		$rows = DB::game()->query($sql);
		foreach($rows as $row) {
			$links[] = new AccountLink($row['id'], $row['player_id'],
					$row['type'], $row['username'], $row['nickname'],
					$row['email'], $row['secret'], false);
		}
		return $links;
	}
	public static function findAccountLink($type, $username) {
		$sql = "SELECT id, player_id, type, username, nickname, email, secret "
		. "FROM accountLink "
		. "WHERE username ='".mysql_real_escape_string($username)."'"
		. " AND type = '".mysql_real_escape_string($type)."'";
		$links = array();
		$rows = DB::game()->query($sql);
		foreach($rows as $row) {
			$links[] = new AccountLink($row['id'], $row['player_id'],
				$row['type'], $row['username'], $row['nickname'],
				$row['email'], $row['secret'], false);
		}
		return $links;
	}

	private function proposeSteamUsernames($steamid) {
		$url = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key='
			.STENDHAL_STEAM_API_KEY.'&steamids='
			.urlencode($steamid);
		$response = requestJson($url);
		$this->nickname = $response['response']['players'][0]['personaname'];
		$res[] = Account::convertToValidUsername($this->nickname);
		$res[] = Account::convertToValidUsername($this->nickname.'steam');
		return $res;
	}

	public function proposeUsernames() {
		$res = array();
		$res[] = Account::convertToValidUsername($this->nickname);
		if (isset($this->email)) {
			$pos = strpos($this->email, '@');
			if ($pos !== false) {
				$res[] = Account::convertToValidUsername(substr($this->email, 0, $pos));
			}
		}
		if ($this->type == 'facebook') {
			$res[] = Account::convertToValidUsername(substr($this->nickname, 0, 19));
		}
		if ($this->type == 'steam') {
			return $this->proposeSteamUsernames($this->username);
		}
		if (strpos($this->username, 'http') === 0) {
			$this->username = str_replace('http://', 'https://', $this->username);
			$lastSlash = strrpos($this->username, '/');

			// Special handling for Steam
			if (strpos($this->username, 'https://steamcommunity.com/openid/id/') === 0) {
				return $this->proposeSteamUsernames(substr($this->username, $lastSlash + 1));
			}

			// apply openid url magic
			if ($lastSlash < strlen($this->username) - 1) {
				$res[] = Account::convertToValidUsername(substr($this->username, $lastSlash + 1));
			} else {
				$lastSlash = strpos($this->username, '://') + 2;
				$dot = strpos($this->username, '.');
				$res[] = Account::convertToValidUsername(substr($this->username, $lastSlash + 1, $dot - $lastSlash - 1));
			}
		} else {
			$res[] = Account::convertToValidUsername($this->username);
		}
		return $res;
	}

	private function pickNewUsernameWithSuffix($proposedUsernames, $suffix) {
	    $first = true;
	    $in = '';
	    foreach ($proposedUsernames As $name ) {
	        if (!isset($name) || ($name == '')) {
	            continue;
	        }
	        if ($first) {
	            $first = false;
	        } else {
	            $in .= ', ';
	        }
	        $in .= "'".mysql_real_escape_string($name.$suffix)."'";
	    }

	    $sql = "SELECT username FROM account WHERE username in (".$in.") "
	        . "UNION SELECT charname FROM characters WHERE charname in (".$in.") FOR UPDATE;";

        // check database
        $existingUsernames = array();
        $rows = DB::game()->query($sql);
        foreach($rows as $row) {
            $existingUsernames[] = $row['username'];
        }

        // pick username
        foreach($proposedUsernames As $name ) {
            if ($name && trim($name) != '') {
                if (!in_array($name.$suffix, $existingUsernames)) {
                    return $name.$suffix;
                }
            }
        }
	}

	private function numberToCharacterString($number) {
	    $res = '';
	    $i = $number;
	    do {
	        $res = $res . chr($i % 26 + 97);
	        $i = intdiv($i, 26);
	    } while ($i > 0);
	    return $res;
	}

	private function pickNewUsername($proposedUsernames) {
	    $suffix = '';
	    $i = 0;
	    do {
	       $username = $this->pickNewUsernameWithSuffix($proposedUsernames, $suffix);
	       $suffix = $this->numberToCharacterString($i);
	       $i++;
	    } while (!$username);
	    return $username;
	}

	public function createAccount() {
		// links verifiedEmail
		if ($this->emailVerified && isset($this->email) && strlen($this->email) > 3) {
			$sql = "SELECT account.id FROM email, account WHERE email.email='"
				.mysql_real_escape_string($this->email)
				."' AND email.confirmed IS NOT NULL AND email.player_id=account.id AND account.status='active' "
				."ORDER BY player_id DESC LIMIT 1";
			$stmt = DB::game()->query($sql);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			if ($row) {
				$this->playerId = $row['id'];
				$this->insert();
				$account = Account::tryLogin($this->type, $this->username, null, null);
				return $account;
			}
		}

		// suggest usernames
		$proposedUsernames = $this->proposeUsernames();

		// create sql statement to check which suggestions exist
		DB::game()->beginTransaction();

		$username = $this->pickNewUsername($proposedUsernames);

		// insert new account
		$account = new Account(-1, $username, null, $this->email, $this->emailVerified, date("Y-m-d").' '.date("H:i:s"), 'active');
		$account->insert();
		$this->playerId = $account->id;
		$this->insert();

		DB::game()->commit();

		return $account;
	}

	/**
	 * inserts a record in the accountLink table.
	 */
	public function insert() {
		$sql = "INSERT INTO accountLink(player_id, type, username";
		$sql2 = ") VALUES ('".mysql_real_escape_string($this->playerId)
			."', '".mysql_real_escape_string($this->type)
			."', '".mysql_real_escape_string($this->username)."'";
		if ($this->nickname) {
			$sql .= ", nickname";
			$sql2 .= ", '".mysql_real_escape_string($this->nickname)."'";
		}
		if ($this->email) {
			$sql .= ", email";
			$sql2 .= ", '".mysql_real_escape_string($this->email)."'";
		}
		if ($this->secret) {
			$sql .= ", secret";
			$sql2 .= ", '".mysql_real_escape_string($this->secret)."'";
		}
		$sql = $sql.$sql2.');';
		DB::game()->exec($sql);
		$this->id = DB::game()->lastInsertId();
	}
}


function fixSessionPermission() {
	session_regenerate_id();
	$id = session_id();
	if (preg_match('/^[a-zA-Z0-9]+$/', $id)) {
		$filename = session_save_path().'/sess_'.$id;
		if (strpos($filename, ';') === false) {
			touch($filename);
			chmod($filename, 0640);
		}
	}
}

/**
 * sends a udp message
 *
 * @param string $prefix  a prefix to tell message sources apart
 * @param string $message message
 */
function sendUdpMessage($prefix, $message) {
	$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	$msg = $prefix.'/'.$message;
	socket_sendto($socket, $msg, strlen($msg), 0, '127.0.0.1', 7839);
	socket_close($socket);
}
