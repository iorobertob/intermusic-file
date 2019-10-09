<?php
defined('MOODLE_INTERNAL') || die();

// include 'io_print.php';

class block_file extends block_base
{

    public function init()
    {
        $this->title = get_string('file', 'block_file');
    }

    public function applicable_formats()
    {
        return array('all' => true);
    }

    public function instance_allow_multiple()
    {
        return true;
    }

    public function specialization()
    {
        if (isset($this->config->title) && $this->config->title !== '') {
            $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        }
    }

    public function instance_config_save($data, $nolongerused = false)
    {
        require(__DIR__.'/../../config.php');

        global $DB, $CFG, $PAGE;

        $data->file = file_save_draft_area_files($data->select_file, $this->context->id, 'block_file', 'file', 0, array('subdirs' => false, 'maxfiles' => -1), '@@PLUGINFILE@@/');

        if($autopopulateCheckbox === "1")
        {
            // TODO: here to implement the autopopulation of metadata, from files' metadata
        }

        require_once("$CFG->dirroot/blocks/file/io_print.php");
        $fs    = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'block_file', 'file', 0);

        // Add at the end those files that did not match the sorting array
        $keys = array_keys($files);
        file_print($files[$keys[1]] -> get_filename());

        // $contextid = context_course::instance($courseid);
        $courseid = $PAGE->course->id;
        file_print("\n CONTEXT: \n");
        file_print($contextid);

        return parent::instance_config_save($data, $nolongerused);
    }

    public function get_content()
    {
        if ($this->content !== null) 
        {
            return $this->content;
        }

        $this->content = new stdClass;

        $height = isset($this->config->height) && $this->config->height !== '' ? $this->config->height : null;

        $fs    = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'block_file', 'file', 0);

//////////////////////////// SORTING TABS ALGORITHM /////////////////////////
        $sortingArray =array(
            "SCORE",
            "TRANS",
            "WORD2",
            "VIDEO",
            "AUDIO",
            "RECIT");

        $filesSorted           = [];
        $newNames              = [];
        $sortedOriginalNames   = [];

        for($x = 0; $x <= sizeof($sortingArray); $x++)
        {
            foreach($files as $file)
            {
                // Splitting the name by "_" 
                $sortString      = explode( "_", $file -> get_filename() );
                $stringToCompare = substr($sortString[2], 0, 5);

               if ( ($stringToCompare == $sortingArray[$x]) && ($stringToCompare != "") )
               {
                    array_push($filesSorted, $file);
                    array_push($newNames, explode( ".", $sortString[2])[0] ); // Getting rid of the extension 
                    array_push($sortedOriginalNames, $file->get_filename());
                    // break;
               }
            }
        }

        // Add at the end those files that did not match the sorting array
        foreach($files as $file)
        {
            if( (gettype(array_search($file->get_filename(), $sortedOriginalNames)) != "integer") && ($file->get_filename()!=".") )
            {
                array_push($filesSorted, $file);
                array_push($newNames, $file->get_filename());
            }
        }
