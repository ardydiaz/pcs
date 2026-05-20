<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Faculty Name</th>
                <th>Email</th>
                <th>Job Title</th>
                <th class="text-center">Evaluations</th>
                <th class="text-center">Responses</th>
                <th class="text-center">Avg Rating</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $faculties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faculty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial bg-primary rounded">
                                    <?php echo e(substr($faculty->name, 0, 2)); ?>

                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0"><?php echo e($faculty->name); ?></h6>
                            </div>
                        </div>
                    </td>
                    <td><?php echo e($faculty->email ?? 'N/A'); ?></td>
                    <td><?php echo e($faculty->job_title ?? 'N/A'); ?></td>
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
                        <span class="badge bg-<?php echo e($faculty->status == 'active' ? 'success' : 'secondary'); ?>">
                            <?php echo e(ucfirst($faculty->status ?? 'active')); ?>

                        </span>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="bx bx-user-x text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">No faculty members found</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div><?php /**PATH C:\laragon\www\post-class-survey\resources\views/content/dashboard/partials/faculty-modal-table.blade.php ENDPATH**/ ?>