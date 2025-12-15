<?php

    session_start();

    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache"); 
    header("Expires: 0"); 
    $errors = [];
    
    include 'php/database_functions.php';
    include 'php/table_functions.php';
    include 'php/validation.php';
    include 'php/POST_to_DB.php';

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['name'])) {

        $_SESSION['name'] = $_GET['name'];
    }

    $view = $_GET['view'] ?? '';
    $person = $_SESSION['name'] ?? '';

    $month;
    $monthNameStr;

    if (isset($_GET['month'])) {
        $month = date('m', strtotime($_GET['month'])); 
        $monthNameStr = $_GET['month'];
    } else {
        $month = date('m', time());
        $monthNameStr = date('F', time()); 
    }

    $year = !empty($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login Page</title>
    <link rel="stylesheet" href="mystyle.css">
</head>
<body>
    <?php include 'navigation.php' ?>
    <?php 
        $role = 'admin'; 
        $_SESSION['user']['role'] = $role;
    ?>
    <div class="content-holder">
        <?php if ($view === 'table') include 'pages/timesheet.php'; ?>
        <?php if ($view === 'staffmanagement') include 'pages/staffmanagement.php'; ?>
    </div>
</body>
</html>