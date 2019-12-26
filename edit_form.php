<?php

class block_file_edit_form extends block_edit_form
{
    protected function specific_definition($mform)
    {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_file'));
        $mform->setDefault('config_title', get_string('file', 'block_file'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('filemanager', 'config_select_file', get_string('configfile', 'block_file'), null, array('subdirs' => false, 'maxfiles' =>-1, 'accepted_types'=>'*'));


        // $mform->addElement('filemanager', 'config_select_file', get_string('configfile', 'block_file'), null, array('subdirs' => false, 'maxfiles' =>-1, 'accepted_types'=>'*','return_types'=> FILE_INTERNAL | FILE_EXTERNAL | FILE_REFERENCE | FILE_CONTROLLED_LINK));

        $mform->setType('config_select_file', PARAM_RAW);
        $mform->addElement('text', 'config_height', get_string('configheight', 'block_file'));
        $mform->setType('config_height', PARAM_TEXT);


    }

    /**
     * Sets the data on load, with the files saved for this block on to the filemanager
     *
     */
    function set_data($defaults) {
        file_print("SET DATA", TRUE);

        $itemid = 0;
        $draftitemid = file_get_submitted_draft_itemid('config_select_file');

        file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_file', 'file', $itemid, array('subdirs'=>true));

        $this->block->config->select_file = $draftitemid;

        parent::set_data($defaults);
    }

}

