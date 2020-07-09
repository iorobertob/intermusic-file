<?php

/**
 * lmta.utility
 * Auxiliary print function to write to a file for debugging and logging purposes
 */
function file_print($txt, $overwrite = false)
{
    global $CFG, $DB;

    $path = "$CFG->dirroot/blocks/file/log.txt";

    $f = ((file_exists($path))? fopen($path, "a+") : fopen($path, "w+"));

    if ($overwrite)
    {
        $myfile = ((file_exists($path))? fopen($path, "w+") : fopen($path, "w+")) or die("Unable to overwrite file with: ".$txt); 
    }
    else
    {
        $myfile = ((file_exists($path))? fopen($path, "a+") : fopen($path, "w+")) or die("Unable to open and write file with: ".$txt); 
    }
    fwrite($myfile, $txt."\n") or die('fwrite failed');
    fclose($myfile);
}
