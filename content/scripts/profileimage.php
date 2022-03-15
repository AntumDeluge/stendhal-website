<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2020  Stendhal

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

class ProfileImagePage extends Page {
    public function writeHttpHeader() {
        header("Content-Type: text/javascript", true);

        $player = getPlayer($_REQUEST['charname']);

        // use a redirect in order for the image file to be cached without
        // having to care about player changing their outfits
        header('Location: https://stendhalgame.org/images/outfit/'.$player->outfit.'.png');

        // do not send and body, this is a redirect
        return false;
    }
}

$page = new ProfileImagePage();
