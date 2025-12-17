<div class="popUpForm" id="addStaff-form">
    <div class="block-holder-popup" >
        <div>
            <h3 class="block-header">Add Staff Member</h3>
            <form method="POST">
                <small class="form-error-message"><?php if (isset($errors['name'])) echo $errors['name']; ?></small>
                <input id="firstname" autocomplete="off" class="form-text-input-name" type="text" placeholder="First Name" name="firstname" value="<?php if (isset($_POST['firstname'])) echo $_POST['firstname'];?>">
                <input autocomplete="off" class="form-text-input-name" type="text" placeholder="Surname" name="surname" value="<?php if (isset($_POST['surname'])) echo $_POST['surname'];?>"><br>

                <small class="form-error-message"><?php if (isset($errors['email'])) echo $errors['email'];?></small>
                <input autocomplete="off" class="form-text-input-email" type="text" placeholder="Email" name="email" value="<?php if (isset($_POST['email'])) echo $_POST['email'];?>"><br>
                <small class="form-error-message"><?php if (isset($errors['number'])) echo $errors['number']; ?></small>
                <input autocomplete="off" class="form-text-input-email" type="text" placeholder="Phone Number" name="number" value="<?php if (isset($_POST['number'])) echo $_POST['number'];?>"><br>
                <small class="form-error-message"><?php if (isset($errors['role'])) echo $errors['role']; ?></small>
                <select class="optionBox" name="role">
                    <option value="" hidden   <?= $role === '' ? 'selected' : '' ?>>Role</option>
                    <option value="manager"   <?= $role === 'manager' ? 'selected' : '' ?>>Manager</option>
                    <option value="admin"     <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="office"    <?= $role === 'office' ? 'selected' : '' ?>>Office</option>
                    <option value="salesrep"  <?= $role === 'salesrep' ? 'selected' : '' ?>>Sales Rep</option>
                    <option value="warehouse" <?= $role === 'warehouse' ? 'selected' : '' ?>>Warehouse</option>
                    <option value="staff"     <?= $role === 'staff' ? 'selected' : '' ?>>General Staff</option>
                </select>
                <input type="hidden" name="addStaffMember" value="true">
                <br>
                <button class="form-button" type="submit">Submit</button>
                <button class="form-button" type="button" onclick="closeForm()">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openForm() {
        document.getElementById("addStaff-form").style.display = "flex";
        
        document.getElementById("firstname").focus();
    }

    function closeForm() {
        document.getElementById("addStaff-form").style.display = "none";
    }
</script>