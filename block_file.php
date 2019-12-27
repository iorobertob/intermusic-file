<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/blocks/file/io_print.php");

class block_file extends block_base
{

    public function instance_config_save($data, $nolongerused = false)
    {
        global $DB, $CFG, $PAGE;

        $data->file = file_save_draft_area_files($data->select_file, $this->context->id, 'block_file', 'file', 0, array('subdirs' => false, 'maxfiles' => -1), '@@PLUGINFILE@@/');

        // The context of the module
        $context = $PAGE->context;

        // In the Intermusic documentation collection code is the first element in the encoded filename 
        // https://github.com/iorobertob/intermusic/wiki/Naming-Convention
        // 0 is for the collection string
        $collection_index = 0;
        $collection = [];
        $collection = $this->get_item_from_filename($context, $collection_index);


        if($collection !== null){
            // TODO: filter for the case when names do not contain this format
            // Commit to database the collection that the first part of the name indicates
            $DB->set_field('poster', 'rs_collection', $collection[0], array('name' => $collection[1]));
            
            // Findout which ID corresponds to this file in RS
            $request_json = $this->get_file_fields_metadata($collection[0]);

            try {
                $DB->set_field('poster', 'rs_id', $request_json[1][0]["ref"], array('name' => $collection[1]));
            } catch (Exception $e) {
                file_print("Exception in Commit to DB:", true);
            }
        }

        return parent::instance_config_save($data, $nolongerused);
    }

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

    /** 
     * Item is each one of the parts in a file name like: item_item_item.extension
     * If filenames of files uploaded to this poster contain information separated by _ (undesrcore), this 
     * function retreives one of those elements from the first of the files to upload. 
     * @param Context  $context the context of the current course
     * @param String   $item_number is the position number of the filename to get
     * @return String  $item is the piece of string from the filename of the first file in the upload. 
    **/
    function get_item_from_filename($context, $item_number)
    {
        global $DB, $CFG, $PAGE;

        // TODO: here to implement the autopopulation of metadata, from files' metadata
        $activity_module      = $DB->get_record('course_modules',array('id' =>$context         ->instanceid)); // get the module where the course is the current course
        $poster_instance      = $DB->get_record('poster',        array('id' =>$activity_module ->instance  )); // get the name of the module instance 
        $poster_name          = $poster_instance->name;
        $autopopulateCheckbox = $poster_instance->autopopulate;
        
        // Get files array and their names, split them by '_' and return the first of those divisions. 
        $fs              = get_file_storage();
        $files           = $fs->get_area_files($this->context->id, 'block_file', 'file', 0);
        $keys            = array_keys($files);

        if (count($files) > 1 ){
            $filename        = $files[$keys[1]] -> get_filename();
            $filename_parts  = explode("_", $filename);
            $item            = $filename_parts[$item_number];
            $characteristics = $filename_parts[2];

            $items    = [];
            $items[0] = $item;
            $items[1] = $poster_name;
            return $items;
        }
        else {
            return null;
        }
        
    }

    /**
     * Get the fields from the Resourcespae metadata
     */
    function get_file_fields_metadata($string)
    {
        $api_result = $this->do_api_search($string);
        return $api_result;
    }

    /**
     * Do an API requeuest with 
     */
    function do_api_search($string)
    {
        $this->init_resourcespace();
        // Set the private API key for the user (from the user account page) and the user we're accessing the system as.
        $private_key = $this->api_key;
        $user        = $this->api_user;
        $url         = $this->resourcespace_api_url ;

        // Formulate the query
        $query="user=" . $user . "&function=do_search&param1=".$string."&param2=&param3=&param4=&param5=&param6=";

        // Sign the query using the private key
        $sign=hash("sha256",$private_key . $query);

        // Make the request and output the JSON results.
        // $results=json_decode(file_get_contents("https://resourcespace.lmta.lt/api/?" . $query . "&sign=" . $sign));
        $results=json_decode(file_get_contents($url . $query . "&sign=" . $sign));
        $results=file_get_contents($url . $query . "&sign=" . $sign);
        $results=json_decode(file_get_contents($url . $query . "&sign=" . $sign), TRUE);
        
        $result = [];
        $result[0] = "https://resourcespace.lmta.lt/api/?" . $query . "&sign=" . $sign;
        $result[1] = $results;

        return $result;
    }

    /**
     * Initialise Resourcespace API variables
     */
    private function init_resourcespace()
    {
        $this->config       = get_config('resourcespace');
        $this->resourcespace_api_url = get_config('resourcespace', 'resourcespace_api_url');
        $this->api_key      = get_config('resourcespace', 'api_key');
        $this->api_user     = get_config('resourcespace', 'api_user');
        $this->enable_help  = get_config('resourcespace', 'enable_help');
        $this->enable_help_url = get_config('resourcespace', 'enable_help_url');
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
                        // break;
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
            // $styles['min-height'] = $height;
            $styles['min-height'] = '100%';
        }

        // TODO: This section used to render a PDFjs source, but it has CORS issues... current implementation 
        // $viewerUrl = new moodle_url('/blocks/file/pdfjs/web/viewer.html');
        // $viewerUrl->param('file', $this->get_file_url($file));

        // $attributes = [
        //     'src' => $viewerUrl,
        //     'style' => $this->build_style_attribute($styles),
        // ];


        $attributes1 = [
            'controls' => '',
            'style' => $this->build_style_attribute($styles),
            'src' => $this->get_file_url($file)
        ];
        $tag = html_writer::tag('iframe','',$attributes1);
        
        $final_tag = "<div style='height:".$height."; overflow-y:scroll;'><div style='height:200vh'>".$tag."</div></div>";
        return $final_tag;
        // return html_writer::tag('iframe', $this->get_content_text_default($file, $height), $attributes);
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
        $tag = html_writer::tag('audio', '', $attributes);
        
        // $tag = $this->get_file_url($file);

        // file_print(serialize($styles));
        // file_print($this->build_style_attribute($styles));

        // $tag = '<audio style="width: 90%" class="vjs-tech"  tabindex="-1" src="'.$this->get_file_url($file).'"></audio>';



        // $tag = html_writer::tag('audio', '', $attributes);
        // echo "<script>console.log('".$tag."');</script>";
        // $audio_exploded = explode(".",$this->get_file_url($file));
        // $audio_exploded = preg_split('~.(?=[^.]*$)~', $this->get_file_url($file)); 
        // $audio_mp3 = $audio_exploded[0].".mp3";


// <source src="'.$audio_mp3.'" type="audio/mp3"/>
        // $tag = '<audio controls="" controlsList="nodownload" style="width: 100%">
        //     <source src="'.$this->get_file_url($file).'" type="audio/x-wav"/>
            
        // </audio>';
        // file_print($tag);

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
