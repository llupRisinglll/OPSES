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
}

/**
 * Class TableInsert
 * This class handles the insertion to the database.
 */
class TableInsert extends DatabaseConnection {

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
     * @param $usn
     * @param $cand_id
     * @return bool
     */
    public function insertVoteData($usn, $cand_id, $position) {
        $task = array(
            ':myUSN' => $usn,
            ':candUSN' => $cand_id,
            ':pos' => $position
        );

        $sql = 'INSERT INTO `_voteRecord` ( `usn`, `cand_id`, `position`) VALUES (:myUSN, :candUSN, :pos);';

        // Prepare the SQL
        $q = $this->pdo->prepare($sql);

        // Use the Prepared SQL and execute with the task array
        return $q->execute($task);
    }
}

/**
 * Class TableUpdate
 * This class handles the updating in the database
 */
class TableUpdate extends DatabaseConnection {

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
     * @param $usn
     * @return bool
     */
    public function updateVotedStatus($usn) {
        $sql = "UPDATE `_studentList` SET `vote_status`='1' WHERE `usn`=$usn";

        // Prepare the SQL
        $q = $this->pdo->prepare($sql);

        // Use the Prepared SQL and execute with the task array
        return $q->execute();
    }

}


// Class Instances
$select = new TableSelect();
$insert = new TableInsert();
$update = new TableUpdate();

// When the user tries to login to the server.
@ $logUSN = $_POST["log_usn"];

if (isset($logUSN)){
    $findUSN = $select->matchUSN($logUSN);

    // When the find USN return a result.
    if ($findUSN > -1){
        echo json_encode($findUSN);
    }else{
        echo "error";
    }
    exit;
}

@ $voteSubmit = $_POST["voteSubmit"];
@ $myUSN = $_POST["myUSN"];

