<?php

class SourceLogPage extends Page {

	public function writeHtmlHeader() {
		if (!isset($_GET['month'])) {
			echo '<title>Source Code Changes'.STENDHAL_TITLE.'</title>';
		} else {
			$month = $_GET['month'];
			if (preg_match("/^\d\d\d\d-\d\d$/", $month)) {
				echo '<title>Source Code Changes in '.$month.STENDHAL_TITLE.'</title>';
			} else {
				echo '<title>Source Code Changes '.STENDHAL_TITLE.'</title>';
				echo '<meta name="robots" content="noindex">'."\n";
			}
		}
	}

	function writeContent() {
startBox("<h1>Source Code</h1>"); ?>
<p>The Arianne project is hosted on
<a href="https://github.com/arianne/">GitHub</a> and
<a href="https://sourceforge.net/projects/arianne/">Sourceforge</a> and
uses Git to manage changes to our source code.</p>

<p>You can use a Git client to download our Stendhal or Marauroa source
code.</p>

<ul>
	<li><a href="https://github.com/arianne/stendhal/">Stendhal source on GitHub</a></li>
	<li><a href="https://sourceforge.net/p/arianne/stendhal/ci/master/tree/">Stendhal source on SourceForge</a></li>
	<li><a href="https://github.com/arianne/marauroa/">Marauroa source on GitHub</a></li>
	<li><a href="https://sourceforge.net/p/arianne/marauroa/ci/master/tree/">Marauroa source on SourceForge</a></li>
</ul>

<p>For more information check out the <a href="/wiki/Arianne_Source_Code_Repositories">Source Code Repositories wiki page</a>.</p>

<p>Recent changes to the code are recorded below. We have an experimental <a href="https://arianne-project.org/postsai/query.html?repository=arianne%2F.*&amp;repositorytype=regexp&amp;date=month">commit database</a>.</p>
<?php endBox();

	$directory = CVS_LOG_DIRECTORY;

	if (isset($_GET['month']) && preg_match("/^\d\d\d\d-\d\d$/", $_GET['month'])) {
	$month = $_GET['month'];

startBox('<h2>Changes in '.htmlspecialchars($month).'</h2>');
?>
	<p>
		<a href="<?php echo rewriteURL('/development/sourcelog.html')?>">Index of logs</a>
	</p>

	<p>Timestamps are in server time.</p>

	<ul class="source">

<?php
for ($day = 1; $day <= 31; $day++) {

	$daystr = $day;
	if ($day < 10) {
		$daystr = '0'.$day;
	}

	$filename = $directory.$month . '-' . $daystr . ".log";
	if (is_file($filename)) {
		echo '<a name="day'.$day.'">';
		$lines = explode("\n", file_get_contents($filename));
		$res = '';
		$first = true;
		for ($i = 0; $i < count($lines); $i++) {
			$line = $lines[$i];

			## make it pretty, yes this code is ugly.
			if (((strpos($line, '< CIA-') > 0) || (strpos($line, 'postman') > 0)) && (strpos($line, '> arianne_rpg: ') > 0)) {

				if (preg_match('/^[0-9][0-9]:[0-9][0-9] < .*> arianne_rpg: [^ ]*( [^ ]*)? \*/', $line)) {
					if ($res != '') {
						echo '<li>' . $res . "</span></li>\n";
					}


					$res = $this->formatLine($month, $daystr, $line);

				} else {
					$pos = strpos($line, 'arianne_rpg: ');
					$res = $res . htmlspecialchars(substr($line, $pos + 12));
				}
			}
		} // for

		if ($res != '') {
			echo '<li>' . $res . "</li>\n";
		}

	}
}

?>
	</ul>

<?php
	} else {
		startBox('<h2>Recent changes</h2>');
		$filearray = array();
		if (is_dir($directory)) {
			$dir = opendir($directory);
			if ($dir !== false) {
				while (false !== ($file = readdir($dir))) {
					if (strpos($file, ".log") == 10) {
						$filearray[] = $file;
					}
				}
				closedir($dir);
				rsort($filearray);
			}
		}

		echo '<ul>';
		$last = '';
		foreach ($filearray as $file) {
			$month = substr($file, 0, 7);
			if ($month != $last) {
				echo '<li><a href="'.rewriteURL('/development/sourcelog/'.htmlspecialchars($month).'.html').'">' . $month . '</a></li>';
				$last = $month;
			}
		}
		echo '</ul>';
	}

	endBox();

	}


	function formatLine($month, $daystr, $line) {
		$line = htmlspecialchars($line);
		$time = '<span class="sourcetime">' . $month . '-' . $daystr . ' ' . substr($line, 0, 5) . '</span>';

		$pos = strpos($line, 'arianne_rpg: ');
		$line = substr($line, $pos + 13);
		$pos = strpos($line, ' ');
		$user = '<span class="sourceuser">' . htmlspecialchars(substr($line, 0, $pos)) . '</span>';

		$line = substr($line, $pos + 1);
		$pos = strpos($line, ' ');
		$branch = substr($line, 0, $pos);
		if ($branch != '*') {
			$class = 'sourcebranch';
			if (strtoupper($branch) != $branch) {
				$class = 'sourcedevbranch';
			}
			$branch = '<span class="'.$class.'">&nbsp;' . htmlspecialchars($branch) . '&nbsp;</span>';
			$pos = $pos + 2;
		} else {
			$branch = '';
		}

		$line = substr($line, $pos + 1);
		$pos = strpos($line, '/');
		$module = substr($line, 0, $pos);
		if (substr($module, -4) == ' exp') {
			$pos = strpos($line, '/', $pos + 1);
			$module = substr($line, 0, $pos);
		}
		$rev = '';
		$posRev = strpos($module, ' ');
		if ($posRev !== FALSE) {
			$rev = substr($line, 1, $posRev - 1);
			$module = substr($line, $posRev + 1, $pos - $posRev - 1);
			$rev = '<a class="sourcerev" href="https://sourceforge.net/p/arianne/'.htmlspecialchars($module).'/ci/'.htmlspecialchars($rev).'">'.htmlspecialchars($rev).'</a>';
		}
		$module = '<span class="sourcemodule">' . htmlspecialchars($module) . '</span>';

		$line = substr($line, $pos + 1);
		$pos = strpos($line, ':');
		$files = '<span class="sourcefiles">' . htmlspecialchars(substr($line, 0, $pos)) . '</span>';

		$commit = '<span class="sourcecommit">' . htmlspecialchars(substr($line, $pos + 1));

		$res = $time .' '. $user .' '. $branch .' '. $rev .' '. $module .' '. $files .':<br>'. $commit;
		return $res;
	}
}
$page = new SourceLogPage();
