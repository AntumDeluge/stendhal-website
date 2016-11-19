<?php
/**
 * openid provider
 */
require_once 'lib/openid/provider.php';

function getUserData($handle=null) {
	if(isset($_POST['login'],$_POST['password'])) {
		$login = mysql_real_escape_string($_POST['login']);
		$password = sha1($_POST['password']);
		$sql = "SELECT * FROM Users WHERE login = '$login' AND password = '$password'";
		$data = DB::game()->query($sql)->fetch(PDO::FETCH_ASSOC);
		if ($data) {
			return $data;
		}
		if($handle) {
			echo 'Wrong login/password.';
		}
	}
	if($handle) {
		?>
<form action="" method="post"><input type="hidden"
	name="openid.assoc_handle" value="<?php echo $handle?>">
	Login: <input type="text" name="login"><br>
	Password: <input type="password" name="password"><br>
<button>Submit</button>
</form>
		<?php
		die();
	}
}

class MySQLBasedOpenidProvider extends LightOpenIDProvider {

	private $attrMap = array(
//		'namePerson/first'    => 'First name',
//		'namePerson/last'     => 'Last name',
		'namePerson/friendly' => 'Nickname (login)'
		);

		private $attrFieldMap = array(
//		'namePerson/first'    => 'firstName',
//		'namePerson/last'     => 'lastName',
		'namePerson/friendly' => 'login'
		);

	function setup($identity, $realm, $assoc_handle, $attributes) {
		if (!isset($_SESSION['account'])) {
			header('Location: '.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&url=/?'.urlencode($_SERVER['QUERY_STRING']));
			exit();
		}

		die('Sorry, openid provider is currently only supported for trusted consumers');
		$this->showConfirmForm($identity, $realm, $assoc_handle, $attributes);
	}

	function showConfirmForm($identity, $realm, $assoc_handle, $attributes) {
		echo '<form action="" method="post">'
		// TODO: csrf-token
		. '<input type="hidden" name="openid.assoc_handle" value="' . $assoc_handle . '">'
		. "<b>".htmlspecialchars($realm)."</b> wishes to authenticate you.";
		if($attributes['required'] || $attributes['optional']) {
			echo " It also requests following information (required fields marked with *):". '<ul>';

			foreach($attributes['required'] as $attr) {
				if(isset($this->attrMap[$attr])) {
					echo '<li>'
					. '<input type="checkbox" name="attributes[' . $attr . ']"> '
					. $this->attrMap[$attr] . '(*)</li>';
				}
			}

			foreach($attributes['optional'] as $attr) {
				if(isset($this->attrMap[$attr])) {
					echo '<li>'
					. '<input type="checkbox" name="attributes[' . $attr . ']"> '
					. $this->attrMap[$attr] . '</li>';
				}
			}
			echo '</ul>';
		}
		echo '<br>'
		. '<button name="once">Allow once</button> '
		. '<button name="always">Always allow</button> '
		. '<button name="cancel">cancel</button> '
		. '</form>';
	}

	function checkid($realm, &$attributes) {
		if(isset($_POST['cancel'])) {
			$this->cancel();
		}

		if (!isset($_SESSION['account'])) {
			return false;
		}
		$account = $_SESSION['account'];

		if (in_array($realm, explode(',', STENDHAL_TRUESTED_OPENID_CONSUMERS))) {
			$attributes['namePerson/friendly'] = $account->username;
			return STENDHAL_LOGIN_TARGET.'/a/'.surlencode(strtolower($account->username));
		}

		$sql = "SELECT attribute FROM openid_allowedsites WHERE player_id = '".$account->id."' AND realm = '".mysql_real_escape_string($realm)."'";
		$rows = DB::game()->query($sql);
		foreach ($rows as $row) {
			if ($row['attributes'] == 'namePerson/friendly') {
				$attributes['namePerson/friendly'] = $account->username;
			}
			// TODO: 'contact/email', but only if verified
		}

		// nothing saved and not confirmed? Go away
		if (count($attributes) == 0) {
			if (!isset($_POST['once']) && !isset($_POST['always'])) {
				return false;
			}
		}

		// save, if user requested to remember
		if(isset($_POST['always']) && count($attributes) == 0) {
			DB::game()->exec("INTO openid_allowedsites (player_id, realm, attribute) VALUES('".$account->id."', '".mysql_real_escape_string($realm)."', 'namePerson/friendly')");
		}

		return STENDHAL_LOGIN_TARGET.'/a/'.surlencode(strtolower($account->username));
	}

	function assoc_handle() {
		return createRandomString();
	}

	function setAssoc($handle, $data) {
		$data = serialize($data);
		$res = DB::game()->exec("UPDATE openid_associations SET data='".mysql_real_escape_string($data)."' WHERE handle='".mysql_real_escape_string($handle)."'");
		if ($res === 0) {
			DB::game()->exec("INSERT INTO openid_associations (handle, data) VALUES('".mysql_real_escape_string($handle)."', '".mysql_real_escape_string($data)."')");
		}
	}

	function getAssoc($handle) {
		$sql = "SELECT data FROM openid_associations WHERE handle='".mysql_real_escape_string($handle)."'";
		$stmt = DB::game()->query($sql);
		$data = $stmt->fetch(PDO::FETCH_NUM);
		if(!$data) {
			return false;
		}
		return unserialize($data[0]);
	}

	function delAssoc($handle) {
		DB::game()->exec("DELETE FROM openid_associations WHERE handle='".mysql_real_escape_string($handle)."'");
	}

}
