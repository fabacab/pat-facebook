<?php
class PATFacebookDatabase {
    private $db_type;
    private $handle;

    function PATFacebookDatabase ($db_type = 'postgres') {
        $this->db_type = $db_type;
        // Create a new connection.
        switch (strtolower($db_type)) {
            case 'postgresql':
                $this->db_type = 'postgres';
                // Fall through so we use "postgres" internally from now on.
            case 'postgres':
            default:
                if (!function_exists('pg_connect')) {
                    throw new Exception(__CLASS__ . ' requires PHP to have PostgreSQL database support.');
                }
            break;
        }
    }

    public function getType () {
        return $this->db_type;
    }
    public function getHandle () {
        return $this->handle;
    }

    public function connect ($str) {
        if ('postgres' === $this->db_type) {
            if (!$this->handle = pg_connect($str)) {
                throw new Exception('Failed to connect to PostgreSQL database: ' . pg_last_error());
            } else {
                return true;
            }
        }
    }
    
//    public function persist ($data) {
//        if ('postgres' === $this->db_type) {
//            $sql  = 'INSERT INTO incidents ';
//            $sql .= $this->asSql($data);
//            return pg_query($this->handle, $sql);
//        }
//    }
//
//    private function asSql ($data) {
//        $str = '(';
//        if (is_array($data)) {
//            $len = count($data);
//            // Write field/column names.
//            $i = 0;
//            foreach ($data as $k => $v) {
//                $str .= "$k";
//                if ($i != $len - 1) { // Add trailing comma if not last loop.
//                    $str .= ',';
//                }
//                $i++;
//            }
//            // Write field/column values.
//            $str .= ') VALUES (';
//            $i = 0;
//            foreach ($data as $v) {
//                switch ($this->db_type) {
//                    case 'postgres':
//                    default:
//                        $str .= pg_escape_string($v);
//                        if ($i != $len - 1) { // Add trailing comma if not last loop.
//                            $str .= ',';
//                        }
//                    break;
//                }
//                $i++;
//            }
//            $str .= ')';
//        }
//        return $str;
//    }
}
