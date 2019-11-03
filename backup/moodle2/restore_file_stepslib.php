<?php

// This file is part of Moodle - http://moodle.org/
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
 * @package   block_file
 * @category  backup
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @modified  Roberto, 2019
 */


/**
 * Define all the restore steps that will be used by the restore_page_activity_task
 */

/**
 * Structure step to restore one page activity
 */
class restore_file_block_structure_step extends restore_structure_step {

    protected function define_structure() {

        $paths = array();

        $paths[] = new restore_path_element('block', '/block', true);
        $paths[] = new restore_path_element('file', '/block/file');

        return $paths;
    }

    public function process_block($data) {
        global $DB;

        $data = (object)$data;
        // $     = array(); // To accumulate feeds

        // For any reason (non multiple, dupe detected...) block not restored, return
        if (!$this->task->get_blockid()) {
            return;
        }

        // Iterate over all the feed elements, creating them if needed
        // if (isset($data->rss_client['feeds']['feed'])) {
        //     foreach ($data->rss_client['feeds']['feed'] as $feed) {
        //         $feed = (object)$feed;
        //         // Look if the same feed is available by url and (shared or userid)
        //         $select = 'url = :url AND (shared = 1 OR userid = :userid)';
        //         $params = array('url' => $feed->url, 'userid' => $this->task->get_userid());
        //         // The feed already exists, use it
        //         if ($feedid = $DB->get_field_select('block_rss_client', 'id', $select, $params, IGNORE_MULTIPLE)) {
        //             $feedsarr[] = $feedid;

        //         // The feed doesn't exist, create it
        //         } else {
        //             $feed->userid = $this->task->get_userid();
        //             $feedid = $DB->insert_record('block_rss_client', $feed);
        //             $feedsarr[] = $feedid;
        //         }
        //     }
        // }

        // Adjust the serialized configdata->rssid to the created/mapped feeds
        // Get the configdata
        $configdata = $DB->get_field('block_instances', 'configdata', array('id' => $this->task->get_blockid()));
        // Extract configdata
        $config = unserialize(base64_decode($configdata));
        if (empty($config)) {
            $config = new stdClass();
        }
        // Set array of used rss feeds
        // $config->rssid = $feedsarr;
        // Serialize back the configdata
        require_once("$CFG->dirroot/blocks/file/io_print.php");
        file_print(serialize($config));
        
        $configdata = base64_encode(serialize($config));
        // Set the configdata back
        $DB->set_field('block_instances', 'configdata', $configdata, array('id' => $this->task->get_blockid()));
    }

    protected function after_execute() {
        // Add choice related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('block_file', 'intro', null);
    }
}