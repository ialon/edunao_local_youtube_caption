<?php

/**
 * This plugin extracts captions from YouTube videos.
 *
 * @package    local_youtube_caption
 * @copyright  2024 Josemaria Bolanos <josemabol@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_youtube_caption;

class extractor {
    /**
     * Fetches the content of a web page using cURL.
     *
     * @param string $url The URL of the web page to fetch.
     * @return string|false The content of the web page as a string, or false if an error occurred.
     */
    function get_web_page_content($url) {
        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
        // Execute the request
        $result = curl_exec($ch);
    
        // Check for errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
            return false;
        }
    
        // Close cURL session
        curl_close($ch);
    
        return $result;
    }

    /**
     * Extracts YouTube URLs from a given text prompt.
     *
     * This function searches the provided text for YouTube video URLs and returns
     * an array of unique URLs found. If no URLs are found, it returns false.
     *
     * @param string $prompt The text prompt to search for YouTube URLs.
     * @return array An array of unique YouTube URLs.
     */
    public function extract_youtube_urls($prompt) {
        $pattern = '/https?:\/\/(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)[\w\-]+/';
        preg_match_all($pattern, $prompt, $matches);
        return $matches[0] ? array_unique($matches[0]) : [];
    }
    
    /**
     * Extracts the caption URL from the given YouTube video content.
     *
     * This function searches for the URL of the YouTube captions API.
     * If a match is found, it returns the decoded URL
     * If no match is found, it returns false.
     *
     * @param string $content The content to search for the caption URL.
     * @return string|false The extracted caption URL or false if not found.
     */
    function extract_caption_url($content) {
        $pattern = '/https:\/\/www\.youtube\.com\/api\/timedtext\?v=[^"]+/';
        preg_match($pattern, $content, $matches);
    
        if (empty($matches)) {
            return false;
        }
        
        return str_replace('\u0026', '&', $matches[0]);
    }
    
    /**
     * Processes a YouTube video URL to extract metadata and captions.
     *
     * This method fetches the content of the YouTube page, extracts the title,
     * description, and language, and attempts to retrieve captions if available.
     *
     * @param string $videourl The URL of the YouTube video to process.
     * @return \stdClass|false An object containing video metadata and captions, or false on failure.
     */
    public function process_video($videourl) {
        // Fetch the content of the YouTube page
        $videopage = self::get_web_page_content($videourl);

        // Check if the content was successfully fetched
        if ($videopage !== false) {
            $video = new \stdClass();

            // Add  the URL
            $video->url = $videourl;

            // Extract title
            $pattern = '/<meta property="og:title" content="([^"]+)"/';
            preg_match($pattern, $videopage, $matches);
            $video->title = $matches[1];

            // Extract description
            $pattern = '/<meta property="og:description" content="([^"]+)"/';
            preg_match($pattern, $videopage, $matches);
            $video->description = $matches[1];

            // Extract caption if available
            $captionurl = self::extract_caption_url($videopage);
            $video->captionurl = $captionurl;
            $video->caption = false;

            if ($captionurl !== false) {
                // Extract lang parameter from caption URL
                $pattern = '/lang=([^&]+)/';
                preg_match($pattern, $captionurl, $matches);
                $video->language = $matches[1];

                $captionxml = self::get_web_page_content($captionurl);
                $xml = simplexml_load_string($captionxml);
                
                $caption = [];
                foreach ($xml->text as $text) {
                    $caption[] = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                }
        
                $video->caption = implode("\n", $caption);
            }

            return $video;
        } else {
            // echo "Failed to fetch page content.";
            return false;
        }
    }

    /**
     * Processes the given prompt to extract YouTube URLs and retrieve their transcripts.
     *
     * This method extracts YouTube URLs from the provided prompt and processes each video
     * to obtain its transcript. The transcripts are then returned as an associative array
     * where the keys are the URLs and the values are the corresponding transcripts.
     *
     * @param string $prompt The input string containing potential YouTube URLs.
     * @return array An associative array of YouTube URLs and their corresponding transcripts.
     */
    public function process_prompt($prompt) {
        $videos = [];

        if ($urls = self::extract_youtube_urls($prompt)) {
            foreach ($urls as $url) {
                $videos[$url] = self::process_video($url);
            }
        }

        return $videos;
    }
}
