

<?php $__env->startSection('title', 'Evaluation Responses'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container py-4">
        
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Evaluation Responses</h4>
                        <p class="text-muted mb-0">
                            Faculty: <strong><?php echo e($evaluation->faculty->name); ?></strong> |
                            Academic Year: <strong><?php echo e($evaluation->academic_year); ?></strong> |
                            Semester: <strong><?php echo e($evaluation->semester); ?></strong>
                        </p>
                    </div>
                    <a href="<?php echo e(route('dm.evaluation')); ?>" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Evaluations
                    </a>
                </div>
            </div>
        </div>

        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?php echo e($responses->count()); ?></h3>
                        <p class="text-muted mb-0">Total Responses</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success">
                            <?php echo e($responses->count() > 0 ? number_format($responses->avg('effectiveness_rating'), 1) : '0.0'); ?>

                        </h3>
                        <p class="text-muted mb-0">Average Rating</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info"><?php echo e($responses->unique('schedule_id')->count()); ?></h3>
                        <p class="text-muted mb-0">Courses Evaluated</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning">
                            <?php echo e($responses->whereNotNull('feedback_comments')->where('feedback_comments', '!=', '')->count()); ?>

                        </h3>
                        <p class="text-muted mb-0">With Feedback</p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Rating Distribution</h5>
            </div>
            <div class="card-body">
                <?php
                    $ratingCounts = $responses->groupBy('effectiveness_rating')->map->count();
                    $totalResponses = $responses->count();
                ?>

                <?php if($totalResponses > 0): ?>
                    <div class="row">
                        <?php $__currentLoopData = ['4' => 'Very Effective', '3' => 'Effective', '2' => 'Somewhat Effective', '1' => 'Not Effective']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $count = $ratingCounts->get($rating, 0);
                                $percentage = ($count / $totalResponses) * 100;
                            ?>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><?php echo e($label); ?></span>
                                        <span><?php echo e($count); ?> (<?php echo e(number_format($percentage, 1)); ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar 
                                                                            <?php if($rating == '4'): ?> bg-success 
                                                                            <?php elseif($rating == '3'): ?> bg-info 
                                                                            <?php elseif($rating == '2'): ?> bg-warning 
                                                                            <?php else: ?> bg-danger <?php endif; ?>"
                                            style="width: <?php echo e($percentage); ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bx bx-bar-chart display-4 mb-3"></i>
                        <p>No responses to display distribution</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Anonymous Responses</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Course</th>
                                <th>Rating</th>
                                <th>Feedback</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $responses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $response): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td>
                                        <div>
                                            <strong><?php echo e($response->schedule->facultyCourse->course->class_code ?? 'N/A'); ?></strong>
                                            <br>
                                            <small
                                                class="text-muted"><?php echo e($response->schedule->facultyCourse->course->subject_code ?? 'N/A'); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge 
                                                                <?php if($response->effectiveness_rating == '4'): ?> bg-success 
                                                                <?php elseif($response->effectiveness_rating == '3'): ?> bg-info 
                                                                <?php elseif($response->effectiveness_rating == '2'): ?> bg-warning 
                                                                <?php else: ?> bg-danger <?php endif; ?>">
                                            <?php echo e($response->effectiveness_text); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($response->feedback_comments): ?>
                                            <div class="text-truncate" style="max-width: 400px;"
                                                title="<?php echo e($response->feedback_comments); ?>">
                                                <?php echo e($response->feedback_comments); ?>

                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No feedback provided</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo e($response->created_at->format('M d, Y H:i')); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="bx bx-bar-chart display-4 text-muted mb-3"></i>
                                            <h5 class="mb-2">No responses yet</h5>
                                            <p class="text-muted mb-0">Students haven't submitted any evaluations yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts/contentNavbarLayout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\faculty-evaluation\resources\views/content/data-management/evaluation-files/evaluation-responses.blade.php ENDPATH**/ ?>