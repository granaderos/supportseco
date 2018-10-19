<?php
/**
 * Class DbPDO
 * Class to connect to Databases
 *
 * @package Base
 * @author  Enorion <enorion@supports.eco>
 * @version 0.1.18
 */
namespace Base;
use PDO,PDOException,Interfaces\DbInterface;

class Db implements DbInterface
{
    private $dbCons, $openedConnections = array(), $currentConnection, $errors, $fetchType = PDO::FETCH_ASSOC, $currentStmt;

    /**
     * Db constructor.
     *
     * @param   string $db
     */
    public function __construct($db = "none")
    {
        $this->dbCons = DbStorage::getDbData();
        $data = $this->dbCons[$db];
        if (!empty($data)) {
            $this->connectToDb($db);
        }
    }

    /**
     * Private function to connect to a specified Database or a custom connection
     *
     * @author Enorion <enorion@supports.eco>
     * @param string $db
     * @param array $custom
     * @return bool
     */
    private function connectToDb($db = 'custom', $custom = array())
    {
        if ($db != 'custom') {
            $data = $this->dbCons[$db];
        } else {
            if (!empty($custom)) {
                $data = $custom;
            } else {
                $this->addError("Custom connection Verbindungsdaten fehlen. Bitte Parameter angeben für Treiber, Host, Port, dbname, User und Passwort.","customConnectionParamsMissing",1);
                return false;
            }
        }

        $dsn = $data["driver"] . ':host=' . $data["host"] . ';port=' . $data["port"] . ';dbname=' . $data["dbname"];
        try {
            $this->openedConnections[$db] = new PDO($dsn, $data["user"], $data["pass"]);
            $this->currentConnection = $this->openedConnections[$db];
            return true;
        } catch (PDOException $e) {
            $this->addError($e->getMessage(),$e->getCode());
            return false;
        }
    }

    /**
     * Get PDO Object of Established Connection or Instanciate it.
     *
     * @author Enorion <enorion@supports.eco>
     * @param string $db
     * @param array $custom
     * @return bool|PDO
     */
    public function getConnection($db = 'custom', $custom = array())
    {
        if ($db != 'custom') {
            $dbCons = $this->dbCons[$db];
            if (!empty($dbCons)) {
                if (isset($this->openedConnections[$db])) $handleDb = $this->openedConnections[$db];
                else $handleDb = "";
                if (!($handleDb instanceof PDO)) {
                    $this->connectToDb($db);
                }
            } else {
                $this->addError("Verbindungsdetails nicht gefunden zur Datenbank ".$db.".", "connectionDatabaseNotFound",1);
                return false;
            }
        } else {
            if (!empty($custom)) {
                if (!isset($custom["driver"])) {
                    $custom["driver"] = 'mysql';
                }
                if (!isset($custom["port"])) {
                    $custom['port'] = 3306;
                }
                $this->connectToDb('custom',$custom);
            } else {
                $this->addError("Custom connection zur Datenbank fehlgeschlagen. Es wurden keine Parameter übergeben.","customConnectionNoDetails",1);
                return false;
            }
        }

        $this->currentConnection = $this->openedConnections[$db];
        return $this->openedConnections[$db];
    }

    /**
     * Get all fetched results with a given sql query and bindings returned with the PDO Fetch Type
     *
     * @author Enorion <enorion@supports.eco>
     * @param $sql
     * @param array $bindings
     * @param int $style
     * @return mixed
     */
    public function getAll($sql,$bindings = array(),$style = -1)
    {
        if ($style !== $this->fetchType) $this->setFetchtype($style);
        $stmt = $this->currentConnection->prepare($sql);
        $this->bindParameters($stmt,$bindings);
        $stmt->execute();
        $this->currentStmt = $stmt;
        if ($this->getRowCount() != 0) {
            return $this->currentStmt->fetchAll($this->parseFetchType());
        } else {
            $this->addError("Query in getAll Action fehlgeschlagen. ".var_dump($this->currentConnection->errorInfo()),"getAllSqlError-".$this->currentConnection->errorCode(), 2);
            return false;
        }
    }

    /**
     * Get one single Selector given the query and returned in the desired Fetch Type.
     *
     * @author Enorion <enorion@supports.eco>
     * @param $sql
     * @param array $bindings
     * @param $style
     * @return mixed
     */
    public function getOne($sql,$bindings = array(),$style = -1)
    {
        if ($style !== $this->fetchType) $this->setFetchtype($style);
        $selector = Helper::getBetween($sql,"SELECT","FROM");
        if (strpos($selector[0],",") == false && strpos($selector[0],"*") == false) {
            if (strpos($sql,"LIMIT 1") == false) {
                $sql .= " LIMIT 1";
                $this->addError("Es wurde keine Limit Anweisung gefunden. Diese wurde automatisch hinzugefügt.","getOneNoLimitNotice",0);
            }
            $stmt = $this->currentConnection->prepare($sql);
            $this->bindParameters($stmt,$bindings);
            $stmt->execute();
            $this->currentStmt = $stmt;
            return $stmt->fetchAll($this->parseFetchType());
        } else {
            $this->addError("Es wurden zu viele Selektoren übergeben in die getOne Action","getOneSelectorViolation",2);
            return false;
        }
    }

