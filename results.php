<?php


/**
 * Class DatabaseConnection
 * This class handles the connection to the database
 */
class DatabaseConnection {
    const DB_HOST = '127.0.0.1';
    const DB_NAME = '_votation';
    const DB_USER = 'root';
    const DB_PASSWORD = '';

    private $pdo = null;

    /**
     * This is what to do when the class is called.
     * - Open the database sconnection and put the result in the variable
     */

    protected function __construct() {

        // open database connection
        $conStr = sprintf("mysql:host=%s;dbname=%s", self::DB_HOST, self::DB_NAME);

        try {
            // Connect to the database Connection
            $this->pdo = new PDO($conStr, self::DB_USER, self::DB_PASSWORD);
        } catch (PDOException $pe) {
            die("An Error occurs. Client cannot connect to the server's database:" . $pe->getMessage());
        }
    }
    protected function getPDO(){
        return $this->pdo;
    }
}

/**
 * Class TableSelect
 * This class handles the selection to the database.
 */
class TableSelect extends DatabaseConnection {
    private $pdo = null;

    /**
     * Open the database connection
     */
    public function __construct() {
        // Use the DatabaseConnection constructor instead
        parent::__construct();
        // Set the pdo result into the parents pdo result
        $this->pdo = parent::getPDO();
    }

    /**
     * Select and List all of the Articles available in the Database
     * @return array
     */
    public function getCandidates(){
        $sql = 'SELECT `id`, `fullname`, `position`, `photo`, `party` FROM `_candidates` WHERE 1;';
        // prepare statement for execution
        $q = $this->pdo->prepare($sql);
        $q->execute();
        $result = $q->fetchAll();
        return $result;
    }

    public function matchUSN($usn){
        $sql = "SELECT `fullname`, `vote_status` FROM `_studentlist` WHERE `usn`='$usn';";
        $q = $this->pdo->prepare($sql);
        $q->execute();
        $result = $q->fetch();

        // Return the result or return a bool
        return $result;
    }
    public function countVotes($cand_id, $position){
        $sql = "SELECT COUNT(*) FROM `_voterecord` WHERE cand_id='$cand_id' AND `position`='$position'";
        $q = $this->pdo->prepare($sql);
        $q->execute();
        $result = $q->fetch();

        // Return the the number of results
        return $result;

    }
}
// Class Instances
$select = new TableSelect();


foreach($select->getCandidates() as $c){

    // Categorize by Position and put in array
    switch ($c["position"]){
        case "president":
            $president[] = $c; break;
        case "vice_internal":
            $vice_internal[] = $c; break;
        case "vice_external":
            $vice_external[] = $c; break;
        case "secretary":
            $secretary[] = $c; break;
        case "treasurer":
            $treasurer[] = $c; break;
        case "auditor":
            $auditor[] = $c; break;
        case "g11_stem":
            $g11_stem[] = $c; break;
        case "g11_ict":
            $g11_ict[] = $c; break;
        case "g11_abm":
            $g11_abm[] = $c; break;
        case "g11_humms":
            $g11_humms[] = $c; break;
        case "g11_gas":
            $g11_gas[] = $c; break;
        case "g11_tourism":
            $g11_tourism[] = $c; break;
        case "g12":
            $g12[] = $c; break;
        case "college1":
            $college1[] = $c; break;
        case "college2":
            $college2[] = $c; break;
        case "college3":
            $college3[] = $c; break;
        default: break;
    }
}

$positionVars = @ array($president, $vice_internal, $vice_external, $secretary, $treasurer, $auditor, $g11_stem, $g11_ict, $g11_abm, $g11_humms, $g11_gas, $g11_tourism, $g12, $college1, $college2, $college3);
$positionString = array('President', 'Vice President - Internal', 'Vice President External', 'Secretary', 'Treasurer', 'Auditor', 'Grade11 - STEM', 'Grade11 - ICT', 'Grade11 - ABM', 'Grade11 - HUMMS', 'Grade11 - GAS', 'Grade11 - Tourism', 'Grade12', 'College 1st Year', 'College 2nd Year', 'College 3rd Year');
$positionPar = array('president', 'vice_internal', 'vice_external', 'secretary', 'treasurer', 'auditor', 'g11_stem', 'g11_ict', 'g11_abm', 'g11_humms', 'g11_gas', 'g11_tourism', 'g12', 'college1', 'college2', 'college3');

for($i = 0; $i < count($positionVars); $i++){
    echo "<b>" .$positionString[$i]. ":</b>";
    echo "<br>";

    foreach (@$positionVars[$i] as $p){
        $numVotes = $select->countVotes($p["id"], $positionPar[$i])[0];
        echo $p["fullname"] . ": ". $numVotes;
        echo "<br>";
    }
    echo "Abstain: ". $numVotes = $select->countVotes(0, $positionPar[$i])[0];
    echo "<br>";
}