if (isset($voteSubmit) && isset($myUSN)){
    $votingData = json_decode($voteSubmit);
    $votePosition = array('president', 'vice_internal', 'vice_external', 'secretary', 'treasurer', 'auditor', 'g11_stem', 'g11_ict', 'g11_abm', 'g11_humms', 'g11_gas', 'g11_tourism', 'g12', 'college1', 'college2', 'college3');
    for ($s = 0; $s < count($votingData); $s++){
        $insert->insertVoteData($myUSN, $votingData[$s], $votePosition[$s]);
    }

    $update->updateVotedStatus($myUSN);
    echo "success";
    exit;
}


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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AMA Votation 2017 | LeafOrg</title>
    <meta name="description" content="Page Loading Effects: Modern ways of revealing new content" />
    <meta name="keywords" content="page loading, svg animation, loading effect, fullscreen svg" />
    <meta name="author" content="Codrops" />
    <link rel="shortcut icon" href="../favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/normalize.css" />
    <link rel="stylesheet" type="text/css" href="css/demo.css" />
    <link rel="stylesheet" type="text/css" href="css/component.css" />
    <script src="js/snap.svg-min.js" type="text/javascript"></script>
    <!--[if IE]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <style type="text/css">
        label > input{ /* HIDE RADIO */
            visibility: hidden; /* Makes input not-clickable */
            position: absolute; /* Remove input from document flow */
        }
        label > input + img{ /* IMAGE STYLES */
            cursor:pointer;
            border: none;
            height: 200px;
        }

        label > input:checked + img{ /* (RADIO CHECKED) IMAGE STYLES */
            border: 15px solid rgba(0,0,0,0.2);
        }

        .studentName{
            text-transform: capitalize;
        }
        body{
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
</head>
<body>
<div id="pagewrap" class="pagewrap">

    <div class="container show" id="page-1">

        <header class="codrops-header">
            <h1><b>AMA</b>CC NAGA <b>—</b> <b style="color: #005276">ELECTION</b><b>2017</b> <span>A web application created by the Leaf Organization.</span></h1>
        </header>
        <section class="related">
            <p>To get started, enter your <b>USN</b> below:</p>
            <p><input id="usnLogin" type="number" placeholder="170NNNNNNNN" style="padding: 13px 20px; background: rgb(255, 255, 255); text-transform: uppercase; letter-spacing: 1px; font-size: 0.6em; white-space: nowrap; border: none; outline: none"></p>
        </section>
        <section class="related">
            <p><img src="./img/amaNav.png" alt="Leaf Organization" align="center" height="150">
            <p>&copy; 2017 <b>-</b> <b style="color: #0b5714">Leaf Organization</b></p>
        </section>
    </div><!-- /container -->

    <div id="loader1" class="pageload-overlay" data-opening="M20,15 50,30 50,30 30,30 Z;M0,0 80,0 50,30 20,45 Z;M0,0 80,0 60,45 0,60 Z;M0,0 80,0 80,60 0,60 Z" data-closing="M0,0 80,0 60,45 0,60 Z;M0,0 80,0 50,30 20,45 Z;M20,15 50,30 50,30 30,30 Z;M30,30 50,30 50,30 30,30 Z">

        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 80 60" preserveAspectRatio="none">
            <path d="M30,30 50,30 50,30 30,30 Z"></path>
        </svg>
    </div><!-- /pageload-overlay -->

    <div id="loader2" class="pageload-overlay" data-opening="M 0,0 0,60 80,60 80,0 z M 80,0 40,30 0,60 40,30 z">

        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 80 60" preserveAspectRatio="none">
            <path d="M 0,0 0,60 80,60 80,0 Z M 80,0 80,60 0,60 0,0 Z"></path>
        </svg>
    </div><!-- /pageload-overlay -->

    <div id="loader3" class="pageload-overlay" data-opening="m -5,-5 0,70 90,0 0,-70 z m 5,35 c 0,0 15,20 40,0 25,-20 40,0 40,0 l 0,0 C 80,30 65,10 40,30 15,50 0,30 0,30 z">
        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 80 60" preserveAspectRatio="none" >
            <path d="m -5,-5 0,70 90,0 0,-70 z m 5,5 c 0,0 7.9843788,0 40,0 35,0 40,0 40,0 l 0,60 c 0,0 -3.944487,0 -40,0 -30,0 -40,0 -40,0 z"></path>
        </svg>
    </div><!-- /pageload-overlay -->

    <form action="" id="votingForm" name="votingForm" style="height: 100%">

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-president">
            <section>
                <h2><b>President:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                            foreach (@$president as $p){
                                echo "<td style=\"padding: 2px\">";
                                echo "<label>";
                                echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                                echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                                echo "</label>";
                                echo "<br/>";
                                echo "<span>";
                                echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                                echo "    <b>Party: </b> ". $p["party"];
                                echo "</span>";
                                echo "</td>";
                            }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="president" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200" id="img_0">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageload-link" href="#page-vice_internal">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-vice_internal">
            <section>
                <h2><b>Internal Vice President:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$vice_internal as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="vice_internal" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-vice_external">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-vice_external">
            <section>
                <h2><b>External Vice President:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$vice_external as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="vice_external" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-secretary">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-secretary">
            <section>
                <h2><b>Secretary:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$secretary as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="secretary" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-treasurer">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-treasurer">
            <section>
                <h2><b>Treasurer:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$treasurer as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="treasurer" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-auditor">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-auditor">
            <section>
                <h2><b>Auditor:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$auditor as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="auditor" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-g11_stem">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-g11_stem">
            <section>
                <h2><b>Grade 11 STEM Representative:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$g11_stem as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="g11_stem" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-g11_ict">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-g11_ict">
            <section>
                <h2><b>Grade 11 ICT Representative:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$g11_ict as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="g11_ict" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-g11_abm">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-g11_abm">
            <section>
                <h2><b>Grade 11 ABM Representative:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$g11_abm as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="g11_abm" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-g11_humms">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-g11_humms">
            <section>
                <h2><b>Grade 11 HUMMS Representative:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$g11_humms as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="g11_humms" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-g11_gas">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-g11_gas">
            <section>
                <h2><b>Grade 11 GAS Representative:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>

                        <?php
                        foreach (@$g11_gas as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="g11_gas" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-g11_tourism">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-g11_tourism">
            <section>
                <h2><b>Grade 11 TOURISM Representative:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>

                        <?php
                        foreach (@$g11_tourism as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="g11_tourism" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200" id="img_0">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-g12">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-g12">
            <section>
                <h2><b>Grade 12 Representative:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$g12 as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="g12" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-college1">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-college1">
            <section>
                <h2><b>College 1st Year Representative:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>

                        <?php
                        foreach (@$college1 as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="college1" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-college2">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-college2">
            <section>
                <h2><b>College 2nd Year Representative:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$college2 as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="college2" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Welcome, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-college3">Next</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-college3">
            <section>
                <h2><b>College 3rd Year Representative:</b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <?php
                        foreach (@$college3 as $p){
                            echo "<td style=\"padding: 2px\">";
                            echo "<label>";
                            echo "      <input type='radio' name='" .$p['position']. "' value='" .$p['id']. "'>";
                            echo "      <img src='./candidates/" .$p['photo']. "' id='img_" .$p['id']. "'>";
                            echo "</label>";
                            echo "<br/>";
                            echo "<span>";
                            echo "    <b style=\"color: rgba(0, 0, 0, 0.58)\"><u>" .$p["fullname"]. "</u></b> <br>";
                            echo "    <b>Party: </b> ". $p["party"];
                            echo "</span>";
                            echo "</td>";
                        }
                        ?>
                        <td style="padding: 2px">
                            <label>
                                <input type="radio" name="college3" value="0" checked="checked"/>
                                <img src="./candidates/abstain.png" alt="image #" height="200">
                            </label>
                            <br>
                            <span>
                                Abstain From Voting <br>
                                in this position
                            </span>
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">All you need to do is to Finalize and Review the summary.</span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#page-finalize">Finalize</a>
                </p>
            </section>
        </div><!-- /container -->

        <!-- The new page dummy; this would be dynamically loaded content -->
        <div class="container" id="page-finalize">
            <section>
                <h2 style="margin-bottom: 0; padding-top: 0"><b><u>Summary of Votes:</u></b></h2>
                <table style="margin: auto auto 20px;">
                    <tr>
                        <td>
                            <b>President</b>
                            <br>
                            <img id="presidentVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>Internal Vice President</b>
                            <br>
                            <img id="vice_internalVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>External Vice President</b>
                            <br>
                            <img id="vice_externalVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>Secretary</b>
                            <br>
                            <img id="secretaryVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Treasurer</b>
                            <br>
                            <img id="treasurerVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>Auditor</b>
                            <br>
                            <img id="auditorVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>G11 STEM</b>
                            <br>
                            <img id="g11_stemVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>G11 ICT</b>
                            <br>
                            <img id="g11_ictVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>G11 ABM</b>
                            <br>
                            <img id="g11_abmVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>G11 HUMMS</b>
                            <br>
                            <img id="g11_hummsVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>G11 GAS</b>
                            <br>
                            <img id="g11_gasVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>G11 Tourism</b>
                            <br>
                            <img id="g11_tourismVal" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>G12</b>
                            <br>
                            <img id="g12Val" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>College 1st year</b>
                            <br>
                            <img id="college1Val" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>College 2nd year</b>
                            <br>
                            <img id="college2Val" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                        <td>
                            <b>College 3rd year</b>
                            <br>
                            <img id="college3Val" src="./candidates/abstain.png" alt="image #" height="200">
                        </td>
                    </tr>
                </table>
                <span style="margin-bottom: 10px; display: block">Go for it!, <span class="studentName">Student</span></span>
                <p>
                    <a class="logout-link" href="#" style="margin-right: 10px">Logout</a>
                    <a class="pageback-link" href="#">Back</a>
                    <a class="pageload-link" href="#">Submit</a>
                </p>
            </section>
        </div><!-- /container -->
    </form>
</div><!-- /pagewrap -->
<script type="text/javascript" src="js/classie.js"></script>
<script type="text/javascript" src="js/svgLoader.js"></script>
<script type="text/javascript">
    (function() {

        function callAjax(url, params, callback){
            var http = new XMLHttpRequest();
            http.open("POST", url, true);

            //Send the proper header information along with the request
            http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            http.onreadystatechange = function() {//Call a function when the state changes.
                if(http.readyState == 4 && http.status == 200) {
                    callback(http.responseText);
                }
            };
            http.send(params);
        }


        var pageWrap = document.getElementById('pagewrap'),
            usnInput = document.getElementById('usnLogin'),
            pages = [].slice.call( pageWrap.querySelectorAll( 'div.container' ) ),
            currentPage = 0,
            triggerLoading = [].slice.call( pageWrap.querySelectorAll( 'a.pageload-link' ) ),
            triggerBacking = [].slice.call( pageWrap.querySelectorAll( 'a.pageback-link' ) ),
            logOut = [].slice.call( pageWrap.querySelectorAll( 'a.logout-link' ) ),
            nameContainer = [].slice.call( pageWrap.querySelectorAll( 'span.studentName' ) ),
            loader1 = new SVGLoader( document.getElementById( 'loader1' ), { speedIn : 100 } ),
            loader2 = new SVGLoader( document.getElementById( 'loader2' ), { speedIn : 100 } ),
            loader3 = new SVGLoader( document.getElementById( 'loader3' ), { speedIn : 100 } );

        var president = document.querySelectorAll('input[type=radio][name="president"]'),
            vice_internal = document.querySelectorAll('input[type=radio][name="vice_internal"]'),
            vice_external = document.querySelectorAll('input[type=radio][name="vice_external"]'),
            secretary = document.querySelectorAll('input[type=radio][name="secretary"]'),
            treasurer = document.querySelectorAll('input[type=radio][name="treasurer"]'),
            auditor = document.querySelectorAll('input[type=radio][name="auditor"]'),
            g11_stem = document.querySelectorAll('input[type=radio][name="g11_stem"]'),
            g11_ict = document.querySelectorAll('input[type=radio][name="g11_ict"]'),
            g11_abm = document.querySelectorAll('input[type=radio][name="g11_abm"]'),
            g11_humms = document.querySelectorAll('input[type=radio][name="g11_humms"]'),
            g11_gas = document.querySelectorAll('input[type=radio][name="g11_gas"]'),
            g11_tourism = document.querySelectorAll('input[type=radio][name="g11_tourism"]'),
            g12 = document.querySelectorAll('input[type=radio][name="g12"]'),
            college1 = document.querySelectorAll('input[type=radio][name="college1"]'),
            college2 = document.querySelectorAll('input[type=radio][name="college2"]'),
            college3 = document.querySelectorAll('input[type=radio][name="college3"]');

        function resetUI() {
            if (currentPage == 0){
                // Reset the form when logging out
                document.getElementById("votingForm").reset();
                usnInput.value = "";
                // Reset the form when logging out
                document.getElementById("votingForm").style.display = "none";
            }else{
                // Reset the form when logging out
                document.getElementById("votingForm").style.display = "";
            }
        }

        function changeHandler(event, summary, value) {
            if (value == 0){
                summary.src = "./candidates/abstain.png";
            }else{
                summary.src = document.getElementById("img_"+value).src;
            }
        }

        function init() {
            resetUI();

            var usnData = localStorage["usnLogged"];

            // When the USN data exists.
            if (usnData !== undefined){
                loader1.show();
                var parsedData = JSON.parse(usnData);

                nameContainer.forEach( function( nm ) {
                    nm.innerHTML = parsedData["fullname"].toLowerCase();
                });

                setTimeout( function() {
                    loader1.hide();     // Remove the Loading
                    classie.removeClass( pages[ currentPage ], 'show' );

                    // Increment the currentPage number
                    currentPage++;

                    classie.addClass( pages[ currentPage ], 'show' );
                    resetUI();
                }, 1000 );
            }

            // Watch For the keydown on the USN BOX
            usnInput.addEventListener("keydown", function (ev) {

                // Watch the enter key on the input box
                if (ev.keyCode == 13) {
                    ev.preventDefault();    // Prevent Default Actions
                    loader1.show();         // Show the Loading

                    // Get the result from the ajax XMLRequest
                    var self = this;
                    callAjax("index.php", "log_usn="+self.value, function (result) {

                        // Check if the USN is whether correct or not
                        if (result != "error"){
                            // This present as a session
                            console.log(result);
                            var parsedResult = JSON.parse(result);

                            var voteStatus = Number(parsedResult["vote_status"]) == 1;

                            // the User must not yet voted.
                            if (!voteStatus){
                                // STORE THE DATA
                                localStorage.setItem("usnLogged", JSON.stringify({
                                        "usn": self.value,
                                        "fullname": parsedResult["fullname"],
                                        "vote_status": parsedResult["vote_status"]
                                    })
                                );

                                nameContainer.forEach( function( nm ) {
                                    nm.innerHTML = parsedResult["fullname"].toLowerCase();
                                });

                                setTimeout( function() {
                                    loader1.hide();     // Remove the Loading
                                    classie.removeClass( pages[ currentPage ], 'show' );

                                    // Increment the currentPage number
                                    currentPage++;

                                    classie.addClass( pages[ currentPage ], 'show' );
                                    resetUI();
                                }, 1000 );

                            }else{
                                setTimeout( function() {
                                    loader1.hide();     // Remove the Loading
                                    classie.removeClass( pages[ currentPage ], 'show' );

                                    // Increment the currentPage number
                                    alert("LUIS EDWARD: Nakaboto ka na! Wag mo ko. May reklamo ka? Hanapin mo ko.");

                                    classie.addClass( pages[ currentPage ], 'show' );
                                    resetUI();

                                }, 1000 );
                            }

                        } else{
                            setTimeout( function() {
                                loader1.hide();     // Remove the Loading
                                classie.removeClass( pages[ currentPage ], 'show' );

                                // Increment the currentPage number
                                alert("LUIS EDWARD: Ayusin mo! mali yung USN na nilagay mo...\nHay nako, ano ba yan!");
                                classie.addClass( pages[ currentPage ], 'show' );
                                resetUI();

                            }, 1000 );
                        }
                    });

                }
            });

            // When a logout Button is pressed
            logOut.forEach( function( logout ) {
                logout.addEventListener( 'click', function( ev ) {
                    ev.preventDefault();
                    loader3.show();

                    // after some time hide loader
                    setTimeout( function() {
                        loader3.hide();

                        classie.removeClass( pages[ currentPage ], 'show' );

                        // If the current page is the last page bring it back to the
                        // first page else procede to the next page
                        currentPage = 0;

                        classie.addClass( pages[ currentPage ], 'show' );

                        localStorage.clear();

                        resetUI();
                    }, 1000 );
                } );
            } );

            // When a Next Button is Pressed
            triggerLoading.forEach( function( trigger ) {
                trigger.addEventListener( 'click', function( ev ) {
                    ev.preventDefault();

                    if (currentPage != 17) {
                        loader2.show();
                    } else {
                        loader3.show();
                    }

                    // after some time hide loader
                    setTimeout( function() {
                        if(currentPage == 17){
                            loader3.hide();
                        } else{
                            loader2.hide();
                        }
                        classie.removeClass( pages[ currentPage ], 'show' );

                        // If we are on the summary page
                        if (currentPage == 17) {
                            // Use this Function to get every value of radio button name
                            function getRadioButtonVal(nameSelector){
                                for (var i = 0, length = nameSelector.length; i < length; i++) {
                                    if (nameSelector[i].checked) {
                                        // do whatever you want with the checked radio
                                        return nameSelector[i].value;
                                    }
                                }
                            }

                            // Create an array of candidates voted
                            var candArray = [];

                            // Create a radio button selectors
                            var presidentVal = document.getElementsByName('president');
                            var vice_internalVal = document.getElementsByName('vice_internal');
                            var vice_externalVal = document.getElementsByName('vice_external');
                            var secretaryVal = document.getElementsByName('secretary');
                            var treasurerVal = document.getElementsByName('treasurer');
                            var auditorVal = document.getElementsByName('auditor');
                            var g11_stemVal = document.getElementsByName('g11_stem');
                            var g11_ictVal = document.getElementsByName('g11_ict');
                            var g11_abmVal = document.getElementsByName('g11_abm');
                            var g11_hummsVal = document.getElementsByName('g11_humms');
                            var g11_gasVal = document.getElementsByName('g11_gas');
                            var g11_tourismVal = document.getElementsByName('g11_tourism');
                            var g12Val = document.getElementsByName('g12');
                            var college1Val = document.getElementsByName('college1');
                            var college2Val = document.getElementsByName('college2');
                            var college3Val = document.getElementsByName('college3');

                            // array of Selected Candidates
                            candArray.push(getRadioButtonVal(presidentVal));
                            candArray.push(getRadioButtonVal(vice_internalVal));
                            candArray.push(getRadioButtonVal(vice_externalVal));
                            candArray.push(getRadioButtonVal(secretaryVal));
                            candArray.push(getRadioButtonVal(treasurerVal));
                            candArray.push(getRadioButtonVal(auditorVal));
                            candArray.push(getRadioButtonVal(g11_stemVal));
                            candArray.push(getRadioButtonVal(g11_ictVal));
                            candArray.push(getRadioButtonVal(g11_abmVal));
                            candArray.push(getRadioButtonVal(g11_hummsVal));
                            candArray.push(getRadioButtonVal(g11_gasVal));
                            candArray.push(getRadioButtonVal(g11_tourismVal));
                            candArray.push(getRadioButtonVal(g12Val));
                            candArray.push(getRadioButtonVal(college1Val));
                            candArray.push(getRadioButtonVal(college2Val));
                            candArray.push(getRadioButtonVal(college3Val));

                            var usnData = localStorage["usnLogged"];

                            // Save the votes without refreshing the page
                            callAjax("index.php", "myUSN="+
                                JSON.parse(usnData)["usn"] +"&voteSubmit="
                                + JSON.stringify(candArray),
                                function (reply) {

                                if (reply == "success"){
                                    alert(" Luis Edward: Ok na po, Nasave na po :) ");
                                } else{
                                    alert(" Luis Edward: May error sa pag sasave ng votes, paki inform agad ako. Salamat. ")
                                }
                            });

                            // Clear the Session
                            localStorage.clear();
                        }

                        // If the current page is the last page bring it back to the
                        // first page else procede to the next page
                        currentPage = currentPage == 17 ? 0 : currentPage + 1;


                        classie.addClass( pages[ currentPage ], 'show' );

                        resetUI();

                    }, 1000 );
                } );
            } );

            // When a Back Button is Pressed
            triggerBacking.forEach( function( trigger ) {
                trigger.addEventListener( 'click', function( ev ) {
                    ev.preventDefault();
                    loader2.show();

                    // after some time hide loader
                    setTimeout( function() {
                        loader2.hide();

                        classie.removeClass( pages[ currentPage ], 'show' );

                        // If the current page is the last page bring it back to the
                        // first page else procede to the next page
                        currentPage--;
                        classie.addClass( pages[ currentPage ], 'show' );

                    }, 1000 );
                } );
            } );

            president.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("presidentVal"), this.value)
                });
            });
            vice_internal.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("vice_internalVal"), this.value)
                });
            });
            vice_external.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("vice_externalVal"), this.value)
                });
            });
            secretary.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("secretaryVal"), this.value)
                });
            });
            treasurer.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("treasurerVal"), this.value)
                });
            });
            auditor.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("auditorVal"), this.value)
                });
            });
            g11_stem.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("g11_stemVal"), this.value)
                });
            });
            g11_ict.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("g11_ictVal"), this.value)
                });
            });
            g11_abm.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("g11_abmVal"), this.value)
                });
            });
            g11_humms.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("g11_hummsVal"), this.value)
                });
            });
            g11_gas.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("g11_gasVal"), this.value)
                });
            });
            g11_tourism.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("g11_tourismVal"), this.value)
                });
            });
            g12.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("g12Val"), this.value)
                });
            });
            college1.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("college1Val"), this.value)
                });
            });
            college2.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("college2Val"), this.value)
                });
            });
            college3.forEach(function(d) {
                d.addEventListener('change', function(e){
                    changeHandler(e, document.getElementById("college3Val"), this.value)
                });
            });
        }

        init();
    })();
</script>
</body>
</html>