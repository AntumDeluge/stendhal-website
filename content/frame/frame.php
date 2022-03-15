<?php
/*
 Copyright (C) 2011-2018 Faiumoni e. V.

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
 * this class represents the outer frame of pages of the website
 *
 * @author hendrik
 */
abstract class PageFrame {


	/**
	 * gets the default page in case none is specified.
	 *
	 * @return string name of default page
	 */
	abstract function getDefaultPage();

	/**
	 * this method can write additional http headers, for example for cache control.
	 *
	 * @param $page_url
	 * @return true, to continue the rendering, false to not render the normal content
	 */
	abstract function writeHttpHeader($page_url);

	/**
	 * this method can write additional html headers.
	 */
	abstract function writeHtmlHeader();

	/**
	 * renders the frame
	 */
	abstract function renderFrame();

	/**
	 * includes java script libraries
	 */
	public function includeJs() {
		echo '<script type="text/javascript" src="'.STENDHAL_FOLDER.'/css/script-'.STENDHAL_CACHE_BUSTER.'.js"></script>';
	}
}
