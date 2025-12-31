<?php
if (!defined('APP_RUNNING')) {
    header("Location: ../index.php?view=admin");
    exit;
}
?>

<body class="admin-login-page">
    <div class="admin-login-in-body">
        <div class="admin-login-entire-form-holder">
            <h3 class="admin-login-header">Admin Login</h3>
            <small class="admin-error"><?php if (isset($errors['login'])) echo $errors['login']; ?></small>
            <form method="POST" class="admin-login-form">
                <input type="hidden" name="adminlogin" value="1">
                <label class="admin-login-label" for="username">Username:</label>
                <input autocomplete="off" id="username" name="username" type="text" 
                class="admin-login-input" value="<?php if (isset($_POST['username'])) echo htmlspecialchars($_POST['username']);?>"
                style="<?php if (isset($errors['username'])) echo 'border-color: red;'?>">

                <label class="admin-login-label" for="password">Password:</label>
                <input id="password" name="password" type="password" class="admin-login-input" style="<?php if (isset($errors['password'])) echo 'border-color: red;'?>">
                <div style="padding-bottom:10px;"></div>
                <button type="submit" class="form-button">Login</button>
            </form>

        </div>
    </div>
</body>