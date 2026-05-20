<?php if($canEdit): ?>
    
    <div class="modal fade" id="assignmentEditModal" tabindex="-1" aria-labelledby="assignmentEditModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered evaluation-modal-dialog evaluation-modal-dialog--narrow">
            <div class="modal-content evaluation-card">
                <form id="assignmentEditForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="assignment_id" value="">
                    <div class="modal-header evaluation-modal-header">
                        <h5 class="modal-title mb-0" id="assignmentEditModalLabel">Edit Assignment</h5>
                        <button type="button" class="evaluation-modal-close" data-bs-dismiss="modal"
                            aria-label="Close">×</button>
                    </div>
                    <div class="modal-body evaluation-modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editAssignmentFacultyToggle">Faculty Member</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="editAssignmentFacultyToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Faculty --">-- Select Faculty --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        <?php $__currentLoopData = $faculties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $rawFacultyName =
                                                    optional($faculty->user)->name ?? ($faculty->name ?? '');
                                                $facultyEmail = optional($faculty->user)->email ?? '';
                                                $facultyDisplayName =
                                                    trim($rawFacultyName) === '' ? 'Unknown' : $rawFacultyName;
                                                $facultyFilter = strtolower(
                                                    trim($rawFacultyName . ' ' . $facultyEmail),
                                                );
                                            ?>
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="<?php echo e($faculty->id); ?>"
                                                data-option-label="<?php echo e($facultyDisplayName); ?>"
                                                data-option-filter="<?php echo e($facultyFilter); ?>">
                                                <span><?php echo e($facultyDisplayName); ?></span>
                                                <?php if($facultyEmail): ?>
                                                    <small><?php echo e($facultyEmail); ?></small>
                                                <?php endif; ?>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                                <input type="hidden" name="faculty_id" id="editAssignmentFaculty" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editAssignmentCourseToggle">Course</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="editAssignmentCourseToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Course --">-- Select Course --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $courseCodeRaw = $course->class_code ?? '';
                                                $courseSubjectRaw = $course->subject_code ?? '';
                                                $courseCodeDisplay = trim($courseCodeRaw);
                                                $courseSubjectDisplay = trim($courseSubjectRaw);
                                                $courseLabel = $courseCodeDisplay;
                                                if ($courseSubjectDisplay !== '') {
                                                    $courseLabel =
                                                        ($courseLabel !== '' ? $courseLabel . ' - ' : '') .
                                                        $courseSubjectDisplay;
                                                }
                                                $courseFilter = strtolower(
                                                    trim($courseCodeRaw . ' ' . $courseSubjectRaw),
                                                );
                                            ?>
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="<?php echo e($course->id); ?>"
                                                data-option-label="<?php echo e($courseLabel); ?>"
                                                data-option-filter="<?php echo e($courseFilter); ?>">
                                                <span><?php echo e($courseCodeDisplay); ?></span>
                                                <?php if($courseSubjectDisplay !== ''): ?>
                                                    <small><?php echo e($courseSubjectDisplay); ?></small>
                                                <?php endif; ?>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                                <input type="hidden" name="course_id" id="editAssignmentCourse" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editAssignmentSection">Section</label>
                            <div class="user-dropdown w-100" data-user-dropdown data-section-dropdown>
                                <input type="text" class="form-control user-dropdown-input"
                                    id="editAssignmentSection" name="section" placeholder="Section" autocomplete="off"
                                    data-user-name-input required>
                                <div class="dropdown-menu p-2">
                                    <div class="user-dropdown-list" data-user-list>
                                        <?php $__currentLoopData = $sectionOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button type="button" class="dropdown-item" data-user-option
                                                data-user-name="<?php echo e($section); ?>"
                                                data-user-name-lower="<?php echo e(strtolower($section)); ?>"
                                                data-user-email-lower="">
                                                <span><?php echo e($section); ?></span>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editAssignmentYearToggle">Academic Year</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="editAssignmentYearToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Academic Year --">-- Select Academic Year
                                        --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        <?php $__currentLoopData = $academicYearOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="<?php echo e($year); ?>"
                                                data-option-label="<?php echo e($year); ?>"
                                                data-option-filter="<?php echo e(strtolower($year)); ?>">
                                                <span><?php echo e($year); ?></span>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                                <input type="hidden" name="academic_year" id="editAssignmentYear" required>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="editAssignmentSemesterToggle">Semester</label>
                            <div class="dropdown w-100 searchable-dropdown" data-searchable-dropdown>
                                <button
                                    class="searchable-dropdown-toggle w-100 d-flex justify-content-between align-items-center text-start"
                                    type="button" id="editAssignmentSemesterToggle" data-bs-toggle="dropdown"
                                    data-bs-display="static" data-dropdown-toggle>
                                    <span class="searchable-dropdown-label is-placeholder" data-dropdown-label
                                        data-placeholder-text="-- Select Semester --">-- Select Semester --</span>
                                    <i class="bx bx-chevron-down fs-5"></i>
                                </button>
                                <div class="dropdown-menu p-2">
                                    <input type="text" class="form-control searchable-dropdown-search"
                                        placeholder="Search..." data-dropdown-search>
                                    <div class="searchable-dropdown-list" data-dropdown-list>
                                        <?php $__currentLoopData = ['1st' => '1st Semester', '2nd' => '2nd Semester', 'Summer' => 'Summer']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button type="button" class="dropdown-item" data-dropdown-option
                                                data-option-value="<?php echo e($value); ?>"
                                                data-option-label="<?php echo e($label); ?>"
                                                data-option-filter="<?php echo e(strtolower($label)); ?>">
                                                <span><?php echo e($label); ?></span>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                                <input type="hidden" name="semester" id="editAssignmentSemester" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer evaluation-modal-footer">
                        <button type="button" class="btn btn-tertiary evaluation-modal-btn"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-course-primary evaluation-modal-btn"
                            data-default-text="Save Changes" data-loading-text="Saving...">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/data-management/partials/courses/edit-assignment-modal.blade.php ENDPATH**/ ?>