<?php
/**
 * Security Settings Tab
 */
?>

<div class="tabs-container">
    <div class="tabs-header">
        <div class="tab active">Authentication</div>
        <div class="tab">Password Policy</div>
        <div class="tab">Access Control</div>
    </div>

    <form id="settings-form" method="POST" action="<?php echo BASE_URL; ?>/settings?tab=security">
        <!-- CSRF Token -->
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">

        <!-- Authentication Tab Content -->
        <div class="tab-content active" id="authentication">
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Login Settings</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Allow Self-Registration</div>
                                <div class="settings-item-description">Allow users to register their own accounts</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[allow_self_registration]" value="1" 
                                           <?php echo ($settings['allow_self_registration']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Require Admin Approval</div>
                                <div class="settings-item-description">Require administrator approval for new registrations</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[require_approval]" value="1" 
                                           <?php echo ($settings['require_approval']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Remember Me Option</div>
                                <div class="settings-item-description">Allow users to stay logged in between sessions</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[allow_remember_me]" value="1" 
                                           <?php echo ($settings['allow_remember_me']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Remember Me Duration</div>
                                <div class="settings-item-description">How long to keep users logged in when using "Remember Me"</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[remember_me_days]" style="width: 180px;">
                                    <option value="7" <?php echo ($settings['remember_me_days']['value'] ?? '30') == '7' ? 'selected' : ''; ?>>7 days</option>
                                    <option value="14" <?php echo ($settings['remember_me_days']['value'] ?? '30') == '14' ? 'selected' : ''; ?>>14 days</option>
                                    <option value="30" <?php echo ($settings['remember_me_days']['value'] ?? '30') == '30' ? 'selected' : ''; ?>>30 days</option>
                                    <option value="90" <?php echo ($settings['remember_me_days']['value'] ?? '30') == '90' ? 'selected' : ''; ?>>90 days</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Maximum Login Attempts</div>
                                <div class="settings-item-description">Number of failed login attempts before account lock</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="number" class="form-control" name="settings[max_login_attempts]" 
                                       value="<?php echo htmlspecialchars($settings['max_login_attempts']['value'] ?? '5'); ?>"
                                       min="1" max="20" style="width: 100px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Account Lockout Duration</div>
                                <div class="settings-item-description">Minutes to lock account after failed login attempts</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="number" class="form-control" name="settings[lockout_duration]" 
                                       value="<?php echo htmlspecialchars($settings['lockout_duration']['value'] ?? '30'); ?>"
                                       min="1" style="width: 100px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Password Reset</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Allow Password Reset</div>
                                <div class="settings-item-description">Allow users to reset their password via email</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[allow_password_reset]" value="1" 
                                           <?php echo ($settings['allow_password_reset']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Password Reset Link Expiry</div>
                                <div class="settings-item-description">Hours until password reset link expires</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="number" class="form-control" name="settings[password_reset_expiry]" 
                                       value="<?php echo htmlspecialchars($settings['password_reset_expiry']['value'] ?? '24'); ?>"
                                       min="1" max="168" style="width: 100px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Send Admin Notification</div>
                                <div class="settings-item-description">Notify admin when password reset is requested</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[notify_admin_password_reset]" value="1" 
                                           <?php echo ($settings['notify_admin_password_reset']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Policy Tab Content -->
        <div class="tab-content" id="password-policy">
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Password Requirements</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Minimum Password Length</div>
                                <div class="settings-item-description">Minimum number of characters required for passwords</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="number" class="form-control" name="settings[min_password_length]" 
                                       value="<?php echo htmlspecialchars($settings['min_password_length']['value'] ?? '8'); ?>"
                                       min="6" max="30" style="width: 100px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Require Uppercase</div>
                                <div class="settings-item-description">Require at least one uppercase letter</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[require_uppercase]" value="1" 
                                           <?php echo ($settings['require_uppercase']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Require Lowercase</div>
                                <div class="settings-item-description">Require at least one lowercase letter</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[require_lowercase]" value="1" 
                                           <?php echo ($settings['require_lowercase']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Require Numbers</div>
                                <div class="settings-item-description">Require at least one number</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[require_numbers]" value="1" 
                                           <?php echo ($settings['require_numbers']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Require Special Characters</div>
                                <div class="settings-item-description">Require at least one special character (!@#$%^&*)</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[require_special]" value="1" 
                                           <?php echo ($settings['require_special']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Password Expiration</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Password Expiration</div>
                                <div class="settings-item-description">Force users to change password periodically</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_password_expiration]" value="1" 
                                           <?php echo ($settings['enable_password_expiration']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Password Expiry Days</div>
                                <div class="settings-item-description">Number of days until passwords expire</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="number" class="form-control" name="settings[password_expiry_days]" 
                                       value="<?php echo htmlspecialchars($settings['password_expiry_days']['value'] ?? '90'); ?>"
                                       min="30" max="365" style="width: 100px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Prevent Password Reuse</div>
                                <div class="settings-item-description">Prevent reusing recently used passwords</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[prevent_password_reuse]" value="1" 
                                           <?php echo ($settings['prevent_password_reuse']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Password History Count</div>
                                <div class="settings-item-description">Number of previous passwords to remember</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="number" class="form-control" name="settings[password_history_count]" 
                                       value="<?php echo htmlspecialchars($settings['password_history_count']['value'] ?? '5'); ?>"
                                       min="1" max="20" style="width: 100px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Control Tab Content -->
        <div class="tab-content" id="access-control">
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">User Permissions</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Default User Role</div>
                                <div class="settings-item-description">Default role assigned to new users</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[default_user_role]" style="width: 180px;">
                                    <option value="user" <?php echo ($settings['default_user_role']['value'] ?? 'user') == 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="manager" <?php echo ($settings['default_user_role']['value'] ?? 'user') == 'manager' ? 'selected' : ''; ?>>Manager</option>
                                    <option value="admin" <?php echo ($settings['default_user_role']['value'] ?? 'user') == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">User-Specific Permissions</div>
                                <div class="settings-item-description">Allow user-specific permission overrides</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_user_permissions]" value="1" 
                                           <?php echo ($settings['enable_user_permissions']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Department-Based Access</div>
                                <div class="settings-item-description">Restrict access to items based on department</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[department_based_access]" value="1" 
                                           <?php echo ($settings['department_based_access']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Activity Tracking</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Activity Logging</div>
                                <div class="settings-item-description">Track user activity in the system</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[activity_logging]" value="1" 
                                           <?php echo ($settings['activity_logging']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Log User IP Address</div>
                                <div class="settings-item-description">Record IP addresses in activity logs</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[log_ip_address]" value="1" 
                                           <?php echo ($settings['log_ip_address']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Log User Agent</div>
                                <div class="settings-item-description">Record browser and device information</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[log_user_agent]" value="1" 
                                           <?php echo ($settings['log_user_agent']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Log Failed Login Attempts</div>
                                <div class="settings-item-description">Track unsuccessful login attempts</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                <input type="checkbox" name="settings[log_failed_logins]" value="1" 
                                           <?php echo ($settings['log_failed_logins']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Activity Log Retention</div>
                                <div class="settings-item-description">Days to keep activity logs before archiving</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[activity_log_retention]" style="width: 180px;">
                                    <option value="30" <?php echo ($settings['activity_log_retention']['value'] ?? '90') == '30' ? 'selected' : ''; ?>>30 days</option>
                                    <option value="90" <?php echo ($settings['activity_log_retention']['value'] ?? '90') == '90' ? 'selected' : ''; ?>>90 days</option>
                                    <option value="180" <?php echo ($settings['activity_log_retention']['value'] ?? '90') == '180' ? 'selected' : ''; ?>>180 days</option>
                                    <option value="365" <?php echo ($settings['activity_log_retention']['value'] ?? '90') == '365' ? 'selected' : ''; ?>>365 days</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Security Measures</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">CSRF Protection</div>
                                <div class="settings-item-description">Enable Cross-Site Request Forgery protection</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[csrf_protection]" value="1" 
                                           <?php echo ($settings['csrf_protection']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">XSS Protection</div>
                                <div class="settings-item-description">Enable Cross-Site Scripting protection</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[xss_protection]" value="1" 
                                           <?php echo ($settings['xss_protection']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">SQL Injection Protection</div>
                                <div class="settings-item-description">Enable SQL injection protection</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[sql_injection_protection]" value="1" 
                                           <?php echo ($settings['sql_injection_protection']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">IP Blacklisting</div>
                                <div class="settings-item-description">Block access from suspicious IP addresses</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[ip_blacklisting]" value="1" 
                                           <?php echo ($settings['ip_blacklisting']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Secure Cookies</div>
                                <div class="settings-item-description">Use secure, HTTP-only cookies</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[secure_cookies]" value="1" 
                                           <?php echo ($settings['secure_cookies']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
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
    });
</script>