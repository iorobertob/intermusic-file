<?php
defined('MOODLE_INTERNAL') || die();

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
        $data->file = file_save_draft_area_files($data->select_file, $this->context->id, 'block_file', 'file', 0, array('subdirs' => false, 'maxfiles' => -1), '@@PLUGINFILE@@/');

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

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'block_file', 'file', 0);

        $content = null;

	    $count = 0;

//////////////////////////// SORTING TABS ALGORITHM /////////////////////////
        $sortingArray =array(
            "SCR+IPA.pdf", 
            "TXT+LYR.pdf",
            "IPA+TXT+W2W.pdf",
            "VID-HR.mp4",
            "AUD.wav",
            "RCN.wav",
            "RCS.wav",
            "RCP.wav",
            "SCR",
            "VID",
            "HR",
            "LC",
            "CLC"
        );
        $filesSorted = []
        $newNames = []

        foreach($files as $file)
        {
            $sortString = explode( "_", $file -> get_filename() );
            
            for($x = 0; $x <= sizeof($sortingArray); $x++)
            {
               if ($sortString[1] == $sortingArray[$x])
               {
                    array_push($filesSorted, $file) ;
                    // array_push($newNames, $sortString)
                    break;
               }
            }
            
        }
        var_dump($filesSorted);
////////////////////////////  \SORTING TABS ALGORITHM /////////////////////////

        // foreach ($files as $file) 
        foreach ($filesSorted as $file)
        {
            if ($count == 0)    
            {
                $count += 1;
                continue;
            }
            else
            {
                $count += 1;
            }

    	    if ($file->is_directory()) 
            {
                continue;
            }

            $filterOptions = new stdClass;
            // $filterOptions->overflowdiv = true;
            $filterOptions->noclean = true;

            $mimeType = $file->get_mimetype();
            echo "<script>console.log('MIME: ".$mimeType."');</script>";

            if ($mimeType === 'application/pdf') {
                //$content = $this->get_content_text_pdf($file, $height);
                $splitname = explode("_", $file->get_filename() );
                $shortname = $splitname[1];
                $content .= '{%:'.'<button> '.'  '.$shortname. '</button>'.'  }'.$this->get_content_text_pdf($file, $height).'{%}';

		        // $content .= '{%:'.'<button> '.'  '.$file->get_filename(). '</button>'.'  }'.$this->get_content_text_pdf($file, $height).'{%}';
                
                //$content = format_text($content, FORMAT_HTML, $filterOptions);
                continue;
                //break;
            }

            if (substr($mimeType, 0, 5) === 'video') 
            {
                $splitname = explode("_", $file->get_filename() );
                $shortname = $splitname[1];
                $content .= '{%:'.'<button> '.'  '.$shortname. '</button>'.'  }'.$this->get_content_text_video($file, $height).'{%}';
                // $content .= '{%:'.'<button> '.'  '.$file->get_filename(). '</button>'.'  }'.$this->get_content_text_video($file, $height).'{%}';
		        continue;
            }

            if (substr($mimeType, 0, 5) === 'audio') 
            {
                $splitname = explode("_", $file->get_filename() );
                $shortname = $splitname[1];
                $content .= '{%:'.'<button> '.'  '.$shortname. '</button>'.'  }'.$this->get_content_text_audio($file, $height).'{%}';
                // $content .= '{%:'.'<button> '.'  '.$file->get_filename(). '</button>'.'  }'.$this->get_content_text_audio($file, $height).'{%}';
                continue;
            }

            if (in_array($mimeType, [
                'image/gif',
                'image/png',
                'image/jpeg',
                'image/svg+xml',
            ])) 
            {
                $splitname = explode("_", $file->get_filename() );
                $shortname = $splitname[1];
                $content .= '{%:'.'<button> '.'  '.$shortname. '</button>'.'  }'.$this->get_content_text_image($file, $height).'{%}';
                // $content .= '{%:'.'<button> '.'  '.$file->get_filename(). '</button>'.'  }'.$this->get_content_text_image($file, $height).'{%}';
                continue;
            }

            $content = $this->get_content_text_default($file, $height);
            $content = format_text($content, FORMAT_HTML, $filterOptions);
            break;
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
            'height' => '100%',
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
