<div class="popUpForm" id="leaveDetailsPopUp">
    <div class="popUpForm-Comment-div leave-popup-div">
        <div>
            <h3 for="commentArea" class="managementCommentHeading" style="margin: 0px;">Leave Details</h3>
            <!-- Staff Name -->
            <p class="leave-personheading" id="leave-staffName"></p>

            <!-- Leave Type & Day Type if Half Day -->
            <p class="leave-badges"><span class="badge" id="leave-leaveType"></span> <span class="badge badge-halfday" id="leave-dayType"></span></p>
            
            <!-- Start & End Dates -->
            <div class="leave-dates">
                <div class="leave-date-range" id="leave-dateRange"></div>
                <div class="leave-duration" id="leave-duration"></div>
            </div>
            <!-- Doctors Note Received -->
            <p id="leave-doctorsNoteReceived" class="popupDetails-p"></p>

            <!-- Leave Comment -->
            <div class="leave-comment-block" id="leave-comment-block">
                <div class="leave-comment-label">Comment</div>
                <div class="leave-comment-text" id="leave-comment"></div>
            </div>

            <div class="button-div-popup">
                <form method="POST" class="popUp-ButtonHolder extraspaceforbuttons">
                    <input id="leaveID" type="hidden" name="leaveID" value="">
                    <input class="delete-button" name="DeleteLeaveSubmit" type="submit" value="Delete">
                    <button class="form-button" type="button" onclick="closeLeaveDetailsPopUp()">Close</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

function formatHumanDate(dateStr) {
    if (!dateStr) return '';

    const [y, m, d] = dateStr.split('-');
    const date = new Date(y, m - 1, d);

    return date.toLocaleDateString('en-GB', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
}

function formatShortHumanDate(dateStr) {
    if (!dateStr) return '';

    const [y, m, d] = dateStr.split('-');
    const date = new Date(y, m - 1, d);

    return date.toLocaleDateString('en-GB', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

function ymdToDmy(dateStr) {
    if (!dateStr) return '';

    const [year, month, day] = dateStr.split('-');

    return `${day}-${month}-${year}`;
}

function openLeaveDetailsPopUp(
    leaveID,
    staffName,
    leaveType,
    dayType,
    startDate,
    endDate,
    durationDays,
    doctorsNoteReceived,
    comment
) {
    document.getElementById("leaveDetailsPopUp").style.display = "flex";

    document.getElementById("leaveID").value = leaveID;

    document.getElementById("leave-staffName").innerText = staffName || '';
    const leaveTypeEl = document.getElementById("leave-leaveType");
    const dayTypeEl = document.getElementById("leave-dayType");

    if (leaveType === 'sick') {
        leaveType = 'Sick Leave';
        leaveTypeEl.className = 'badge badge-sick';
    } else if (leaveType === 'family') {
        leaveType = 'Family Responsibility';
        leaveTypeEl.className = 'badge badge-family';
    } else if (leaveType === 'annual') {
        leaveType = 'Annual Leave';
        leaveTypeEl.className = 'badge badge-annual';
    } else {
        leaveType = 'Unpaid Leave';
        leaveTypeEl.className = 'badge badge-unpaid';
    }

    leaveTypeEl.innerText = leaveType || '';

    if (dayType === 'Half' || dayType === 'half') {
        dayTypeEl.innerText = 'Half Day';
        dayTypeEl.style.display = 'inline-block';
    } else {
        dayTypeEl.innerText = '';
        dayTypeEl.style.display = 'none';
    }

    if (startDate === endDate) {
        document.getElementById("leave-dateRange").innerText =
            formatHumanDate(startDate);
        document.getElementById("leave-duration").innerText = '';
    } else {
        document.getElementById("leave-dateRange").innerText =
            `${formatShortHumanDate(startDate)} → ${formatShortHumanDate(endDate)}`;
    }

    const durationEl = document.getElementById("leave-duration");

    if (durationDays > 1) {
        durationEl.innerText = `${durationDays} working days`;
        durationEl.style.display = 'block';
    } else {
        durationEl.innerText = '';
        durationEl.style.display = 'none';
    }

    const commentBlock = document.getElementById("leave-comment-block");
    const commentEl = document.getElementById("leave-comment");

    if (comment && comment.trim() !== '') {
        commentEl.innerText = comment;
        commentBlock.style.display = 'block';
    } else {
        commentBlock.style.display = 'none';
    }

    // commentEl.innerText = comment || '';

    // document.getElementById("leave-doctorsNoteReceived").innerText =
    //     doctorsNoteReceived ? "Doctor's note received" : "No doctor's note";
}
    function closeLeaveDetailsPopUp() {
        document.getElementById("leaveDetailsPopUp").style.display = "none";
    }
</script>