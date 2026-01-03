<?php
    session_start();
    define('APP_RUNNING', true); 

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
        if (in_array($_GET['name'], getAllStaffNames())) {
            $_SESSION['name'] = $_GET['name'];
        } else {
            $_SESSION['name'] = '';
        }
    }

    $view = $_GET['view'] ?? '';

    if ($view ==='logout') {
        unset($_SESSION['admin']);
        session_destroy();
        header('Location: index.php');
        exit;
    }

    if (($view === 'dashboard' || $view === 'table' || $view === 'staffmanagement') && !isset($_SESSION['admin'])) {
        header('Location: index.php?view=admin');
        exit;
    }

    if (isset($_SESSION['admin']) && $view === '') $view = 'table';
    elseif (!isset($_SESSION['admin']) && $view === '') $view = 'stafflogin';

    $pagenames = [  'dashboard'=>'Dashboard', 
                    'table'=>'Calander Table', 
                    'staffmanagement'=>'Staff Management', 
                    'stafflogin'=>'Staff Login', 
                    'admin'=>'Admin Login'];

    if (!key_exists($view, $pagenames)) $view = 'stafflogin';

    $page = $pagenames[$view] ?? $pagenames['stafflogin'];

    $person = $_SESSION['name'] ?? '';

    $month;
    $monthNameStr;
    $role = $_SESSION['user']['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthy Pet: <?= $page ?></title>
    <link rel="stylesheet" href="mystyle.css?v=3">
    <link rel="icon" type="image/x-icon" href="images/icons/staff_management_icon.svg">
</head>
<body <?php if ($view === 'stafflogin') echo 'style="justify-content: center;"'?>>

    <?php if (isset( $_SESSION['admin'])) include 'components/navigation.php'; ?>
    
    <div class="<?php if (isset( $_SESSION['admin'])) echo 'content-holder'?>">
    <?php
            if (isset( $_SESSION['admin'])) {
                if ($view === 'staffmanagement') include 'pages/staffmanagement.php';
                else if ($view === 'dashboard') include 'pages/dashboard.php';
                else include 'pages/timesheet.php';
            } else {
                if ($view === 'admin') include 'pages/adminlogin.php';
                else include 'pages/stafflogin.php';
            }
        ?>
    </div>
</body>

<script>
    document.querySelectorAll('textarea').forEach(el => {
  el.addEventListener('input', () => {
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
  });
});
</script>
</html>