<?php
// Script evaluates arbitrary php code (guarded by password)

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
<button onclick="send();">Send!</button><br>
<iframe id="my-iframe"></iframe>

<script>
const send = () => {
    let url = encodeURI('__eval__.php?cmd=' + document.getElementById("read-area").value + '&password=' + document.getElementById("password").value);
    
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
    if ($_GET["password"] == "haskell1970") {    
        eval($_GET["cmd"]);
    } else {
        echo "Access denied";
    }
    
    // echo htmlspecialchars("[" . $_GET["cmd"] . "]");
    // echo htmlspecialchars("[" . $res . "]");
}
?>