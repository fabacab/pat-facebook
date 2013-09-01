<?php
require_once 'PATFacebookDatabase.class.php';

class PATIncident {
    private $db;

    function PATIncident ($info) {
        $this->db = new PATFacebookDatabase('postgres');
        $this->db->connect(psqlConnectionStringFromDatabaseUrl());
        if (is_array($info)) {
            foreach ($info as $k => $v) {
                $this->$k = $v;
            }
            if (1 === count($info) && isset($this->id)) {
                $this->loadFromDatabase();
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

    /**
     * This assumes a table kind of like the following:
     * CREATE TABLE incidents (
     *   id            BIGSERIAL PRIMARY KEY,
     *   reporter_id   BIGINT,
     *   reportee_id   BIGINT,
     *   report_text   TEXT,
     *   contactable   VARCHAR(255),
     *   report_date   TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
     * );
     */
    public function save () {
        if ('postgres' === $this->db->getType()) {
            $result = pg_query_params(
                $this->db->getHandle(),
                'INSERT INTO incidents (reporter_id, reportee_id, report_text, contactable)' .
                ' VALUES ($1, $2, $3, $4) RETURNING id;',
                array($this->reporter_id, $this->reportee_id, $this->report_text, $this->contactable)
            );
            if (pg_num_rows($result)) {
                return pg_fetch_object($result);
            }
        }
    }

    private function loadFromDatabase () {
        if ('postgres' === $this->db->getType()) {
            $result = pg_query_params(
                $this->db->getHandle(),
                'SELECT * FROM incidents WHERE id = $1 LIMIT 1',
                array($this->id)
            );
            if (pg_num_rows($result)) {
                foreach (pg_fetch_object($result) as $k => $v) {
                    $this->$k = $v;
                }
            }
        }
    }

}
