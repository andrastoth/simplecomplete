<?php
if (!class_exists('Database')) {
	class Database {
		static private $instance;
		private $serverName = 'server';
		private $connectionInfo = array('CharacterSet' => 'UTF-8', 'Database' => 'db', 'LoginTimeout' => 10, 'ReturnDatesAsStrings' => false);
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
			if ($res && sqlsrv_has_rows($res)) {
				return sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
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
		public function connect($serverName = false, $connectionInfo = false) {
			if(!$serverName){
				$serverName = $this->serverName;
			}
			if(!$connectionInfo){
				$connectionInfo = $this->connectionInfo;
			}
			$this->connectionInfo['ReturnDatesAsStrings'] = $this->returnDatesAsStrings;
			if (!$this->databaseConnected || $this->conn === null) {
				$this->conn = sqlsrv_connect($serverName, $connectionInfo);
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
			sqlsrv_close($this->conn);
			$this->databaseConnected = true;
		}
		public function queryResource($sql, $params = array()) {
			$res = sqlsrv_query($this->conn, $sql, $params, array(
				"Scrollable" => 'forward'
			));
			return $res;
		}
		public function query($sql, $params = array(), $actionQuery = false) {
			if ($this->conn === null)
				$this->connect();
			$res = sqlsrv_query($this->conn, $sql, $params, array(
				"Scrollable" => 'forward'
			));
			if (!$res) {
				$this->printErrors();
				die('Database error!');
			}
			$data = array();
			if (!$actionQuery) {
				if (sqlsrv_has_rows($res)) {
					while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
						$data[] = $row;
					}
					sqlsrv_free_stmt($res);
				}
			} else {
				sqlsrv_free_stmt($res);
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
        function getHTMLtable($sql, $tableId, $params = array(), $source = false, $target = false) {
            $res = $this->queryResource($sql, $params);
			$ret           = '';
			$firstrow      = true;
			$firstrow_data = '';
			while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
				if ($firstrow == true) {
					$ret .= '<table id = "' . $tableId . '" ><thead><tr>';
				} else {
					$ret .= '<tr>';
				}
				foreach ($row as $key => $value) {
					if ($firstrow == true) {
						$ret .= '<th>' . $this->headDefinier($key) . '</th>';
						$firstrow_data = $firstrow_data . '<td>' . $this->typeChecker($value, $source, $target) . '</td>';
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
		public function printErrors() {
			$err = sqlsrv_errors();
			print_r($err);
		}
		public function typeChecker($data, $source, $target) {
			if ($source == '') {
				$source = "UTF-8";
			}
			if ($target == '') {
				$target = "UTF-8";
			}
			if ($data instanceof DateTime) {
				$data = $data->format('Y-m-d H:m:s');
			} else {
				$data = iconv($source, $target, $data);
			}
			return $data;
		}
		public function headDefinier($data) {
			include('name_def.php');
			if (isset($nevek[$data])) {
				$data = $nevek[$data];
			}
			return $data;
		}
	}
}
?>