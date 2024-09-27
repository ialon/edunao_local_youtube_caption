<?php

/**
 * YouTube Captions test page.
 *
 * @package    local_youtube_caption
 * @copyright  Josemaria Bolanos <josemabol@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');

/**
 * Class edit_form
*/
class test_form extends moodleform {

    /**
     * Form definition
     */
    public function definition(): void {
        global $CFG;

        $mform =& $this->_form;

        // Header
        $mform->addElement('header','youtube_caption', get_string('youtube_caption', 'local_youtube_caption'));

        // AI Prompt
        $mform->addElement('textarea', 'prompt', get_string('aiprompt', 'local_youtube_caption'), 'wrap="virtual" rows="4" cols="30"');
        $mform->addRule('prompt', get_string('missingprompt', 'local_youtube_caption'), 'required', null, 'client');
        $mform->setDefault('prompt', get_string('aiprompt_default', 'local_youtube_caption'));
        $mform->setType('prompt', PARAM_TEXT);

        // Buttons.
        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
