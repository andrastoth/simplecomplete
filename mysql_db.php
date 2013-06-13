<?php
if (!class_exists('Database')) {
    class Database {
        static private $instance;
        private $server = 'server';
        private $root = 'user';
        private $pass = 'pass';
        private $selDb = 'database';
        public $conn = null;
        private $test = false;
        private $returnDatesAsStrings = false;
        public $databaseConnected = false;
        static function getInstance() {
            if (!self::$instance)
                self::$instance = new Database();
            return self::$instance;
        }
        static function escape($sql) {
            return str_replace("'", "''", $sql);
        }
        static function fetch($res) {
            if ($res && mysql_num_rows($res)) {
                return mysql_fetch_array($res, MYSQL_ASSOC);
            }
            return false;
        }
        static function jsonEncode($mixed, $flags = -1) {
            if ($flags == -1)
                $flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
            return json_encode($mixed, $flags);
        }
        function setTest($test) {
            if (!$this->conn) {
                $this->test = $test;
            } else {
                throw Exception('DB already open, cannot change DB');
            }
        }
        function getTest() {
            return $this->test;
        }
        public function returnDatesAsStrings($value) {
            $this->returnDatesAsStrings = $value;
        }
        public function connect($server = false, $root = false, $pass = false, $selDb = false) {
            if (!$server) {
                $server = $this->server;
            }
            if (!$root) {
                $root = $this->root;
            }
            if (!$pass) {
                $pass = $this->pass;
            }
            if (!$selDb) {
                $selDb = $this->selDb;
            }
            if (!$this->databaseConnected || $this->conn === null) {
                $this->conn = mysql_connect($server, $root, $pass);
                mysql_select_db($selDb);
                mysql_query($sql = "SET NAMES UTF8;");
                if (!$this->conn || $this->conn === null) {
                    $this->databaseConnected = false;
                    throw new Exception();
                    return false;
                } else {
                    $this->databaseConnected = true;
                    return true;
                }
                return false;
            } else {
                return true;
            }
        }
        public function disconnect() {
            mysql_close($this->conn);
            $this->databaseConnected = true;
        }
        public function queryResource($sql, $params = array()) {
            $sql = $this->queryDebug($sql, $params);
            $res = mysql_query($sql, $this->conn);
            return $res;
        }
        public function query($sql, $params = array(), $actionQuery = false) {
            if ($this->conn === null)
                $this->connect();
            $sql = $this->queryDebug($sql, $params);
            $res = mysql_query($sql, $this->conn);
            if (!$res) {
                $this->printErrors();
                die('Database error!');
            }
            $data = array();
            if (!$actionQuery) {
                if (mysql_num_rows($res)) {
                    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
                        $data[] = $row;
                    }
                    mysql_free_result($res);
                }
            }
            return $data;
        }
        public function queryDebug($sql, $params = array()) {
            foreach ($params as $param) {
                if (strpos($sql, '?') !== false) {
                    $sql = substr($sql, 0, strpos($sql, '?')) . "'" . $param . "'" . substr($sql, strpos($sql, '?') + 1);
                }
            }
            return $sql;
        }
        public function printErrors() {
            $err = mysql_error();
            print_r($err);
        }
        public function getHTMLtable($sql, $tableId, $params = array(), $source = false, $target = false) {
            $res           = $this->queryResource($sql, $params);
            $ret           = '';
            $firstrow      = true;
            $firstrow_data = '';
            while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
                if ($firstrow == true) {
                    $ret .= '<table id = "' . $tableId . '" ><thead><tr>';
                } else {
                    $ret .= '<tr>';
                }
                foreach ($row as $key => $value) {
                    if ($firstrow == true) {
                        $ret .= '<th>' . $this->headDefinier($key) . '</th>';
                        $firstrow_data .= '<td>' . $this->typeChecker($value, $source, $target) . '</td>';
                    } else {
                        $ret .= '<td>' . $this->typeChecker($value, $source, $target) . '</td>';
                    }
                }
                if ($firstrow == true) {
                    $firstrow = false;
                    $ret .= '</tr><tbody>';
                    $ret .= '<tr>' . $firstrow_data . '</tr>';
                } else {
                    $ret .= '</tr>';
                }
            }
            $ret .= '</tbody>';
            $ret .= '</table>';
            return $ret;
        }
        public function typeChecker($data, $source, $target) {
            if ($data instanceof DateTime) {
                $data = $data->format('Y-m-d H:m:s');
            } else if ($target != $target) {
                $data = iconv($source, $target, $data);
            }
            return $data;
        }
        public function headDefinier($data) {
            include ('name_def.php');
            if (isset($nevek[$data])) {
                $data = $nevek[$data];
            }
            return $data;
        }
    }
}
?>
