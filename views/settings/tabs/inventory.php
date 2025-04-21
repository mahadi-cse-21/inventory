<?php
/**
 * Inventory Settings Tab
 * Redesigned with improved layout and organization
 */
?>

<div class="tabs-container">
    <div class="tabs-header">
        <div class="tab active"><i class="fas fa-box-open fa-fw mr-2"></i>Inventory Management</div>
        <div class="tab"><i class="fas fa-dolly fa-fw mr-2"></i>Check-out & Reservations</div>
        <div class="tab"><i class="fas fa-barcode fa-fw mr-2"></i>Barcoding Options</div>
    </div>

    <form id="settings-form" method="POST" action="<?php echo BASE_URL; ?>/settings?tab=inventory">
        <!-- CSRF Token -->
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">

        <!-- Required fields note -->
        <div class="required-text">Required fields are marked with an asterisk (*)</div>

        <!-- Inventory Management Tab Content -->
        <div class="tab-content active" id="inventory-management">
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">
                        <i class="fas fa-cogs fa-fw mr-2"></i>Inventory Management
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Inventory Method *</div>
                                <div class="settings-item-description">Determines how inventory cost and valuation is calculated</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[inventory_method]" style="width: 240px;">
                                    <option value="fifo" <?php echo ($settings['inventory_method']['value'] ?? 'fifo') == 'fifo' ? 'selected' : ''; ?>>FIFO (First In, First Out)</option>
                                    <option value="lifo" <?php echo ($settings['inventory_method']['value'] ?? 'fifo') == 'lifo' ? 'selected' : ''; ?>>LIFO (Last In, First Out)</option>
                                    <option value="avg" <?php echo ($settings['inventory_method']['value'] ?? 'fifo') == 'avg' ? 'selected' : ''; ?>>Average Cost</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Low Stock Threshold *</div>
                                <div class="settings-item-description">Percentage threshold that triggers low stock alerts for inventory items</div>
                            </div>
                            <div class="settings-item-control">
                                <div class="input-group" style="width: 140px;">
                                    <input type="number" class="form-control" name="settings[low_stock_threshold]" 
                                        value="<?php echo htmlspecialchars($settings['low_stock_threshold']['value'] ?? '20'); ?>"
                                        min="1" max="99">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Auto-Generate Purchase Orders</div>
                                <div class="settings-item-description">System will automatically create draft purchase orders when inventory falls below threshold</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[auto_generate_purchase_orders]" value="1" 
                                           <?php echo ($settings['auto_generate_purchase_orders']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Auto-assign Asset Tags</div>
                                <div class="settings-item-description">New inventory items will automatically receive sequential asset tags</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[auto_assign_asset_tags]" value="1" 
                                           <?php echo ($settings['auto_assign_asset_tags']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item" data-depends-on="settings[auto_assign_asset_tags]">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Asset Tag Prefix</div>
                                <div class="settings-item-description">Text prefix used when generating sequential asset tags</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="text" class="form-control" name="settings[asset_tag_prefix]" 
                                       value="<?php echo htmlspecialchars($settings['asset_tag_prefix']['value'] ?? 'ASSET-'); ?>"
                                       style="width: 150px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Require Inventory Photos</div>
                                <div class="settings-item-description">Users must upload at least one photo when adding new inventory items</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[require_inventory_photos]" value="1" 
                                           <?php echo ($settings['require_inventory_photos']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">
                        <i class="fas fa-tools fa-fw mr-2"></i>Maintenance Settings
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Enable Maintenance Scheduling</div>
                                <div class="settings-item-description">Track and manage maintenance schedules for equipment and assets</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_maintenance_scheduler]" value="1" 
                                           <?php echo ($settings['enable_maintenance_scheduler']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item" data-depends-on="settings[enable_maintenance_scheduler]">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Default Maintenance Interval</div>
                                <div class="settings-item-description">Default number of days between recurring maintenance tasks</div>
                            </div>
                            <div class="settings-item-control">
                                <div class="input-group" style="width: 180px;">
                                    <input type="number" class="form-control" name="settings[default_maintenance_interval]" 
                                           value="<?php echo htmlspecialchars($settings['default_maintenance_interval']['value'] ?? '90'); ?>"
                                           min="1">
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="settings-item" data-depends-on="settings[enable_maintenance_scheduler]">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Maintenance Alert Days</div>
                                <div class="settings-item-description">Send notifications this many days before scheduled maintenance</div>
                            </div>
                            <div class="settings-item-control">
                                <div class="input-group" style="width: 180px;">
                                    <input type="number" class="form-control" name="settings[maintenance_alert_days]" 
                                           value="<?php echo htmlspecialchars($settings['maintenance_alert_days']['value'] ?? '7'); ?>"
                                           min="1">
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Enable Damage Reports</div>
                                <div class="settings-item-description">Allow users to submit reports when equipment is damaged or malfunctioning</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[enable_damage_reports]" value="1" 
                                           <?php echo ($settings['enable_damage_reports']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Check-out & Reservations Tab Content -->
        <div class="tab-content" id="checkout-reservations">
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">
                        <i class="fas fa-clipboard-check fa-fw mr-2"></i>Borrowing Settings
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Maximum Checkout Period</div>
                                <div class="settings-item-description">The longest period that items can be checked out</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[max_borrow_days]" style="width: 180px;">
                                    <option value="7" <?php echo ($settings['max_borrow_days']['value'] ?? '14') == '7' ? 'selected' : ''; ?>>7 days</option>
                                    <option value="14" <?php echo ($settings['max_borrow_days']['value'] ?? '14') == '14' ? 'selected' : ''; ?>>14 days</option>
                                    <option value="30" <?php echo ($settings['max_borrow_days']['value'] ?? '14') == '30' ? 'selected' : ''; ?>>30 days</option>
                                    <option value="90" <?php echo ($settings['max_borrow_days']['value'] ?? '14') == '90' ? 'selected' : ''; ?>>90 days</option>
                                    <option value="unlimited" <?php echo ($settings['max_borrow_days']['value'] ?? '14') == 'unlimited' ? 'selected' : ''; ?>>Unlimited</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Default Borrowing Period</div>
                                <div class="settings-item-description">Pre-selected checkout duration for new borrowing requests</div>
                            </div>
                            <div class="settings-item-control">
                                <div class="input-group" style="width: 180px;">
                                    <input type="number" class="form-control" name="settings[default_borrow_days]" 
                                           value="<?php echo htmlspecialchars($settings['default_borrow_days']['value'] ?? '7'); ?>"
                                           min="1">
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Allow Renewals</div>
                                <div class="settings-item-description">Allow users to extend their checkout periods on borrowed items</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[allow_renewals]" value="1" 
                                           <?php echo ($settings['allow_renewals']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-item" data-depends-on="settings[allow_renewals]">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Maximum Renewals</div>
                                <div class="settings-item-description">Maximum number of times a user can renew a single checkout</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="number" class="form-control" name="settings[max_renewals]" 
                                       value="<?php echo htmlspecialchars($settings['max_renewals']['value'] ?? '3'); ?>"
                                       min="0" max="10" style="width: 100px;">
                            </div>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Advance Reservation</div>
                                <div class="settings-item-description">How far in the future users can reserve equipment</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[advance_reservation_days]" style="width: 180px;">
                                    <option value="7" <?php echo ($settings['advance_reservation_days']['value'] ?? '30') == '7' ? 'selected' : ''; ?>>7 days</option>
                                    <option value="14" <?php echo ($settings['advance_reservation_days']['value'] ?? '30') == '14' ? 'selected' : ''; ?>>14 days</option>
                                    <option value="30" <?php echo ($settings['advance_reservation_days']['value'] ?? '30') == '30' ? 'selected' : ''; ?>>30 days</option>
                                    <option value="90" <?php echo ($settings['advance_reservation_days']['value'] ?? '30') == '90' ? 'selected' : ''; ?>>90 days</option>
                                    <option value="180" <?php echo ($settings['advance_reservation_days']['value'] ?? '30') == '180' ? 'selected' : ''; ?>>6 months</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">
                        <i class="fas fa-bell fa-fw mr-2"></i>Notifications & Reminders
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Overdue Notifications</div>
                                <div class="settings-item-description">Automatically send reminder emails to users with overdue items</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[send_overdue_notifications]" value="1" 
                                           <?php echo ($settings['send_overdue_notifications']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item" data-depends-on="settings[send_overdue_notifications]">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Due Date Reminder</div>
                                <div class="settings-item-description">Send reminder this many days before item is due</div>
                            </div>
                            <div class="settings-item-control">
                                <div class="input-group" style="width: 180px;">
                                    <input type="number" class="form-control" name="settings[overdue_reminder_days]" 
                                          value="<?php echo htmlspecialchars($settings['overdue_reminder_days']['value'] ?? '3'); ?>"
                                          min="1">
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="settings-item" data-depends-on="settings[send_overdue_notifications]">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Maximum Overdue Days</div>
                                <div class="settings-item-description">Number of days an item can be overdue before escalating to management</div>
                            </div>
                            <div class="settings-item-control">
                                <div class="input-group" style="width: 180px;">
                                    <input type="number" class="form-control" name="settings[max_overdue_days]" 
                                          value="<?php echo htmlspecialchars($settings['max_overdue_days']['value'] ?? '14'); ?>"
                                          min="1">
                                    <div class="input-group-append">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Notify Manager on Approval</div>
                                <div class="settings-item-description">Send notification to department manager when a borrow request is approved</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[notify_manager_on_approval]" value="1" 
                                           <?php echo ($settings['notify_manager_on_approval']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Notify Department Head</div>
                                <div class="settings-item-description">Copy department heads on important borrow notifications</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[notify_department_head]" value="1" 
                                           <?php echo ($settings['notify_department_head']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barcoding Options Tab Content -->
        <div class="tab-content" id="barcoding-options">
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">
                        <i class="fas fa-barcode fa-fw mr-2"></i>Barcode Generation
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Barcode Format</div>
                                <div class="settings-item-description">Type of barcode to generate for inventory items</div>
                            </div>
                            <div class="settings-item-control">
                            <select class="form-control" name="settings[barcode_format]" style="width: 180px;">
                                    <option value="code128" <?php echo ($settings['barcode_format']['value'] ?? 'code128') == 'code128' ? 'selected' : ''; ?>>Code 128</option>
                                    <option value="code39" <?php echo ($settings['barcode_format']['value'] ?? 'code128') == 'code39' ? 'selected' : ''; ?>>Code 39</option>
                                    <option value="ean13" <?php echo ($settings['barcode_format']['value'] ?? 'code128') == 'ean13' ? 'selected' : ''; ?>>EAN-13</option>
                                    <option value="upc" <?php echo ($settings['barcode_format']['value'] ?? 'code128') == 'upc' ? 'selected' : ''; ?>>UPC</option>
                                    <option value="qr" <?php echo ($settings['barcode_format']['value'] ?? 'code128') == 'qr' ? 'selected' : ''; ?>>QR Code</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Auto-generate Barcodes</div>
                                <div class="settings-item-description">Automatically generate barcodes for new inventory items</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[auto_generate_barcodes]" value="1" 
                                           <?php echo ($settings['auto_generate_barcodes']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="settings-item" data-depends-on="settings[auto_generate_barcodes]">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Barcode Prefix</div>
                                <div class="settings-item-description">Text prefix used when generating barcodes</div>
                            </div>
                            <div class="settings-item-control">
                                <input type="text" class="form-control" name="settings[barcode_prefix]" 
                                       value="<?php echo htmlspecialchars($settings['barcode_prefix']['value'] ?? 'INV-'); ?>"
                                       style="width: 150px;">
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Include Location in Barcode</div>
                                <div class="settings-item-description">Add location code to generated barcodes for easier tracking</div>
                            </div>
                            <div class="settings-item-control">
                                <label class="toggle-switch">
                                    <input type="checkbox" name="settings[include_location_in_barcode]" value="1" 
                                           <?php echo ($settings['include_location_in_barcode']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-title">
                        <i class="fas fa-print fa-fw mr-2"></i>Label Printing
                    </div>
                </div>
                <div class="settings-panel-body">
                    <div class="settings-group">
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Label Paper Size</div>
                                <div class="settings-item-description">Default paper size for printing barcode labels</div>
                            </div>
                            <div class="settings-item-control">
                                <select class="form-control" name="settings[label_paper_size]" style="width: 180px;">
                                    <option value="letter" <?php echo ($settings['label_paper_size']['value'] ?? 'letter') == 'letter' ? 'selected' : ''; ?>>Letter (8.5" x 11")</option>
                                    <option value="a4" <?php echo ($settings['label_paper_size']['value'] ?? 'letter') == 'a4' ? 'selected' : ''; ?>>A4</option>
                                    <option value="2x4" <?php echo ($settings['label_paper_size']['value'] ?? 'letter') == '2x4' ? 'selected' : ''; ?>>2" x 4" Labels</option>
                                    <option value="1x3" <?php echo ($settings['label_paper_size']['value'] ?? 'letter') == '1x3' ? 'selected' : ''; ?>>1" x 3" Labels</option>
                                    <option value="custom" <?php echo ($settings['label_paper_size']['value'] ?? 'letter') == 'custom' ? 'selected' : ''; ?>>Custom Size</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="settings-item">
                            <div class="settings-item-info">
                                <div class="settings-item-title">Label Content</div>
                                <div class="settings-item-description">Information to include on printed barcode labels</div>
                            </div>
                            <div class="settings-item-control" style="display: flex; flex-direction: column; gap: 10px;">
                                <label class="form-check">
                                    <input type="checkbox" name="settings[label_include_name]" value="1" class="form-check-input"
                                           <?php echo ($settings['label_include_name']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="form-check-label">Item Name</span>
                                </label>
                                
                                <label class="form-check">
                                    <input type="checkbox" name="settings[label_include_asset_id]" value="1" class="form-check-input"
                                           <?php echo ($settings['label_include_asset_id']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="form-check-label">Asset ID</span>
                                </label>
                                
                                <label class="form-check">
                                    <input type="checkbox" name="settings[label_include_model]" value="1" class="form-check-input"
                                           <?php echo ($settings['label_include_model']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="form-check-label">Model Number</span>
                                </label>
                                
                                <label class="form-check">
                                    <input type="checkbox" name="settings[label_include_location]" value="1" class="form-check-input"
                                           <?php echo ($settings['label_include_location']['value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <span class="form-check-label">Location</span>
                                </label>
                                
                                <label class="form-check">
                                    <input type="checkbox" name="settings[label_include_company_name]" value="1" class="form-check-input"
                                           <?php echo ($settings['label_include_company_name']['value'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <span class="form-check-label">Company Name</span>
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
    // Initialize tabs with icons
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