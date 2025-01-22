<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2013 Faiumoni e. V.

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
class TradePage extends Page {

	private $singleEntry;

	public function writeHttpHeader() {
		if (isset($_REQUEST['tradeid'])) {
			$entries = TradeOffer::getTradeOffer($_REQUEST['tradeid']);
			if (count($entries) > 0) {
				$this->singleEntry = $entries[0];
			} else {
				header('HTTP/1.1 404 Not Found');
			}
		}
		return true;
	}

	public function writeHtmlHeader() {
		if (isset($this->singleEntry)) {
			$description = 'Offer ' . htmlspecialchars($this->singleEntry->id)
				 . ': ' . htmlspecialchars($this->singleEntry->quantity)
				 . ' ' . htmlspecialchars($this->singleEntry->itemname)
				 . 'at ' . htmlspecialchars($this->singleEntry->price);
			echo '<title>'.htmlspecialchars($description).STENDHAL_TITLE.'</title>';
			echo '<meta name="description" content="'.htmlspecialchars($description).'">';
		} else {
			echo '<title>Trade offers'.STENDHAL_TITLE.'</title>';
		}
		echo '<link rel="alternate" type="application/atom+xml" title="Stendhal Trade Offers" href="/trade.atom">'."\n";
		echo '<meta name="robots" content="noindex">'."\n";
		return true;
	}

	public function writeContent() {
		startBox('<h1>New trade offers</h1>');
		if (isset($_REQUEST['tradeid'])) {
			$this->writeEntry($_REQUEST['tradeid']);
			echo '<p>See <a href="/trade/">all recent trade offers</a>.</p>';
		} else {
			$this->writeRecent();
		}
		endBox();
	}

	public function writeRecent() {
		global $cache;
		$feed = $cache->fetch('stendhal-trade');
		if (!isset($feed) || $feed == '') {
			$feed = $this->generateFeed();
			$cache->store('stendhal-trade', $feed, 5*60);
		}
		echo $feed;
	}

	public function writeEntry($tradeId) {
		if (isset($this->singleEntry)) {
			echo $this->generateEntry($this->singleEntry);
		} else {
			echo '<p class="error">No such trade offer.</p>';
		}
	}

	public function generateFeed() {
		$entries = TradeOffer::getTradeOffers();
		$res = $this->generateHeader($entries);
		foreach($entries as $entry) {
			$res .= $this->generateEntry($entry);
		}
		$res .= $this->generateFooter();
		return $res;
	}

	private function formatedDate($date) {
		$datetime = new DateTime($date);
		return $datetime->format('Y-m-d H:i');
	}

	private function generateHeader($entries) {
		$date = date(DATE_ATOM);
		if (count($entries) > 0) {
			$date = $entries[0]->timedate;
		}
		$res = '';
		$res .= '<img class="newsIcons" src="/images/outfit/body-1-0_dress-34-0_head-1-0_mouth-0-0_eyes-0-0_mask-0-0_hair-5-0_hat-0-0_detail-0-0.png">';
		$res .= '<p>I am Harold and I have my little shop in Semos Tavern. On this page, I announce new trade offers by other players. Please note that I won\'t take offers down once the items are sold.</p>';

		$res .= '<span class="newsIcons"> <a href="/trade.atom"><img src="/images/buttons/feed-icon-28x28.png"></a><a href="https://mastodon.social/@stendhaltrade/"><img src="/images/thirdparty/mastodon.svg" alt="m" title="Stendhal Trade on Mastodon" height="28px" width="28px"></a></span>';
		$res .= '<p>I suggest that you use a feed reader to access this page. For reference, the current server time is '.date('G:i') . '.</p>';
		if (sizeof($entries)==0) {
			$res .= '<p>There are no new trade offers.</p>';
		}
		return $res;
	}

	private function generateEntry($entry) {
		$res = '<p>';
		$res .= '<a href="'.rewriteURL('https://stendhalgame.org/trade/'.htmlspecialchars($entry->id).'.html').'">';
		$res .= $this->formatedDate($entry->timedate) . '</a>: ';
		$res .= 'New offer for ' . htmlspecialchars($entry->quantity) . ' ';
		$res .= getItem($entry->itemname)->generateImageWithPopup();
		$res .= 'at ' . htmlspecialchars($entry->price);
		$res .= ".</p>\r\n";
		return $res;
	}

	private function generateFooter() {
		return '</feed>';
	}
}

$page = new TradePage();
