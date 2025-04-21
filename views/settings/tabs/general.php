<?php
/**
 * General Settings Tab
 */
?>

<div class="tabs-container">
    <div class="tabs-header">
        <div class="tab active">System Preferences</div>
        <div class="tab">Regional Settings</div>
        <div class="tab">Interface Options</div>
    </div>

    <form id="settings-form" method="POST" action="<?php echo BASE_URL; ?>/settings?tab=general">
        <!-- CSRF Token -->
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">

        <!-- System Preferences Tab Content -->
        <div class="tab-content active" id="system-preferences">
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">General System Settings</div>
                    <div>
                        <span class="badge badge-blue">System Default</span>
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">System Name</div>
                                <div class="settings-item-description">Name displayed in browser tabs and system communications</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="text" class="form-control" name="settings[site_name]" 
                                       value="<?php echo htmlspecialchars($settings['site_name']['value'] ?? 'Inventory Pro'); ?>"
                                       style="width: 220px;">
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Company Name</div>
                                <div class="settings-item-description">Your company or organization name used in emails and reports</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="text" class="form-control" name="settings[company_name]"
                                       value="<?php echo htmlspecialchars($settings['company_name']['value'] ?? 'Your Company'); ?>"
                                       style="width: 280px;">
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Company Email</div>
                                <div class="settings-item-description">Default email address for system notifications</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="email" class="form-control" name="settings[company_email]"
                                       value="<?php echo htmlspecialchars($settings['company_email']['value'] ?? 'admin@example.com'); ?>"
                                       style="width: 280px;">
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Navigation Sidebar</div>
                                <div class="settings-item-description">Default sidebar state when users log in</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[default_sidebar_state]" style="width: 180px;">
                                    <option value="expanded" <?php echo ($settings['default_sidebar_state']['value'] ?? 'expanded') === 'expanded' ? 'selected' : ''; ?>>Expanded</option>
                                    <option value="collapsed" <?php echo ($settings['default_sidebar_state']['value'] ?? '') === 'collapsed' ? 'selected' : ''; ?>>Collapsed</option>
                                    <option value="remember" <?php echo ($settings['default_sidebar_state']['value'] ?? '') === 'remember' ? 'selected' : ''; ?>>Remember last state</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Session Timeout</div>
                                <div class="settings-item-description">Automatically log out inactive users after this period</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[session_timeout]" style="width: 180px;">
                                    <option value="15" <?php echo ($settings['session_timeout']['value'] ?? '30') == '15' ? 'selected' : ''; ?>>15 minutes</option>
                                    <option value="30" <?php echo ($settings['session_timeout']['value'] ?? '30') == '30' ? 'selected' : ''; ?>>30 minutes</option>
                                    <option value="60" <?php echo ($settings['session_timeout']['value'] ?? '30') == '60' ? 'selected' : ''; ?>>1 hour</option>
                                    <option value="120" <?php echo ($settings['session_timeout']['value'] ?? '30') == '120' ? 'selected' : ''; ?>>2 hours</option>
                                    <option value="240" <?php echo ($settings['session_timeout']['value'] ?? '30') == '240' ? 'selected' : ''; ?>>4 hours</option>
                                    <option value="480" <?php echo ($settings['session_timeout']['value'] ?? '30') == '480' ? 'selected' : ''; ?>>8 hours</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Dashboard Auto-Refresh</div>
                                <div class="settings-item-description">Automatically refresh dashboard data at regular intervals</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[dashboard_refresh]" value="1" 
                                           <?php echo ($settings['dashboard_refresh']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Refresh Interval</div>
                                <div class="settings-item-description">How often the dashboard will refresh automatically</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[dashboard_refresh_interval]" style="width: 180px;">
                                    <option value="30" <?php echo ($settings['dashboard_refresh_interval']['value'] ?? '60') == '30' ? 'selected' : ''; ?>>30 seconds</option>
                                    <option value="60" <?php echo ($settings['dashboard_refresh_interval']['value'] ?? '60') == '60' ? 'selected' : ''; ?>>1 minute</option>
                                    <option value="300" <?php echo ($settings['dashboard_refresh_interval']['value'] ?? '60') == '300' ? 'selected' : ''; ?>>5 minutes</option>
                                    <option value="600" <?php echo ($settings['dashboard_refresh_interval']['value'] ?? '60') == '600' ? 'selected' : ''; ?>>10 minutes</option>
                                    <option value="1800" <?php echo ($settings['dashboard_refresh_interval']['value'] ?? '60') == '1800' ? 'selected' : ''; ?>>30 minutes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Usage Analytics</div>
                    <div>
                        <span class="badge badge-green">Enabled</span>
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Collect Anonymous Usage Data</div>
                                <div class="settings-item-description">Helps us improve the system by collecting anonymous usage statistics</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[collect_usage_data]" value="1" 
                                           <?php echo ($settings['collect_usage_data']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Feature Usage Tracking</div>
                                <div class="settings-item-description">Track which features are most commonly used to help prioritize improvements</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[track_feature_usage]" value="1" 
                                           <?php echo ($settings['track_feature_usage']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Error Reporting</div>
                                <div class="settings-item-description">Automatically report errors to help us improve stability</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[error_reporting]" value="1" 
                                           <?php echo ($settings['error_reporting']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Usage Reports</div>
                                <div class="settings-item-description">Receive periodic reports about your system usage and optimization suggestions</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[usage_reports_frequency]" style="width: 180px;">
                                    <option value="never" <?php echo ($settings['usage_reports_frequency']['value'] ?? 'monthly') == 'never' ? 'selected' : ''; ?>>Never</option>
                                    <option value="monthly" <?php echo ($settings['usage_reports_frequency']['value'] ?? 'monthly') == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                    <option value="quarterly" <?php echo ($settings['usage_reports_frequency']['value'] ?? 'monthly') == 'quarterly' ? 'selected' : ''; ?>>Quarterly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">System Maintenance</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Automatic Updates</div>
                                <div class="settings-item-description">Automatically install minor updates and security patches</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[automatic_updates]" value="1" 
                                           <?php echo ($settings['automatic_updates']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Maintenance Window</div>
                                <div class="settings-item-description">Schedule when system maintenance should occur</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[maintenance_window]" style="width: 180px;">
                                    <option value="anytime" <?php echo ($settings['maintenance_window']['value'] ?? 'night') == 'anytime' ? 'selected' : ''; ?>>Anytime</option>
                                    <option value="night" <?php echo ($settings['maintenance_window']['value'] ?? 'night') == 'night' ? 'selected' : ''; ?>>Overnight (12am-5am)</option>
                                    <option value="weekend" <?php echo ($settings['maintenance_window']['value'] ?? 'night') == 'weekend' ? 'selected' : ''; ?>>Weekends</option>
                                    <option value="custom" <?php echo ($settings['maintenance_window']['value'] ?? 'night') == 'custom' ? 'selected' : ''; ?>>Custom Schedule</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Database Optimization</div>
                                <div class="settings-item-description">Automatically optimize database performance</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[db_optimization_frequency]" style="width: 180px;">
                                    <option value="daily" <?php echo ($settings['db_optimization_frequency']['value'] ?? 'weekly') == 'daily' ? 'selected' : ''; ?>>Daily</option>
                                    <option value="weekly" <?php echo ($settings['db_optimization_frequency']['value'] ?? 'weekly') == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                    <option value="monthly" <?php echo ($settings['db_optimization_frequency']['value'] ?? 'weekly') == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                    <option value="never" <?php echo ($settings['db_optimization_frequency']['value'] ?? 'weekly') == 'never' ? 'selected' : ''; ?>>Never</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">System Logs</div>
                                <div class="settings-item-description">How long to keep system logs before archiving</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[log_retention_days]" style="width: 180px;">
                                    <option value="7" <?php echo ($settings['log_retention_days']['value'] ?? '30') == '7' ? 'selected' : ''; ?>>7 days</option>
                                    <option value="30" <?php echo ($settings['log_retention_days']['value'] ?? '30') == '30' ? 'selected' : ''; ?>>30 days</option>
                                    <option value="90" <?php echo ($settings['log_retention_days']['value'] ?? '30') == '90' ? 'selected' : ''; ?>>90 days</option>
                                    <option value="180" <?php echo ($settings['log_retention_days']['value'] ?? '30') == '180' ? 'selected' : ''; ?>>6 months</option>
                                    <option value="365" <?php echo ($settings['log_retention_days']['value'] ?? '30') == '365' ? 'selected' : ''; ?>>1 year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Advanced Settings</div>
                    <div>
                        <span class="badge badge-red">Admin Only</span>
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Debug Mode</div>
                                <div class="settings-item-description">Enable detailed logging for system troubleshooting</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[debug_mode]" value="1" 
                                           <?php echo ($settings['debug_mode']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">API Access</div>
                                <div class="settings-item-description">Allow external applications to access the system API</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_api]" value="1" 
                                           <?php echo ($settings['enable_api']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">System Cache</div>
                                <div class="settings-item-description">Clear system cache to resolve display issues</div>
                            </div>
                            <div class="settings-item-control">
                                <button type="button" class="btn btn-outline" id="clear-cache-btn">Clear Cache</button>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">System Reset</div>
                                <div class="settings-item-description">Reset all system settings to their default values</div>
                            </div>
                            <div class="settings-item-control">
                                <button type="button" class="btn btn-danger" id="reset-settings-btn">Reset Defaults</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regional Settings Tab Content -->
        <div class="tab-content" id="regional-settings">
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Localization</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Language</div>
                                <div class="settings-item-description">Default system language</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[language]" style="width: 180px;">
                                    <?php foreach (SettingsHelper::getLanguageOptions() as $code => $name): ?>
                                        <option value="<?php echo $code; ?>" <?php echo ($settings['language']['value'] ?? 'en') == $code ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Time Zone</div>
                                <div class="settings-item-description">Default time zone for date and time displays</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[timezone]" style="width: 250px;">
                                    <?php foreach (SettingsHelper::getTimezoneOptions() as $code => $name): ?>
                                        <option value="<?php echo $code; ?>" <?php echo ($settings['timezone']['value'] ?? 'America/New_York') == $code ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Date Format</div>
                                <div class="settings-item-description">How dates should be displayed throughout the system</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[date_format]" style="width: 250px;">
                                    <?php foreach (SettingsHelper::getDateFormatOptions() as $format => $example): ?>
                                        <option value="<?php echo $format; ?>" <?php echo ($settings['date_format']['value'] ?? 'M j, Y') == $format ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($example); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Time Format</div>
                                <div class="settings-item-description">Time display format</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[time_format]" style="width: 180px;">
                                    <?php foreach (SettingsHelper::getTimeFormatOptions() as $format => $example): ?>
                                        <option value="<?php echo $format; ?>" <?php echo ($settings['time_format']['value'] ?? 'g:i A') == $format ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($example); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">First Day of Week</div>
                                <div class="settings-item-description">Which day is considered the start of the week</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[first_day_of_week]" style="width: 180px;">
                                    <option value="0" <?php echo ($settings['first_day_of_week']['value'] ?? '0') == '0' ? 'selected' : ''; ?>>Sunday</option>
                                    <option value="1" <?php echo ($settings['first_day_of_week']['value'] ?? '0') == '1' ? 'selected' : ''; ?>>Monday</option>
                                    <option value="6" <?php echo ($settings['first_day_of_week']['value'] ?? '0') == '6' ? 'selected' : ''; ?>>Saturday</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Currency and Units</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Currency</div>
                                <div class="settings-item-description">Default currency for financial values</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[currency]" style="width: 180px;">
                                    <option value="USD" <?php echo ($settings['currency']['value'] ?? 'USD') == 'USD' ? 'selected' : ''; ?>>US Dollar ($)</option>
                                    <option value="EUR" <?php echo ($settings['currency']['value'] ?? 'USD') == 'EUR' ? 'selected' : ''; ?>>Euro (€)</option>
                                    <option value="GBP" <?php echo ($settings['currency']['value'] ?? 'USD') == 'GBP' ? 'selected' : ''; ?>>British Pound (£)</option>
                                    <option value="JPY" <?php echo ($settings['currency']['value'] ?? 'USD') == 'JPY' ? 'selected' : ''; ?>>Japanese Yen (¥)</option>
                                    <option value="CAD" <?php echo ($settings['currency']['value'] ?? 'USD') == 'CAD' ? 'selected' : ''; ?>>Canadian Dollar (C$)</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Measurement System</div>
                                <div class="settings-item-description">Unit system for dimensions and weights</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[measurement_system]" style="width: 180px;">
                                    <option value="imperial" <?php echo ($settings['measurement_system']['value'] ?? 'imperial') == 'imperial' ? 'selected' : ''; ?>>Imperial (in, lb)</option>
                                    <option value="metric" <?php echo ($settings['measurement_system']['value'] ?? 'imperial') == 'metric' ? 'selected' : ''; ?>>Metric (cm, kg)</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Number Format</div>
                                <div class="settings-item-description">How numbers should be formatted</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[number_format]" style="width: 180px;">
                                    <option value="1,234.56" <?php echo ($settings['number_format']['value'] ?? '1,234.56') == '1,234.56' ? 'selected' : ''; ?>>1,234.56</option>
                                    <option value="1.234,56" <?php echo ($settings['number_format']['value'] ?? '1,234.56') == '1.234,56' ? 'selected' : ''; ?>>1.234,56</option>
                                    <option value="1 234.56" <?php echo ($settings['number_format']['value'] ?? '1,234.56') == '1 234.56' ? 'selected' : ''; ?>>1 234.56</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interface Options Tab Content -->
        <div class="tab-content" id="interface-options">
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Display Settings</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Items Per Page</div>
                                <div class="settings-item-description">Default number of items to display per page in listings</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[items_per_page]" style="width: 150px;">
                                    <option value="10" <?php echo ($settings['items_per_page']['value'] ?? '20') == '10' ? 'selected' : ''; ?>>10 items</option>
                                    <option value="20" <?php echo ($settings['items_per_page']['value'] ?? '20') == '20' ? 'selected' : ''; ?>>20 items</option>
                                    <option value="50" <?php echo ($settings['items_per_page']['value'] ?? '20') == '50' ? 'selected' : ''; ?>>50 items</option>
                                    <option value="100" <?php echo ($settings['items_per_page']['value'] ?? '20') == '100' ? 'selected' : ''; ?>>100 items</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Default Landing Page</div>
                                <div class="settings-item-description">Page shown after login</div>
                            </div>
                            <div class="settings-item-control">
                            <select class="form-control" name="settings[default_landing_page]" style="width: 180px;">
                                    <option value="dashboard" <?php echo ($settings['default_landing_page']['value'] ?? 'dashboard') == 'dashboard' ? 'selected' : ''; ?>>Dashboard</option>
                                    <option value="items/browse" <?php echo ($settings['default_landing_page']['value'] ?? 'dashboard') == 'items/browse' ? 'selected' : ''; ?>>Browse Items</option>
                                    <option value="borrow/my-items" <?php echo ($settings['default_landing_page']['value'] ?? 'dashboard') == 'borrow/my-items' ? 'selected' : ''; ?>>My Borrowed Items</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Enable Theme Customization</div>
                                <div class="settings-item-description">Allow users to customize theme and appearance</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_theme_customization]" value="1" 
                                           <?php echo ($settings['enable_theme_customization']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Show Quick Actions</div>
                                <div class="settings-item-description">Display quick action buttons on dashboard</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[show_quick_actions]" value="1" 
                                           <?php echo ($settings['show_quick_actions']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">Appearance</div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Default Theme</div>
                                <div class="settings-item-description">Default theme for all users</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[default_theme]" style="width: 180px;">
                                    <option value="light" <?php echo ($settings['default_theme']['value'] ?? 'light') == 'light' ? 'selected' : ''; ?>>Light</option>
                                    <option value="dark" <?php echo ($settings['default_theme']['value'] ?? 'light') == 'dark' ? 'selected' : ''; ?>>Dark</option>
                                    <option value="system" <?php echo ($settings['default_theme']['value'] ?? 'light') == 'system' ? 'selected' : ''; ?>>Follow System Preference</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Primary Color</div>
                                <div class="settings-item-description">Main accent color for the interface</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="color" class="form-control" name="settings[primary_color]" 
                                       value="<?php echo htmlspecialchars($settings['primary_color']['value'] ?? '#6d28d9'); ?>"
                                       style="width: 50px; height: 38px; padding: 0; cursor: pointer;">
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Secondary Color</div>
                                <div class="settings-item-description">Secondary accent color</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="color" class="form-control" name="settings[secondary_color]" 
                                       value="<?php echo htmlspecialchars($settings['secondary_color']['value'] ?? '#10b981'); ?>"
                                       style="width: 50px; height: 38px; padding: 0; cursor: pointer;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">UI Density</div>
                                <div class="settings-item-description">Controls spacing in the user interface</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[ui_density]" style="width: 180px;">
                                    <option value="comfortable" <?php echo ($settings['ui_density']['value'] ?? 'comfortable') == 'comfortable' ? 'selected' : ''; ?>>Comfortable</option>
                                    <option value="compact" <?php echo ($settings['ui_density']['value'] ?? 'comfortable') == 'compact' ? 'selected' : ''; ?>>Compact</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Animation Settings</div>
                                <div class="settings-item-description">Control UI animations and transitions</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[animation_level]" style="width: 180px;">
                                    <option value="full" <?php echo ($settings['animation_level']['value'] ?? 'full') == 'full' ? 'selected' : ''; ?>>Full Animations</option>
                                    <option value="reduced" <?php echo ($settings['animation_level']['value'] ?? 'full') == 'reduced' ? 'selected' : ''; ?>>Reduced Animations</option>
                                    <option value="none" <?php echo ($settings['animation_level']['value'] ?? 'full') == 'none' ? 'selected' : ''; ?>>No Animations</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript for Advanced Settings buttons -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Clear Cache button
        const clearCacheBtn = document.getElementById('clear-cache-btn');
        if (clearCacheBtn) {
            clearCacheBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to clear the system cache?')) {
                    fetch('<?php echo BASE_URL; ?>/api/settings/clear-cache', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': '<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const flashContainer = document.getElementById('flash-message-container');
                        if (flashContainer) {
                            const alertClass = data.success ? 'success' : 'danger';
                            flashContainer.innerHTML = `
                                <div class="flash-message flash-${alertClass}">
                                    <div class="flash-icon">
                                        <i class="fas fa-${data.success ? 'check-circle' : 'exclamation-circle'}"></i>
                                    </div>
                                    <div class="flash-content">
                                        ${data.message}
                                    </div>
                                    <button class="flash-close" onclick="this.parentElement.remove()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `;
                            
                            setTimeout(() => {
                                const flashMsg = flashContainer.querySelector('.flash-message');
                                if (flashMsg) flashMsg.remove();
                            }, 5000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            });
        }
        
        // Reset Settings button
        const resetSettingsBtn = document.getElementById('reset-settings-btn');
        if (resetSettingsBtn) {
            resetSettingsBtn.addEventListener('click', function() {
                if (confirm('WARNING: This will reset ALL system settings to their default values. This action cannot be undone. Are you sure you want to continue?')) {
                    fetch('<?php echo BASE_URL; ?>/api/settings/reset', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': '<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const flashContainer = document.getElementById('flash-message-container');
                        if (flashContainer) {
                            const alertClass = data.success ? 'success' : 'danger';
                            flashContainer.innerHTML = `
                                <div class="flash-message flash-${alertClass}">
                                    <div class="flash-icon">
                                        <i class="fas fa-${data.success ? 'check-circle' : 'exclamation-circle'}"></i>
                                    </div>
                                    <div class="flash-content">
                                        ${data.message}
                                    </div>
                                    <button class="flash-close" onclick="this.parentElement.remove()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `;
                            
                            // Reload page after successful reset
                            if (data.success) {
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                setTimeout(() => {
                                    const flashMsg = flashContainer.querySelector('.flash-message');
                                    if (flashMsg) flashMsg.remove();
                                }, 5000);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            });
        }
    });
</script>