    /**
     * Get one row given the query and returned in the desired Fetch Type.
     *
     * @author Enorion <enorion@supports.eco>
     * @param $sql
     * @param array $bindings
     * @param $style
     * @return mixed
     */
    public function getRow($sql,$bindings = array(),$style = -1)
    {
        if ($style !== $this->fetchType) $this->setFetchtype($style);

        if (strpos($sql,"LIMIT 1") == false) {
            $sql .= " LIMIT 1";
            $this->addError("Es wurde keine Limit Anweisung gefunden. Diese wurde automatisch hinzugefügt.","getRowNoLimitNotice",0);
        }
        $stmt = $this->currentConnection->prepare($sql);
        $this->bindParameters($stmt,$bindings);
        $stmt->execute();
        $this->currentStmt = $stmt;
        $row = $stmt->fetchAll($this->parseFetchType());
        if (count($row) > 0) {
            return $row[0];
        } else {
            $this->addError("Es wurde keine Row zu diesem SQL Statement gefunden","getRowCountNotice",0);
            return false;
        }
    }

    /**
     * Executes a Query
     *
     * @author Enorion <enorion@supports.eco>
     * @param $sql
     * @param array $bindings
     * @return $this
     */
    public function executeQuery($sql,$bindings = array())
    {
        $stmt = $this->currentConnection->prepare($sql);
        $this->bindParameters($stmt,$bindings);
        $stmt->execute();
        $this->currentStmt = $stmt;

        return $this;
    }

    /**
     * Binding parameters to the current statement
     *
     * @author Enorion <enorion@supports.eco>
     * @param $stmt
     * @param array $bindings
     * @return bool
     */
    private function bindParameters($stmt,$bindings = array())
    {
        if (!empty($bindings)) {
            $i = 1;
            foreach($bindings AS $binding) {
                if (is_int($binding)) {
                    $stmt->bindParam($i, $binding, PDO::PARAM_INT);
                } elseif (is_bool($binding)) {
                    $stmt->bindParam($i, $binding, PDO::PARAM_BOOL);
                } else {
                    if (strlen($binding < 1025)) {
                        $stmt->bindParam($i, $binding, PDO::PARAM_STR);
                    } else {
                        $stmt->bindParam($i, $binding, PDO::PARAM_LOB);
                    }
                }
                $i++;
            }
        } else {
            $this->addError("Es wurden keine Bindings übergeben. Bindings werden ignoriert.","bindParamterersNotExistant",0);
        }

        return true;
    }

    /**
     * Pushes an Error to the $this->errors array
     *
     * @author Enorion <enorion@supports.eco>
     * @param $message
     * @param $code
     * @param int $type
     * @return bool
     */
    private function addError($message,$code,$type = 0)
    {
        $this->errors[] = array("message" => $message, "code" => $code, "Errortype" => $type);
        return true;
    }

    /**
     * Returns the $this->errors Array with possible $minlevel
     *
     * @author Enorion <enorion@supports.eco>
     * @param int $minlevel
     * @return bool
     */
    public function getErrors($minlevel = 2)
    {
        $filtered = array();
        $types = array(0 => "Notice", 1 => "Warning", 2 => "Error");
        foreach ($this->errors AS $error) {
            if ($error['Fehlertyp'] >= $minlevel) {
                $filtered[] = array("message" => $error["message"], "code" => $error["code"], "Errortype" => $types[$error["Fehlertyp"]]);
            }
        }
        echo Helper::echoarray($filtered);
        return true;
    }

    /**
     * Returns the PDO errorInfo()
     *
     * @author Enorion <enorion@supports.eco>
     */
    public function getSQLerror()
    {
        echo Helper::echoarray($this->currentStmt->errorInfo());
    }

    /**
     * Flattens the $this->errors Array.
     *
     * @author Enorion <enorion@supports.eco>
     * @return bool
     */
    public function flushErrors()
    {
        $this->errors = array();

        return true;
    }

