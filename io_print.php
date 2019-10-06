<?php

function file_print($txt, $overwrite = false)
{
    global $CFG, $DB;

    // $myfile = fopen($CFG->wwwroot."/blocks/file/log.txt", "w") or die("Unable to open file!");

    if ($overwrite)
    {
        $myfile = fopen("$CFG->dirroot/blocks/file/log.txt", "w") or die("Unable to open file!"); 
    }
    else
    {
        $myfile = fopen("/var/www/intermusic.lmta.lt/blocks/file/log.txt", "a+") or die("Unable to open file!");
    }

    fwrite($myfile, $txt."\n") or die('fwrite failed');
    fclose($myfile);
}
