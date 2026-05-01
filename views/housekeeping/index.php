<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Housekeeping | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
</head>
<body>
<div class="app-layout">
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once VIEWS_PATH . '/layouts/header.php'; ?>
        <div class="content-area">
            <?php renderFlashMessages(); ?>

            <div class="mb-lg">
                <span class="text-muted text-sm">
                    <?php echo count($tasksByStatus['Pending']); ?> pending &middot;
                    <?php echo count($tasksByStatus['InProgress']); ?> in progress &middot;
                    <?php echo count($tasksByStatus['Completed']); ?> completed
                </span>
            </div>

            <!-- Kanban Board -->
            <div class="kanban-board">
                <!-- Pending Column -->
                <div class="kanban-column" style="border-top: 3px solid var(--warning);">
                    <div class="kanban-column-header">
                        <h4 style="color: var(--warning);">Pending</h4>
                        <span class="kanban-column-count"><?php echo count($tasksByStatus['Pending']); ?></span>
                    </div>
                    <?php foreach ($tasksByStatus['Pending'] as $task): ?>
                        <div class="kanban-card">
                            <div class="kanban-card-room">
                                Room <?php echo sanitize($task['room_number']); ?>
                                <span class="text-muted text-sm">(<?php echo sanitize($task['room_type']); ?>)</span>
                            </div>
                            <div class="kanban-card-task"><?php echo sanitize($task['task_type']); ?></div>
                            <?php if ($task['notes']): ?>
                                <div class="text-xs text-muted mb-sm"><?php echo sanitize($task['notes']); ?></div>
                            <?php endif; ?>
                            <div class="kanban-card-meta">
                                <span class="badge badge-<?php echo strtolower($task['priority']); ?>" style="<?php
                                    echo $task['priority'] === 'High' ? 'background: var(--danger-light); color: var(--danger);' :
                                        ($task['priority'] === 'Medium' ? 'background: var(--warning-light); color: var(--warning);' :
                                        'background: var(--info-light); color: var(--info);');
                                ?>"><?php echo sanitize($task['priority']); ?></span>
                                <form method="POST" action="<?php echo url('housekeeping', ['action' => 'update']); ?>" style="display: inline;">
                                    <?php csrfField(); ?>
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <input type="hidden" name="status" value="InProgress">
                                    <button type="submit" class="btn btn-ghost btn-sm" style="font-size: 0.6875rem;">Start</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($tasksByStatus['Pending'])): ?>
                        <div class="text-center text-muted text-sm p-lg">No pending tasks</div>
                    <?php endif; ?>
                </div>

                <!-- In Progress Column -->
                <div class="kanban-column" style="border-top: 3px solid var(--accent-purple);">
                    <div class="kanban-column-header">
                        <h4 style="color: var(--accent-purple);">In Progress</h4>
                        <span class="kanban-column-count"><?php echo count($tasksByStatus['InProgress']); ?></span>
                    </div>
                    <?php foreach ($tasksByStatus['InProgress'] as $task): ?>
                        <div class="kanban-card">
                            <div class="kanban-card-room">
                                Room <?php echo sanitize($task['room_number']); ?>
                                <span class="text-muted text-sm">(<?php echo sanitize($task['room_type']); ?>)</span>
                            </div>
                            <div class="kanban-card-task"><?php echo sanitize($task['task_type']); ?></div>
                            <?php if ($task['assigned_name']): ?>
                                <div class="text-xs text-muted mb-sm">Assigned: <?php echo sanitize($task['assigned_name']); ?></div>
                            <?php endif; ?>
                            <div class="kanban-card-meta">
                                <span class="badge badge-<?php echo strtolower($task['priority']); ?>" style="<?php
                                    echo $task['priority'] === 'High' ? 'background: var(--danger-light); color: var(--danger);' :
                                        ($task['priority'] === 'Medium' ? 'background: var(--warning-light); color: var(--warning);' :
                                        'background: var(--info-light); color: var(--info);');
                                ?>"><?php echo sanitize($task['priority']); ?></span>
                                <form method="POST" action="<?php echo url('housekeeping', ['action' => 'update']); ?>" style="display: inline;">
                                    <?php csrfField(); ?>
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <input type="hidden" name="status" value="Completed">
                                    <button type="submit" class="btn btn-ghost btn-sm" style="font-size: 0.6875rem; color: var(--success);">Complete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($tasksByStatus['InProgress'])): ?>
                        <div class="text-center text-muted text-sm p-lg">No tasks in progress</div>
                    <?php endif; ?>
                </div>

                <!-- Completed Column -->
                <div class="kanban-column" style="border-top: 3px solid var(--success);">
                    <div class="kanban-column-header">
                        <h4 style="color: var(--success);">Completed</h4>
                        <span class="kanban-column-count"><?php echo count($tasksByStatus['Completed']); ?></span>
                    </div>
                    <?php foreach ($tasksByStatus['Completed'] as $task): ?>
                        <div class="kanban-card" style="opacity: 0.7;">
                            <div class="kanban-card-room">
                                Room <?php echo sanitize($task['room_number']); ?>
                            </div>
                            <div class="kanban-card-task"><?php echo sanitize($task['task_type']); ?></div>
                            <div class="kanban-card-meta">
                                <span class="text-xs"><?php echo $task['completed_at'] ? formatDateTime($task['completed_at']) : ''; ?></span>
                                <?php if (hasRole(ROLE_ADMIN)): ?>
                                <form method="POST" action="<?php echo url('housekeeping', ['action' => 'delete']); ?>" style="display: inline;">
                                    <?php csrfField(); ?>
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm text-danger" style="font-size: 0.6875rem;" data-confirm-delete="Remove this completed task?">Remove</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($tasksByStatus['Completed'])): ?>
                        <div class="text-center text-muted text-sm p-lg">No completed tasks</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <?php require_once VIEWS_PATH . '/layouts/footer.php'; ?>
    </div>
</div>


<script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>
