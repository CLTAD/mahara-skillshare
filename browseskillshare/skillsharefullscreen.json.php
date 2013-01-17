<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-browseskillshare
 * @author     Mike Kelly UAL m.f.kelly@arts.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
define('PUBLIC', 1); // if we don't define public and user isn't logged in we'll get an error

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'browseskillshare');

$id = param_integer('id', 0);
$imageindex = param_integer('imageindex', 0);
$fullscreen = param_integer('fullscreen', 0);

$item = ArtefactTypeBrowseSkillshare::get_fullscreen_item($id, $imageindex);
ArtefactTypeBrowseSkillshare::build_fullscreen_html($item);

json_reply(false, (object) array('message' => false, 'data' => $item));
