<?php
const SECONDS_IN_HOUR = 3600;
const SQL_DATE_TIME_FORMAT = "Y-m-d H:i:s";

// Don't forget to set executable flag for a.out
// $ cd lib/tg; chmod +x ./a.out
function send_message($text, $send_to_Roman = false) {
    file_put_contents("./lib/tg/message.txt", $text);
    $_ = shell_exec("cd lib/tg; chmod +x ./a.out");
    $_ = shell_exec("cd lib/tg; ./a.out 415280808");
    if ($send_to_Roman) {
        $_ = shell_exec("cd lib/tg; ./a.out 631122102");
    }
}

function localize_people($num) {
    if ($num == 1) {
        return "человек";
    } elseif ($num < 5) {
        return "человека";
    } elseif ($num < 10) {
        return "человек";
    } elseif ($num == 10) {
        return "человек";
    } elseif ($num > 10) {
        // unreachable
        return "человек";
    }
}

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}
set_error_handler('exceptions_error_handler');

function format_error( $errno, $errstr, $errfile, $errline ) {
    $trace = print_r( debug_backtrace( false ), true );

    return "Fatal error: $errstr\nin $errfile:$errline (errno: $errno)\n$trace";
}

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

        send_message(format_error($errno, $errstr, $errfile, $errline));
    }
}
register_shutdown_function("fatal_handler");

$logs = "";

function logus($s) {
    global $logs;
    send_message($s);
    $logs .= $s . "\n";
}

function dictionary_to_string($array) {
    $str = "";
    foreach($array as $key => $value ){
        $str .= $key . " => " . $value . "\n";
    }
    return $str;
}

function sql_request($mysqli, $sql) {
    $res = $mysqli->query($sql);
    if (!$res) {
        throw new Exception("Query failed: (" . $mysqli->errno . ") " . $mysqli->error);
    }
    return $res;
}

// Table "blocks" has type
// CREATE TABLE blocks (ip CHAR(45), date TIMESTAMP);

// When user tries to send message, SQL request is performed to see if user's is already in the database.
// If user is not in database, $is_logged is set to false and when save_ip is performed, row with their ip and unblock datetime is INSERT'ed.
// If user is already in database, $is_logged is set to true and when save_ip is performed, row with unblock datetime is UPDATE'd
// also current_datetime is compared against unblock_datetime to see if a user is allowed to send messages.

// Returns [true, true] if an ip is allowed to send messages and if it's already in the database
function check_ip($mysqli, $ip) {
    $current_datetime = date(SQL_DATE_TIME_FORMAT);

    // Check if ip is yet to be unblocked
    // $sql = "SELECT date FROM blocks WHERE ip = '$ip' AND date < '$current_datetime'";
    // Check if ip is in the database
    $sql = "SELECT date FROM blocks WHERE ip = '$ip'";
    $res = sql_request($mysqli, $sql);

    if ($res->num_rows == 0) {
        $is_allowed = true;
        $is_logged = false;
        return [$is_allowed, $is_logged];
    }

    if ($res->num_rows > 1) {
        logus('[warning] multiple rows with ip "$ip" in database "blocked"');
    }

    $res->data_seek(0);
    $row = $res->fetch_assoc();

    $current_timestamp = time();
    $blocked_until_timestamp = strtotime($row["date"]);

    $is_allowed = $blocked_until_timestamp < $current_timestamp;
    $is_logged = true;

    return [$is_allowed, $is_logged];
}

// Save ip to disallow further sending of messages
function save_ip($mysqli, $ip, $is_logged) {
    /*
    if (strlen($ip) != 15) {
        throw new Exception("ip should be 15 characters long, but was \"$ip\"");
    }
    */
    $current_timestamp = time();
    $blocked_until = $current_timestamp + SECONDS_IN_HOUR;
    $blocked_until_datetime = date(SQL_DATE_TIME_FORMAT, $blocked_until);

    $sql = "";
    if ($is_logged) {
        $sql = "UPDATE blocks SET date = '$blocked_until_datetime' WHERE ip = '$ip'";
    } else {
        $sql = "INSERT INTO blocks (ip, date) VALUES('$ip', '$blocked_until_datetime')";
    }

    $res = sql_request($mysqli, $sql);

    $res->data_seek(0);
    $row = $res->fetch_assoc();

    logus("save_ip result" . dictionary_to_string($row));
}

function main() {
    // Get ip address
    $ip = $_SERVER['REMOTE_ADDR'];

    logus("[] starting processing $ip");

    // Create MySQL connection
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli('localhost', 'u1242320_jit', 'pass12345', 'u1242320_jitdb');
    $mysqli->set_charset('utf8');

    if ($mysqli->connect_errno) {
        throw new Exception("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
    }

    $body = file_get_contents('php://input');

    $data = json_decode(file_get_contents('php://input'), true);

    $time = $data["time"];
    $numberOfPeople = $data["numberOfPeople"];
    $name = $data["name"];
    $phone = $data["phone"];
    $user_message = $data["user_message"];

    // If ip is blocked, stop processing
    [$is_allowed, $is_logged] = check_ip($mysqli, $ip);
    if (!$is_allowed && $user_message != "Admin") {
        logus("[] not allowed");
        return null;
    }
    logus("[] allowed");

    if ($user_message == "Admin") {
        $user_message = "";
    }

    $numberOfPeople = strval($numberOfPeople) . " " . localize_people($numberOfPeople);

    $text = "Новое бронирование\n" .
        "$numberOfPeople в $time, $name ($phone)\n";

    if ($user_message != "") {
        $text .= "Пожелания: $user_message";
    }

    send_message($text, $name != "Testing");

    // Save visitor's IP to ignore further requests
    save_ip($mysqli, $ip, $is_logged);
}

try {
    main();
} catch (Exception $e) {
    send_message($e->getMessage() . "\n" . $e->getTraceAsString());
    send_message($logs);
}
?>