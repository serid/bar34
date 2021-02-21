<?php
// Script arguments: "m" can be either "inc" or "show"
// "inc" increments column "count" of single row in table "counter"
// "show" shows column "count" of single row in table "counter"
// Intended use -- visitor counter

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli('localhost', 'u1242320_jit', 'pass12345', 'u1242320_jitdb');
$mysqli->set_charset('utf8');

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if ($_GET["m"] == "inc") {
    if (!$mysqli->query("CREATE TABLE IF NOT EXISTS counter(count INT)")) {
        echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    // If table is empty, add a single row
    $rows = $mysqli->query("SELECT * FROM counter");
    if ($rows->num_rows == 0) {
        if (!$mysqli->query("INSERT INTO counter(count) VALUE (0)")) {
            echo "Row insertion failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
    }
    
    // Increment
    if (!$mysqli->query("UPDATE counter SET count = count + 1")) {
        echo "Increment failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
} else if ($_GET["m"] == "show") {
    // Return current count
    $res = $mysqli->query("SELECT * FROM counter");
    if (!$res) {
        echo "Query failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    $res->data_seek($res->num_rows - 1);
    $row = $res->fetch_assoc();
    $count = $row['count'];
    
    echo "[OK]" . $count;
} else {
    echo "[ERR]Unknown \"m\" method";
}
?>