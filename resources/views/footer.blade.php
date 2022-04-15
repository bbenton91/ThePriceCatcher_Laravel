<footer id="footer" class="drop-shadow-sm border-2 mt-4 flex justify-end h-full">
    <script>
        function openForm() {
            document.getElementById("emailFormContainer").classList.remove("hide-feedback-form");
            document.getElementById("emailFormContainer").classList.remove("hidden");
            document.getElementById("emailFormContainer").classList.add("show-feedback-form");
        }

        function closeForm() {
            document.getElementById("emailFormContainer").classList.add("hide-feedback-form");
            document.getElementById("emailFormContainer").classList.remove("show-feedback-form");
        }

        function validateInput(container){
            const form = container.childNodes[1]; // Not sure why it has to be [1] but [0] returns empty text?

            let email = form.querySelectorAll("input[name=email]")[0];
            let subject = form.querySelectorAll("input[name=subject]")[0];
            let message = form.querySelectorAll("textarea[name=content]")[0];

            let errors = Object();
            if(!/@/.test(email.value))
                errors.email = true;
            if(subject.value.length <= 0)
                errors.subject = true;
            if(message.value.length < 10)
                errors.message = true;

            // Client side validation
            let hasErrors = applyErrors(errors, form);

            // If we had errors, don't proceed with sending it to the server
            if(hasErrors)
                return false;

            let data = `email=${email.value}&subject=${subject.value}&message=${message.value}`;

            var request = new XMLHttpRequest();

            // Get data back from the POST request. This will validate info server side in case client side
            // doesn't catch it
            request.onload = () => {
                const resp = request.responseText
                if(resp.length != 0){ // Don't apply errors if we have no response data
                    const errors = JSON.parse(request.responseText);
                    applyErrors(errors, form)
                }
                closeForm();
            }

            request.open('POST', '../php/email.php', true);
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            request.send(data);
        }

        function applyErrors(errors, form){
            let flag = false;

            if('email' in errors){
                let error = form.querySelectorAll("#emailErrorPlaceholder")[0];
                error.classList.remove("hidden");
                error.classList.add("pulse");
                flag = true;
            }else{
                let error = form.querySelectorAll("#emailErrorPlaceholder")[0];
                error.classList.remove("pulse");
                error.classList.add("hidden");
            }

            if('subject' in errors){
                let error = form.querySelectorAll("#subjectErrorPlaceholder")[0];
                error.classList.remove("hidden");
                error.classList.add("pulse");
                flag = true;
            }else{
                let error = form.querySelectorAll("#subjectErrorPlaceholder")[0];
                error.classList.remove("pulse");
                error.classList.add("hidden");
            }

            if('message' in errors){
                let error = form.querySelectorAll("#contentErrorPlaceholder")[0];
                error.classList.remove("hidden");
                error.classList.add("pulse");
                flag = true;
            }else{
                let error = form.querySelectorAll("#contentErrorPlaceholder")[0];
                error.classList.remove("pulse");
                error.classList.add("hidden");
            }

            return flag;
        }
    </script>

    <button id="sendFeedbackButton" class="mr-2" onclick="openForm()">Send Feedback</button>
</footer>

<div class="form-popup hide-feedback-form hidden" id="emailFormContainer">
    <div class="form-container" id="emailForm">
        <div id="feedbackInnerContainer">
            <h1>Send Feedback</h1>
            <label for="email" class="email-input feedback-label"><b>Your Email</b></label><br>
            <input type="email" name="email" placeholder="Enter Email" id="feedbackEmail" required class="border-2"><br>
            <p id="emailErrorPlaceholder" class = "error-text small-text hidden">
                Please provide a valid email address.
            </p>
            <br>

            <label for="subject" class="subject-input feedback-label"><b>Subject</b></label><br>
            <input type="text" name="subject" placeholder="Enter Subject" id="feedbackSubject" required class="border"><br>
            <p id="subjectErrorPlaceholder" class = "error-text  small-text hidden">
                Please provide a subject.
            </p><br>

            <div id="feedbackMessageContainer">
                <label for="content" class="content-input feedback-label"><b>Message</b></label><br>
                <textarea name="content" placeholder="Enter Message" id="feedbackMessage" cols="30" rows="10" required class="border-2"></textarea><br>
                <p id="contentErrorPlaceholder" class = "error-text  small-text hidden">
                    Please enter a message to send.
                </p>
            </div>

            <br>
            <br>

            <div id="feedbackButtons">
                <button type="button" class="button accept" onclick="validateInput(document.getElementById('emailFormContainer'))">Send</button><br>
                <button type="button" class="button cancel" onclick="closeForm()">Close</button>
            </div>

        </div>

    </div>
</div>
