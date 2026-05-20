<div class="table-responsive">
    <table class="table table-hover faculty-modal-table" id="facultyTable">
        <thead>
            <tr>
                <th data-sort-key="name" class="sortable" data-sort-state="none">
                    <span class="faculty-sort-wrapper">
                        <span class="faculty-sort-label">Faculty Name</span>
                        <span class="faculty-sort-indicator">
                            <i class="bx bx-chevron-up icon-up"></i>
                            <i class="bx bx-chevron-down icon-down"></i>
                        </span>
                    </span>
                </th>
                <th>Email</th>
                <th>Job Title</th>
                <th class="text-center sortable" data-sort-key="evaluations" data-sort-state="none">
                    <span class="faculty-sort-wrapper">
                        <span class="faculty-sort-label">Evaluations</span>
                        <span class="faculty-sort-indicator">
                            <i class="bx bx-chevron-up icon-up"></i>
                            <i class="bx bx-chevron-down icon-down"></i>
                        </span>
                    </span>
                </th>
                <th class="text-center sortable" data-sort-key="responses" data-sort-state="none">
                    <span class="faculty-sort-wrapper">
                        <span class="faculty-sort-label">Responses</span>
                        <span class="faculty-sort-indicator">
                            <i class="bx bx-chevron-up icon-up"></i>
                            <i class="bx bx-chevron-down icon-down"></i>
                        </span>
                    </span>
                </th>
                <th class="text-center sortable" data-sort-key="avg_rating" data-sort-state="none">
                    <span class="faculty-sort-wrapper">
                        <span class="faculty-sort-label">Avg Rating</span>
                        <span class="faculty-sort-indicator">
                            <i class="bx bx-chevron-up icon-up"></i>
                            <i class="bx bx-chevron-down icon-down"></i>
                        </span>
                    </span>
                </th>
                <th class="text-center sortable" data-sort-key="status" data-sort-state="none">
                    <span class="faculty-sort-wrapper">
                        <span class="faculty-sort-label">Status</span>
                        <span class="faculty-sort-indicator">
                            <i class="bx bx-chevron-up icon-up"></i>
                            <i class="bx bx-chevron-down icon-down"></i>
                        </span>
                    </span>
                </th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $faculties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $facultyName = data_get($faculty, 'name', 'Unknown');
                    $facultyEmail = data_get($faculty, 'email');
                    $facultyJobTitle = data_get($faculty, 'job_title');
                    $facultyStatus = data_get($faculty, 'status', 'active');
                ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-primary rounded">
                                    <?php echo e(substr($facultyName, 0, 2)); ?>

                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0"><?php echo e($facultyName); ?></h6>
                            </div>
                        </div>
                    </td>
                    <td><?php echo e($facultyEmail ?? 'N/A'); ?></td>
                    <td><?php echo e($facultyJobTitle ?? 'N/A'); ?></td>
                    <td class="text-center">
                        <span class="badge bg-info"><?php echo e($faculty->evaluation_count); ?></span>
                        <br>
                        <small class="text-success"><?php echo e($faculty->active_evaluations); ?> active</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success"><?php echo e($faculty->response_count); ?></span>
                    </td>
                    <td class="text-center">
                        <?php if($faculty->average_rating > 0): ?>
                            <span class="fw-medium"><?php echo e($faculty->average_rating); ?>/4.0</span>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-<?php echo e($faculty->average_rating >= 3 ? 'success' : ($faculty->average_rating >= 2 ? 'warning' : 'danger')); ?>"
                                    style="width: <?php echo e(($faculty->average_rating / 4) * 100); ?>%"></div>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">No ratings</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-<?php echo e($facultyStatus === 'active' ? 'success' : 'secondary'); ?>">
                            <?php echo e(ucfirst($facultyStatus)); ?>

                        </span>
                    </td>
                    <td class="text-center">
                        <?php if(!empty($faculty->evaluation_id)): ?>
                            <a class="btn btn-sm btn-outline-primary"
                                href="<?php echo e(route('dm.evaluation.responses', $faculty->evaluation_id)); ?>?from=reports">
                                <i class="bx bx-show me-1"></i>View Responses
                            </a>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="bx bx-user-x text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No faculty members found</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\laragon\www\postclasssurvey\resources\views/content/dashboard/partials/faculty-modal-table.blade.php ENDPATH**/ ?>