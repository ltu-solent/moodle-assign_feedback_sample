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
            if ($sample->sample != 0) {
                //$check = $mform->addElement('checkbox', 'sample_check', get_string('check_label', 'assignfeedback_sample'));
                //$mform->setDefault('sample_check', true);
                $mform->addElement('advcheckbox', 'sample_check', get_string('check_label', 'assignfeedback_sample'), null, null, array(0, 1));
                $mform->setDefault('sample_check', true);
            } else {
                $mform->addElement('advcheckbox', 'sample_check', get_string('check_label', 'assignfeedback_sample'), null, null, array(0, 1));
            }

        } else {
            $mform->addElement('advcheckbox', 'sample_check', get_string('check_label', 'assignfeedback_sample'), null, null, array(0, 1));
        }

        return true;
    }

    public function supports_quickgrading() {
        return true;
    }

    public function get_quickgrading_html($userid, $grade) {
        $sample = 0;
        $checked = false;
        
        if ($grade) {
            
            $s = $this->get_sample($grade->id);
            
            if ($s) {
                $sample = $s->sample;
            }
            
            if ($sample == 1) {
                $checked = true;
            }
        }        
      
        $selectoptions = array('name'=>'quickgrade_sample_' . $userid,
                               'id'=>'quickgrade_sample_' . $userid,
                               );

        $out = html_writer::checkbox($selectoptions['name'], $sample , $checked, null, array('id'=>$selectoptions['id']));

        return $out;
    }

    /**
     * Has the plugin quickgrading form element been modified in the current form submission?
     *
     * @param int $userid The user id in the table this quickgrading element relates to
     * @param stdClass $grade The grade
     * @return boolean - true if the quickgrading form element has been modified
     */

    public function is_quickgrading_modified($userid, $grade) {
        $sampletext = '';
        if ($grade) {
            $sample = $this->get_sample($grade->id);
            if ($sample) {
                $sampletext = $sample->sample;
            }
        }
        // Note that this handles the difference between empty and not in the quickgrading
        // form at all (hidden column).
        $newvalue = optional_param('quickgrade_sample_' . $userid, null, PARAM_RAW);
        return ($newvalue !== false) && ($newvalue != $sampletext);
    }

    /**
     * Has the comment feedback been modified?
     *
     * @param stdClass $grade The grade object.
     * @param stdClass $data Data from the form submission.
     * @return boolean True if the comment feedback has been modified, else false.
     */
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
        $sampletext = 0;
        if ($grade) {
            $sample = $this->get_sample($grade->id);
            if ($sample) {
                $sampletext = $sample->sample;
            }
        }
var_dump($data);die();
        $formtext = $data->quickgrade_sample_ . $grade->userid;
     
        $formtext = $formtext ? 1 : 0;

        if ($formtext != $sampletext) {
            return false;
        } else {
            return true;
        }
    }

    public function save_quickgrading_changes($userid, $grade) {
        global $DB;
        $sample = $this->get_sample($grade->id);
        $quickgradesample = optional_param('quickgrade_sample_' . $userid, null, PARAM_RAW);
       
        if ($sample) {
            $sample->sample = $quickgradesample;
            return $DB->update_record('assignfeedback_sample', $sample);
        } else {
            $sample = new stdClass();
            $sample->assignment = $this->assignment->get_instance()->id;
            $sample->grade = $grade->id;
            $sample->sample = $quickgradesample;
            $sample->userid = $userid;
            return $DB->insert_record('assignfeedback_sample', $sample) > 0;
        }
    }

    public function save(stdClass $grade, stdClass $data) {
        global $DB, $USER;

        $sample = $this->get_sample($grade->id);
        if ($sample) {
            if ($data->sample_check !== $sample->sample) {
                $sample->sample = ($data->sample_check != null ? 1 : 0);
            }

            // if ($data->sample_check !== $sample->sample) {
                // $sample->sample = $data->sample_check;
            // }

			return $DB->update_record('assignfeedback_sample', $sample);

        } else {
            $sample = new stdClass();
            $sample->assignment = $this->assignment->get_instance()->id;
            $sample->grade = $grade->id;
            $sample->sample = ($data->sample_check != null ? 1 : 0);
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
		if($sample){
			if ($sample->sample == 1) {
				$sample_text = 'Yes';
				return format_text($sample_text, FORMAT_HTML);
			} else {
        $sample_text = 'No';
        return format_text($sample_text, FORMAT_HTML);
      }

		}
        return '';
    }
}
