<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2010 The Arianne Project

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

require_once('lib/openid/lightopenid.php');

class OpenID {
	public $error;
	public $isAuth = false;

	public function doOpenidRedirectIfRequired($requestedIdentifier) {
		if (!isset($_GET['openid_mode'])) {
			if (isset($requestedIdentifier)) {
				$this->isAuth = true;
				$openid = new LightOpenID;
				$openid->identity = $requestedIdentifier;
				$openid->required = array('contact/email', 'namePerson/friendly');
				$openid->realm     = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
				$openid->returnUrl = $this->createReturnUrl();
				try {
					header('Location: ' . $openid->authUrl());
				} catch (ErrorException $e) {
					$this->error = $e->getMessage();
				}
			}
		}
	}

	/**
	 * creates the return url
	 */
	private function createReturnUrl() {
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
		if ($_REQUEST['url']) {
			$res .= '&url='.urlencode($_REQUEST['url']);
		}
		return $res;
	}

	/**
	 * creates an AccountLink object based on the openid identification
	 * 
	 * @return AccountLink or <code>FALSE</code> if  the validation failed
	 */
	public function createAccountLink() {
		$openid = new LightOpenID();
		$openid->realm     = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
		$openid->returnUrl = $this->createReturnUrl();
		try {
			if (!$openid->validate()) {
				$this-$openid->error = 'Open ID validation failed.';
				return false;
			}
		} catch (Exception $e) {
			$this->openid->error = $e->getMessage();
		}
		$attributes = $openid->getAttributes();
		$accountLink = new AccountLink(null, null, 'openid', $openid->identity, 
			$attributes['namePerson/friendly'], $attributes['contact/email'], $secret);
		return $accountLink;
	}

	public function getStendhalAccountName() {
		$openid = new LightOpenID();
		$openid->realm     = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
		$openid->returnUrl = $this->createReturnUrl();
		if (!$openid->validate()) {
			$this-$openid->error = 'Open ID validation failed.';
			return false;
		}
		$identifier = $openid->identity;
		if (strpos($identifier, 'https://stendhalgame.org/a/') !== 0) {
			$this-$openid->error = 'Only Stendhal Accounts accepted';
			return false;
		}
		return substr($identifier, 27);
	}

	/**
	 * handles a succesful openid authentication
	 * 
	 * @param AccountLink $accountLink the account link created for the login
	 */
	public function merge($accountLink) {
		$oldAccount = $_SESSION['account'];
		$newAccount = Account::readAccountByLink('openid', $accountLink->username, null);

		if (!$newAccount || is_string($newAccount)) {
			$accountLink->playerId = $oldAccount->id;
			$accountLink->insert();
		} else {
			if ($oldAccount->username != $newAccount->username) {
				mergeAccount($newAccount->username, $oldAccount->username);
			}
		}
	}

	public function succesfulOpenidAuthWhileNotLoggedIn($accountLink) {
		unset($_SESSION['account']);
		$account = Account::tryLogin('openid', $accountLink->username, null);

		if (!$account || is_string($account)) {
			$account = $accountLink->createAccount();
		}
		$_SESSION['account'] = $account;
		$_SESSION['csrf'] = createRandomString();
		$_SESSION['marauroa_authenticated_username'] = $account->username;
		fixSessionPermission();
	}
}

