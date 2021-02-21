<?php
// A self countained SQL admin page. File should be named __admin__.php
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
<button onclick="send();">Send!</button><br>
<iframe id="my-iframe"></iframe>

<script>
const send = () => {
    let url = encodeURI('__admin__.php?query=' + document.getElementById("read-area").value);
    
    document.getElementById('my-iframe').src = url;
    
    // var xhr = new XMLHttpRequest();
    // xhr.open('GET', url, true);
    
    // xhr.onload = function () {
    //     xhr.response;
    // };
    
    // xhr.send(null);
}
</script>

</body>
</html>
HERE;
} else {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli('localhost', 'u1242320_jit', 'pass12345', 'u1242320_jitdb');
    $mysqli->set_charset('utf8');
    
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    
    // if (!$mysqli->query("DROP TABLE IF EXISTS test") ||
    //     !$mysqli->query("CREATE TABLE test(count INT)") ||
    //     !$mysqli->query("INSERT INTO test(count) VALUE (1)")) {
    //     echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
    // }

    $res = $mysqli->query($_GET["query"]);
    if (!$res) {
        echo "Query failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    echo "<pre>";
    for ($row_no = $res->num_rows - 1; $row_no >= 0; $row_no--) {
        $res->data_seek($row_no);
        $row = $res->fetch_assoc();
        echo " " . $row_no . ": count = " . $row['count'] . "<br>";
    }
    echo "</pre>";
    
    // echo htmlspecialchars("[" . $_GET["query"] . "]");
    // echo htmlspecialchars("[" . $res . "]");
}
?>