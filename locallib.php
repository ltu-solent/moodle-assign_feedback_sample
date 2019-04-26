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

  public function supports_quickgrading() {
      return true;
  }

  public function get_quickgrading_html($userid, $grade) {
      $sample = $this->get_sample($grade->id);

      $pluginname = get_string('pluginname', 'assignfeedback_sample');
      $labeloptions = array('for'=>'quickgrade_sample_' . $userid,
                            'class'=>'accesshide');
      $selectoptions = array('name'=>'quickgrade_sample_' . $userid,
                               'id'=>'quickgrade_sample_' . $userid,
                               'class'=>'quickgrade');

      $out = html_writer::tag('label', $pluginname, $labeloptions);

      if ($sample->sample == 1) {
        $out .= html_writer::start_tag('input type="hidden" name="' . $selectoptions['name'] .'" value="0"'); //this hidden input sets the value to 0 if the checkbox is unchecked
        $out .= html_writer::start_tag('input type="checkbox" name="' . $selectoptions['name'] .'" value="1" checked');
      } else {
        $out .= html_writer::start_tag('input type="hidden" name="' . $selectoptions['name'] .'" value="0"');
        $out .= html_writer::start_tag('input type="checkbox" name="' . $selectoptions['name'] .'" value="1"');
      }

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
     $sample_text = '';
     if ($grade) {
         $sample = $this->get_sample($grade->id);
         if ($sample) {
             $sample_text = $sample->sample;
         }
     }
     // Note that this handles the difference between empty and not in the quickgrading
     // form at all (hidden column).
     $newvalue = optional_param('quickgrade_sample_' . $userid, false, PARAM_RAW);
     return ($newvalue !== false) && ($newvalue != $sample_text);
 }

public function save_quickgrading_changes($userid, $grade) {
    global $DB;
    $sample = $this->get_sample($grade->id);
    $quickgradesample = optional_param('quickgrade_sample_' . $userid, 0, PARAM_RAW);
    if ($sample) {
        $sample->sample = $quickgradesample;
        return $DB->update_record('assignfeedback_sample', $sample);
    } else {
        $sample = new stdClass();
        $sample->sample = $quickgradesample;
        $sample->sampleformat = FORMAT_HTML;
        $sample->grade = $grade->id;
        $sample->assignment = $this->assignment->get_instance()->id;
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
