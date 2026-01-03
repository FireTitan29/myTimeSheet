<form method="POST" class="popUpForm" id="popUpForm-Leave">
    <div class="popUpForm-Comment-div leave-popup-div">
        <div>
            <label for="" class="managementCommentHeading">Record Leave Day</label>
            <input id="commentID-leave" name="commentID" hidden type="text" value="">
            <input id="calendarDate-leave" name="calendarDate" type="hidden" value="">
            <label for="leaveType" class="managementCommentHeading smallerheading">Leave Type</label>
            <div class="leave-radio-holder">
                <select name="leaveType" id="leaveType">
                    <option value="" default hidden></option>
                    <option value="annual">Annual</option>
                    <option value="sick">Sick</option>
                    <option value="family">Family Responsibility</option>
                </select>
            </div>
            <label for="" class="managementCommentHeading smallerheading">Doctors Note</label>
            <div class="leave-radio-holder">
                <label for="doctor-yes">
                    <input type="radio" name="doctorsNote" value="yes" id="doctor-yes"> Yes
                </label>
                <label for="doctor-no">
                    <input type="radio" name="doctorsNote" value="no" id="doctor-no"> No
                </label>
            </div>
            <label for="" class="managementCommentHeading smallerheading">Extra Details</label>
            <textarea name="commentText" placeholder="Add comment here..." class="commentTextArea leaveTextArea" id="commentArea-leave"></textarea><br>
            <div class="button-div-popup">
                <div class="popUp-ButtonHolder">
                    <input class="form-button" name="LeavePopUpFormSubmit" type="submit" value="Save">
                    <button class="form-button" type="button" onclick="closeLeaveForm()">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function openLeaveForm(recordID = null, commentText = '', calendarDate = null) {
    document.getElementById("popUpForm-Leave").style.display = "flex";

    document.getElementById("commentID-leave").value = recordID || '';
    document.getElementById("commentArea-leave").value = commentText || '';
    document.getElementById("calendarDate-leave").value = calendarDate || '';
    
    document.getElementById("commentArea-leave").focus();
}

function closeLeaveForm() {
    document.getElementById("popUpForm-Leave").style.display = "none";
}
</script>