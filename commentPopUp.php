<form method="POST" class="popUpForm" id="popUpForm-Comment">
    <div class="popUpForm-Comment-div">
        <div>
            <label for="commentArea" class="managementCommentHeading">Management Comment</label>
            <input id="commentID" name="commentID" hidden type="text" value="">
            <input id="calendarDate" name="calendarDate" type="hidden" value="">
            <br>
            <textarea name="commentText" placeholder="Add comment here..." class="commentTextArea" id="commentArea"></textarea><br>
            <div class="popUp-ButtonHolder">
                <input class="form-button" name="CommentPopUpFormSubmit" type="submit" value="Save">
                <button class="form-button" type="button" onclick="closeForm()">Cancel</button>
            </div>
        </div>
    </div>
</form>