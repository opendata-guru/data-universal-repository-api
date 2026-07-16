<?php
	class GuruDatabase extends mysqli {
		public function __construct() {
			$dbServername = 'db1234567890.hosting-data.io';
			$dbUsername = 'dbu1234567';
			$dbPassword = 'pw12345678';
			$dbName = 'dbs12345678';

			parent::__construct($dbServername, $dbUsername, $dbPassword, $dbName);

			if ($this->connect_error) {
				throw new Exception('Connection failed: ' . $this->connect_error);
			}

			$this->set_charset('utf8mb4');

			$this->initTables($dbName);
		}

		public function getLObjects() {
			$result = $this->query('SELECT * FROM lobject');
			$objects = [];

			if ($result !== false) {
				if ($result->num_rows > 0) {
					while ($row = $result->fetch_assoc()) {
						$objects[] = $row;
					}
				}
			}

			return $objects;
		}

		public function getLObjectByLID($lid) {}
		public function getLObjectByPIDIdentifier($pid, $identifier) {}
		public function getSObjectBySID($sid) {}

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

			if (!in_array('lobject', $tables)) {
				$result = $this->query('CREATE TABLE lobject( ' .
				   'lid CHAR(5) NOT NULL, ' .
				   'pid CHAR(4) NOT NULL, ' .
				   'identifier VARCHAR(1024), ' .
				   'title VARCHAR(1024), ' .
				   'haspart VARCHAR(1024), ' .
				   'ispartof VARCHAR(1024), ' .
				   'sid VARCHAR(5), ' .
				   'lastseen DATE, ' .
				   'PRIMARY KEY ( lid )' .
				   ');');
//				$result = $this->query('Drop Table lobject');
			}

//			var_dump($result);
		}
	}

	function testDB() {
		try {
			$db = new GuruDatabase();
			var_dump($db->getLObjects());
			$db->close();
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	function bar() {
		$mysqli = new mysqli("localhost", "user", "password", "database");
		if ($mysqli->connect_errno) {
			die("Verbindung fehlgeschlagen: " . $mysqli->connect_error);
		}

		$sql = "UPDATE tabelle SET spalte = 'Wert' WHERE id = 1";
		$mysqli->query($sql);
	}

	function buz() {
		$mysqli = new mysqli("localhost", "user", "Password", "database");
		if ($mysqli->connect_errno) {
			die("Verbindung fehlgeschlagen: " . $mysqli->connect_error);
		}
		$sql = "UPDATE user SET email = ?, passwort = ? WHERE id = ?";
		$statement = $mysqli->prepare($sql);
		$statement->bind_param('ssi', $email, $passwort, $id);

		//Variablen Werte zuweisen
		$id= 1;
		$email = "ein@beispiel.de";
		$passwort = "neues passwort";
		$statement->execute();
	}

	function boom() {
		$link = mysqli_connect("localhost", "root", "", "test");

		// Datensätze auslesen:
		$datensaetze = mysqli_query($link, "SELECT `name`, `text`, `datum` FROM `nachrichten`");

		// Datensätze ausgeben:
		while (list($name, $text, $datum) = mysqli_fetch_array($datensaetze)) {
			echo "<p>$name - $titel - $text - $datum</p>";
		}
	}

	function foo($parameterLink) {
		if ($parameterLink == '') {
			$error = (object) array(
				'error' => 400,
				'header' => 'HTTP/1.0 400 Bad Request',
				'message' => 'Bad Request. Parameter \'link\' is not set',
				'parameter' => $parameterLink,
			);
		}

		return (object) array(
			'error' => $error,
			'parameter' => $parameterLink,
			'system' => $system,
			'url' => $url,
		);
	}
?>