<nav>
    <form method="GET" class="nav-form">
        <label class="nav-icon-label" for='table' data-tip="Timesheet" <?php if ($view === 'table'){ echo 'style="box-shadow: inset 4px 0 0 var(--mainColor);"';} ?>>
            <img class="nav-icon-svg" src="images/icons/table_icon<?php if ($view === 'table'){ echo '_selected';} ?>.svg">
            <input hidden type="radio" name="view" id="table" value="table" onchange="this.form.submit()">
        </label>

        <label class="nav-icon-label" for='dashboard' data-tip="Dashboard"  <?php if ($view === 'dashboard'){ echo 'style="box-shadow: inset 4px 0 0 var(--mainColor);"';} ?>>
            <img class="nav-icon-svg" src="images/icons/dashboard_icon<?php if ($view === 'dashboard'){ echo '_selected';} ?>.svg?v=2">
            <input hidden type="radio" name="view" id="dashboard" value="dashboard" onchange="this.form.submit()">
        </label>
        
        <label class="nav-icon-label" for='staffmanagement' data-tip="Staff Management"  <?php if ($view === 'staffmanagement'){ echo 'style="box-shadow: inset 4px 0 0 var(--mainColor);"';} ?>>
            <img class="nav-icon-svg" src="images/icons/staff_management_icon<?php if ($view === 'staffmanagement'){ echo '_selected';} ?>.svg">
            <input hidden type="radio" name="view" id="staffmanagement" value="staffmanagement" onchange="this.form.submit()">
        </label>
        <label class="nav-icon-label" for='logout' data-tip="Logout">
            <img class="nav-icon-svg" src="images/icons/logout_icon.svg">
            <input hidden type="radio" name="view" id="logout" value="logout">
        </label>
    </form>
</nav>
<div class="popUpForm">
    <div class="block-holder-popup popUp-logout" id="logoutPopup">
        <h3>Are you sure you want to logout?</h3>
        <div style="text-align: center;">
        <button class="form-button" type="button" id="confirmLogout">Logout</button>
        <button class="form-button" type="button" id="cancelLogout">Cancel</button>
        </div>
    </div>
</div>
<script>
    const logoutRadio = document.getElementById('logout');
    const logoutOverlay = document.querySelector('.popUpForm');
    const confirmLogout = document.getElementById('confirmLogout');
    const cancelLogout = document.getElementById('cancelLogout');

    logoutRadio.addEventListener('change', () => {
        logoutOverlay.style.display = 'flex';
    });

    cancelLogout.addEventListener('click', () => {
        logoutOverlay.style.display = 'none';
        logoutRadio.checked = false;
    });

    logoutOverlay.addEventListener('click', (e) => {
        if (e.target === logoutOverlay) {
            logoutOverlay.style.display = 'none';
            logoutRadio.checked = false;
        }
    });

    confirmLogout.addEventListener('click', () => {
        logoutRadio.form.submit();
    });
</script>