<?php

function file_print($txt, $overwrite = false)
{
    global $CFG, $DB;

    $path = "$CFG->dirroot/blocks/file/log.txt";

    $f = ((file_exists($path))? fopen($path, "a+") : fopen($path, "w+"));

    if ($overwrite)
    {
        // $myfile = fopen($path, "w") or die("Unable to open file!"); 
        $myfile = ((file_exists($path))? fopen($path, "w+") : fopen($path, "w+")) or die("Unable to open file!"); 
    }
    else
    {
        // $myfile = fopen($path, "a+") or die("Unable to open file!");
        $myfile = ((file_exists($path))? fopen($path, "a+") : fopen($path, "w+")) or die("Unable to open file!"); 
    }
    fwrite($myfile, $txt."\n") or die('fwrite failed');
    fclose($myfile);
}
