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
    

    $PDO = getDatabase();
    $historical_crosswords = $PDO->prepare(
        "SELECT 
            `grid`,
            `day_of_week`,
            `rows`,
            `columns`
        FROM historical_crosswords
        WHERE
            `day_of_week` = :day
            AND `rows` = 15"
    );
    $historical_crosswords->bindValue(":day", $day, PDO::PARAM_STR);
    $historical_crosswords->execute();

    $crossword_data = [];
    foreach ($historical_crosswords->fetchAll() as $crossword) {
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
    // Create an array with the error message
    $errorInfo = array(
        'status' => 200,
        'grids' => $crossword_data,
    );

    // Convert the array to a JSON string and output it
    echo json_encode($errorInfo);
?>