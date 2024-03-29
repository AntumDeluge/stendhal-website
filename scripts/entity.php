<?php
/*
 * Stendhal website - a website to manage and ease playing of Stendhal game
 * Copyright (C) 2008-2023 The Arianne Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


abstract class Entity {

	public $name;
	public $description;
	public $class;
	public $imageurl;


	function __construct($name, $description, $class) {
		$this->name = $name;
		$this->description = $description;
		$this->class = $class;
	}

	function setImage($imageurl) {
		$this->imageurl = $imageurl;
	}

	function getImage() {
		return $this->imageurl;
	}

	function getBorderedImage() {
		return "<img class=\"bordered_image\" src=\"".$this->imageurl."\" alt=\"\">";
	}
}
