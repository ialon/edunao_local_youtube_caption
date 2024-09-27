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
     * @return array|false An array of unique YouTube URLs or false if none are found.
     */
    function extract_youtube_urls($prompt) {
        $pattern = '/https?:\/\/(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)[\w\-]+/';
        preg_match_all($pattern, $prompt, $matches);
        return $matches[0] ? array_unique($matches[0]) : false;
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
     * Processes a YouTube video URL to extract and return its captions.
     *
     * This function performs the following steps:
     * 1. Fetches the content of the YouTube page using the provided video URL.
     * 2. Checks if the content was successfully fetched.
     * 3. Extracts the caption URL from the fetched YouTube page content.
     * 4. Fetches the caption XML content from the extracted caption URL.
     * 5. Parses the XML content to extract the transcript text.
     * 6. Decodes HTML entities in the transcript text.
     * 7. Outputs the transcript text.
     *
     * @param string $videourl The URL of the YouTube video.
     *
     * @return string|bool The transcript text if successful, false otherwise.
     */
    public function process_video($videourl) {
        // Fetch the content of the YouTube page
        $videopage = self::get_web_page_content($videourl);

        // Check if the content was successfully fetched
        if ($videopage !== false) {
            $captionurl = self::extract_caption_url($videopage);

            if ($captionurl !== false) {
                $captionxml = self::get_web_page_content($captionurl);
                $xml = simplexml_load_string($captionxml);
                
                $transcript = [];
                foreach ($xml->text as $text) {
                    $transcript[] = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                }
        
                return implode("\n", $transcript);
            } else {
                // echo "Failed extracting caption URL.";
                return false;
            }
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
        $urls = self::extract_youtube_urls($prompt);
        $transcripts = [];
        if ($urls) {
            foreach ($urls as $url) {
                $transcripts[$url] = self::process_video($url);
            }
        }
        return $transcripts;
    }
}
