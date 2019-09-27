<?php
// This function is copied and adapted from block_html and should be reviewed.
// Although it works, it might be better to adapt the one from https://docs.moodle.org/dev/File_API#Serving_files_to_users.
function block_file_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload, array $options = array())
{
    global $DB, $CFG, $USER;

    echo "<string>console.log('PARENT CONTEXT ');</string>";
    echo "<string>console.log('".$parentcontext->instanceid." ');</string>"; 
    die;
    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    $parentcontext = $context->get_parent_context();
    echo "<string>console.log('PARENT CONTEXT ');</string>";
    echo "<string>console.log('".$parentcontext->instanceid." ');</string>";
    // If block is in course context, then check if user has capability to access course.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        // Get parent context and see if user have proper permission.
        $parentcontext = $context->get_parent_context();
        echo "<string>console.log('PARENT CONTEXT ');</string>";
        echo "<string>console.log('".$parentcontext->instanceid." ');</string>";

        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
            // Check if category is visible and user can view this category.
            $category = $DB->get_record('course_categories', array('id' => $parentcontext->instanceid), '*', MUST_EXIST);
            if (!$category->visible) {
                require_capability('moodle/category:viewhiddencategories', $parentcontext);
            }
        } else if ($parentcontext->contextlevel === CONTEXT_USER && $parentcontext->instanceid != $USER->id) {
            // The block is in the context of a user, it is only visible to the user who it belongs to.
            send_file_not_found();
        }
        // At this point there is no way to check SYSTEM context, so ignoring it.
    }

    if ($filearea !== 'file') {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (!$file = $fs->get_file($context->id, 'block_file', $filearea, 0, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    if ($parentcontext = context::instance_by_id($birecord_or_cm->parentcontextid, IGNORE_MISSING)) {
        if ($parentcontext->contextlevel == CONTEXT_USER) {
            // force download on all personal pages including /my/
            //because we do not have reliable way to find out from where this is used
            $forcedownload = true;
        }
    } else {
        // weird, there should be parent context, better force dowload then
        $forcedownload = true;
    }

    // NOTE: it would be nice to have file revisions here, for now rely on standard file lifetime,
    //       do not lower it because the files are dispalyed very often.
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}
