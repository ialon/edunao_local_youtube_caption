<?php

/**
 * This plugin extracts captions from YouTube videos.
 *
 * @package    local_youtube_caption
 * @copyright  Josemaria Bolanos <josemabol@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

require_login();

$context = context_system::instance();

$title = get_string('testpage', 'local_youtube_caption');

$PAGE->set_context($context);
$PAGE->set_url('/local/youtube_caption/test.php');
$PAGE->navbar->add($title);
$PAGE->set_heading($title);

require_once('test_form.php');
$testform = new test_form('test.php');

$transcripts = [];

if ($testform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/youtube_caption/test.php');
} else if ($data = $testform->get_data()) {
    $prompt = $data->prompt;

    $extractor = new \local_youtube_caption\extractor();

    if ($urls = $extractor->extract_youtube_urls($prompt)) {
        foreach ($urls as $url) {
            $transcripts[$url] = $extractor->process_video($url);
        }
    }
}

echo $OUTPUT->header();

$testform->display();

foreach ($transcripts as $url => $transcript) {
    if ($transcript) {
        echo $url . ":<br>" . $transcript . "<br><br>";
    } else {
        echo $url . ":<br>NO CAPTION<br><br>";
    }
}

echo $OUTPUT->footer();
