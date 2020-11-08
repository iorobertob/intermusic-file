<?php

class block_file_edit_form extends block_edit_form
{
    protected function specific_definition($mform)
    {
        $mform->addElement('header', 'configheader', get_string('settings', 'block_file'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_file'));
        $mform->setDefault('config_title', get_string('file', 'block_file'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('filemanager', 'config_select_file', get_string('configfile', 'block_file'), null, array('subdirs' => false, 'maxfiles' =>-1, 'accepted_types'=>'*'));
        $mform->setType('config_select_file', PARAM_RAW);
        
        $mform->addElement('text', 'config_height', get_string('configheight', 'block_file'));
        $mform->setType('config_height', PARAM_TEXT);

        $mform->addElement('header', 'tabsheader', get_string('tabs_header', 'block_file'));

        $mform->addElement('text', 'config_meta1', get_string('tab1', 'block_file'));
        $mform->setType('config_meta1', PARAM_TEXT);
        $mform->setDefault('config_meta1', get_string('string1', 'block_file'));

        $mform->addElement('text', 'config_meta2', get_string('tab2', 'block_file'));
        $mform->setType('config_meta2', PARAM_TEXT);
        $mform->setDefault('config_meta2', get_string('string2', 'block_file'));

        $mform->addElement('text', 'config_meta3', get_string('tab3', 'block_file'));
        $mform->setType('config_meta3', PARAM_TEXT);
        $mform->setDefault('config_meta3', get_string('string3', 'block_file'));

        $mform->addElement('text', 'config_meta4', get_string('tab4', 'block_file'));
        $mform->setType('config_meta4', PARAM_TEXT);
        $mform->setDefault('config_meta4', get_string('string4', 'block_file'));

        $mform->addElement('text', 'config_meta5', get_string('tab5', 'block_file'));
        $mform->setType('config_meta5', PARAM_TEXT);
        $mform->setDefault('config_meta5', get_string('string5', 'block_file'));

        $mform->addElement('text', 'config_meta6', get_string('tab6', 'block_file'));
        $mform->setType('config_meta6', PARAM_TEXT);
        $mform->setDefault('config_meta6', get_string('string6', 'block_file'));

        $mform->addElement('text', 'config_meta7', get_string('tab7', 'block_file'));
        $mform->setType('config_meta7', PARAM_TEXT);
        $mform->setDefault('config_meta7', get_string('string7', 'block_file'));
    }

    /**
     * Sets the data on load, with the files saved for this block on to the filemanager
     *
     */
    function set_data($defaults) {

        $itemid = 0;
        $draftitemid = file_get_submitted_draft_itemid('config_select_file');

        file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_file', 'file', $itemid, array('subdirs'=>true));

        if (!issset($this->block->config))
            {
                $this->block->config = new \stdClass();
            }
        $this->block->config->select_file = $draftitemid;

        parent::set_data($defaults);
    }
}

