<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 
/**
 * Adds admin settings for the plugin.
 *
 * @package     mod_mposter
 * @category    admin
 * @copyright   2020 Roberto Becerra <roberto.becerra@lmta.lt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();
 
if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/block/file/lib.php');

    // Introductory explanation that all the settings are defaults for the add lesson form.
    $settings->add(new admin_setting_heading('block_file/intro', '', get_string('default_titles', 'mposter')));

    $settings->add(new admin_setting_configtext('block_file/meta1', get_string('meta1', 'mposter')." ".get_string('meta_title','mposter'),
            '', "SCORE", PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_file/meta2', get_string('meta2', 'mposter')." ".get_string('meta_title','mposter'),
            '', "TRANS", PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_file/meta3', get_string('meta3', 'mposter')." ".get_string('meta_title','mposter'),
            '', "WORD", PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_file/meta4', get_string('meta4', 'mposter')." ".get_string('meta_title','mposter'),
            '', "VIDEO", PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_file/meta5', get_string('meta5', 'mposter')." ".get_string('meta_title','mposter'),
            '', "RECIT", PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_file/meta6', get_string('meta6', 'mposter')." ".get_string('meta_title','mposter'),
            '', "1ST", PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_file/meta7', get_string('meta7', 'mposter')." ".get_string('meta_title','mposter'),
            '', "LANG", PARAM_TEXT));
}