    /**
     * Setter for the fetch type
     *
     * @author Enorion <enorion@supports.eco>
     * @param int $type
     * @return bool
     */
    public function setFetchtype($type = PDO::FETCH_ASSOC) {
        if ($type != -1) {
            $allowed = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 196608, 262144, 65536);
            if (in_array($type, $allowed)) {
                $this->fetchType = $type;
                return true;
            } else {
                $this->addError("Fetch Typ konnte nicht aufgelöst werden.", "fetchTypeViolation", 1);
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Returns the name of the current fetch type
     *
     * @author Enorion <enorion@supports.eco>
     * @return string
     */
    public function getFetchtype()
    {
        $types = array(1 => "PDO::FETCH_LAZY",
                       "PDO::FETCH_LAZY" => "PDO::FETCH_LAZY",
                       2 => "PDO::FETCH_ASSOC",
                       "PDO::FETCH_ASSOC" => "PDO::FETCH_ASSOC",
                       3 => "PDO::FETCH_NUM",
                       "PDO::FETCH_NUM" => "PDO::FETCH_NUM",
                       4 => "PDO::FETCH_BOTH",
                       "PDO::FETCH_BOTH" => "PDO::FETCH_BOTH",
                       5 => "PDO::FETCH_OBJ",
                       "PDO::FETCH_OBJ" => "PDO::FETCH_OBJ",
                       6 => "PDO::FETCH_BOUND",
                       "PDO::FETCH_BOUND" => "PDO::FETCH_BOUND",
                       7 => "PDO::FETCH_COLUMN",
                       "PDO::FETCH_COLUMN" => "PDO::FETCH_COLUMN",
                       8 => "PDO::FETCH_CLASS",
                       "PDO::FETCH_CLASS" => "PDO::FETCH_CLASS",
                       9 => "PDO::FETCH_INTO",
                       "PDO::FETCH_INTO" => "PDO::FETCH_INTO",
                       10 => "PDO::FETCH_FUNC",
                       "PDO::FETCH_FUNC" => "PDO::FETCH_FUNC",
                       11 => "PDO::FETCH_NAMED",
                       "PDO::FETCH_NAMED" => "PDO::FETCH_NAMED",
                       12 => "PDO::FETCH_KEY_PAIR",
                       "PDO::FETCH_KEY_PAIR" => "PDO::FETCH_KEY_PAIR",
                       196608 => "PDO::FETCH_UNIQUE",
                       "PDO::FETCH_UNIQUE" => "PDO::FETCH_UNIQUE",
                       262144 => "PDO::FETCH_CLASSTYPE",
                       "PDO::FETCH_CLASSTYPE" => "PDO::FETCH_CLASSTYPE",
                       65536 => "PDO::FETCH_GROUP",
                       "PDO::FETCH_GROUP" => "PDO::FETCH_GROUP");

        return $types[$this->fetchType];
    }

    /**
     * Returns the parsed PDO Fetch Type
     *
     * @author Enorion <enorion@supports.eco>
     * @return mixed
     */
    private function parseFetchType()
    {
        $types = array(1 => PDO::FETCH_LAZY,
                       "PDO::FETCH_LAZY" => PDO::FETCH_LAZY,
                       2 => PDO::FETCH_ASSOC,
                       "PDO::FETCH_ASSOC" => PDO::FETCH_ASSOC,
                       3 => PDO::FETCH_NUM,
                       "PDO::FETCH_NUM" => PDO::FETCH_NUM,
                       4 => PDO::FETCH_BOTH,
                       "PDO::FETCH_BOTH" => PDO::FETCH_BOTH,
                       5 => PDO::FETCH_OBJ,
                       "PDO::FETCH_OBJ" => PDO::FETCH_OBJ,
                       6 => PDO::FETCH_BOUND,
                       "PDO::FETCH_BOUND" => PDO::FETCH_BOUND,
                       7 => PDO::FETCH_COLUMN,
                       "PDO::FETCH_COLUMN" => PDO::FETCH_COLUMN,
                       8 => PDO::FETCH_CLASS,
                       "PDO::FETCH_CLASS" => PDO::FETCH_CLASS,
                       9 => PDO::FETCH_INTO,
                       "PDO::FETCH_INTO" => PDO::FETCH_INTO,
                       10 => PDO::FETCH_FUNC,
                       "PDO::FETCH_FUN" => PDO::FETCH_FUNC,
                       11 => PDO::FETCH_NAMED,
                       "PDO::FETCH_NAME" => PDO::FETCH_NAMED,
                       12 => PDO::FETCH_KEY_PAIR,
                       "PDO::FETCH_KEY_PAIR" => PDO::FETCH_KEY_PAIR,
                       196608 => PDO::FETCH_UNIQUE,
                       "PDO::FETCH_UNIQUE" => PDO::FETCH_UNIQUE,
                       262144 => PDO::FETCH_CLASSTYPE,
                       "PDO::FETCH_CLASSTYPE" => PDO::FETCH_CLASSTYPE,
                       65536 => PDO::FETCH_GROUP,
                       "PDO::FETCH_GROUP" => PDO::FETCH_GROUP);

        return $types[$this->fetchType];
    }

    /**
     * Returns the current Row Count
     * Deprecated with v0.1.10
     *
     * @deprecated switched to getRowCount()
     * @author Enorion <enorion@supports.eco>
     * @return mixed
     */
    public function getRows()
    {
        return $this->getRowCount();
    }

    /**
     * Returns the currrent Row Count
     *
     * @author Enorion <enorion@supports.eco>
     * @return mixed
     */
    public function getRowCount()
    {
        return $this->currentStmt->rowCount();
    }

    /**
     * Returns the last inserted ID
     *
     * @author Enorion <enorion@supports.eco>
     * @return mixed
     */
    public function getLastInsertId()
    {
        return $this->currentConnection->lastInsertId();
    }
}
