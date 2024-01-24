<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
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

require_once('vendor/autoload.php');
use Jumbojett\OpenIDConnectClient;


class OAuth {

	function doRedirect($providerName) {
		$this->authenticate($providerName);
	}

	function authenticate($providerName) {
		if (!isset(STENDHAL_EXTERNAL_AUTH[$providerName])) {
			return false;
		}
		$_SESSION['stendhal_oauth_provider'] = $providerName;
		$authConfig = STENDHAL_EXTERNAL_AUTH[$providerName];
		$oidc = new OpenIDConnectClient($authConfig['url'], $authConfig['client_id'], $authConfig['client_secret']);
		$oidc->addScope(['openid']);
		$oidc->addScope(['email']);
		$oidc->addScope(['profile']);
		$oidc->authenticate();
		return $oidc;
	}


	/**
	 * creates an AccountLink object based on the facebook identification
	 *
	 * @return AccountLink or <code>FALSE</code> if  the validation failed
	 */
	public function createAccountLink($providerName) {
		$oidc = $this->authenticate($providerName);
/*
		echo '<pre>';
		foreach (array('iss', 'sub', 'email', 'email_verified', 'given_name', 'family_name', 'name', 'picture', 'locale', 'preferred_username', 'website') as $attr) {
			echo $attr . ': ' . htmlspecialchars($oidc->getVerifiedClaims($attr))."\n";
		}
*/
		$authConfig = STENDHAL_EXTERNAL_AUTH[$providerName];
		if ($authConfig['iss'] != $oidc->getVerifiedClaims('iss')) {
			echo 'Invalid ISS';
			return false;
		}
		$username = $oidc->getVerifiedClaims('iss') . '/' . $oidc->getVerifiedClaims('sub');
		$email = $oidc->getVerifiedClaims('email');
		$emailVerified = $oidc->getVerifiedClaims('email_verified');
		$nickname = null;
		foreach (array('nickname', 'preferred_username', 'given_name') as $attr) {
			$value = $oidc->getVerifiedClaims($attr);
			if (isset($value)) {
				$nickname = strtolower($value);
				break;
			}
		}

		//                           $id, $playerId, $type, $username, $nickname, $email, $secret) {
		$accountLink = new AccountLink(null, null, 'oauth', $username, $nickname, $email, null, $emailVerified);
		return $accountLink;
	}


}
