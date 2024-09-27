<?php
/**
 * Plugin to extract captions from YouTube videos
 *
 * @package    local_youtube_caption
 * @copyright  Josemaria Bolanos <josemabol@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = "YouTube Captions";

$string['testpage'] = "YouTube Captions Test Page";
$string['youtube_caption'] = "YouTube Captions";
$string['aiprompt'] = "AI Prompt";
$string['aiprompt_default'] = "Test:

Sample test with URL in the middle https://www.youtube.com/watch?v=MNgJBIx-hK8 of the text

Single URL repeated
https://www.youtube.com/watch?v=MNgJBIx-hK8

URL with params no captions
https://www.youtube.com/watch?v=FM7MFYoylVs&list=RD__TmPtQ9hxg&index=15

URL with params
https://www.youtube.com/watch?v=SSCzDykng4g&list=RD__TmPtQ9hxg&index=17

Short format URL
https://youtu.be/aHjpOzsQ9YI?si=Hd-GhvybVlrpPAFd

Short format URL with no params
https://youtu.be/8UsXq64gl58

Short format URL no captions
https://youtu.be/FM7MFYoylVs";
$string['missingprompt'] = "Please enter an AI prompt";
