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

$videos = [];
$urls = [];

if ($testform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/youtube_caption/test.php');
} else if ($data = $testform->get_data()) {
    $prompt = $data->prompt;

    $extractor = new \local_youtube_caption\extractor();
    $urls = $extractor->extract_youtube_urls($prompt);
    $videos = $extractor->process_prompt($prompt);
}

echo $OUTPUT->header();

$testform->display();

if (!empty($urls)) {
    echo "<h2>Found URLs</h3>";
    echo "<ul>";
    foreach ($urls as $url) {
        echo "<li>" . $url . "</li>";
    }
    echo "</ul>";
}

if (!empty($videos)) {
    echo "<h2>Captions</h3>";
    foreach ($videos as $url => $video) {
        if ($video) {
            echo '<div style="overflow-wrap: anywhere;">';
            echo "<b>Title</b>: " . $video->title . "<br>";
            echo "<b>URL</b>: " . $video->url . "<br>";
            echo "<b>Description</b>: " . $video->description . "<br>";
            if ($video->caption) {
                echo "<b>Caption Language:</b> " . $video->language . "<br>";
                echo "<b>Caption URL</b>: " . $video->captionurl . "<br>";
                echo "<b>Caption</b>: " . $video->caption . "<br>";
            }
            echo "</div><br><br>";
        }
    }
}

echo $OUTPUT->footer();
