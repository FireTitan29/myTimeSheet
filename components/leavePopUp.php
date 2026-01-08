<?php 

    $leaveFormHasErrors =
    isset($_POST['LeavePopUpFormSubmit']) && !empty($errors); 

    $publicHolidays = getAllPublicHolidays($year);
?>
<form method="POST" class="popUpForm" id="popUpForm-Leave">
    <!-- Hidden Inputs -->
    <input type="hidden" name="leaveRequest" value="true">
    <input type="hidden" name="staffID" value="<?php if ($person) echo getStaffMemberID($person);?>">
    <input id="calendarDate-leave" name="calendarDate" type="hidden" value="">

    <div class="popUpForm-Comment-div leave-popup-div">
        <div>
            <label class="managementCommentHeading">Record Leave Day</label>
            <span class="error leave-error-red"><?php if (isset($errors['leave'])) echo $errors['leave'];?></span>

            <label for="leaveType" class="managementCommentHeading smallerheading" style="margin-top: 5px;">Leave Type</label>
            <div class="leave-radio-holder">
                <select name="leaveType" id="leaveType">
                    <option value="" default hidden>Select</option>
                    <option value="annual">Annual</option>
                    <option value="sick">Sick</option>
                    <option value="family">Family Responsibility</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>
            <div class="doctorsNote-Holder">
                <span id="weekday-note" class="error leave-error" style="display:none;">
                    This leave falls on a Monday or Friday, a doctors note will be required
                </span>
                <label id="doctorsNode-label" class="managementCommentHeading smallerheading doctorsNoteHeading">
                    Doctors Note
                </label>
            </div>
            <!-- Doctors Note -->
            <div id="doctorsNode-div" class="leave-radio-holder">
                <label class="doctor-radio-holder" for="doctor-yes">
                    <input type="radio" name="doctorsNote" value="1" id="doctor-yes">
                    <span>Yes</span>
                </label>

                <label class="doctor-radio-holder" for="doctor-no">
                    <input type="radio" name="doctorsNote" value="0" id="doctor-no">
                    <span>No</span>
                </label>
            </div>
            <label class="managementCommentHeading smallerheading doctorsNoteHeading">
                Day Type
            </label>
            <!-- Fullday / Halfday -->
            <div class="day-type-toggle">
                <label>
                    <input type="radio" name="dayType" value="half">
                    <span>Half Day</span>
                </label>
                <label>
                    <input type="radio" name="dayType" value="full" checked>
                    <span>Full Day</span>
                </label>
            </div>
            <div>

                <label for="noOfDays" class="managementCommentHeading smallerheading doctorsNoteHeading">Number of Days</label>
                <input id="noOfDays" name="numberOfDaysLeave" class="numberOfDaysLeaveInput" type="number" min="1" step="1" value="1">
                <label id="thisLeaveDate" class="managementCommentHeading smallerheading dateHeadingPopUp leave-date-start">
                    Start Date:
                </label>
                <span id="leave-skip-helper"
                      class="error leave-error"
                      style="display:none; margin:4px 0;">
                </span>
                <label id="endLeaveDate" class="managementCommentHeading smallerheading dateHeadingPopUp leave-date-end">
                    End Date:
                </label>
            </div>
            <label class="managementCommentHeading smallerheading">Extra Details</label>
            <textarea name="commentText" placeholder="Add comment here..." class="commentTextArea leaveTextArea" id="commentArea-leave"></textarea><br>
            <div class="button-div-popup" style="margin-bottom: 8px;">
                <div class="popUp-ButtonHolder">
                    <input class="form-button" name="LeavePopUpFormSubmit" type="submit" value="Save">
                    <button class="form-button" type="button" onclick="closeLeaveForm()">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const PUBLIC_HOLIDAYS = <?= json_encode(array_column($publicHolidays, 'holiday_date')) ?>;

function isPublicHolidayJS(dateObj) {
    const yyyy = dateObj.getFullYear();
    const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
    const dd = String(dateObj.getDate()).padStart(2, '0');
    const dateStr = `${yyyy}-${mm}-${dd}`;

    return PUBLIC_HOLIDAYS.indexOf(dateStr) !== -1;
}

function parseDate(dateStr) {
    if (!dateStr) return null;

    let day, month, year;

    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
        [year, month, day] = dateStr.split('-').map(Number);
    } else if (/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
        [day, month, year] = dateStr.split('-').map(Number);
    } else {
        return null;
    }

    return new Date(year, month - 1, day);
}

function formatDateLabel(dateStr) {
    if (!dateStr) return '';

    let day, month, year;

    // yyyy-mm-dd (from DB / PHP)
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
        [year, month, day] = dateStr.split('-').map(Number);
    }
    // dd-mm-yyyy (manual / UI)
    else if (/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
        [day, month, year] = dateStr.split('-').map(Number);
    } else {
        return '';
    }

    const date = new Date(year, month - 1, day);

    const weekday = date.toLocaleDateString('en-GB', { weekday: 'short' });
    const monthName = date.toLocaleDateString('en-GB', { month: 'long' });

    function ordinal(n) {
        if (n >= 11 && n <= 13) return 'th';
        switch (n % 10) {
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
            default: return 'th';
        }
    }

    return `${weekday}, ${day}${ordinal(day)} ${monthName} ${year}`;
}

function isMondayOrFriday(dateStr) {
    if (!dateStr) return false;

    let day, month, year;

    // yyyy-mm-dd (DB / PHP)
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
        [year, month, day] = dateStr.split('-').map(Number);
    }
    // dd-mm-yyyy (UI / legacy)
    else if (/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
        [day, month, year] = dateStr.split('-').map(Number);
    } else {
        return false;
    }

    const date = new Date(year, month - 1, day);
    const dayOfWeek = date.getDay();

    return dayOfWeek === 1 || dayOfWeek === 5;
}

