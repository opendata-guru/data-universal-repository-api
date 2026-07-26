<?php
	class GuruDatabase extends mysqli {
		private string $dbCharset;
		private string $dbCollation;

		public function __construct() {
			$dbServername = 'db1234567890.hosting-data.io';
			$dbUsername = 'dbu1234567';
			$dbPassword = 'pw12345678';
			$dbName = 'dbs12345678';

			// charset: utf8mb3, utf8mb3_bin (deprecated, multibyte 3 is only UTF-8)
			//          utf8mb4, utf8mb4_bin (last one is prefered)
			$this->dbCharset = 'utf8mb4';

			// collation_connection: utf8mb3_bin (deprecated)
			//                       utf8mb4_bin (use 'bin' for case sensitive comparission)
			$this->dbCollation = 'utf8mb3_bin';

			parent::__construct($dbServername, $dbUsername, $dbPassword, $dbName);
			if ($this->connect_error) {
				throw new Exception('Connection failed: ' . $this->connect_error);
			}
			$this->set_charset($this->dbCharset);
			$this->query('SET collation_connection = ' . $this->dbCollation);

			$this->initTables($dbName);
		}

		// get an array of all lObjects
		public function getLObjects() {
			$result = $this->query('SELECT * FROM lobject');
			$objects = [];

			if ($result !== false) {
				if ($result->num_rows > 0) {
					while ($row = $result->fetch_assoc()) {
						$row['haspart'] = json_decode($row['haspart']);
						$row['ispartof'] = json_decode($row['ispartof']);

						$objects[] = $row;
					}
				}
			}

			return $objects;
		}

		// get lObject by lid
		public function getLObject($lid_in) {
			$stmt = $this->prepare('SELECT lid, pid, identifier, title, haspart, ispartof, sid, lastseen FROM lobject WHERE lid COLLATE ' . $this->dbCollation . ' =?');
			$stmt->bind_param('s', $lid_in);
			$stmt->execute();

			$stmt->bind_result($lid, $pid, $identifier, $title, $haspart, $ispartof, $sid, $lastseen);
			while ($stmt->fetch()) {
				$stmt->close();

				return (object) [
					'lid' => $lid,
					'pid' => $pid,
					'identifier' => $identifier,
					'title' => $title,
					'haspart' => json_decode($haspart),
					'ispartof' => json_decode($ispartof),
					'sid' => $sid,
					'lastseen' => $lastseen
				];
			}

			$stmt->close();

			return null;
		}

		// get an array of filtered lObjects
		public function getLObjectsByPIDIdentifier($pid_in, $identifier_in) {
			$objects = [];

			$stmt = $this->prepare('SELECT lid, pid, identifier, title, haspart, ispartof, sid, lastseen FROM lobject WHERE pid COLLATE ' . $this->dbCollation . ' =? AND identifier COLLATE ' . $this->dbCollation . ' =?');
			$stmt->bind_param('ss', $pid_in, $identifier_in);
			$stmt->execute();

			$stmt->bind_result($lid, $pid, $identifier, $title, $haspart, $ispartof, $sid, $lastseen);
			while ($stmt->fetch()) {
				$objects[] = (object) [
					'lid' => $lid,
					'pid' => $pid,
					'identifier' => $identifier,
					'title' => $title,
					'haspart' => json_decode($haspart),
					'ispartof' => json_decode($ispartof),
					'sid' => $sid,
					'lastseen' => $lastseen
				];
			}

			$stmt->close();

			return $objects;
		}

		// create a new lObject (fatal error if $lid already in use)
		public function createLObject($lid, $pid, $identifier, $title, $haspart = [], $ispartof = [], $sid = null, $lastseen = null) {
			// emulate PRIMARY KEY with case sensitiveness
			if (is_null($this->getLObject($lid))) {
				$haspartString = json_encode($haspart);
				$ispartofString = json_encode($ispartof);

				$stmt = $this->prepare('INSERT INTO lobject VALUES (?,?,?,?,?,?,?,?)');
				$stmt->bind_param('ssssssss', $lid, $pid, $identifier, $title, $haspartString, $ispartofString, $sid, $lastseen);
				$stmt->execute();

				$stmt->close();
			} else {
				throw new Exception('Duplicate entry \'' . $lid . '\' for key \'lid\'');
			}
		}

		// todo
		public function getSObjectBySID($sid) {
			return null;
		}

		private function initTables($dbName) {
			$result = $this->query('SHOW TABLES IN `' . $dbName . '`');
			$tables = [];

			if ($result !== false) {
				if ($result->num_rows > 0) {
					while ($row = $result->fetch_assoc()) {
						$tables[] = $row['Tables_in_' . $dbName];
					}
				}
			}

//			$result = $this->query('Drop Table lobject');
			if (!in_array('lobject', $tables)) {
				$result = $this->query('CREATE TABLE lobject( ' .
				   'lid CHAR(5) NOT NULL, ' .
				   'pid CHAR(4) NOT NULL, ' .
				   'identifier VARCHAR(1024), ' .
				   'title VARCHAR(1024), ' .
				   'haspart VARCHAR(1024), ' .
				   'ispartof VARCHAR(1024), ' .
				   'sid VARCHAR(5), ' .
				   'lastseen DATE ' .
//				   ', PRIMARY KEY ( lid )' .
				   ');');
			}

//			var_dump($result);
		}
	}

	function testDB() {
		try {
			$db = new GuruDatabase();

			echo "lObjects: " . count($db->getLObjects()) . "\n";
			echo "--------------\n";
			echo "\n";

			$db->close();
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
?>