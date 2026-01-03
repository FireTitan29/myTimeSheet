<?php if (!empty($person['staff_comment_late']) || !empty($person['staff_comment_early']) || !empty($person['management_comment'])): ?>
    <div class="dashboard-popup">
        <?php if (!empty($person['staff_comment_late'])): ?>
            <div>
                <h4>Arrived Late - Reason</h4>
                <p><?= $person['staff_comment_late'] ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($person['staff_comment_early'])): ?>
            <div>
                <h4>Left Early - Reason</h4>
                <p><?= $person['staff_comment_early'] ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($person['management_comment'])): ?>
            <div>
                <h4>Management Comment</h4>
                <p><?= $person['management_comment'] ?></p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
