<?php

    session_start();

    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache"); 
    header("Expires: 0"); 

    date_default_timezone_set('Africa/Johannesburg');
    
    $errors = [];
    
    include 'php/database_functions.php';
    include 'php/table_functions.php';
    include 'php/validation.php';
    include 'php/POST_to_DB.php';

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['name'])) {

        $_SESSION['name'] = $_GET['name'];
    }

    $view = $_GET['view'] ?? 'stafflogin';
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
<body <?php if ($view === 'stafflogin' || $view === '') echo 'style="justify-content: center;"'?>>

    <?php if ($view !== 'stafflogin' && $view !== '') include 'components/navigation.php'; ?>

    <div class="<?php if ($view !== 'stafflogin' && $view !== '') echo 'content-holder'?>">

    <?php 
        $role = 'admin'; 
        $_SESSION['user']['role'] = $role;
    ?>

        <?php 
            if ($view === 'table') include 'pages/timesheet.php'; 
            else if ($view === 'staffmanagement') include 'pages/staffmanagement.php';
            else if ($view === 'dashboard') include 'pages/dashboard.php';
            else if ($view === 'stafflogin') include 'pages/stafflogin.php'; 
        ?>
    </div>
</body>
</html>