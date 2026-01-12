<?php 
    $person = $_SESSION['name'] ?? ''; 
?>
<tr
    data-has-expand="<?php
        echo (!empty($staffRecord['staff_comment_late'])
            || !empty($staffRecord['staff_comment_early'])
            || !empty($staffRecord['management_comment'])
            || !empty($staffRecord['leave_id']))
            ? '1'
            : '0';
    ?>"
    onclick="toggleRow(event, this)"

    <?php 
        if ($weekDay == 'Saturday' || $weekDay == 'Sunday' || $isPublicHoliday) { 
            echo "class='weekDay expandable-row'";
        } else if ($calendarDate === $todayDate) {
            echo "class='today expandable-row'";
        } else {
            echo "class='expandable-row'";
        } 
    ?>
>
    <!-- Date -->
<td> 
    <?= $date ?>
</td>
    <!-- Day of Week -->
<td data-tip="<?php if ($isPublicHoliday) echo $publicHolidayName ?>" class="<?php if ($isPublicHoliday) echo 'publicHoliday-td' ?>"><?php if ($isPublicHoliday){
        echo "Public Holiday";
    } else {
        echo $weekDay;
} ?> </td>
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

    $leaveRecord = null;

    if (!empty($staffRecord) && !empty($staffRecord['is_leave']) && !empty($staffRecord['leave_id'])) {
        $leaveRecord = getLeaveDetails($staffRecord['leave_id']);
    }
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
        <label id="managment-comment-closed-<?php if (isset($staffRecord['recordID'])) echo $staffRecord['recordID']; ?>" data-tip="Edit comment"
            class="commentLabel row-preview"
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
        <div class="comment-block" id="managment-comment-open-<?php if (isset($staffRecord['recordID'])) echo $staffRecord['recordID']; ?>" style="display: none; margin-top: 15px;">
            <span class="reason-table-heading">Management Comment</span>
            <p class="reason-table-text"><?= htmlspecialchars($staffRecord['management_comment']) ?></p>
        </div>
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
        <button class="toggle-comments row-preview">
            View comments
        </button>
        
        <div class="comments-content" style="display:none;">
        <span style="display: block;margin-top: 10px;"></span>
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
<td>
<?php if ($weekDay !== 'Saturday' && $weekDay !== 'Sunday' && $person !== '' && (!isset($staffRecord['is_leave']) || $staffRecord['is_leave'] === 0) && !$isPublicHoliday): ?>
        <button
            type="button"
            class="comment-button"
            name="recordLeave"
            onclick='openLeaveForm(
                null,
                "",
                <?= json_encode($dbDate) ?>
            )'
        >
            🗂️ Record Leave
        </button>
    <?php elseif(isset($staffRecord['is_leave']) && $staffRecord['is_leave'] === 1): ?>
    <?php 
        $leaveStart = getLeaveStartDate($staffRecord["staffID"], $staffRecord["leave_id"]) ?? '';
        $leaveEnd   = getLeaveEndDate($staffRecord["staffID"], $staffRecord["leave_id"]) ?? '';
     ?>
    <button
        class="toggle-comments row-preview leave-preview"
        onclick="openLeaveDetailsPopUp(
            <?= (int)$leaveRecord['leave_id'] ?>,
            <?= json_encode($person, JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            <?= json_encode($leaveRecord['leave_type'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            <?= json_encode(ucwords($leaveRecord['day_type']), JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            <?= json_encode($leaveStart, JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            <?= json_encode($leaveEnd, JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            <?= (int)$leaveRecord['duration_days'] ?>,
            <?= (int)$leaveRecord['doctors_note_received'] ?>,
            <?= json_encode($leaveRecord['leave_comment'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        )"
    >
        <?= isset($staffRecord["leave_id"]) ? ucwords($leaveRecord["leave_type"]) : "" ?>
        Leave
    </button>

        <div class="comments-content" style="display:none;">

            <!-- Adding some margin here -->
            <span style="display: block;margin-top: 10px;"></span>

            <?php if (!empty($leaveRecord['leave_type'])): ?>
                <div class="comment-block">
                    <span class="reason-table-heading">Leave Type</span>
                    <p class="reason-table-text"><?= ucwords($leaveRecord['leave_type'])?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($leaveRecord['day_type'])): ?>
                <div class="comment-block">
                    <span class="reason-table-heading">Day Type</span>
                    <p class="reason-table-text"><?= ucwords($leaveRecord['day_type'])?> Day</p>
                </div>
            <?php endif; ?>

            <!-- <?php if ($leaveRecord['doctors_note_received'] !== 0): ?>
                <div class="comment-block">
                    <span class="reason-table-heading">Doctors Note</span>
                    <p class="reason-table-text"><?= $leaveRecord['doctors_note_received'] === 1 ? "Yes" : "No" ?></p>
                </div>
            <?php endif; ?> -->

            <?php if (!empty($leaveRecord['leave_comment'])): ?>
                <div class="comment-block">
                    <span class="reason-table-heading">Comment</span>
                    <p class="reason-table-text"><?= htmlspecialchars($leaveRecord['leave_comment']) ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>

<?php endif;?>
</td>
</tr>

<script>
function toggleRow(event, row) {
    // Do not toggle row when clicking interactive elements,
    // EXCEPT for "View comments" preview buttons
    if (
        event.target.closest('.commentLabel') ||
        event.target.closest('.leave-preview') ||
        (event.target.closest('button') && !event.target.closest('.toggle-comments'))
    ) {
        return;
    }

    if (row.dataset.hasExpand !== '1') {
        return;
    }

    const isOpen = row.classList.contains('row-open');

    // Expanded content blocks
    const contentBlocks = row.querySelectorAll('.comments-content, .comment-block');

    // Collapsed preview elements (titles, truncated text, view buttons)
    const previewBlocks = row.querySelectorAll('.row-preview');

    contentBlocks.forEach(block => {
        block.style.display = isOpen ? 'none' : 'block';
    });

    previewBlocks.forEach(el => {
        el.style.display = isOpen ? '' : 'none';
    });

    row.classList.toggle('row-open', !isOpen);
}
</script>