////////////////////////////  \SORTING TABS ALGORITHM /////////////////////////

        $content = null;

        $count = 0;
        // foreach ($files as $file) 
        foreach ($filesSorted as $file)
        {
            if ($count == 0)    
            {
                // $count += 1;
                // continue;
            }
            else
            {
                // $count += 1;
            }

    	    if ($file->is_directory()) 
            {
                continue;
                // $count += 1;
            }

            $filterOptions = new stdClass;
            // $filterOptions->overflowdiv = true;
            $filterOptions->noclean = true;

            $mimeType = $file->get_mimetype();
            if ($mimeType === 'application/pdf') 
            {
                $shortname = $newNames[$count];
                $content .= '{%:'.'<button>'.'  '.$shortname. '</button>'.' }'.$this->get_content_text_pdf($file, $height).'{%}';
                $count += 1;
                continue;
            }

            if (substr($mimeType, 0, 5) === 'video') 
            {
                $shortname = $newNames[$count];
                $content .= '{%:'.'<button onclick="for(i=0; i<document.getElementsByTagName(\'video\').length; i++) document.getElementsByTagName(\'video\')[i].pause();for(i=0; i<document.getElementsByTagName(\'audio\').length; i++) document.getElementsByTagName(\'audio\')[i].pause()">'.'  '.$shortname. '</button>'.' }'.$this->get_content_text_video($file, $height).'{%}';
                $count += 1;
		        continue;
            }

            if (substr($mimeType, 0, 5) === 'audio') 
            {
                $shortname = $newNames[$count];
                $content .= '{%:'.'<button onclick="for(i=0; i<document.getElementsByTagName(\'audio\').length; i++) document.getElementsByTagName(\'audio\')[i].pause();for(i=0; i<document.getElementsByTagName(\'video\').length; i++) document.getElementsByTagName(\'video\')[i].pause()">'.'  '.$shortname. '</button>'.' }'.$this->get_content_text_audio($file, $height).'{%}';
                $count += 1;
                continue;
            }

            if (in_array($mimeType, [
                'image/gif',
                'image/png',
                'image/jpeg',
                'image/svg+xml',
            ])) 
            {
                $shortname = $newNames[$count];
                $content .= '{%:'.'<button> '.'  '.$shortname. '</button>'.'  }'.$this->get_content_text_image($file, $height).'{%}';
                $count += 1;
                continue;
            }
        }

        $content = format_text($content, FORMAT_HTML, $filterOptions);
        $this->content->text = $content ?? get_string('nofileselected', 'block_file');
        return $this->content;
    }

    protected function get_content_text_default($file, $height = null)
    {
        return html_writer::tag('a', $file->get_filename(), ['href' => $this->get_file_url($file)]);
    }

    protected function get_content_text_pdf($file, $height = null)
    {
        $styles = [
            'width' => '100%',
            // 'height' => '100%',
        ];

        if ($height !== null) {
            $styles['min-height'] = $height;
        }

        $viewerUrl = new moodle_url('/blocks/file/pdfjs/web/viewer.html');
        $viewerUrl->param('file', $this->get_file_url($file));

        $attributes = [
            'src' => $viewerUrl,
            'style' => $this->build_style_attribute($styles),
        ];

        return html_writer::tag('iframe', $this->get_content_text_default($file, $height), $attributes);
    }

    protected function get_content_text_video($file, $height = null)
    {
        $styles = [
            'width' => '100%',
        //     'height' => '100%',
        ];

        $attributes = [
            'controls' => '',
            'style' => $this->build_style_attribute($styles),
            'src' => $this->get_file_url($file),
        ];

        return html_writer::tag('video', '', $attributes);
    }

    protected function get_content_text_audio($file, $height = null)
    {
        $styles = [
            'width' => '100%',
        ];

        $attributes = [
            'controls' => '',
            'style' => $this->build_style_attribute($styles),
            'src' => $this->get_file_url($file),
            'type'=>"audio/wav",
        ];
        $tag = html_writer::tag('audio', '', $attributes);
        echo "<script>console.log('".$tag."');</script>";
        return $tag;
    }

    protected function get_content_text_image($file, $height = null)
    {
        $styles = [
            'width' => '100%',
        ];

        $attributes = [
            'style' => $this->build_style_attribute($styles),
            'src' => $this->get_file_url($file),
            'alt' => $file->get_filename(),
        ];

        return html_writer::tag('img','', $attributes);
        //return html_writer::empty_tag('img', $attributes);
    }

    protected function get_file_url($file)
    {
        return moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), ($file->get_itemid() !== '0' ? $file->get_itemid() : null), $file->get_filepath(), $file->get_filename());
    }

    protected function build_style_attribute($style)
    {
        $rules = [];
        foreach ($style as $key => $value) {
            $rules[] = "$key: $value";
        }
        return implode('; ', $rules);
    }
}
