<?php

class EventsPage extends Page {

	public function __construct() {
		$this->setupFilter();
	}

	function writeHttpHeader() {
		if ($this->filter=="friends" && !isset($_SESSION['account'])) {
			header('Location: '.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&url='.urlencode(rewriteURL('/world/events/'.urlencode($this->filter).'.html')));
			return false;
		}
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>Recent Events'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		// $this->writeTabs();
		$this->printRecentEvents();
		// $this->closeTabs();
	}

	function writeTabs() {
        ?>
        <br>
        <table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
        <td class="barTab" width="2%"> &nbsp;</td>
        <?php echo '<td class="'.$this->getTabClass('all').'" width="25%"><a class="'.$this->getTabClass('all').'A" href="'.htmlspecialchars(rewriteURL('/world/events/all.html')).'">All</a></td>';?>
        <td class="barTab" width="2%"> &nbsp;</td>
        <?php echo '<td class="'.$this->getTabClass('friends').'" width="25%"><a class="'.$this->getTabClass('friends').'A" href="'.htmlspecialchars(rewriteURL('/world/events/friends.html')).'">Friends</a></td>';?>
        <td class="barTab">&nbsp;</td>
        </tr>
        <tr><td colspan="7" class="tabPageContent">
        <br>
        <?php
    }


    function closeTabs() {
        ?></td></tr></table><?php
    }

    function getTabClass($tab) {
        if ($this->filter == $tab) {
            return 'activeTab';
        } else {
            return 'backgroundTab';
        }
    }

	function setupFilter() {
		$this->filter = 'all';
		if (isset($_REQUEST['filter'])) {
			$this->filter = urlencode($_REQUEST['filter']);
		}
 	}

	function printRecentEvents(){
		global $cache;
		if (!isset($_REQUEST['profiling'])) {
			$suffix = 'anonymous';
			if (isset($_SESSION) && isset($_SESSION['account'])) {
				$suffix = $_SESSION['account']->id;
			}
			$events = $cache->fetchAsArray('stendhal_events_'.$this->filter.'_'.$suffix);
			if (!isset($events) || !is_array($events) || isset($_REQUEST['test'])) {
				$events=array_merge(getKillEvents($this->filter),
					getQuestEvents($this->filter),
					getLevelEvents($this->filter),
					getSignEvents($this->filter),
					getPoisonEvents($this->filter),
					getChangeZoneEvents($this->filter),
					getOutfitEvents($this->filter),
					getEquipEvents($this->filter),
					getAchievementEvents($this->filter));
				$cache->store('stendhal_events_'.$this->filter.'_'.$suffix, new ArrayObject($events), 60);
			}
		} else {
			echo '<pre>';
			$time_start = microtime(true); getKillEvents($this->filter); $time_end = microtime(true); $time = ($time_end - $time_start); echo 'getKillEvents: '. $time."\n";
			$time_start = microtime(true); getQuestEvents($this->filter); $time_end = microtime(true); $time = ($time_end - $time_start); echo 'getQuestEvents: '. $time."\n";
			$time_start = microtime(true); getLevelEvents($this->filter); $time_end = microtime(true); $time = ($time_end - $time_start); echo 'getLevelEvents: '. $time."\n";
			$time_start = microtime(true); getSignEvents($this->filter); $time_end = microtime(true); $time = ($time_end - $time_start); echo 'getSignEvents: '. $time."\n";
			$time_start = microtime(true); getPoisonEvents($this->filter); $time_end = microtime(true); $time = ($time_end - $time_start); echo 'getPoisonEvents: '. $time."\n";
			$time_start = microtime(true); getChangeZoneEvents($this->filter); $time_end = microtime(true); $time = ($time_end - $time_start); echo 'getChangeZoneEvents: '. $time."\n";
			$time_start = microtime(true); getOutfitEvents($this->filter); $time_end = microtime(true); $time = ($time_end - $time_start); echo 'getOutfitEvents: '. $time."\n";
			$time_start = microtime(true); getEquipEvents($this->filter); $time_end = microtime(true); $time = ($time_end - $time_start); echo 'getEquipEvents: '. $time."\n";
			$time_start = microtime(true); getAchievementEvents($this->filter); $time_end = microtime(true); $time = ($time_end - $time_start); echo 'getAchievementEvents: '. $time."\n";
			echo '</pre>';
			return;
		}



		function cmp($a, $b)
			{
			if ($a->timedate == $b->timedate) {
				return 0;
			}
			return ($a->timedate < $b->timedate) ? 1 : -1;
		}

		usort($events,"cmp");

		startBox('<h1>A selection of recent events</h1>');
		echo 'The current server time is '.date('G:i');
		if(sizeof($events)==0) {
			echo '<br>There are no recent events to report on.';
		}
		echo '<div>';
		$players = array();
		foreach($events as $e) {
			$e->addPlayersToList($players);
		}

		$outfits = getOutfitsForPlayers($players);

		foreach($events as $e) {
			echo $e->getHtml($outfits);
		}

		echo '</div>';
		endBox();
	}
}

$page = new EventsPage();
