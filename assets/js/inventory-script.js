 // Sidebar Toggle Functionality
 document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const sidebar = document.querySelector('.sidebar');
    const navToggle = document.querySelector('.nav-toggle');
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const main = document.querySelector('.main');
    const themeSwitch = document.getElementById('themeSwitch');
    const mobileOverlay = document.querySelector('.mobile-overlay');
    
    // Toggle sidebar on button click (inside sidebar)
    navToggle.addEventListener('click', function() {
        // For desktop
        if (window.innerWidth > 576) {
            sidebar.classList.toggle('collapsed');
            main.classList.toggle('expanded');
        }
        // For mobile
        else {
            sidebar.classList.toggle('mobile-expanded');
            mobileOverlay.classList.toggle('visible');
        }
    });
    
    // Toggle sidebar on mobile menu button click
    mobileMenuButton.addEventListener('click', function() {
        sidebar.classList.toggle('mobile-expanded');
        mobileOverlay.classList.toggle('visible');
    });
    
    // Close sidebar when clicking outside (mobile only)
    mobileOverlay.addEventListener('click', function() {
        sidebar.classList.remove('mobile-expanded');
        mobileOverlay.classList.remove('visible');
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 576) {
            // Mobile view
            sidebar.classList.remove('collapsed');
            main.classList.remove('expanded');
            
            // If sidebar was open on mobile, keep it open
            if (sidebar.classList.contains('mobile-expanded')) {
                mobileOverlay.classList.add('visible');
            }
        } else {
            // Desktop view - remove mobile specific classes
            sidebar.classList.remove('mobile-expanded');
            mobileOverlay.classList.remove('visible');
        }
    });
    
    // Dark mode toggle
    if (themeSwitch) {
        // Check for saved theme preference or prefer-color-scheme
        const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem('theme');
        
        // Apply saved theme or system preference
        if (savedTheme === 'dark' || (!savedTheme && prefersDarkMode)) {
            document.body.classList.add('dark-mode');
            themeSwitch.checked = true;
        }
        
        // Toggle theme on switch change
        themeSwitch.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
        });
    }
    
    // Add hover animations to nav items
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'rotate(5deg)';
            }
        });
        
        item.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'rotate(0deg)';
            }
        });
    });
    
    // Initialize sidebar state based on screen size
    if (window.innerWidth <= 576) {
        // For mobile, we keep the sidebar visible in collapsed state
        sidebar.classList.remove('mobile-expanded');
        mobileOverlay.classList.remove('visible');
        // No need to hide the sidebar completely, as it stays visible in mini form
    }
});



    // Flash Message JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const flashMessage = document.getElementById('flash-message');
        if (!flashMessage) return;

        // Auto dismiss after 5 seconds if data-auto-dismiss attribute exists
        if (flashMessage.hasAttribute('data-auto-dismiss')) {
            setTimeout(dismissFlash, 5000);
        }

        // Add keyboard support (Escape to dismiss)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && flashMessage) {
                dismissFlash();
            }
        });
    });

    // Dismiss flash message function
    function dismissFlash() {
        const flashMessage = document.getElementById('flash-message');
        if (!flashMessage) return;

        flashMessage.classList.add('dismissing');
        setTimeout(() => {
            if (flashMessage.parentNode) {
                flashMessage.parentNode.removeChild(flashMessage);
            }
        }, 300);
    }

    // Handle multiple flash messages if needed
    function createFlashMessage(message, type = 'info') {
        // Remove existing message if any
        dismissFlash();

        // Create flash container
        const flashContainer = document.createElement('div');
        flashContainer.id = 'flash-message';
        flashContainer.className = `flash-message flash-${type}`;
        flashContainer.setAttribute('data-auto-dismiss', '');

        // Icon based on type
        let iconClass = 'info-circle';
        if (type === 'success') iconClass = 'check-circle';
        else if (type === 'error' || type === 'danger') iconClass = 'exclamation-circle';
        else if (type === 'warning') iconClass = 'exclamation-triangle';

        // Build flash content
        flashContainer.innerHTML = `
            <div class="flash-icon">
                <i class="fas fa-${iconClass}"></i>
            </div>
            <div class="flash-content">
                ${message}
            </div>
            <button class="flash-close" onclick="dismissFlash()">
                <i class="fas fa-times"></i>
            </button>
        `;

        // Add to document
        document.body.appendChild(flashContainer);

        // Set auto dismiss
        setTimeout(dismissFlash, 5000);

        return flashContainer;
    }


    // Enhanced Settings Form Submission with Debug
document.addEventListener('DOMContentLoaded', function() {
    // Find all settings forms
    const settingsForms = document.querySelectorAll('form[id^="settings-form"]');
    
    settingsForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submission intercepted', form.id);
            
            // Create FormData object
            const formData = new FormData(form);
            
            // Debug: Log form data being submitted
            console.log('Form data being submitted:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }
            
            // Show loading indicator
            const submitBtn = form.querySelector('button[type="submit"]');
            if (!submitBtn) {
                console.error('Submit button not found in form', form.id);
                return;
            }
            
            const originalBtnContent = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            // Submit form via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                // Display flash message
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
                    
                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        const flashMsg = flashContainer.querySelector('.flash-message');
                        if (flashMsg) {
                            flashMsg.style.opacity = '0';
                            setTimeout(() => flashMsg.remove(), 300);
                        }
                    }, 5000);
                } else {
                    console.error('Flash message container not found');
                }
                
                // Restore button state
                submitBtn.innerHTML = originalBtnContent;
                submitBtn.disabled = false;
                
                // Reload page or refresh data if needed
                if (data.success && (data.reload === true)) {
                    console.log('Reloading page as requested by response');
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                
                // Display error message
                const flashContainer = document.getElementById('flash-message-container');
                if (flashContainer) {
                    flashContainer.innerHTML = `
                        <div class="flash-message flash-danger">
                            <div class="flash-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="flash-content">
                                An error occurred while saving settings. Please try again.
                            </div>
                            <button class="flash-close" onclick="this.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                }
                
                // Restore button state
                submitBtn.innerHTML = originalBtnContent;
                submitBtn.disabled = false;
            });
        });
    });
});