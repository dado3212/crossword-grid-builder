<?php
    // Fetch from PHP and just inline it for JS
    include("secret.php");

    // ini_set('memory_limit', '512M');

    error_reporting(E_ALL);
    ini_set('display_errors', 'On');

    // Function to return a 400 error code with information
    function returnBadRequest($message) {
        // Set the response code to 400
        http_response_code(400);

        // Set the Content-Type header to application/json
        header('Content-Type: application/json');

        // Create an array with the error message
        $errorInfo = array(
            'status' => 400,
            'message' => $message
        );

        // Convert the array to a JSON string and output it
        echo json_encode($errorInfo);
    }

    // Source: https://stackoverflow.com/a/46227341/3951475
    function getPercentile($array, $percentile) {
        $percentile = min(100, max(0, $percentile));
        $array = array_values($array);
        sort($array);
        $index = ($percentile / 100) * (count($array) - 1);
        $fractionPart = $index - floor($index);
        $intPart = floor($index);

        $percentile = $array[$intPart];
        $percentile += ($fractionPart > 0) ? $fractionPart * ($array[$intPart + 1] - $array[$intPart]) : 0;

        return $percentile;
    }

     // Make sure it's post only
     if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        returnBadRequest('Invalid request method. Please use POST.');
        exit;
    }

    // Check that all of the data is well-formed
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $day = $_POST['day'] ?? '';
    if (strlen($day) === 0) {
        returnBadRequest('Must specify the date.');
        exit;
    }
    if (!in_array($day, $days)) {
        returnBadRequest('Provided day is not a valid day of the week.');
        exit;
    }

    $size = (int)($_POST['size'] ?? 0);
    if (!in_array($size, [21, 23, 15])) {
        returnBadRequest('Provided size is a non-supported size.');
        exit;
    }

    $min_date = $_POST['minDate'] ?? '';
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $min_date)) {
        returnBadRequest('Provided min date is not a date.');
        exit;
    }

    $max_date = $_POST['maxDate'] ?? '';
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $max_date)) {
        returnBadRequest('Provided max date is not a date.');
        exit;
    }

    $PDO = getDatabase();
    $historical_crosswords = $PDO->prepare(
        "SELECT 
            `grid`,
            `day_of_week`,
            `rows`,
            `columns`,
            `words`,
            `blocks`
        FROM historical_crosswords
        WHERE
            `day_of_week` = :day
            AND `rows` = :size
            AND `columns` = `rows`
            AND `date` >= :minDate
            AND `date` <= :maxDate"
    );
    $historical_crosswords->bindValue(":day", $day, PDO::PARAM_STR);
    $historical_crosswords->bindValue(":size", $size, PDO::PARAM_INT);
    $historical_crosswords->bindValue(":minDate", $min_date, PDO::PARAM_STR);
    $historical_crosswords->bindValue(":maxDate", $max_date, PDO::PARAM_STR);
    $historical_crosswords->execute();

    $crossword_data = [];
    $words = [];
    $blocks = [];
    foreach ($historical_crosswords->fetchAll() as $crossword) {
        $words[] = (int)$crossword['words'];
        $blocks[] = (int)$crossword['blocks'];

        $grid = [];
        $row_index = -1;
        for ($i = 0; $i < strlen($crossword['grid']); $i++) {
            if ($i % $crossword['rows'] === 0) {
                $grid[] = [];
                $row_index += 1;
            }
            if ($crossword['grid'][$i] === '0') {
                $grid[$row_index][] = false;
            } else {
                $grid[$row_index][] = true;
            }
        }
        $crossword_data[] = $grid;
    }
    
    // Set the response code to 200
    http_response_code(200);
    // Set the Content-Type header to application/json
    header('Content-Type: application/json');
    header('Content-Encoding: gzip');
    // Create an array with the error message
    $errorInfo = array(
        'status' => 200,
        'grids' => $crossword_data,
        'word_range' => [getPercentile($words, 10), getPercentile($words, 90)],
        'block_range' => [getPercentile($blocks, 10), getPercentile($blocks, 90)],
    );

    // Convert the array to a JSON string and output it
    echo gzencode(json_encode($errorInfo));
?>