<?php 
    $staffMembers = getAllStaffNames();
?>



<?php if ($role === 'admin'): ?>
<form method="GET">
    <label for="name"> Select Staff Member<br>
    <input type="hidden" name="view" value="table">
        <select class="optionBox" name="name" id="name" onchange="this.form.submit()">
            <option hidden default <?php if (!isset($person)) {echo "selected";}?>><?php if ($person) {echo $person;} else {echo 'None';}?></option>
            <?php foreach ($staffMembers AS $name):?>
                <option <?php if ($person === $name) echo 'selected' ?>><?php echo $name ?></option>
            <?php endforeach; ?>
        </select>
    </label>
</form>

<br>
<?php endif; ?>
<h3><?php echo $person; ?></h3>
<!-- Seleting the Month for the Table -->
<form class="date-holder" method="GET">
    <input type="hidden" name="view" value="table">
    <label for="month">
        <select class="optionBox" name="month" id="month" onchange="this.form.submit()">
            <option hidden disabled selected>
                <?php echo $monthNameStr ?: date('F'); ?>
            </option>
            <!-- Creating Options -->
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <?php $monthName = date('F', mktime(0,0,0,$i,1)); ?>
                <option <?php if ($monthName === $monthNameStr) echo 'selected'; ?>>
                    <?= $monthName ?>
                </option>
            <?php endfor; ?>
        </select>
    </label>
    <input class="yearInput" type="number" name="year" value="<?= $year?>" onchange="this.form.submit()">
</form>
<table>
    <tr>
        <th>Date</th>
        <th>Day</th>
        <th>Time in</th>
        <th>Time Out</th>
        <th class="comments">Management Comments</th>
        <th class="comments">Staff Comments</th>
    </tr>
    <?php fillTable($month, $year, $person);?>
</table>
<?php include 'commentPopUp.php'; ?>

<script>
function openForm(recordID = null, commentText = '', calendarDate = null) {
    document.getElementById("popUpForm-Comment").style.display = "flex";

    document.getElementById("commentID").value = recordID || '';
    document.getElementById("commentArea").value = commentText || '';
    document.getElementById("calendarDate").value = calendarDate || '';
    
    document.getElementById("commentArea").focus();
}

function closeForm() {
    document.getElementById("popUpForm-Comment").style.display = "none";
}
</script>