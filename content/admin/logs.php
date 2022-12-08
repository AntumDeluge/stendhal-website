<?php

class AdminLogsPage extends Page {
	function writeContent() {

	if(getAdminLevel()<100) {
		die("Ooops!");
	}
	startBox('<h1>'.SUPPORT_CHANNEL . ' IRC log</h1>');
	$directory = SUPPORT_LOG_DIRECTORY;

	if (isset($_GET['date']) && preg_match("/^\d\d\d\d-\d\d-\d\d$/", $_GET['date'])) {
	$date = $_GET['date'];
?>
    <p>
     <a href="./?id=content/admin/logs">Index of logs</a>
    </p>

    <h2><?php echo(SUPPORT_CHANNEL); ?> IRC Log for <?php echo($date); ?></h2>
    <p>
     Timestamps are in server time.
    </p>
    <p>

<?php
$lines = explode("\n", file_get_contents($directory.$date . ".log"));
for ($i = 0; $i < count($lines); $i++) {
	$line = $lines[$i];

	## make it pretty, yes this code is ugly.
	$class = "irctext";
	if (substr($line, 5, 5) == ' -!- ') {
		$class = "ircstatus";
	} else if (substr($line, 8, 13) == 'postman-bot> ') {

		if (substr($line, 21, 10) == 'Character ') {
			$class = "ircnewchar";
		} else if (substr($line, 21, 22) == 'Administrator SHOUTS: ') {
			$class = "ircshout";
		} else if (strpos($line, 'asks for support to') > 10) {
			$class = "ircsupport";
		} else if ((strpos($line, 'answers') > 10) && (strpos($line, 'support question') > 10)) {
			$class = "ircsupportanswer";
		} else if (strpos($line, 'rented a sign saying') > 10) {
			$class = "ircsign";
		}
	}

	echo '<span class="'.$class.'">'.htmlspecialchars($line).'</span><br>'."\n";
}

?>
    </p>
<?php
    }
    else {
	$filearray = array();
	if (is_dir($directory)) {
	  $dir = opendir($directory);
	  if (!is_bool($dir)) {
	    while (false !== ($file = readdir($dir))) {
		if (strpos($file, ".log") == 10) {
		    $filearray[] = $file;
		}
	    }
	    closedir($dir);

	    rsort($filearray);
	  }
	}
?>
    <ul>
<?php


        foreach ($filearray as $file) {
            $file = substr($file, 0, 10);
?>
	 <li><a href="<?php echo($_SERVER['PHP_SELF'] . "/?id=content/admin/logs&amp;date=" . $file); ?>"><?php echo($file); ?></a></li>

<?php
        }
?>
    </ul>
<?php
    }

?>

<p>
 These logs of <?php echo(SUPPORT_CHANNEL); ?> were automatically created by <?php echo(IRC_BOT); ?> bot on
 <a href="irc://<?php echo(SUPPORT_SERVER . "/" . substr(SUPPORT_CHANNEL, 1)); ?>"><?php echo(SUPPORT_SERVER); ?></a>. <b>These logs are for administrators eyes ONLY and should not be copied or pasted to others.</b>

</p>
<?php
		endBox();
	}
}
$page = new AdminLogsPage();
