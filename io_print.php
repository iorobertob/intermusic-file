<?php

$myfile = fopen("/var/www/intermusic.lmta.lt/blocks/file/log.txt", "w") or die("Unable to open file!");

$activity_modules = $DB->get_record('course_modules',array('id' =>$parentcontext->instanceid));
$names = $DB->get_record('poster',array('id' =>$activity_modules->instance));
$nameinstance = $names->name;

$txt = $parentcontext->instanceid;
fwrite($myfile, $txt."\n") or die('fwrite failed');
$txt = $context->id;
fwrite($myfile, $txt."\n") or die('fwrite failed');
$txt = $birecord_or_cm->id;
fwrite($myfile, $txt."\n") or die('fwrite failed');
$txt = $nameinstance;
fwrite($myfile, $txt."\n") or die('fwrite failed');
fclose($myfile);