<?php
function getErrorTypeName($errno) {
    switch ($errno) {
        case E_ERROR: return "E_ERROR";
        case E_WARNING: return "E_WARNING";
        case E_PARSE: return "E_PARSE";
        case E_NOTICE: return "E_NOTICE";
        case E_CORE_ERROR: return "E_CORE_ERROR";
        case E_CORE_WARNING: return "E_CORE_WARNING";
        case E_COMPILE_ERROR: return "E_COMPILE_ERROR";
        case E_COMPILE_WARNING: return "E_COMPILE_WARNING";
        case E_USER_ERROR: return "E_USER_ERROR";
        case E_USER_WARNING: return "E_USER_WARNING";
        case E_USER_NOTICE: return "E_USER_NOTICE";
        case E_STRICT: return "E_STRICT";
        case E_RECOVERABLE_ERROR: return "E_RECOVERABLE_ERROR";
        case E_DEPRECATED: return "E_DEPRECATED";
        case E_USER_DEPRECATED: return "E_USER_DEPRECATED";
        default: return "UNKNOWN";
    }
}

function customErrorHandler($errno, $errstr, $errfile, $errline) {
    global $conn;

    $error_type_name = getErrorTypeName($errno);

    $stmt = $conn->prepare("INSERT INTO error_logs (error_type, error_message, file, line) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $error_type_name, $errstr, $errfile, $errline);
    $stmt->execute();

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo "<p>Something went wrong. Please try again later.</p>";
    } else {
        echo "<pre><strong>Error:</strong> [$error_type_name] $errstr in $errfile on line $errline</pre>";
    }

    return true;
}

function fatalErrorHandler() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        customErrorHandler($error['type'], $error['message'], $error['file'], $error['line']);
    }
}

set_error_handler("customErrorHandler");
register_shutdown_function("fatalErrorHandler");
?>
