<?php
defined('MOODLE_INTERNAL') || die();

class block_file extends block_base
{

    public function instance_config_save($data, $nolongerused = false)
    {
        global $DB, $CFG, $PAGE;

        $data->file = file_save_draft_area_files($data->select_file, $this->context->id, 'block_file', 'file', 0, array('subdirs' => false, 'maxfiles' => -1), '@@PLUGINFILE@@/');


        return parent::instance_config_save($data, $nolongerused);
    }

    /**
     * Moodle's system function
     *
     */
    public function init()
    {

        $this->title = get_string('file', 'block_file');
    }

    /**
     * Moodle's system function
     *
     */
    public function applicable_formats()
    {

        return array('all' => true);
    }

    /**
     * Moodle's system function
     *
     */
    public function instance_allow_multiple()
    {

        return true;
    }

    /**
     * Moodle's system function
     *
     */
    public function specialization()
    {
        if (isset($this->config->title) && $this->config->title !== '') {
            $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        }
    }


    /**
     * lmta.utility
     * Sorting algorithm for filenames according to an arbitrary "sorting array"
     * to later display in order in tabbed view in the file plugin
     */
    public function sortingTabsAlgorithm($files)
    {
        //////////////////////////// SORTING TABS ALGORITHM /////////////////////////
        $sortingArray =array(
            get_config('mod_mposter','meta1' ),
            get_config('mod_mposter','meta2' ),
            get_config('mod_mposter','meta3' ),
            get_config('mod_mposter','meta4' ),
            get_config('mod_mposter','meta5' ),
            get_config('mod_mposter','meta6' ),
            get_config('mod_mposter','meta7' ));

        $filesSorted           = [];
        $newNames              = [];
        $sortedOriginalNames   = [];

        for($x = 0; $x < sizeof($sortingArray); $x++)
        {
            foreach($files as $file)
            {
                // Splitting the name by "_" 
                $sortString      = explode( "_", $file -> get_filename() );

                if (count($sortString) >= 3){
                    $stringToCompare = substr($sortString[2], 0, 5);
 
                    if ( ($stringToCompare == $sortingArray[$x]) && ($stringToCompare != "") )
                    {
                        array_push($filesSorted, $file);
                        array_push($newNames, explode( ".", $sortString[2])[0] ); // Getting rid of the extension 
                        array_push($sortedOriginalNames, $file->get_filename());
                    }
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

        return array($filesSorted, $newNames);
    }

    /**
     * Default function.
     */
    public function get_content()
    {
        global $PAGE;

        // Because we will use js functions in some of the rendered html 
        $PAGE->requires->js('/blocks/file/file.js',true);

        if ($this->content !== null) 
        {
            return $this->content;
        }

        $this->content = new stdClass;

        $height = isset($this->config->height) && $this->config->height !== '' ? $this->config->height : null;

        $fs    = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'block_file', 'file', 0);

        $sortingResult = $this->sortingTabsAlgorithm($files);

        $filesSorted   = $sortingResult[0];
        $newNames      = $sortingResult[1];

        $content = null;

        $count = 0;
        // foreach ($files as $file) 
        foreach ($filesSorted as $file)
        {

    	    if ($file->is_directory()) 
            {
                continue;
            }

            $filterOptions = new stdClass;
            $filterOptions->noclean = true;

            $mimeType = $file->get_mimetype();
            if ($mimeType === 'application/pdf') 
            {
                $shortname = $newNames[$count];

                $content .= '{%:'.'<button  class="tabButtons" onclick="selectedTab(this)">'.'  '.$shortname. '</button>'.' }'.$this->get_content_text_pdf($file, $height).'{%}';

                $count += 1;
                continue;
            }

            if (substr($mimeType, 0, 5) === 'video') 
            {
                $shortname = $newNames[$count];
 
                $content .= '{%:'.'<button  class="tabButtons" onclick="selectedTabMedia(this)">'.'  '.$shortname. '</button>'.' }'.$this->get_content_text_video($file, $height).'{%}';

                $count += 1;
		        continue;
            }

            if (substr($mimeType, 0, 5) === 'audio') 
            {
                $shortname = $newNames[$count];

                $content .= '{%:'.'<button  class="tabButtons" onclick="selectedTabMedia(this)">'.'  '.$shortname. '</button>'.' }'.$this->get_content_text_audio($file, $height).'{%}';

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

                $content .= '{%:'.'<button  class="tabButtons" onclick="selectedTab(this)">'.'  '.$shortname. '</button>'.' }'.$this->get_content_text_image($file, $height).'{%}';

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
        ];

        if ($height !== null) {
            $styles['min-height'] = $height;
            // $styles['min-height'] = '100%';
        }

        $attributes1 = [
            'controls' => '',
            'style' => $this->build_style_attribute($styles),
            'src' => $this->get_file_url($file).'#view=FitH'
        ];
        $tag = html_writer::tag('iframe','',$attributes1);
        
        // Wrap the pdf viewer in divs to enable extra scrolling
        $final_tag = "<div style='height:".$height."; overflow-y:scroll;'><div style='height:200vh'>".$tag."</div></div>";
        return $final_tag;
    }

    protected function get_content_text_video($file, $height = null)
    {
        $styles = [
            'width' => '100%',
        //     'height' => '100%',
        ];

        $attributes = [
            'controls' => '',
            'controlsList'=>'nodownload', 
            'oncontextmenu'=>'return false;',
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
            'controlsList'=>'nodownload', 
            'oncontextmenu'=>'return false;',
            'style'  => $this->build_style_attribute($styles),
            'src'    => $this->get_file_url($file),
            'type'   =>"audio/wav",
        ];
        return html_writer::tag('audio', '', $attributes);
    }

    protected function get_content_text_image($file, $height = null)
    {
        $styles = [
            'width' => '100%',
        ];

        $attributes = [
            'style' => $this->build_style_attribute($styles),
            'src'   => $this->get_file_url($file),
            'alt'   => $file->get_filename(),
        ];

        return html_writer::tag('img','', $attributes);
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
