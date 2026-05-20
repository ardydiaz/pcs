<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post-Class Survey Form</title>
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('storage/images/favicon.png')); ?>" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .section-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-bottom: 20px;
            display: none;
        }

        .section-card.active {
            display: block;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .header-section h1 {
            color: #764ba2;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header-section .subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .department-pill-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .department-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            background-color: #eef2ff;
            color: #4338ca;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .consent-section {
            border-left: 4px solid #764ba2;
            padding-left: 20px;
        }

        .rating-container {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .rating-option {
            text-align: center;
            flex: 1;
            margin: 0 10px;
            min-width: 120px;
        }

        .rating-option input[type="radio"] {
            width: 20px;
            height: 20px;
            margin-bottom: 10px;
        }

        .rating-option label {
            display: block;
            font-weight: 500;
            color: #495057;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }

        .btn-outline-secondary {
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
        }

        .progress-bar-custom {
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            margin-bottom: 20px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }

        .form-select:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }

        .illustration {
            text-align: center;
            margin-bottom: 30px;
        }

        .illustration svg {
            max-width: 200px;
            height: auto;
        }

        .section-title {
            color: #764ba2;
            font-weight: 600;
            margin-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 10px;
        }

        /* Thank you screen styles */
        .thank-you-screen {
            text-align: center;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            margin-bottom: 20px;
            display: none;
        }

        .thank-you-screen.active {
            display: block;
            animation: fadeIn 0.5s ease-in;
        }

        .thank-you-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }

        .cooldown-timer {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }

        .pulse {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <!-- Progress Bar (hidden during thank you screens) -->
        <div class="progress-bar-custom" id="progressBarContainer">
            <div class="progress-fill" id="progressBar" style="width: 25%"></div>
        </div>

        <!-- Thank You Screen for Successful Submission -->
        <div class="thank-you-screen" id="successThankYou">
            <div class="thank-you-icon">✅</div>
            <h2 class="text-success mb-4">Thank You!</h2>
            <h4 class="text-primary mb-3">Your evaluation has been submitted successfully</h4>
            <p class="lead mb-4">
                We appreciate your feedback! Your responses help us improve the quality of education.
            </p>
            <div class="alert alert-info">
                <strong>Important:</strong> Your responses have been recorded and cannot be modified.
            </div>
            <button type="button" class="btn btn-primary mt-3" onclick="resetForm()">
                Evaluate Another Course
            </button>
        </div>

        <!-- Thank You Screen for Cooldown Period -->
        <div class="thank-you-screen" id="cooldownThankYou">
            <div class="thank-you-icon">⏰</div>
            <h2 class="text-primary mb-4">Thank You for Your Recent Evaluation!</h2>
            <h4 class="mb-3">You've already evaluated this course recently</h4>
            <p class="lead mb-4">
                To prevent duplicate submissions, please wait a moment before submitting another evaluation for this
                course.
            </p>
            <div class="cooldown-timer pulse" id="cooldownDisplay">01:00</div>
            <p class="text-muted">Time remaining</p>
            <div class="alert alert-light mt-4">
                <strong>Feel free to:</strong> Evaluate other courses or return later
            </div>
            <button type="button" class="btn btn-outline-primary mt-3" onclick="backToForm()">
                Select Different Course
            </button>
        </div>

        <form id="evaluationForm" method="POST"
            action="<?php echo e(route('evaluation.submit', last(explode('/', $evaluation->form_link)))); ?>">
            <?php echo csrf_field(); ?>

            <!-- Section 1: Header & Privacy Consent -->
            <div class="section-card active" data-section="1">
                <div class="header-section">
                    <h1><?php echo e($evaluation->resolved_faculty_name); ?></h1>
                    <?php
                        $facultyDepartments = collect(explode(',', $evaluation->resolved_faculty_department ?? ''))
                            ->map(function ($value) {
                                return trim($value);
                            })
                            ->filter(function ($value) {
                                return $value !== '';
                            })
                            ->values();
                    ?>
                    <div class="department-pill-group">
                        <?php $__empty_1 = true; $__currentLoopData = $facultyDepartments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <span class="department-pill"><?php echo e($department); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <span class="department-pill">No department</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted"><?php echo e($evaluation->semester); ?> Semester Post-Class Student Survey</p>
                </div>

                <div class="consent-section">
                    <h4 class="text-primary mb-3">DATA PRIVACY STATEMENT</h4>

                    <p class="mb-3">
                        Manila Central University (MCU) is committed to protecting the privacy of its data subjects and
                        ensuring the safety and security of their personal data under its control and custody.
                    </p>

                    <p class="mb-3">
                        This policy provides information on how the MCU will <strong>collect, use, process, share,
                            secure</strong> and
                        <strong>dispose</strong> of personal data, in accordance with <strong>Republic Act. No.
                            10173</strong>, also known as the
                        <strong>Data Privacy Act of 2012</strong> and its Implementing Rules and Regulations.
                    </p>

                    <p class="mb-4">
                        I understand that my personal information is protected by <strong>RA 10173 (Data Privacy Act of
                            2012)</strong> to
                        provide truthful information. <span class="text-danger">*</span>
                    </p>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="privacy_consent" id="consent_accept"
                            value="accept" required>
                        <label class="form-check-label" for="consent_accept">Accept</label>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="radio" name="privacy_consent" id="consent_decline"
                            value="decline">
                        <label class="form-check-label" for="consent_decline">Decline</label>
                    </div>
                </div>

                <div class="text-end">
                    <button type="button" class="btn btn-primary" onclick="nextSection()">Next</button>
                </div>
            </div>

            <!-- Section 2: Course Selection -->
            <div class="section-card" data-section="2">
                <h3 class="section-title">Course Selection</h3>

                <?php
                    $sectionOptions = $schedules
                        ->map(function ($schedule) {
                            return optional($schedule->facultyCourse)->section;
                        })
                        ->filter(function ($section) {
                            return $section !== null && trim($section) !== '';
                        })
                        ->unique()
                        ->values();
                ?>

                <div class="mb-4">
                    <h5 class="text-primary mb-3">Select your section:</h5>
                    <select name="section" id="section" class="form-select" required>
                        <option value="">Select your answer</option>
                        <?php $__currentLoopData = $sectionOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($section); ?>"><?php echo e($section); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="mb-4">
                    <h5 class="text-primary mb-3">Select your course:</h5>
                    <select name="schedule_id" id="schedule_id" class="form-select" required
                        onchange="checkCooldownForSchedule()">
                        <option value="">Select your answer</option>
                        <?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($schedule->id); ?>"
                                data-section="<?php echo e(optional($schedule->facultyCourse)->section); ?>">
                                <?php echo e($schedule->facultyCourse->course->class_code); ?> -
                                <?php echo e($schedule->facultyCourse->course->subject_code); ?> -
                                <?php echo e($schedule->facultyCourse->section); ?> -
                                <?php echo e($schedule->day); ?> <?php echo e($schedule->time); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="form-text">Class Code - Subject Code - Section - Schedule</div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" onclick="prevSection()">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextSection()">Next</button>
                </div>
            </div>

            <!-- Section 3: Rating -->
            <div class="section-card" data-section="3">
                <h3 class="section-title">Subject Rating</h3>

                <div class="mb-4">
                    <h5 class="text-primary mb-3">Effectiveness Rating</h5>
                    <p class="mb-4">
                        How would you rate the faculty member's <strong>effectiveness</strong> in <strong>delivering the
                            lesson</strong>
                        and <strong>facilitating learning</strong>? <span class="text-danger">*</span>
                    </p>

                    <div class="rating-container">
                        <div class="rating-option">
                            <input type="radio" name="effectiveness_rating" id="rating_1" value="1" required>
                            <label for="rating_1">Not Effective</label>
                        </div>
                        <div class="rating-option">
                            <input type="radio" name="effectiveness_rating" id="rating_2" value="2" required>
                            <label for="rating_2">Somewhat Effective</label>
                        </div>
                        <div class="rating-option">
                            <input type="radio" name="effectiveness_rating" id="rating_3" value="3" required>
                            <label for="rating_3">Effective</label>
                        </div>
                        <div class="rating-option">
                            <input type="radio" name="effectiveness_rating" id="rating_4" value="4" required>
                            <label for="rating_4">Very Effective</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" onclick="prevSection()">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextSection()">Next</button>
                </div>
            </div>

            <!-- Section 4: Feedback -->
            <div class="section-card" data-section="4">
                <h3 class="section-title">Feedback</h3>

                <div class="mb-4">
                    <h5 class="text-primary mb-3">Additional Comments</h5>
                    <p class="mb-3"><strong>Additional Comments on the Faculty Member:</strong></p>
                    <p class="mb-4">
                        Please share any feedback on the faculty's <strong>teaching style</strong>,
                        <strong>clarity</strong>,
                        <strong>engagement</strong>, or <strong>areas for improvement</strong>
                    </p>

                    <textarea name="feedback_comments" id="feedback_comments" class="form-control" rows="6"
                        placeholder="Enter your feedback (optional)"></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" onclick="prevSection()">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextSection()">Next</button>
                </div>
            </div>

            <!-- Section 5: Submit -->
            <div class="section-card" data-section="5">
                <div class="text-center">
                    <div class="illustration mb-4">
                        <svg viewBox="0 0 200 150" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="100" cy="75" r="50" fill="#667eea" opacity="0.2" />
                            <path d="M70 75 L90 95 L130 55" stroke="#667eea" stroke-width="4" fill="none"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    <h3 class="text-primary mb-3">Review Your Responses</h3>
                    <p class="mb-4">Please review your answers before submitting the evaluation.</p>

                    <div class="alert alert-info mb-4">
                        <strong>Note:</strong> Once submitted, you cannot modify your responses.
                        Make sure all information is accurate.
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="prevSection()">Previous</button>
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="bi bi-check-circle me-2"></i>Submit Evaluation
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Error Messages (only for non-cooldown errors) -->
    <?php if(session('error') && !str_contains(session('error'), 'Please wait')): ?>
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentSection = 1;
        const totalSections = 5;
        let cooldownTimer = null;
        const sectionSelect = document.getElementById('section');
        const scheduleSelect = document.getElementById('schedule_id');

        function updateProgressBar() {
            const progress = (currentSection / totalSections) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }

        function showSection(sectionNumber) {
            // Hide thank you screens
            hideAllThankYouScreens();

            // Show progress bar
            document.getElementById('progressBarContainer').style.display = 'block';

            // Hide all form sections
            document.querySelectorAll('.section-card').forEach(card => {
                card.classList.remove('active');
            });

            // Show target section
            document.querySelector(`[data-section="${sectionNumber}"]`).classList.add('active');
            updateProgressBar();
        }

        function hideAllThankYouScreens() {
            document.getElementById('successThankYou').classList.remove('active');
            document.getElementById('cooldownThankYou').classList.remove('active');
        }

        function showSuccessThankYou() {
            // Hide form and progress bar
            document.querySelectorAll('.section-card').forEach(card => {
                card.classList.remove('active');
            });
            document.getElementById('progressBarContainer').style.display = 'none';

            // Show success thank you screen
            document.getElementById('successThankYou').classList.add('active');
        }

        function showCooldownThankYou(scheduleId) {
            // Hide form and progress bar
            document.querySelectorAll('.section-card').forEach(card => {
                card.classList.remove('active');
            });
            document.getElementById('progressBarContainer').style.display = 'none';

            // Show cooldown thank you screen
            document.getElementById('cooldownThankYou').classList.add('active');

            // Start countdown if schedule ID is available
            if (scheduleId) {
                startCooldownCountdown(scheduleId);
            }
        }

        function nextSection() {
            if (validateCurrentSection()) {
                if (currentSection < totalSections) {
                    currentSection++;
                    showSection(currentSection);
                }
            }
        }

        function prevSection() {
            if (currentSection > 1) {
                currentSection--;
                showSection(currentSection);
            }
        }

        function validateCurrentSection() {
            const currentCard = document.querySelector(`[data-section="${currentSection}"]`);
            const requiredFields = currentCard.querySelectorAll('[required]');

            for (let field of requiredFields) {
                if (field.type === 'radio') {
                    const radioGroup = currentCard.querySelectorAll(`[name="${field.name}"]`);
                    const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                    if (!isChecked) {
                        alert('Please complete all required fields before proceeding.');
                        return false;
                    }
                } else if (!field.value.trim()) {
                    alert('Please complete all required fields before proceeding.');
                    field.focus();
                    return false;
                }
            }

            if (currentSection === 1) {
                const consentValue = document.querySelector('input[name="privacy_consent"]:checked')?.value;
                if (consentValue === 'decline') {
                    alert('You must accept the privacy consent to proceed with the evaluation.');
                    return false;
                }
            }

            return true;
        }

        // Handle form submission
        document.getElementById('evaluationForm').addEventListener('submit', function (e) {
            if (!validateCurrentSection()) {
                e.preventDefault();
                return false;
            }

            // Check cooldown before allowing submission
            const scheduleId = scheduleSelect?.value;
            if (scheduleId && isInCooldown(scheduleId)) {
                e.preventDefault();
                showCooldownThankYou(scheduleId);
                return false;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
            submitBtn.disabled = true;
        });

        // Cooldown management
        function isInCooldown(scheduleId) {
            const lastSubmission = localStorage.getItem(`lastSubmission_${scheduleId}`);
            if (!lastSubmission) return false;

            const cooldownEnd = parseInt(lastSubmission) + (60 * 1000); // 1 minute in milliseconds
            return Date.now() < cooldownEnd;
        }

        function getRemainingCooldown(scheduleId) {
            const lastSubmission = localStorage.getItem(`lastSubmission_${scheduleId}`);
            if (!lastSubmission) return 0;

            const cooldownEnd = parseInt(lastSubmission) + (60 * 1000);
            const remaining = cooldownEnd - Date.now();
            return remaining > 0 ? Math.ceil(remaining / 1000) : 0;
        }

        function checkCooldownForSchedule() {
            const scheduleId = scheduleSelect?.value;
            if (scheduleId && isInCooldown(scheduleId)) {
                showCooldownThankYou(scheduleId);
            }
        }

        function startCooldownCountdown(scheduleId) {
            if (cooldownTimer) {
                clearInterval(cooldownTimer);
            }

            function updateCountdown() {
                const remaining = getRemainingCooldown(scheduleId);
                if (remaining <= 0) {
                    clearInterval(cooldownTimer);
                    // Auto return to course selection
                    currentSection = 2;
                    showSection(currentSection);
                } else {
                    const minutes = Math.floor(remaining / 60);
                    const seconds = remaining % 60;
                    const timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    document.getElementById('cooldownDisplay').textContent = timeString;
                }
            }

            // If we don't have a valid schedule ID or cooldown info, try to extract from server error
            if (!scheduleId || !isInCooldown(scheduleId)) {
                // Try to get initial countdown from server-side session error message
                <?php if(session('error') && str_contains(session('error'), 'Please wait')): ?>
                    const errorMessage = '<?php echo e(session('error')); ?>';
                    const timeMatch = errorMessage.match(/(\d+):(\d+)/);
                    if (timeMatch) {
                        const minutes = parseInt(timeMatch[1]);
                        const seconds = parseInt(timeMatch[2]);
                        const totalSeconds = (minutes * 60) + seconds;

                        // Set a fake timestamp for countdown
                        const fakeTimestamp = Date.now() - (60000 - (totalSeconds * 1000));
                        localStorage.setItem(`lastSubmission_${scheduleId}`, fakeTimestamp.toString());
                    }
                <?php endif; ?>
            }

            updateCountdown();
            cooldownTimer = setInterval(updateCountdown, 1000);
        }

        function recordSubmission(scheduleId) {
            localStorage.setItem(`lastSubmission_${scheduleId}`, Date.now().toString());
        }

        function resetForm() {
            // Reset form
            document.getElementById('evaluationForm').reset();
            currentSection = 1;
            showSection(currentSection);
            filterSchedulesBySection();
        }

        function backToForm() {
            if (cooldownTimer) {
                clearInterval(cooldownTimer);
            }
            currentSection = 2; // Go back to course selection
            showSection(currentSection);
            // Clear the course selection to allow choosing a different course
            if (scheduleSelect) {
                scheduleSelect.value = '';
            }
            if (sectionSelect) {
                sectionSelect.value = '';
            }
            filterSchedulesBySection();
        }

        function filterSchedulesBySection(resetSchedule = true) {
            if (!sectionSelect || !scheduleSelect) {
                return;
            }
            const selectedSection = sectionSelect.value.trim();
            const options = scheduleSelect.querySelectorAll('option[data-section]');
            options.forEach((option) => {
                const matches = selectedSection !== '' && option.dataset.section === selectedSection;
                option.hidden = !matches;
                option.disabled = !matches;
            });
            scheduleSelect.disabled = selectedSection === '';
            if (resetSchedule) {
                scheduleSelect.value = '';
            }
        }

        function applySectionFromSchedule(scheduleId) {
            if (!sectionSelect || !scheduleSelect || !scheduleId) {
                return;
            }
            const selectedOption = scheduleSelect.querySelector(`option[value="${scheduleId}"]`);
            if (!selectedOption) {
                return;
            }
            const sectionValue = selectedOption.dataset.section || '';
            sectionSelect.value = sectionValue;
            filterSchedulesBySection(false);
        }

        // Check for successful submission and show thank you screen
        <?php if(session('success')): ?>
            window.addEventListener('load', function () {
                const scheduleId = '<?php echo e(old('schedule_id')); ?>';
                if (scheduleId) {
                    recordSubmission(scheduleId);
                    applySectionFromSchedule(scheduleId);
                }
                showSuccessThankYou();
            });
        <?php endif; ?>

        // Check for cooldown error and show cooldown thank you screen
        <?php if(session('error') && str_contains(session('error'), 'Please wait')): ?>
            // Show cooldown thank you screen when there's a cooldown error
            window.addEventListener('load', function () {
                const scheduleId = '<?php echo e(old('schedule_id')); ?>';
                // Force show cooldown thank you screen immediately
                showCooldownThankYou(scheduleId);

                // Also set the schedule dropdown to the previously selected value
                if (scheduleSelect && scheduleId) {
                    scheduleSelect.value = scheduleId;
                }
                applySectionFromSchedule(scheduleId);
            });
        <?php endif; ?>

        if (sectionSelect) {
            sectionSelect.addEventListener('change', () => {
                filterSchedulesBySection();
            });
            filterSchedulesBySection(false);
        }

        updateProgressBar();
    </script>
</body>

</html>
<?php /**PATH /var/www/postclasssurvey.mcu.edu.ph/resources/views/content/data-management/evaluation-files/evaluation-form.blade.php ENDPATH**/ ?>