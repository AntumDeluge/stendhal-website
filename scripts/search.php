<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2014   Faiumoni e. V.

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
 * searches for results across multiple entity types
 *
 * @author hendrik
 */
class Searcher {
	private $terms;
	private $searchTerm;

	function __construct($searchTerm) {
		$this->searchTerm = $searchTerm;
		$this->terms = preg_split("/[\s,]+/", strtolower($searchTerm), -1, PREG_SPLIT_NO_EMPTY);
	}

	function search() {
		$results = array();
		if (count($this->terms) == 0) {
			return $results;
		}
		$results = array_merge($results, $this->searchIndex());
		return $results;
	}

	function searchIndex() {
		profilePoint('searchIndex');
		$terms = $this->terms;

		// apply stopword filter (keep in sync with SearchIndexManager.java)
		$stopwords = array("a", "an", "and", "is", "it", "of", "see", "the", "to", "you");
		foreach ($stopwords As $word) {
			$offset = array_search($word, $terms);
			if ($offset !== false) {
				array_splice($terms, $offset, 1);
			}
		}

		$sql = "SELECT s0.entitytype, s0.entityname, s0.searchscore * "
				. count($terms) ." As score FROM searchindex s0 WHERE s0.searchterm = '"
						. mysql_real_escape_string($this->searchTerm) ."' ORDER BY s0.searchscore DESC";

		$result = fetchToArray($sql, DB::game());
		profilePoint($sql);

		if (strpos($this->searchTerm, ' ') !== false) {
			$columns = "SELECT s0.entitytype, s0.entityname, s0.searchscore";
			$from = ' As score FROM searchindex s0';
			$where = " WHERE s0.searchterm = '". mysql_real_escape_string($terms[0]) ."'";
			$order = " ORDER BY score DESC";
			for ($i = 1; $i < count($terms); $i++) {
				$columns .= "+ s".$i.".searchscore";
				$from .= ", searchindex s".$i;
				$where .= " AND s".$i.".searchterm = '". mysql_real_escape_string($terms[$i]) ."'"
						. " AND s0.entitytype=s".$i.".entitytype AND s0.entityname=s".$i.".entityname";
			}
			$sql = $columns . $from . $where . $order;
			$result = array_merge($result, fetchToArray($sql, DB::game()));
			profilePoint($sql);
		}

		//join character_stats because very old, unused accoutns don't have an entry there
		$sql = "SELECT 'P' As entitytype, charname As entityname, if(account.status='active', if(character_stats.level=0, 0, 1), -1) * 3050 As score"
				. " FROM characters, account, character_stats"
				. " WHERE characters.status='active' AND characters.charname=character_stats.name AND charname = '"
				. mysql_real_escape_string($this->searchTerm) . "' AND account.id=characters.player_id"
				. " AND (age > 5 OR level > 0)";
		$result = array_merge($result, fetchToArray($sql, DB::game()));
		profilePoint($sql);


		// wiki
		if (defined("STENDHAL_WIKI_CONNECTION") && constant("STENDHAL_WIKI_CONNECTION")) {
			$sql = "SELECT stendhal_category_search.entitytype, IFNULL(pp_title.pp_value, page.page_title) As entityname, (stendhal_category_search.searchscore + 2000) * "
					. count($terms)	. " As score, stendhal_category_search.category As category, pp_canonical.pp_value As path, pp_lang.pp_value As lang"
					. " FROM a1111_wiki.searchindex, a1111_wiki.categorylinks, a1111_wiki.stendhal_category_search, a1111_wiki.page"
					. " LEFT JOIN a1111_wiki.page_props As pp_canonical ON (pp_canonical.pp_propname='externalcanonical' AND page.page_id=pp_canonical.pp_page)"
					. " LEFT JOIN a1111_wiki.page_props As pp_title ON (pp_title.pp_propname='externaltitle' AND page.page_id=pp_title.pp_page)"
					. " LEFT JOIN a1111_wiki.page_props As pp_lang ON (pp_lang.pp_propname='pagelanguage' AND page.page_id=pp_lang.pp_page)"
					. " WHERE page_id=si_page AND MATCH(si_title) AGAINST('+".mysql_real_escape_string(str_replace(' ', ' +', $this->searchTerm))
					. "' IN BOOLEAN MODE) AND page_is_redirect=0 AND page_namespace=0 AND categorylinks.cl_from=page.page_id"
					. " AND stendhal_category_search.category=categorylinks.cl_to LIMIT 100";

			$result = array_merge($result, fetchToArray($sql, DB::game()));
			profilePoint($sql);

			$sql = "SELECT stendhal_category_search.entitytype, IFNULL(pp_title.pp_value, page.page_title) As entityname, (stendhal_category_search.searchscore + 1000) * "
					. count($terms)	. " As score, stendhal_category_search.category As category, pp_canonical.pp_value As path, pp_lang.pp_value As lang"
					. " FROM a1111_wiki.searchindex, a1111_wiki.categorylinks, a1111_wiki.stendhal_category_search, a1111_wiki.page"
					. " LEFT JOIN a1111_wiki.page_props As pp_canonical ON (pp_canonical.pp_propname='externalcanonical' AND page.page_id=pp_canonical.pp_page)"
					. " LEFT JOIN a1111_wiki.page_props As pp_title ON (pp_title.pp_propname='externaltitle' AND page.page_id=pp_title.pp_page)"
					. " LEFT JOIN a1111_wiki.page_props As pp_lang ON (pp_lang.pp_propname='pagelanguage' AND page.page_id=pp_lang.pp_page)"
					. " WHERE page_id=si_page AND MATCH(si_text) AGAINST('+".mysql_real_escape_string(str_replace(' ', ' +', $this->searchTerm))
					. "' IN BOOLEAN MODE) AND page_is_redirect=0 AND page_namespace=0 AND categorylinks.cl_from=page.page_id"
							. " AND stendhal_category_search.category=categorylinks.cl_to LIMIT 100";
			$result = array_merge($result, fetchToArray($sql, DB::game()));
			profilePoint($sql);
		}

		return $result;
	}

}
