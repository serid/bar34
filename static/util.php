<?php
// utility functions

function send_message($text, $send_to_Roman = false) {
    global $is_windows;
    if ($is_windows) {
        // message send function does not work on windows
        return;

        echo "sending message";
        file_put_contents("./message.txt", $text);
        echo shell_exec("./optim.exe 415280808");
        if ($send_to_Roman) {
            //$_ = shell_exec("cd lib/tg; ./a.out 631122102");
        }
    } else {
        file_put_contents("./message.txt", $text);
        $_ = shell_exec("chmod +x ./a.out");
        $_ = shell_exec("./a.out 415280808");
        if ($send_to_Roman) {
            $_ = shell_exec("./a.out 631122102");
        }
    }
}

// setup shutdown_function
function fatal_handler() {
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if($error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];

        send_message("error: $errstr\n\nin file: $errfile:$errline");
    }
}
register_shutdown_function("fatal_handler");

// this function needs to be called in every file that includes "util.php"
function init_util() {
    global $is_windows;

    $is_windows = stristr("WINNT", PHP_OS);

    if ($is_windows) {
        $nonstatic = "C:/Users/jitrs/Documents/code/small-sicilia/build/lib/tg/";

        chdir($nonstatic);

//         echo shell_exec("dir");
    } else {
        $nonstatic = "./lib/tg/";

        chdir($nonstatic);

//         echo shell_exec("ls");
    }
}
?>