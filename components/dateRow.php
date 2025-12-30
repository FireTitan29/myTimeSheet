<?php $person = $_SESSION['name'] ?? ''; ?>

<tr <?php if ($weekDay == 'Saturday' || $weekDay == 'Sunday') { 
        echo "class = 'weekDay'";
    } else if ($calendarDate === $todayDate) {
        echo "class = 'today'";
    }?>>
    <!-- Date -->
<td> <?php echo $date?> </td>
    <!-- Day of Week -->
<td> <?php echo $weekDay?> </td>
<!-- Time in -->
 
<td <?php if ($isLate) {echo "class='isLate'";} ?>>
    <?php if (isset($staffRecord['timeIn'])): ?>
        <?php echo date('H:i:s', strtotime($staffRecord['timeIn'])); ?>
    <?php else: ?>
        <?php if (($calendarDate === $todayDate) && $person !== ''): ?>
            <form method='POST'>
                <input class="stamp-button" type='submit' value='Stamp In' name='timeIn'>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</td>

<!-- Time Out -->
<td>
    <?php if (isset($staffRecord['timeOut'])): ?>
        <?php echo date('H:i:s', strtotime($staffRecord['timeOut'])); ?>
    <?php else: ?>
        <?php if (($calendarDate === $todayDate) && isset($staffRecord['timeIn'])): ?>
            <form method='POST'>
                <input class="stamp-button" type='submit' value='Stamp Out' name='timeOut'>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</td>
<!-- Comments -->
<td>
<?php
    $role = $_SESSION['user']['role'] ?? '';
    $isManager = in_array($role, ['admin', 'management'], true);
    $hasRecord = !empty($staffRecord);                          // row exists?
    $hasComment = $hasRecord && !empty($staffRecord['management_comment']);
?>

<?php if ($hasRecord && $person !== ''): ?>
    <?php if (!$hasComment && $isManager): ?>
        <!-- Record exists but no comment yet → button opens popup for this record -->
        <button
            type="button"
            class="comment-button"
            name="addComment"
            onclick='openForm(
                <?= (int)$staffRecord["recordID"] ?>,
                "",
                <?= json_encode($dbDate) ?>
            )'
        >
            📝 Add Comment
        </button>
    <?php elseif ($isManager && $person !== ''): ?>
        <!-- Record exists and (maybe) comment exists -> label to edit/view -->
        <label data-tip="<?= htmlspecialchars($staffRecord['management_comment'] ?? '') ?>"
            class="commentLabel"
            onclick='openForm(
                <?= (int)$staffRecord["recordID"] ?>,
                <?= json_encode($staffRecord["management_comment"] ?? "") ?>,
                <?= json_encode($dbDate) ?>
            )'
        >
            <?php if ($hasComment) {
                    $fullComment = $staffRecord['management_comment'];
                    $shortComment = mb_strlen($fullComment) > 30
                        ? mb_substr($fullComment, 0, 30) . '…'
                        : $fullComment;

                    echo htmlspecialchars($shortComment);
                } else {
                    echo 'Add comment';
                } 
            ?>
        </label>
    <?php else: ?>
        <div class="commentLabelNoEdit"><?= $hasComment
                ? htmlspecialchars($staffRecord['management_comment'])
                : '' ?></div>
    <?php endif; ?>

<?php else: ?>
    <?php if ($isManager && $person !== ''): ?>
        <!-- No record for this date yet → create new record with comment -->
        <button
            type="button"
            class="comment-button"
            name="addComment"
            onclick='openForm(
                null,
                "",
                <?= json_encode($dbDate) ?>
            )'
        >
            📝 Add Comment
        </button>
    <?php else: ?>
        <!-- No record and not admin/management: show nothing or plain text -->
    <?php endif; ?>

<?php endif; ?>
</td>
    <!-- Staff Comment -->
    <td class="staff-comments">
    <?php if (!empty($staffRecord['staff_comment_late']) || !empty($staffRecord['staff_comment_early'])): ?>
        <button class="toggle-comments" onclick="toggleComments(this)">
            View comments
        </button>

        <div class="comments-content" style="display:none;">
            <?php if (!empty($staffRecord['staff_comment_late'])): ?>
                <div class="comment-block">
                    <span class="reason-table-heading">Arrived Late - Reason</span>
                    <p class="reason-table-text"><?= htmlspecialchars($staffRecord['staff_comment_late']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($staffRecord['staff_comment_early'])): ?>
                <div class="comment-block">
                    <span class="reason-table-heading">Left Early - Reason</span>
                    <p class="reason-table-text"><?= htmlspecialchars($staffRecord['staff_comment_early']) ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</td>
</tr>

<script>
function toggleComments(button) {
    const content = button.nextElementSibling;
    const isOpen = content.style.display === 'block';

    content.style.display = isOpen ? 'none' : 'block';
    button.textContent = isOpen ? 'View comments' : 'Hide comments';
}
</script>