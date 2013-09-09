<?php
require_once 'FacebookEntity.class.php';
require_once 'PATFacebookDatabase.class.php';

class PATFacebookUser extends FacebookEntity {
    private $db;
    private $preferences;

    public function __construct ($FB) {
        parent::__construct($FB, 'me');
        $this->db = new PATFacebookDatabase('postgres');
        $this->db->connect(psqlConnectionStringFromDatabaseUrl());
        if (!$this->preferences = $this->loadPreferences()) {
            $this->createNewUser();
        }
        // Try again, should give us defaults now.
        $this->preferences = $this->loadPreferences();
    }

    public function getPreferences () {
        return $this->preferences;
    }

    /**
     * This assumes a table kind of like the following: 
     * CREATE TABLE user_preferences (
     *   fbid                      BIGINT NOT NULL PRIMARY KEY,
     *   notify_on_same_reportee   BOOLEAN NOT NULL DEFAULT TRUE,
     *   notify_on_friend_reported BOOLEAN NOT NULL DEFAULT TRUE
     * );
     */
    private function loadPreferences () {
        $ret = array();
        $result = pg_query_params($this->db->getHandle(),
            'SELECT * FROM user_preferences WHERE fbid = $1',
            array($this->getId())
        );
        if (1 === pg_num_rows($result)) {
            $x = pg_fetch_assoc($result);
            foreach ($x as $k => $v) {
                switch ($k) {
                    case 'notify_on_same_reportee':
                    case 'notify_on_friend_reported':
                        $ret[$k] = ('t' === $v) ? true : false;
                    break;
                }
            }
            return $ret;
        } else {
            return false;
        }
    }

    public function savePreferences ($new_prefs) {
        if (!is_array($new_prefs)) { return false; }
        return pg_query_params($this->db->getHandle(),
            'UPDATE user_preferences SET notify_on_same_reportee=$1, notify_on_friend_reported=$2 WHERE fbid=$3',
            array(
                // Manually convert booleans the way Postgres expects.
                ($new_prefs['notify_on_same_reportee']) ? 't': 'f',
                ($new_prefs['notify_on_friend_reported']) ? 't' : 'f',
                $this->getId()
            )
        );
    }

    private function createNewUser () {
        return pg_query_params($this->db->getHandle(),
            'INSERT INTO user_preferences (fbid) VALUES ($1)',
            array($this->getId())
        );
    }
}