function updateEndDate() {
    const startDateStr = document.getElementById("calendarDate-leave").value;
    const days = parseInt(document.getElementById("noOfDays").value, 10);
    const endDateLabel = document.getElementById("endLeaveDate");
    const helper = document.getElementById("leave-skip-helper");

    helper.style.display = "none";
    helper.textContent = "";

    const startDate = parseDate(startDateStr);

    if (!startDate || !days || days < 1) {
        endDateLabel.textContent = "End Date:";
        return;
    }

    let endDate = new Date(startDate);
    let daysRemaining = days - 1;

    let skippedWeekend = false;
    let skippedHoliday = false;

    while (daysRemaining > 0) {
        endDate.setDate(endDate.getDate() + 1);

        const dayOfWeek = endDate.getDay();
        const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
        const isHoliday = isPublicHolidayJS(endDate);

        if (isWeekend) {
            skippedWeekend = true;
            continue;
        }

        if (isHoliday) {
            skippedHoliday = true;
            continue;
        }

        daysRemaining--;
    }

    const yyyy = endDate.getFullYear();
    const mm = String(endDate.getMonth() + 1).padStart(2, '0');
    const dd = String(endDate.getDate()).padStart(2, '0');

    endDateLabel.textContent =
        "End Date: " + formatDateLabel(`${yyyy}-${mm}-${dd}`);

    // Show helper message if anything was skipped
    if (skippedHoliday || skippedWeekend) {
        helper.style.display = "block";

        if (skippedHoliday && skippedWeekend) {
            helper.textContent = "Public holiday and weekend skipped within selected date range.";
        } else if (skippedHoliday) {
            helper.textContent = "Public holiday skipped within selected date range.";
        } else if (skippedWeekend) {
            helper.textContent = "Weekend skipped within selected date range.";
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const leaveTypeSelect = document.getElementById("leaveType");
    const doctorsNoteDiv = document.getElementById("doctorsNode-div");
    const doctorsNoteLabel = document.getElementById("doctorsNode-label");

    // Hide on load
    doctorsNoteDiv.style.display = "none";
    doctorsNoteLabel.style.display = "none";

    leaveTypeSelect.addEventListener("change", () => {

        if (leaveTypeSelect.value === "sick") {
            doctorsNoteDiv.style.display = "flex";
            doctorsNoteLabel.style.display = "flex";

            // AFTER openLeaveForm set it
            const calendarDateValue =
                document.getElementById("calendarDate-leave").value;

            if (isMondayOrFriday(calendarDateValue)) {
                document.getElementById("weekday-note").style.display = "block";
            } else {
                document.getElementById("weekday-note").style.display = "none";
            }

        } else {
            doctorsNoteDiv.style.display = "none";
            doctorsNoteLabel.style.display = "none";
            document.getElementById("weekday-note").style.display = "none";

            const radios = doctorsNoteDiv.querySelectorAll("input[type='radio']");
            radios.forEach(radio => radio.checked = false);
        }
    });

    const daysInput = document.getElementById("noOfDays");

    daysInput.addEventListener("input", () => {
        updateEndDate();
    });

    const dayTypeRadios = document.querySelectorAll('input[name="dayType"]');
    const numberOfDaysWrapper = document.querySelector('.numberOfDaysLeaveInput').parentElement;

    dayTypeRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.value === 'half' && radio.checked) {
                numberOfDaysWrapper.style.display = 'none';
                daysInput.value = 1;
                updateEndDate();
            } else if (radio.value === 'full' && radio.checked) {
                numberOfDaysWrapper.style.display = 'block';
                updateEndDate();
            }
        });
    });
});

function openLeaveForm(recordID = null, commentText = '', calendarDate = null) {
    document.getElementById("popUpForm-Leave").style.display = "flex";

    document.getElementById("commentArea-leave").value = commentText || '';
    document.getElementById("calendarDate-leave").value = calendarDate || '';

    document.getElementById("noOfDays").value = 1;

    // SET THE VISIBLE DATE LABEL
    document.getElementById("thisLeaveDate").textContent = "Start Date: " +
        formatDateLabel(calendarDate);

    updateEndDate();

    // Trigger sick-leave logic immediately
    document.getElementById("leaveType")
        .dispatchEvent(new Event("change"));

    document.getElementById("commentArea-leave").focus();
}

function closeLeaveForm() {
    const form = document.getElementById("popUpForm-Leave");

    // Reset all inputs to default values
    form.reset();

    // Hide doctors note section
    const doctorsNoteDiv = document.getElementById("doctorsNode-div");
    const doctorsNoteLabel = document.getElementById("doctorsNode-label");

    doctorsNoteDiv.style.display = "none";
    doctorsNoteLabel.style.display = "none";

    // CLEAR leave error
    const leaveError = form.querySelector('.leave-error-red');
    if (leaveError) {
        leaveError.textContent = '';
        leaveError.style.display = 'none';
    }

    // Close popup
    form.style.display = "none";
}

</script>

<?php if (!empty($leaveFormHasErrors)): ?>
<script>
document.addEventListener("DOMContentLoaded", () => {
    openLeaveForm(
        null,
        <?= json_encode($_POST['commentText'] ?? '') ?>,
        <?= json_encode($_POST['calendarDate'] ?? '') ?>
    );
});
</script>
<?php endif; ?>