<?php
require_once('scripts/account.php');

/**
 * Tests for Account
 *
 * @author hendrik
 */
class AccountTest extends PHPUnit_Framework_TestCase {

	public function testConvertToValidUsername() {
		$this->assertEquals(null, Account::convertToValidUsername(null));
		$this->assertEquals(null, Account::convertToValidUsername(''));
		$this->assertEquals(null, Account::convertToValidUsername('#'));
		$this->assertEquals('hellohi', Account::convertToValidUsername(' h-e#l+l_ohi '));
		$this->assertEquals('hhhhhh', Account::convertToValidUsername('94320480h32'));
		$this->assertEquals('qwertz', Account::convertToValidUsername('qwertz'));
	}
}

/**
 * Tests for AccountLink
 *
 * @author hendrik
 */
class AccountLinkTest extends PHPUnit_Framework_TestCase {

	public function testProposeUsernamese() {
		$accountLink = new AccountLink(-1, -1, 'openid', 'http://bla.example.com/', 'nick', 'mail@mailinator.com', null, false);
		$this->assertEquals('nicknick,mailmail,blabla,httpblaexamplecom,http://bla.example.com/', implode(',', $accountLink->proposeUsernames()));

		$accountLink = new AccountLink(-1, -1, 'openid', 'http://login.example.com/bla', '', null, null. false);
		$this->assertEquals(',blabla,httploginexamplecomb,http://login.example.com/bla', implode(',', $accountLink->proposeUsernames()));
	}
}
