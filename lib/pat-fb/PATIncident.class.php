<?php
class PATIncident {

    function PATIncident ($info) {
        if (is_array($info)) {
            foreach ($info as $k => $v) {
                $this->$k = $v;
            }
        }
    }

    public function fieldsValidate () {
        foreach ($this as $k => $v) {
            switch ($k) {
                case 'reporter_id':
                    $this->validateReporterId();
                    break;
                case 'reportee_id':
                    $this->validateReporteeId();
                    break;
                case 'report_text':
                    $this->validateReportText();
                    break;
                case 'contactable':
                    $this->validateContactable();
                    break;
            }
        }
        return ($this->getValidationErrors()) ? false : true;
    }

    public function getValidationErrors ($field = false) {
        return ($field) ? $this->validation_errors[$field] : $this->validation_errors;
    }

    private function validateReporterId () {
        if (!$this->isValidId($this->reporter_id)) {
            $this->validation_errors['reporter_id'] = array('Reporter ID not a valid ID.');
            return false;
        }
        return true;
    }
    private function validateReporteeId () {
        if (!$this->isValidId($this->reportee_id)) {
            $this->validation_errors['reportee_id'] = array('Reportee ID not a valid ID.');
            return false;
        }
        return true;
    }
    private function isValidId ($x) {
        return is_numeric($x);
    }

    private function validateReportText () {
        if (249 > strlen($this->report_text)) {
            $this->validation_errors['report_text'] = array('Report text must be at least 250 characters.');
            return false;
        }
        return true;
    }

    private function validateContactable () {
        switch ($this->contactable) {
            case 'approval':
            case 'allowed' :
                return true;
                break;
            default:
                $this->validation_errors['contactable'] = array("Contactable preference '{$this->contactable}' not an understood value.");
                return false;
        }
    }

    public function save () {
        var_dump('gonna save now');
    }

}
