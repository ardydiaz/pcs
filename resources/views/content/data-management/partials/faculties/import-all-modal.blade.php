@if ($canImportAll)
    {{-- Convert Alternate Faculty Load Format to Import All Template --}}
    <div class="modal fade" id="facultyConvertImportModal" tabindex="-1" aria-labelledby="facultyConvertImportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <form method="POST" action="{{ route('faculties.convert-import-template') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header evaluation-modal-header">
                        <h5 class="modal-title mb-0" id="facultyConvertImportModalLabel">Convert Excel Template</h5>
                        <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                            aria-label="Close">Ã—</button>
                    </div>
                    <div class="modal-body evaluation-modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="facultyConvertImportFile">Source Excel File</label>
                            <input type="file" name="file" id="facultyConvertImportFile" class="form-control"
                                accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted d-block mt-2">Accepted source headers: CLASS CODE, SUBJECT CODE,
                                SECTION, INFORMATION, EMP. NO., FACULTY.</small>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="facultyConvertAcademicYear">Academic Year</label>
                                <input type="text" name="academic_year" id="facultyConvertAcademicYear"
                                    class="form-control" value="2026-2027" placeholder="2026-2027" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="facultyConvertSemester">Semester</label>
                                <select name="semester" id="facultyConvertSemester" class="form-select" required>
                                    <option value="1st Semester" selected>1st Semester</option>
                                    <option value="2nd Semester">2nd Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="facultyConvertSubjectType">Subject Type</label>
                                <select name="subject_type" id="facultyConvertSubjectType" class="form-select" required>
                                    <option value="major" selected>major</option>
                                    <option value="minor">minor</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="facultyConvertStatus">Status</label>
                                <select name="status" id="facultyConvertStatus" class="form-select" required>
                                    <option value="scheduled" selected>scheduled</option>
                                    <option value="completed">completed</option>
                                    <option value="cancelled">cancelled</option>
                                </select>
                            </div>
                        </div>

                        <small class="text-muted d-block mt-3">This only downloads a converted file. It will not change
                            the database.</small>
                    </div>
                    <div class="modal-footer evaluation-modal-footer">
                        <button type="button" class="btn btn-tertiary evaluation-modal-btn"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-faculty-primary evaluation-modal-btn">Convert and
                            Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Import All Modal (Faculty, Course, Schedule) --}}
    <div class="modal fade" id="facultyImportAllModal" tabindex="-1" aria-labelledby="facultyImportAllModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <form method="POST" action="{{ route('faculties.import-all') }}" enctype="multipart/form-data"
                    id="facultyImportAllForm">
                    @csrf
                    <div class="modal-header evaluation-modal-header">
                        <h5 class="modal-title mb-0" id="facultyImportAllModalLabel">Import All Records</h5>
                        <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                            aria-label="Close">×</button>
                    </div>
                    <div class="modal-body evaluation-modal-body">
                        <!-- File Input Section (shown by default) -->
                        <div id="facultyImportAllFileSection" class="mb-0">
                            <label class="form-label" for="facultyImportAllFile">Select File</label>
                            <input type="file" name="file" id="facultyImportAllFile" class="form-control"
                                accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted d-block mt-2">Headers: employeeno, classcode, section,
                                academicyear, semester, time, day, subjectcode (+ optional: subjecttype, status, name or
                                fullname, department, jobtitle)</small>
                            <small class="text-muted d-block mt-2">Import All adds new assignments and schedules without
                                deleting previous data.</small>
                        </div>

                        <!-- Progress Section (hidden by default) -->
                        <div id="facultyImportAllProgressSection" class="d-none">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Import Progress</label>
                                    <small id="facultyImportAllProgressText" class="text-muted">0%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div id="facultyImportAllProgressBar" class="progress-bar progress-bar-animated"
                                        role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                            <small class="text-muted d-block">Processing your file...</small>
                        </div>

                        <!-- Skipped Records Section (hidden by default) -->
                        <div id="facultyImportAllSkippedSection" class="mt-3 d-none">
                            <div class="alert alert-warning mb-0">
                                <div class="d-flex align-items-start">
                                    <span class="me-2">⚠️</span>
                                    <div>
                                        <strong class="d-block mb-2">Records Skipped</strong>
                                        <small id="facultyImportAllSkippedCount" class="text-muted d-block mb-2">0
                                            records were skipped</small>
                                        <div id="facultyImportAllSkippedList" class="small"
                                            style="max-height: 150px; overflow-y: auto;">
                                            <!-- Skipped records will be listed here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Success Section (hidden by default) -->
                        <div id="facultyImportAllSuccessSection" class="mt-3 d-none">
                            <div class="alert alert-success mb-0">
                                <div class="d-flex align-items-start">
                                    <span class="me-2">✓</span>
                                    <div>
                                        <strong class="d-block mb-1">Import Complete</strong>
                                        <small id="facultyImportAllSuccessText" class="text-muted d-block">Records have
                                            been imported successfully.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer evaluation-modal-footer">
                        <button type="button" id="facultyImportAllCancelBtn"
                            class="btn btn-tertiary evaluation-modal-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="facultyImportAllSubmitBtn"
                            class="btn btn-faculty-primary evaluation-modal-btn" data-default-text="Import All"
                            data-loading-text="Importing...">Import All</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const modal = document.getElementById('facultyImportAllModal');
            if (!modal) return;

            const form = document.getElementById('facultyImportAllForm');
            const fileSection = document.getElementById('facultyImportAllFileSection');
            const progressSection = document.getElementById('facultyImportAllProgressSection');
            const progressBar = document.getElementById('facultyImportAllProgressBar');
            const progressText = document.getElementById('facultyImportAllProgressText');
            const skippedSection = document.getElementById('facultyImportAllSkippedSection');
            const skippedCount = document.getElementById('facultyImportAllSkippedCount');
            const skippedList = document.getElementById('facultyImportAllSkippedList');
            const successSection = document.getElementById('facultyImportAllSuccessSection');
            const successText = document.getElementById('facultyImportAllSuccessText');
            const submitBtn = document.getElementById('facultyImportAllSubmitBtn');

            const resetModal = () => {
                fileSection.classList.remove('d-none');
                progressSection.classList.add('d-none');
                skippedSection.classList.add('d-none');
                successSection.classList.add('d-none');
                progressBar.style.width = '0%';
                progressText.textContent = '0%';
                skippedList.innerHTML = '';
                document.getElementById('facultyImportAllFile').value = '';
                submitBtn.disabled = false;

                // Remove any error alerts
                form.querySelectorAll('.alert-danger').forEach(el => el.remove());
            };

            // Show progress section and hide file input when import starts
            const showProgress = () => {
                fileSection.classList.add('d-none');
                progressSection.classList.remove('d-none');
                skippedSection.classList.add('d-none');
                successSection.classList.add('d-none');
            };

            // Update progress bar and text
            const updateProgress = (percentage) => {
                progressBar.style.width = percentage + '%';
                progressText.textContent = percentage + '%';
            };

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const formatSkippedRecord = (record) => {
                if (record.message) {
                    return record.message;
                }

                const reason = record.reason || 'Unknown error';
                const details = [];

                if (record.row_number) {
                    details.push(`Row ${record.row_number}`);
                }

                const employeeNo = record.employee_no || record.row?.employeeno || '';
                details.push(`Employee: ${employeeNo || 'blank'}`);

                const facultyName = record.faculty_name || record.row?.fullname || record.row?.name || '';
                if (facultyName) details.push(`Faculty: ${facultyName}`);

                const classCode = record.class_code || record.row?.classcode || '';
                if (classCode) details.push(`Class: ${classCode}`);

                const section = record.section || record.row?.section || '';
                if (section) details.push(`Section: ${section}`);

                const subjectCode = record.subject_code || record.row?.subjectcode || '';
                if (subjectCode) details.push(`Subject: ${subjectCode}`);

                return `${reason} (${details.join(', ')})`;
            };

            // Show skipped records if there are any after import completes
            const showSkipped = (skippedRecords) => {
                if (skippedRecords && skippedRecords.length > 0) {
                    skippedSection.classList.remove('d-none');
                    skippedCount.textContent =
                        `${skippedRecords.length} record${skippedRecords.length !== 1 ? 's' : ''} could not be imported`;
                    skippedList.innerHTML = skippedRecords
                        .map((record) =>
                            `<div class="py-1 px-2 bg-light rounded mb-1"><small>${escapeHtml(formatSkippedRecord(record))}</small></div>`
                        )
                        .join('');
                }
            };

            // Show success message and update UI after import completes
            const showSuccess = (data) => {
                const importedCount = data.imported || 0;
                progressSection.classList.add('d-none');
                fileSection.classList.add('d-none');
                successSection.classList.remove('d-none');
                successText.textContent =
                    `${importedCount} schedule record${importedCount !== 1 ? 's have' : ' has'} been imported successfully. Previous assignments and schedules were kept.`;
                submitBtn.textContent = 'Done';
                submitBtn.disabled = false;
                submitBtn.addEventListener('click', () => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById(
                        'facultyImportAllModal'));
                    modal.hide();
                });
            };

            // const showErrorMessage = (message) => {
            //     progressSection.classList.add('d-none');

            //     // Create error alert
            //     const errorAlert = document.createElement('div');
            //     errorAlert.className = 'alert alert-danger alert-dismissible fade show mt-3';
            //     errorAlert.innerHTML = `
        //         <strong>Import Error</strong>
        //         <p class="mb-0">${message}</p>
        //         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        //     `;

            //     // Insert error alert into modal body
            //     const modalBody = form.closest('.modal-body') || form.parentElement;
            //     if (modalBody) {
            //         modalBody.querySelector('#facultyImportAllFileSection').after(errorAlert);
            //     }

            //     fileSection.classList.remove('d-none');
            //     submitBtn.disabled = false;
            // };

            const showErrorMessage = (response = {}) => {
                progressSection.classList.add('d-none');

                console.log('FULL RESPONSE:', response); // debug

                let messageHtml = `
                    <strong>Import Error</strong>
                    <p class="mb-2">${response.message ?? 'Import failed'}</p>
                `;

                const skipped = response.skipped_details ?? [];

                if (skipped.length > 0) {
                    messageHtml += `<hr><strong>Skipped Records:</strong><ul class="mb-0 mt-2">`;

                    skipped.forEach((item, index) => {
                        messageHtml += `<li>${index + 1}. ${escapeHtml(formatSkippedRecord(item))}</li>`;
                    });

                    messageHtml += `</ul>`;
                }

                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger alert-dismissible fade show mt-3';

                errorAlert.innerHTML = `
                    ${messageHtml}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                const modalBody = form.closest('.modal-body') || form.parentElement;
                const anchor = modalBody.querySelector('#facultyImportAllFileSection');

                if (anchor) {
                    anchor.after(errorAlert);
                }

                fileSection.classList.remove('d-none');
                submitBtn.disabled = false;
            };

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                showProgress();
                submitBtn.disabled = true;

                // Simulate progress animation
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += Math.random() * 30;
                    if (progress > 90) progress = 90;
                    updateProgress(Math.floor(progress));
                }, 300);

                // Submit form via AJAX
                const formData = new FormData(form);
                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            throw new Error('Server returned invalid response type: ' + contentType);
                        }
                        return response.json().then(data => {
                            if (!response.ok) {
                                const error = new Error(data.message || 'Import failed: ' + response.statusText);
                                error.data = data;
                                throw error;
                            }
                            return data;
                        });
                    })
                    .then(data => {
                        console.log('Import All Response:', data);
                        clearInterval(progressInterval);
                        updateProgress(100);

                        // Wait a moment before showing results
                        setTimeout(() => {

                            if (!data.success) {
                                showErrorMessage(data); // ✅ FIX HERE
                                submitBtn.disabled = false;
                                return;
                            }

                            const skippedRecords = data.skipped_details || [];

                            if (skippedRecords.length >
                                0) {
                                console.log('Showing skipped records:', skippedRecords);
                                showSkipped(skippedRecords);
                            } else {
                                console.log('No skipped records');
                            }

                            showSuccess(data);

                        }, 500);
                    })
                    .catch(error => {
                        clearInterval(progressInterval);
                        console.error('Import All error:', error);
                        if (error.data) {
                            showErrorMessage(error.data);
                            submitBtn.disabled = false;
                            return;
                        }
                        progressSection.classList.add('d-none');
                        skippedSection.classList.add('d-none');
                        fileSection.classList.remove('d-none');

                        // Show error alert
                        const errorMessage = document.createElement('div');
                        errorMessage.innerHTML = `
                        <div class="alert alert-danger mt-3 mb-0">
                            <div class="d-flex align-items-start">
                                <span class="me-2">✕</span>
                                <div>
                                    <strong class="d-block mb-1">Import Failed</strong>
                                    <small class="d-block">${error.message || 'An unexpected error occurred'}</small>
                                </div>
                            </div>
                        </div>
                    `;
                        form.appendChild(errorMessage);

                        submitBtn.disabled = false;
                        setTimeout(() => {
                            errorMessage.remove();
                        }, 5000);
                    });
            });

            // Reset modal when shown
            modal.addEventListener('show.bs.modal', () => {
                resetModal();
            });

            // Handle modal close/dismiss to refresh page if import was successful
            modal.addEventListener('hide.bs.modal', () => {
                if (successSection.classList.contains('d-none') === false) {
                    // Import was successful, refresh page to show new data
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                }
            });
        })();
    </script>
@endif
