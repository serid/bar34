<?php
include "util.php";

init_util();

const SECRET = "343434";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if ($_GET["key"] === SECRET) {
        echo "ok";
    } else {
        echo "wrongkey";
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST["key-field"] === SECRET) {
        if (isset($_FILES['wine-file-field'])) {
            move_uploaded_file($_FILES['wine-file-field']['tmp_name'], "./wine.txt");
        }
        if (isset($_FILES['dish-file-field'])) {
            move_uploaded_file($_FILES['dish-file-field']['tmp_name'], "./dish.txt");
        }

        // After submitting form, send user back to manager.html
        echo 1;<<<END
        <!DOCTYPE HTML>
        <html lang="en-US">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="refresh" content="0; url=/manager.html">
                <script type="text/javascript">
                    window.location.href = "/manager.html"
                </script>
                <title>Page Redirection</title>
            </head>
            <body>
                <!-- Note: don't tell people to `click` the link, just tell them that it is a link. -->
                If you are not redirected automatically, follow this <a href='/manager.html'>link to example</a>.
            </body>
        </html>
        END;
    } else {
        echo "wrongkey in file upload";
    }
} else {
    echo "error: method not supported";
}
?>