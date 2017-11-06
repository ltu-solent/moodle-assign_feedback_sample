<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   assignfeedback_sample
 * @copyright 2017 Southampton Solent University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

class assign_feedback_sample extends assign_feedback_plugin {

    public function get_name() {
        return get_string('sample', 'assignfeedback_sample');
    }

    public function get_sample($gradeid) {
        global $DB;
        return $DB->get_record('assignfeedback_sample', array('grade' => $gradeid));
    }

    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {

        if ($grade) {
            $sample = $this->get_sample($grade->id);
        }

        if ($sample) {
            if ($sample->sample != '0') {
                $check = $mform->addElement('checkbox', 'sample_check', get_string('check_label', 'assignfeedback_sample'));
                $mform->setDefault('sample_check', true);
            } else {
                $mform->addElement('checkbox', 'sample_check', get_string('check_label', 'assignfeedback_sample'));
            }        
            
        } else {
            $mform->addElement('checkbox', 'sample_check', get_string('check_label', 'assignfeedback_sample'));         
            
        }		

        return true;
    }

    /**
     * Get the double marking grades from the database.
     *
     * @param int $gradeid
     * @return stdClass|false The double marking grades for the given grade if it exists.
     *                        False if it doesn't.
     */
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
        if ($grade) {
            $sample = $this->get_sample($grade->id);
        }

        if ($sample) {
            if ($sample->sample == $data->sample_check) {
                return false;
            }
        } else {
            return true;
        }
        return true;
    }

    public function save(stdClass $grade, stdClass $data) {
        global $DB, $USER;
        $sample = $this->get_sample($grade->id);
        if ($sample) {
            if ($data->sample_check !== $sample->sample) {
                $sample->sample = $data->sample_check;                            
            }

            if ($data->sample_check !== $sample->sample) {
                $sample->sample = $data->sample_check;                               
            }
			
			return $DB->update_record('assignfeedback_sample', $sample);
			
        } else {
            $sample = new stdClass();
            $sample->assignment = $this->assignment->get_instance()->id;
            $sample->grade = $grade->id;
            $sample->sample = $data->sample_check;
            $sample->userid = $USER->id;
            return $DB->insert_record('assignfeedback_sample', $sample) > 0;
        }
    }

    /**
     * Display the comment in the feedback table.
     *
     * @param stdClass $grade
     * @param bool $showviewlink Set to true to show a link to view the full feedback
     * @return string
     */
    public function view_summary(stdClass $grade, & $showviewlink) {
		global $DB;
        $sample = $this->get_sample($grade->id);
        if ($sample->sample == 1) {                          
            $sample_text = "Yes";
            return format_text($sample_text, FORMAT_HTML);
        }
        return '';
    }   
}
