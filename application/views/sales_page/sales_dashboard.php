
    <!-- ===== DASHBOARD PAGE ===== -->
    <section id="dashboard" class="page active">
        <h2>My Tasks</h2>
        <div class="tasks">
            <div class="card">
                <h2><?php echo isset($total_orders_today) ? $total_orders_today : 0; ?></h2>
                <p>Orders Assigned Today</p>
                <span><?php echo isset($needs_approval_count) ? $needs_approval_count : 0; ?> need Approval</span>
                <button onclick="window.location.href='<?php echo base_url('SalesCon/sales_orders'); ?>'">View Orders</button>
            </div>
            <div class="card">
                <h2><?php echo isset($under_review_count) ? $under_review_count : 0; ?></h2>
                <p>Receipts to Review</p>
                <span>Awaiting Verification</span>
                <button onclick="window.location.href='<?php echo base_url('SalesCon/sales_payments'); ?>'">Review Payments</button>
            </div>
            <div class="card warning">
                <h2><span class="circle-badge"><?php echo isset($high_priority_count) ? $high_priority_count : 0; ?></span></h2>
                <p>High Priority Issues</p>
                <span><?php echo isset($issue_category) ? htmlspecialchars($issue_category) : 'No Issues'; ?></span>
                <button onclick="window.location.href='<?php echo base_url('SalesCon/sales_issues'); ?>'">View Issues</button>
            </div>
        </div>

        <div class="dash-tabs">
            <h2>Recent Activities</h2>
        </div>

        <div class="activities-container">
            <div class="activities">
                <table>
                    <thead>
                        <tr class="activity-header">
                            <th>Action</th>
                            <th>Description</th>
                            <th>Role</th>
                            <th>User</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <?php
                                $action = strtolower($activity->Action ?? 'info');
                                $badge_class = 'info';
                                if ($action === 'success') {
                                    $badge_class = 'success';
                                } elseif ($action === 'error') {
                                    $badge_class = 'error';
                                } elseif ($action === 'warning') {
                                    $badge_class = 'warning';
                                }
                                
                                $timestamp = $activity->Timestamp ?? date('Y-m-d H:i:s');
                                $formatted_date = date('m/d/Y', strtotime($timestamp));
                                $formatted_time = date('h:i A', strtotime($timestamp));
                                ?>
                                <tr>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($activity->Action ?? 'Info'); ?></span></td>
                                    <td><?php echo htmlspecialchars($activity->Description ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($activity->Role ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($activity->UserName ?? 'N/A'); ?></td>
                                    <td><?php echo $formatted_date; ?> – <?php echo $formatted_time; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px;">No recent activities found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</main>