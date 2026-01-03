<form method="POST" class="popUpForm" id="popUpForm-Comment">
    <div class="popUpForm-Comment-div">
        <div>
            <label for="commentArea" class="managementCommentHeading">Management Comment</label>
            <input id="commentID" name="commentID" hidden type="text" value="">
            <input id="calendarDate" name="calendarDate" type="hidden" value="">
            <br>
            <textarea name="commentText" placeholder="Add comment here..." class="commentTextArea textReason" id="commentArea"></textarea><br>
            <div class="button-div-popup">
                <div class="popUp-ButtonHolder">
                    <input class="form-button" name="CommentPopUpFormSubmit" type="submit" value="Save">
                    <button class="form-button" type="button" onclick="closeForm()">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</form>

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