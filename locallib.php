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
 * Class file for feedback type
 * @package   assignfeedback_sample
 * @copyright 2017 Southampton Solent University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Assign feedback Sample
 */
class assign_feedback_sample extends assign_feedback_plugin {

    /**
     * Get feedback name
     *
     * @return string
     */
    public function get_name() {
        return get_string('sample', 'assignfeedback_sample');
    }

    /**
     * Get the sample record for this grade item
     *
     * @param int $gradeid
     * @return stdClass
     */
    public function get_sample($gradeid) {
        global $DB;
        return $DB->get_record('assignfeedback_sample', array('grade' => $gradeid));
    }

    /**
     * Form elements for grading an assignment
     *
     * @param stdClass $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data Formdata
     * @param int $userid
     * @return bool Returns true if the elements have been created
     */
    public function get_form_elements_for_user($grade, MoodleQuickForm $mform, stdClass $data, $userid) {
        if ($grade) {
            $sample = $this->get_sample($grade->id);
        }
        $mform->addElement('selectyesno', 'sample', get_string('label', 'assignfeedback_sample'));
        if ($sample) {
            $mform->setDefault('sample', $sample->sample);
        } else {
            $mform->setDefault('sample', '0');
        }

        return true;
    }

    /**
     * Supports Quickgrading
     *
     * @return boolean
     */
    public function supports_quickgrading() {
        return true;
    }

    /**
     * The HTML required for quickgrading column
     *
     * @param int $userid
     * @param stdClass $grade Grade item
     * @return string HTML
     */
    public function get_quickgrading_html($userid, $grade) {
        $selected = 0;

        if ($grade) {
            $sample = $this->get_sample($grade->id);
            if ($sample) {
                $selected = $sample->sample;
            }
        }

        global $DB;
        $locked = $DB->get_record_sql('SELECT locked FROM {grade_items} where itemmodule = ? AND iteminstance = ?',
            array('assign', $this->assignment->get_instance()->id));
        if ($locked->locked != 0) {
            $disabled = ' disabled';
        } else {
            $disabled = '';
        }

        $selectoptions = array('name' => 'quickgrade_sample_' . $userid,
                               'id' => 'quickgrade_sample_' . $userid,
                               'disabled' => $disabled,
                               );

        $out = html_writer::select_yes_no('quickgrade_sample_' . $userid, $selected , $selectoptions);

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
        $newvalue = optional_param('quickgrade_sample_' . $userid, false, PARAM_BOOL);

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

        $formtext = $data->sample;
        if ($formtext == $sampletext) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Save quick grading changes
     *
     * @param int $userid
     * @param stdClass $grade
     * @return mixed
     */
    public function save_quickgrading_changes($userid, $grade) {
        global $DB;
        $sample = $this->get_sample($grade->id);
        $quickgradesample = optional_param('quickgrade_sample_' . $userid, false, PARAM_BOOL);

        if ($sample) {
            $sample->sample = $quickgradesample;
            if (isset($quickgradesample)) {
                return $DB->update_record('assignfeedback_sample', $sample);
            } else {
                return null;
            }
        } else {
            $sample = new stdClass();
            $sample->assignment = $this->assignment->get_instance()->id;
            $sample->grade = $grade->id;
            $sample->sample = $quickgradesample;
            $sample->userid = $userid;
            return $DB->insert_record('assignfeedback_sample', $sample) > 0;
        }
    }

    /**
     * Save the changes
     *
     * @param stdClass $grade
     * @param stdClass $data Form data
     * @return void
     */
    public function save(stdClass $grade, stdClass $data) {
        global $DB, $USER;
        $sample = $this->get_sample($grade->id);
        if ($sample) {
            if ($data->sample !== $sample->sample) {
                $sample->sample = $data->sample;
            }
            return $DB->update_record('assignfeedback_sample', $sample);
        } else {
            $sample = new stdClass();
            $sample->assignment = $this->assignment->get_instance()->id;
            $sample->grade = $grade->id;
            $sample->sample = $data->sample;
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
        if ($sample) {
            if ($sample->sample == 1) {
                $sampletext = 'Yes';
                return format_text($sampletext, FORMAT_HTML);
            }
        }
        return '';
    }
}
