<?php
/**
 * Notifications Settings Tab
 */
?>

<div class="tabs-container">
    <div class="tabs-header">
        <div class="tab active">Email Notifications</div>
        <div class="tab">System Notifications</div>
        <div class="tab">Alert Settings</div>
    </div>

    <form id="settings-form" method="POST" action="<?php echo BASE_URL; ?>/settings?tab=notifications">
        <!-- CSRF Token -->
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">

        <!-- Email Notifications Tab Content -->
        <div class="tab-content active" id="email-notifications">
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Email Settings</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Enable Email Notifications</div>
                                <div class="settings-item-description">Send email notifications to users</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_email_notifications]" value="1" 
                                           <?php echo ($settings['enable_email_notifications']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">From Email Address</div>
                                <div class="settings-item-description">Email address used as sender for notifications</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="email" class="form-control" name="settings[from_email]" 
                                       value="<?php echo htmlspecialchars($settings['from_email']['value'] ?? 'noreply@example.com'); ?>"
                                       style="width: 280px;">
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">From Name</div>
                                <div class="settings-item-description">Name displayed as sender for notifications</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="text" class="form-control" name="settings[from_name]" 
                                       value="<?php echo htmlspecialchars($settings['from_name']['value'] ?? 'Inventory System'); ?>"
                                       style="width: 280px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">SMTP Server</div>
                                <div class="settings-item-description">SMTP server hostname for sending emails</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="text" class="form-control" name="settings[smtp_host]" 
                                       value="<?php echo htmlspecialchars($settings['smtp_host']['value'] ?? 'smtp.example.com'); ?>"
                                       style="width: 280px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">SMTP Port</div>
                                <div class="settings-item-description">SMTP server port</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="number" class="form-control" name="settings[smtp_port]" 
                                       value="<?php echo htmlspecialchars($settings['smtp_port']['value'] ?? '587'); ?>"
                                       style="width: 100px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">SMTP Security</div>
                                <div class="settings-item-description">SMTP connection security</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[smtp_security]" style="width: 180px;">
                                    <option value="none" <?php echo ($settings['smtp_security']['value'] ?? 'tls') == 'none' ? 'selected' : ''; ?>>None</option>
                                    <option value="tls" <?php echo ($settings['smtp_security']['value'] ?? 'tls') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                    <option value="ssl" <?php echo ($settings['smtp_security']['value'] ?? 'tls') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">SMTP Authentication</div>
                                <div class="settings-item-description">Use authentication for SMTP server</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[smtp_auth]" value="1" 
                                           <?php echo ($settings['smtp_auth']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">SMTP Username</div>
                                <div class="settings-item-description">Username for SMTP authentication</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="text" class="form-control" name="settings[smtp_username]" 
                                       value="<?php echo htmlspecialchars($settings['smtp_username']['value'] ?? ''); ?>"
                                       style="width: 280px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">SMTP Password</div>
                                <div class="settings-item-description">Password for SMTP authentication</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="password" class="form-control" name="settings[smtp_password]" 
                                       value="<?php echo htmlspecialchars($settings['smtp_password']['value'] ?? ''); ?>"
                                       style="width: 280px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Test Email Configuration</div>
                                <div class="settings-item-description">Send a test email to verify settings</div>
                            </div>
                            <div class="settings-item-control">
                                <button type="button" class="btn btn-outline" id="test-email-btn">Send Test Email</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Email Notification Events</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">User Registration</div>
                                <div class="settings-item-description">Send email when new user registers</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[email_user_registration]" value="1" 
                                           <?php echo ($settings['email_user_registration']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Account Approval</div>
                                <div class="settings-item-description">Send email when user account is approved</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[email_account_approval]" value="1" 
                                           <?php echo ($settings['email_account_approval']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Password Reset</div>
                                <div class="settings-item-description">Send email for password reset requests</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[email_password_reset]" value="1" 
                                           <?php echo ($settings['email_password_reset']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Borrow Request</div>
                                <div class="settings-item-description">Send email when new borrow request is created</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[email_borrow_request]" value="1" 
                                           <?php echo ($settings['email_borrow_request']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Borrow Approval</div>
                                <div class="settings-item-description">Send email when borrow request is approved/rejected</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[email_borrow_approval]" value="1" 
                                           <?php echo ($settings['email_borrow_approval']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Due Date Reminder</div>
                                <div class="settings-item-description">Send email reminder before items are due</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[email_due_date_reminder]" value="1" 
                                           <?php echo ($settings['email_due_date_reminder']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Overdue Items</div>
                                <div class="settings-item-description">Send email when items become overdue</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[email_overdue_items]" value="1" 
                                           <?php echo ($settings['email_overdue_items']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Maintenance Alerts</div>
                                <div class="settings-item-description">Send email for scheduled maintenance</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[email_maintenance_alerts]" value="1" 
                                           <?php echo ($settings['email_maintenance_alerts']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Notifications Tab Content -->
        <div class="tab-content" id="system-notifications">
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">In-App Notifications</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Enable In-App Notifications</div>
                                <div class="settings-item-description">Show notifications within the application</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_in_app_notifications]" value="1" 
                                           <?php echo ($settings['enable_in_app_notifications']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Real-time Notifications</div>
                                <div class="settings-item-description">Show notifications in real-time</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[realtime_notifications]" value="1" 
                                           <?php echo ($settings['realtime_notifications']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Notification Sound</div>
                                <div class="settings-item-description">Play sound for new notifications</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[notification_sound]" value="1" 
                                           <?php echo ($settings['notification_sound']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Auto-dismiss Notifications</div>
                                <div class="settings-item-description">Automatically hide notifications after a delay</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[auto_dismiss_notifications]" value="1" 
                                           <?php echo ($settings['auto_dismiss_notifications']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Auto-dismiss Delay</div>
                                <div class="settings-item-description">Seconds before notifications are dismissed</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="number" class="form-control" name="settings[notification_dismiss_delay]" 
                                       value="<?php echo htmlspecialchars($settings['notification_dismiss_delay']['value'] ?? '5'); ?>"
                                       min="1" max="30" style="width: 100px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Notification History</div>
                                <div class="settings-item-description">Number of days to keep notification history</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="number" class="form-control" name="settings[notification_history_days]" 
                                       value="<?php echo htmlspecialchars($settings['notification_history_days']['value'] ?? '30'); ?>"
                                       min="1" max="365" style="width: 100px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Notification Events</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Borrow Requests</div>
                                <div class="settings-item-description">Show notifications for borrow requests</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[notify_borrow_requests]" value="1" 
                                           <?php echo ($settings['notify_borrow_requests']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Borrow Approvals</div>
                                <div class="settings-item-description">Show notifications for request approvals</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[notify_borrow_approvals]" value="1" 
                                           <?php echo ($settings['notify_borrow_approvals']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Due Date Reminders</div>
                                <div class="settings-item-description">Show notifications for upcoming due dates</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[notify_due_dates]" value="1" 
                                           <?php echo ($settings['notify_due_dates']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Item Returns</div>
                                <div class="settings-item-description">Show notifications for returned items</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[notify_item_returns]" value="1" 
                                           <?php echo ($settings['notify_item_returns']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Maintenance Notifications</div>
                                <div class="settings-item-description">Show notifications for maintenance tasks</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[notify_maintenance]" value="1" 
                                           <?php echo ($settings['notify_maintenance']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">User Registrations</div>
                                <div class="settings-item-description">Show notifications for new user registrations</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[notify_user_registrations]" value="1" 
                                           <?php echo ($settings['notify_user_registrations']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Settings Tab Content -->
        <div class="tab-content" id="alert-settings">
            <div class="settings-panel">
                <div class="settings-panel-header">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">System Alerts</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Low Stock Alerts</div>
                                <div class="settings-item-description">Enable alerts for low inventory levels</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_low_stock_alerts]" value="1" 
                                           <?php echo ($settings['enable_low_stock_alerts']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Overdue Item Alerts</div>
                                <div class="settings-item-description">Enable alerts for overdue items</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_overdue_alerts]" value="1" 
                                           <?php echo ($settings['enable_overdue_alerts']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Maintenance Due Alerts</div>
                                <div class="settings-item-description">Enable alerts for upcoming maintenance</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_maintenance_alerts]" value="1" 
                                           <?php echo ($settings['enable_maintenance_alerts']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">System Error Alerts</div>
                                <div class="settings-item-description">Enable alerts for system errors</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_error_alerts]" value="1" 
                                           <?php echo ($settings['enable_error_alerts']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Security Alerts</div>
                                <div class="settings-item-description">Enable alerts for security events</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_security_alerts]" value="1" 
                                           <?php echo ($settings['enable_security_alerts']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Alert Recipients</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Alert Administrator</div>
                                <div class="settings-item-description">Send alerts to system administrators</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[alert_administrators]" value="1" 
                                           <?php echo ($settings['alert_administrators']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Alert Department Managers</div>
                                <div class="settings-item-description">Send alerts to department managers</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[alert_department_managers]" value="1" 
                                           <?php echo ($settings['alert_department_managers']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Alert Item Owners</div>
                                <div class="settings-item-description">Send alerts to users assigned as item owners</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[alert_item_owners]" value="1" 
                                           <?php echo ($settings['alert_item_owners']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Additional Alert Email</div>
                                <div class="settings-item-description">Extra email address to receive all alerts</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="email" class="form-control" name="settings[additional_alert_email]" 
                                       value="<?php echo htmlspecialchars($settings['additional_alert_email']['value'] ?? ''); ?>"
                                       placeholder="alerts@example.com"
                                       style="width: 280px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Alert Delivery Methods</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Email Alerts</div>
                                <div class="settings-item-description">Send alerts via email</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[email_alerts]" value="1" 
                                           <?php echo ($settings['email_alerts']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">In-App Alerts</div>
                                <div class="settings-item-description">Show alerts in the application</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[in_app_alerts]" value="1" 
                                           <?php echo ($settings['in_app_alerts']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">SMS Alerts</div>
                                <div class="settings-item-description">Send critical alerts via SMS</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[sms_alerts]" value="1" 
                                           <?php echo ($settings['sms_alerts']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Dashboard Alerts</div>
                                <div class="settings-item-description">Show alerts on dashboard</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[dashboard_alerts]" value="1" 
                                           <?php echo ($settings['dashboard_alerts']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Alert Digest</div>
                                <div class="settings-item-description">Send daily or weekly alert digest instead of individual alerts</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[alert_digest]" style="width: 180px;">
                                    <option value="none" <?php echo ($settings['alert_digest']['value'] ?? 'none') == 'none' ? 'selected' : ''; ?>>No Digest</option>
                                    <option value="daily" <?php echo ($settings['alert_digest']['value'] ?? 'none') == 'daily' ? 'selected' : ''; ?>>Daily Digest</option>
                                    <option value="weekly" <?php echo ($settings['alert_digest']['value'] ?? 'none') == 'weekly' ? 'selected' : ''; ?>>Weekly Digest</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tabs
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                tab.classList.add('active');
                tabContents[index].classList.add('active');
            });
        });
        
        // Test Email button functionality
        const testEmailBtn = document.getElementById('test-email-btn');
        if (testEmailBtn) {
            testEmailBtn.addEventListener('click', function() {
                const testEmail = prompt('Enter email address to send test message:');
                if (!testEmail) return;
                
                if (!isValidEmail(testEmail)) {
                    alert('Please enter a valid email address.');
                    return;
                }
                
                // Show loading state
                testEmailBtn.disabled = true;
                testEmailBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                
                fetch('<?php echo BASE_URL; ?>/api/settings/test-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>'
                    },
                    body: JSON.stringify({
                        email: testEmail,
                        smtp_host: document.querySelector('input[name="settings[smtp_host]"]').value,
                        smtp_port: document.querySelector('input[name="settings[smtp_port]"]').value,
                        smtp_security: document.querySelector('select[name="settings[smtp_security]"]').value,
                        smtp_auth: document.querySelector('input[name="settings[smtp_auth]"]').checked ? 1 : 0,
                        smtp_username: document.querySelector('input[name="settings[smtp_username]"]').value,
                        smtp_password: document.querySelector('input[name="settings[smtp_password]"]').value,
                        from_email: document.querySelector('input[name="settings[from_email]"]').value,
                        from_name: document.querySelector('input[name="settings[from_name]"]').value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Reset button state
                    testEmailBtn.disabled = false;
                    testEmailBtn.innerHTML = 'Send Test Email';
                    
                    // Show response message
                    if (data.success) {
                        alert('Test email sent successfully! Please check your inbox.');
                    } else {
                        alert('Failed to send test email: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    testEmailBtn.disabled = false;
                    testEmailBtn.innerHTML = 'Send Test Email';
                    alert('An error occurred while sending the test email. Please try again.');
                });
            });
        }
        
        // Helper function to validate email
        function isValidEmail(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        }
    });
</script>