<?php 
    $pin = $_POST['pin'] ?? '';
    $typeOfComment = $errors['earlyLate'] ?? '';
?>

<?php if (!isset($errors['late'])): ?>
<div class="clock-in-body">
    <form action="" method="POST" class="clock-in-form" id="clockInForm">
        <input type="hidden" name="clockInClockOut" value="1">
        <span class="error" id='error-pin'><?php if (isset($errors['pin'])) echo $errors['pin']; ?></span>
        <label for="" class="clock-in-tool-pin">Enter your pin</label>
            <div class="pin-input-row" aria-label="4 digit PIN">
                <input
                    type="text"
                    class="form-text-input-name pin-input"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    autocomplete="one-time-code"
                    aria-label="PIN digit 1"
                    name="pin1"
                    id="pin1"
                >
                <input
                    type="text"
                    class="form-text-input-name pin-input"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    aria-label="PIN digit 2"
                    name="pin2"
                    id="pin2"
                >
                <input
                    type="text"
                    class="form-text-input-name pin-input"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    aria-label="PIN digit 3"
                    name="pin3"
                    id="pin3"
                >
                <input
                    type="text"
                    class="form-text-input-name pin-input"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    aria-label="PIN digit 4"
                    name="pin4"
                    id="pin4"
                >

                <!-- Combined pin posted to PHP -->
                <input type="hidden" name="pin" id="pin">
            </div>
        <button type="submit" id="hiddenSubmit" style="display:none;">Submit</button>
    </form>
</div>
<?php endif;?>

<?php if (isset($errors['late'])): ?>
    <div class="popUpForm" style="display: flex;" id="addStaff-form">
        <div class="block-holder-popup reasonPopup" >
            <div>
                <h3 class="block-header">Hi <?php if (isset($errors['late'])) echo $errors['late']?> 👋</h3>
                <span class="clock-in-late"><?php if (isset($errors['offence'])) {
                    echo $errors['offence'];
                    
                } ?></span>
                <form method="POST">
                    <input type="hidden" name="pin" id="pinLate" value="<?= $pin ?>">
                    <input type="hidden" name="typeOfComment" id="typeOfComment" value="<?= $typeOfComment ?>">
                    <textarea name="lateReasonArea" placeholder="Add details (e.g. traffic, appointment, delivery)" class="commentTextArea textReason" id="lateReasonArea" required></textarea>
                    <br>
                    <button name="exceptionFormSubmission" value="1" class="form-button" type="submit">Confirm</button>
                </form>
            </div>
        </div>
    </div>
<?php endif;?>

<!-- JS Script -->
<script>
    (function () {
        const form = document.getElementById('clockInForm');
        if (!form) return;
        console.log('stafflogin pin JS loaded');

        const inputs = Array.from(form.querySelectorAll('.pin-input'));
        const hiddenPin = document.getElementById('pin');
        const hiddenSubmit = document.getElementById('hiddenSubmit');

        function sanitizeToDigit(value) {
            const digits = (value || '').replace(/\D/g, '');
            return digits.length ? digits[0] : '';
        }

        function updateHiddenPin() {
            hiddenPin.value = inputs.map(i => (i.value || '')).join('');
        }

        function focusIndex(idx) {
            if (idx < 0 || idx >= inputs.length) return;
            inputs[idx].focus();
            inputs[idx].select?.();
        }

        // Auto-focus first box
        focusIndex(0);

        inputs.forEach((input, idx) => {
            input.addEventListener('input', (e) => {
                // ensure only one digit
                const digit = sanitizeToDigit(input.value);
                input.value = digit;
                const errPin = document.getElementById('error-pin');
                if (errPin) errPin.textContent = '';
                updateHiddenPin();

                // auto-submit once all 4 digits are entered
                if (isPinComplete()) {
                    submitFormSafely();
                }

                // move to next
                if (digit && idx < inputs.length - 1) {
                    focusIndex(idx + 1);
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    // If current empty, move back
                    if (!input.value && idx > 0) {
                        focusIndex(idx - 1);
                    }
                    return;
                }

                // Optional: left/right arrow navigation
                if (e.key === 'ArrowLeft' && idx > 0) {
                    e.preventDefault();
                    focusIndex(idx - 1);
                }
                if (e.key === 'ArrowRight' && idx < inputs.length - 1) {
                    e.preventDefault();
                    focusIndex(idx + 1);
                }
            });

            // Paste support (e.g. 5175)
            input.addEventListener('paste', (e) => {
                const text = (e.clipboardData || window.clipboardData).getData('text');
                const digits = (text || '').replace(/\D/g, '').slice(0, 4);
                if (!digits) return;

                e.preventDefault();

                for (let i = 0; i < inputs.length; i++) {
                    inputs[i].value = digits[i] || '';
                }
                updateHiddenPin();
                const errPin = document.getElementById('error-pin');
                if (errPin) errPin.textContent = '';

                // auto-submit if paste completed the PIN
                if (isPinComplete()) {
                    submitFormSafely();
                }

                // focus next empty or last
                const nextEmpty = inputs.findIndex(i => !i.value);
                focusIndex(nextEmpty === -1 ? inputs.length - 1 : nextEmpty);
            });
        });

        function isPinComplete() {
            return /^\d{4}$/.test(hiddenPin.value);
        }

        let hasSubmitted = false;

        function submitFormSafely() {
            if (hasSubmitted) return;
            hasSubmitted = true;

            // requestSubmit triggers submit event + built-in constraint validation
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit(hiddenSubmit || undefined);
            } else if (hiddenSubmit) {
                hiddenSubmit.click();
            } else {
                // last resort
                form.submit();
            }
        }

        form.addEventListener('submit', (e) => {
            updateHiddenPin();

            // Basic client-side validation: must be 4 digits
            if (!/^\d{4}$/.test(hiddenPin.value)) {
                e.preventDefault();

                const errPin3 = document.getElementById('error-pin');
                if (errPin3) errPin3.textContent = 'Please enter your 4-digit PIN';

                // Clear and refocus
                inputs.forEach(i => i.value = '');
                updateHiddenPin();
                focusIndex(0);
            }

            // If we prevented submission due to validation, allow a retry
            if (e.defaultPrevented) {
                hasSubmitted = false;
            }
        });
    })();
</script>