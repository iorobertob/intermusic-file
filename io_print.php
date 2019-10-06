<?php

function file_print($txt)
{
    global $CFG, $DB;

    $myfile = fopen($CFG->wwwroot."/blocks/file/log.txt", "w") or die("Unable to open file!");
    // $activity_modules = $DB->get_record('course_modules',array('id' =>$parentcontext->instanceid));
    // $names = $DB->get_record('poster',array('id' =>$activity_modules->instance));
    // $nameinstance = $names->name;
    fwrite($myfile, $txt."\n") or die('fwrite failed');
    fclose($myfile);
}
