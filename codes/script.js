// Hamburger menu toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const hamburgerMenu = document.getElementById('hamburgerMenu');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');

    hamburgerMenu.addEventListener('click', function() {
        if (sidebar.classList.contains('hidden')) {
            // Show sidebar
            sidebar.classList.remove('hidden');
            sidebar.classList.add('open');
            mainContent.classList.remove('expanded');
        } else {
            // Hide sidebar with animation
            sidebar.classList.remove('open');
            sidebar.classList.add('hidden');
            mainContent.classList.add('expanded');
        }
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnHamburger = hamburgerMenu.contains(event.target);
        const isMobile = window.innerWidth <= 768;

        if (isMobile && !isClickInsideSidebar && !isClickOnHamburger && !sidebar.classList.contains('hidden')) {
            sidebar.classList.remove('open');
            sidebar.classList.add('hidden');
            mainContent.classList.add('expanded');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            if (sidebar.classList.contains('hidden')) {
                sidebar.classList.remove('hidden');
            }
            sidebar.classList.remove('open');
            mainContent.classList.remove('expanded');
        }
    });

    // Handle nav item clicks - change active state
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            // Don't prevent default if it has a valid href (like index.php or inventory.php)
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
            }
            
            // Remove active class from all nav items
            navItems.forEach(function(navItem) {
                navItem.classList.remove('active');
            });
            
            // Add active class to clicked item
            this.classList.add('active');
        });
    });

    // Handle actions menu dropdown
    const actionsButtons = document.querySelectorAll('.actions-btn');
    
    actionsButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.closest('.actions-menu');
            const dropdown = menu.querySelector('.actions-dropdown');
            const isActive = menu.classList.contains('active');
            
            // Close all other dropdowns
            document.querySelectorAll('.actions-menu').forEach(function(m) {
                if (m !== menu) {
                    m.classList.remove('active');
                    m.querySelector('.actions-dropdown').classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            if (isActive) {
                menu.classList.remove('active');
                dropdown.classList.remove('show');
            } else {
                menu.classList.add('active');
                dropdown.classList.add('show');
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.actions-menu')) {
            document.querySelectorAll('.actions-menu').forEach(function(menu) {
                menu.classList.remove('active');
                menu.querySelector('.actions-dropdown').classList.remove('show');
            });
        }
    });

    // Notification Bell Icon Functionality
    const notificationIcon = document.querySelector('.top-bar-right .icon-button:first-child');
    if (notificationIcon) {
        // Create notification dropdown
        let notificationDropdown = document.getElementById('notificationDropdown');
        if (!notificationDropdown) {
            notificationDropdown = document.createElement('div');
            notificationDropdown.id = 'notificationDropdown';
            notificationDropdown.className = 'notification-dropdown';
            notificationDropdown.innerHTML = `
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <button class="mark-all-read" onclick="markAllNotificationsRead()">Mark all as read</button>
                </div>
                <div class="notification-list" id="notificationList">
                    <!-- Notifications will be added here -->
                </div>
                <div class="notification-footer">
                    <button class="view-all-notifications" onclick="viewAllNotifications()">View All Notifications</button>
                </div>
            `;
            document.body.appendChild(notificationDropdown);
        }

        // Notifications data loaded from database
        let notifications = [];
        let readNotifications = JSON.parse(localStorage.getItem('readNotifications') || '[]');

        // Load notifications from database
        function loadNotificationsFromDatabase() {
            fetch('api/get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Mark notifications as read if they're in localStorage
                        notifications = data.data.map(notification => {
                            const isRead = readNotifications.includes(notification.id.toString()) || notification.read;
                            return {
                                ...notification,
                                read: isRead
                            };
                        });
                        
                        // Store globally and render
                        window.notifications = notifications;
                        renderNotifications();
                    } else {
                        console.error('Error loading notifications:', data.message);
                        notifications = [];
                        window.notifications = notifications;
                        renderNotifications();
                    }
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                    notifications = [];
                    window.notifications = notifications;
                    renderNotifications();
                });
        }

        function renderNotifications() {
            const notificationList = document.getElementById('notificationList');
            if (!notificationList) return;

            const unreadCount = notifications.filter(n => !n.read).length;
            const notificationBadge = notificationIcon.querySelector('.notification-badge');
            
            // Add badge if there are unread notifications
            if (unreadCount > 0) {
                if (!notificationBadge) {
                    const badge = document.createElement('span');
                    badge.className = 'notification-badge';
                    badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                    notificationIcon.appendChild(badge);
                } else {
                    notificationBadge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                }
            } else if (notificationBadge) {
                notificationBadge.remove();
            }

            notificationList.innerHTML = '';

            if (notifications.length === 0) {
                notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
                return;
            }

            notifications.slice(0, 5).forEach(notification => {
                const notificationItem = document.createElement('div');
                notificationItem.className = `notification-item ${notification.read ? 'read' : 'unread'}`;
                notificationItem.onclick = () => markNotificationRead(notification.id);
                
                notificationItem.innerHTML = `
                    <div class="notification-content">
                        <div class="notification-message">${escapeHtml(notification.message)}</div>
                        <div class="notification-time">${escapeHtml(notification.time)}</div>
                    </div>
                    ${!notification.read ? '<div class="notification-dot"></div>' : ''}
                `;
                
                notificationList.appendChild(notificationItem);
            });
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load notifications on page load
        loadNotificationsFromDatabase();
        
        // Auto-refresh notifications every 30 seconds for real-time updates
        setInterval(function() {
            loadNotificationsFromDatabase();
        }, 30000); // Refresh every 30 seconds

        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = notificationDropdown.classList.contains('show');
            
            // Close profile dropdown if open
            const profileDropdown = document.getElementById('profileDropdown');
            if (profileDropdown) {
                profileDropdown.classList.remove('show');
            }
            
            // Toggle notification dropdown
            if (isVisible) {
                notificationDropdown.classList.remove('show');
            } else {
                notificationDropdown.classList.add('show');
                renderNotifications();
            }
        });

        // Store globally
        window.notifications = notifications;
        window.renderNotifications = renderNotifications;
    }

    // Create Profile and Settings Modals first (before profile dropdown)
    function createProfileModal() {
        if (document.getElementById('profileModal')) return;
        
        const modal = document.createElement('div');
        modal.id = 'profileModal';
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>User Profile</h2>
                    <button class="modal-close" onclick="closeProfileModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="profile-view">
                        <div class="profile-avatar-large">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="#002A3A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="7" r="4" stroke="#002A3A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <button class="btn-change-avatar" onclick="changeAvatar()">Change Avatar</button>
                        </div>
                        <div class="profile-details">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" id="profileName" value="Admin User" readonly class="readonly-input">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" id="profileEmail" value="admin@glassworth.com" readonly class="readonly-input">
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <input type="text" id="profileRole" value="Administrator" readonly class="readonly-input">
                            </div>
                            <div class="form-group">
                                <label>Member Since</label>
                                <input type="text" id="profileMemberSince" value="January 2024" readonly class="readonly-input">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Add click outside to close
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeProfileModal();
            }
        });
    }

    function createSettingsModal() {
        if (document.getElementById('settingsModal')) return;
        
        const modal = document.createElement('div');
        modal.id = 'settingsModal';
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content settings-modal-content">
                <div class="modal-header">
                    <h2>Settings</h2>
                    <button class="modal-close" onclick="closeSettingsModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="settings-tabs">
                        <button class="settings-tab active" data-tab="general" onclick="switchSettingsTab('general', this)">General</button>
                        <button class="settings-tab" data-tab="security" onclick="switchSettingsTab('security', this)">Security</button>
                        <button class="settings-tab" data-tab="notifications" onclick="switchSettingsTab('notifications', this)">Notifications</button>
                    </div>
                    
                    <div class="settings-content">
                        <div id="generalSettings" class="settings-panel active">
                            <div class="form-group">
                                <label>Language</label>
                                <select id="languageSetting">
                                    <option value="en">English</option>
                                    <option value="es">Spanish</option>
                                    <option value="fr">French</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Theme</label>
                                <select id="themeSetting" onchange="toggleDarkMode(this.value)">
                                    <option value="light">Light</option>
                                    <option value="dark">Dark</option>
                                    <option value="auto">Auto</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date Format</label>
                                <select id="dateFormatSetting">
                                    <option value="mm/dd/yyyy">MM/DD/YYYY</option>
                                    <option value="dd/mm/yyyy">DD/MM/YYYY</option>
                                    <option value="yyyy-mm-dd">YYYY-MM-DD</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="securitySettings" class="settings-panel">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" id="currentPassword" placeholder="Enter current password">
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" id="newPassword" placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" id="confirmPassword" placeholder="Confirm new password">
                            </div>
                            <button class="btn-save" onclick="updatePassword()">Change Password</button>
                        </div>
                        
                        <div id="notificationsSettings" class="settings-panel">
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="emailNotifications" checked>
                                    Email Notifications
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="pushNotifications" checked>
                                    Push Notifications
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="stockAlerts" checked>
                                    Stock Alerts
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeSettingsModal()">Cancel</button>
                        <button type="button" class="btn-save" onclick="saveSettings()">Save Changes</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Add click outside to close
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeSettingsModal();
            }
        });
    }

    // Profile Icon Functionality
    const profileIcon = document.querySelector('.top-bar-right .icon-button:last-child');
    if (profileIcon) {
        // Create modals immediately when profile icon exists
        createProfileModal();
        createSettingsModal();
        
        // Load user profile from database on page load
        loadUserProfile();
        
        // Auto-refresh profile every 30 seconds for real-time updates
        setInterval(function() {
            loadUserProfile();
        }, 30000); // Refresh every 30 seconds
        
        // Create profile dropdown
        let profileDropdown = document.getElementById('profileDropdown');
        if (!profileDropdown) {
            profileDropdown = document.createElement('div');
            profileDropdown.id = 'profileDropdown';
            profileDropdown.className = 'profile-dropdown';
            profileDropdown.innerHTML = `
                <div class="profile-header">
                    <div class="profile-avatar">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="profile-info">
                        <div class="profile-name" id="dropdownProfileName">Loading...</div>
                        <div class="profile-email" id="dropdownProfileEmail">Loading...</div>
                    </div>
                </div>
                <div class="profile-menu">
                    <a href="#" class="profile-menu-item" id="viewProfileBtn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>View Profile</span>
                    </a>
                    <a href="#" class="profile-menu-item" id="openSettingsBtn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 1v6m0 6v6M23 12h-6m-6 0H1" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <span>Settings</span>
                    </a>
                    <div class="profile-divider"></div>
                    <a href="#" class="profile-menu-item" id="logoutBtn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <polyline points="16 17 21 12 16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span>Logout</span>
                    </a>
                </div>
            `;
            document.body.appendChild(profileDropdown);
            
            // Add event listeners to menu items
            const viewProfileBtn = profileDropdown.querySelector('#viewProfileBtn');
            const openSettingsBtn = profileDropdown.querySelector('#openSettingsBtn');
            const logoutBtn = profileDropdown.querySelector('#logoutBtn');
            
            if (viewProfileBtn) {
                viewProfileBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (window.viewProfile) {
                        window.viewProfile();
                    } else {
                        console.error('viewProfile function not found');
                    }
                });
            }
            
            if (openSettingsBtn) {
                openSettingsBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (window.openSettings) {
                        window.openSettings();
                    } else {
                        console.error('openSettings function not found');
                    }
                });
            }
            
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (window.logout) {
                        window.logout();
                    } else {
                        console.error('logout function not found');
                    }
                });
            }
        }

        profileIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = profileDropdown.classList.contains('show');
            
            // Close notification dropdown if open
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationDropdown) {
                notificationDropdown.classList.remove('show');
            }
            
            // Toggle profile dropdown
            if (isVisible) {
                profileDropdown.classList.remove('show');
            } else {
                profileDropdown.classList.add('show');
            }
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.top-bar-right')) {
            const notificationDropdown = document.getElementById('notificationDropdown');
            const profileDropdown = document.getElementById('profileDropdown');
            
            if (notificationDropdown) {
                notificationDropdown.classList.remove('show');
            }
            if (profileDropdown) {
                profileDropdown.classList.remove('show');
            }
        }
    });

    // Notification functions
    window.markAllNotificationsRead = function() {
        if (window.notifications) {
            // Mark all as read in localStorage
            window.notifications.forEach(n => {
                n.read = true;
                if (!readNotifications.includes(n.id.toString())) {
                    readNotifications.push(n.id.toString());
                }
            });
            
            // Save to localStorage
            localStorage.setItem('readNotifications', JSON.stringify(readNotifications));
            
            // Update in database (for notifications table entries)
            fetch('api/mark_all_notifications_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            }).catch(error => {
                console.error('Error marking all notifications as read:', error);
            });
            
            if (window.renderNotifications) {
                window.renderNotifications();
            }
        }
    };

    window.markNotificationRead = function(notificationId) {
        if (window.notifications) {
            const notification = window.notifications.find(n => n.id === notificationId);
            if (notification) {
                notification.read = true;
                
                // Add to read notifications list
                if (!readNotifications.includes(notificationId.toString())) {
                    readNotifications.push(notificationId.toString());
                    localStorage.setItem('readNotifications', JSON.stringify(readNotifications));
                }
                
                // Update in database
                fetch('api/mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ notificationId: notificationId })
                }).catch(error => {
                    console.error('Error marking notification as read:', error);
                });
                
                if (window.renderNotifications) {
                    window.renderNotifications();
                }
            }
        }
    };

    window.viewAllNotifications = function() {
        alert('Viewing all notifications...\nIn a real application, this would navigate to a notifications page.');
    };

    // Helper function to handle avatar image errors
    function handleAvatarError(imgElement, size) {
        const svgContent = size === 'large' 
            ? `<svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="#002A3A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="7" r="4" stroke="#002A3A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
               </svg>
               <button class="btn-change-avatar" onclick="changeAvatar()">Change Avatar</button>`
            : `<svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
               </svg>`;
        imgElement.parentElement.innerHTML = svgContent;
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }


    // Load user profile from database
    function loadUserProfile() {
        fetch('api/get_user_profile.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const user = data.data;
                    console.log('Profile loaded:', user); // Debug log
                    
                    // Update profile modal
                    const profileName = document.getElementById('profileName');
                    const profileEmail = document.getElementById('profileEmail');
                    const profileRole = document.getElementById('profileRole');
                    const profileMemberSince = document.getElementById('profileMemberSince');
                    
                    if (profileName) profileName.value = user.name;
                    if (profileEmail) profileEmail.value = user.email;
                    if (profileRole) profileRole.value = user.role;
                    if (profileMemberSince) profileMemberSince.value = user.member_since;
                    
                    // Update avatar in profile modal
                    const avatarLarge = document.querySelector('.profile-avatar-large');
                    if (avatarLarge) {
                        if (user.avatar && user.avatar.trim() !== '') {
                            // Escape HTML to prevent XSS
                            const avatarPath = escapeHtml(user.avatar);
                            // Add cache-busting parameter to ensure fresh image
                            const avatarUrl = avatarPath + '?t=' + Date.now();
                            avatarLarge.innerHTML = `
                                <img src="${avatarUrl}" alt="Profile Avatar" class="profile-avatar-img" id="profileAvatarImg" onerror="handleAvatarError(this, 'large')">
                                <button class="btn-change-avatar" onclick="changeAvatar()">Change Avatar</button>
                            `;
                        } else {
                            // Keep default SVG if no avatar
                            avatarLarge.innerHTML = `
                                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="#002A3A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="7" r="4" stroke="#002A3A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <button class="btn-change-avatar" onclick="changeAvatar()">Change Avatar</button>
                            `;
                        }
                    }
                    
                    // Update profile dropdown
                    const profileDropdownName = document.getElementById('dropdownProfileName') || document.querySelector('.profile-dropdown .profile-name');
                    const profileDropdownEmail = document.getElementById('dropdownProfileEmail') || document.querySelector('.profile-dropdown .profile-email');
                    const profileDropdownAvatar = document.querySelector('.profile-dropdown .profile-avatar');
                    
                    if (profileDropdownName) profileDropdownName.textContent = user.name;
                    if (profileDropdownEmail) profileDropdownEmail.textContent = user.email;
                    
                    if (profileDropdownAvatar) {
                        if (user.avatar && user.avatar.trim() !== '') {
                            // Escape HTML to prevent XSS
                            const avatarPath = escapeHtml(user.avatar);
                            // Add cache-busting parameter to ensure fresh image
                            const avatarUrl = avatarPath + '?t=' + Date.now();
                            profileDropdownAvatar.innerHTML = `
                                <img src="${avatarUrl}" alt="Profile Avatar" class="profile-avatar-small" onerror="handleAvatarError(this, 'small')">
                            `;
                        } else {
                            // Keep default SVG if no avatar
                            profileDropdownAvatar.innerHTML = `
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            `;
                        }
                    }
                } else {
                    console.error('Error loading user profile:', data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching user profile:', error);
            });
    }

    window.viewProfile = function() {
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileDropdown) {
            profileDropdown.classList.remove('show');
        }
        
        // Ensure modals are created
        createProfileModal();
        
        // Load latest profile data from database
        loadUserProfile();
        
        const modal = document.getElementById('profileModal');
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        } else {
            console.error('Profile modal not found!');
        }
    };

    window.closeProfileModal = function() {
        const modal = document.getElementById('profileModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    window.changeAvatar = function() {
        // Create a file input element
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.style.display = 'none';
        
        // Add event listener for file selection
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file.');
                    return;
                }
                
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size must be less than 5MB.');
                    return;
                }
                
                // Show loading state
                const avatarLarge = document.querySelector('.profile-avatar-large');
                if (avatarLarge) {
                    const changeBtn = avatarLarge.querySelector('.btn-change-avatar');
                    if (changeBtn) {
                        changeBtn.disabled = true;
                        changeBtn.textContent = 'Uploading...';
                    }
                }
                
                // Create FormData for file upload
                const formData = new FormData();
                formData.append('avatar', file);
                
                // Upload to server
                fetch('api/upload_avatar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Avatar upload successful:', data);
                        // Reload profile to get updated avatar URL
                        // Use setTimeout to ensure database is updated
                        setTimeout(function() {
                            loadUserProfile();
                        }, 100);
                        
                        // Show success notification
                        showAvatarChangeNotification('Avatar updated successfully!');
                    } else {
                        console.error('Avatar upload failed:', data);
                        alert('Error: ' + (data.message || 'Failed to upload avatar'));
                        // Reset button
                        const changeBtn = avatarLarge.querySelector('.btn-change-avatar');
                        if (changeBtn) {
                            changeBtn.disabled = false;
                            changeBtn.textContent = 'Change Avatar';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error uploading avatar:', error);
                    alert('Error uploading avatar. Please try again.');
                    // Reset button
                    const changeBtn = avatarLarge.querySelector('.btn-change-avatar');
                    if (changeBtn) {
                        changeBtn.disabled = false;
                        changeBtn.textContent = 'Change Avatar';
                    }
                });
            }
        });
        
        // Trigger file picker
        document.body.appendChild(fileInput);
        fileInput.click();
        document.body.removeChild(fileInput);
    };

    function showAvatarChangeNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'export-notification';
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Hide and remove notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    window.openSettings = function() {
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileDropdown) {
            profileDropdown.classList.remove('show');
        }
        
        // Ensure modals are created
        createSettingsModal();
        
        const modal = document.getElementById('settingsModal');
        if (modal) {
            // Load current theme setting
            const savedTheme = localStorage.getItem('theme') || 'light';
            const themeSelect = document.getElementById('themeSetting');
            if (themeSelect) {
                themeSelect.value = savedTheme;
            }
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            switchSettingsTab('general', null);
        } else {
            console.error('Settings modal not found!');
        }
    };

    window.closeSettingsModal = function() {
        const modal = document.getElementById('settingsModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    window.switchSettingsTab = function(tabName, buttonElement) {
        // Remove active class from all tabs and panels
        document.querySelectorAll('.settings-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.settings-panel').forEach(panel => panel.classList.remove('active'));
        
        // Add active class to selected tab and panel
        if (buttonElement) {
            buttonElement.classList.add('active');
        } else {
            document.querySelector(`.settings-tab[data-tab="${tabName}"]`)?.classList.add('active');
        }
        document.getElementById(tabName + 'Settings')?.classList.add('active');
    };

    // Dark Mode Functions
    function toggleDarkMode(theme) {
        const body = document.body;
        
        if (theme === 'dark') {
            body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
        } else if (theme === 'auto') {
            // Use system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (prefersDark) {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }
            localStorage.setItem('theme', 'auto');
        } else {
            body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
        }
    }

    function initializeTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        const themeSelect = document.getElementById('themeSetting');
        
        if (themeSelect) {
            themeSelect.value = savedTheme;
        }
        
        toggleDarkMode(savedTheme);
        
        // Listen for system theme changes if auto mode is selected
        if (savedTheme === 'auto') {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                toggleDarkMode('auto');
            });
        }
    }

    // Initialize theme on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeTheme();
    });

    window.saveSettings = function() {
        // Get all settings values
        const language = document.getElementById('languageSetting').value;
        const theme = document.getElementById('themeSetting').value;
        const dateFormat = document.getElementById('dateFormatSetting').value;
        const emailNotif = document.getElementById('emailNotifications').checked;
        const pushNotif = document.getElementById('pushNotifications').checked;
        const stockAlerts = document.getElementById('stockAlerts').checked;
        
        // Apply theme immediately
        toggleDarkMode(theme);
        
        // In a real application, this would save to server
        alert('Settings saved successfully!\n\nLanguage: ' + language + '\nTheme: ' + theme + '\nDate Format: ' + dateFormat);
        
        closeSettingsModal();
    };

    // Make toggleDarkMode globally accessible
    window.toggleDarkMode = toggleDarkMode;

    window.updatePassword = function() {
        const currentPass = document.getElementById('currentPassword').value;
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmPassword').value;
        
        if (!currentPass || !newPass || !confirmPass) {
            alert('Please fill in all password fields.');
            return;
        }
        
        if (newPass !== confirmPass) {
            alert('New passwords do not match!');
            return;
        }
        
        if (newPass.length < 8) {
            alert('New password must be at least 8 characters long.');
            return;
        }
        
        // In a real application, this would validate and update password on server
        alert('Password updated successfully!');
        
        // Clear password fields
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
    };

    window.logout = function() {
        if (confirm('Are you sure you want to logout?')) {
            // Close profile dropdown
            const profileDropdown = document.getElementById('profileDropdown');
            if (profileDropdown) {
                profileDropdown.classList.remove('show');
            }
            
            // Show loading message
            alert('Logging out...\nIn a real application, this would end the session and redirect to the login page.');
            
            // In a real app, redirect to login page:
            // window.location.href = 'login.php';
        }
    };

    // Handle action menu item clicks
    const actionMenuItems = document.querySelectorAll('.actions-dropdown a');
    
    actionMenuItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const menu = this.closest('.actions-menu');
            const tableRow = menu.closest('tr');
            const itemId = tableRow.querySelector('td:nth-child(2)').textContent.trim();
            const itemName = tableRow.querySelector('td:nth-child(3)').textContent.trim();
            const action = this.textContent.trim();
            
            // Close the dropdown
            menu.classList.remove('active');
            menu.querySelector('.actions-dropdown').classList.remove('show');
            
            // Call appropriate function based on action
            if (action === 'Manage Stock') {
                handleManageStock(itemId, itemName, tableRow);
            } else if (action === 'Edit Item') {
                handleEditItem(itemId, itemName, tableRow);
            } else if (action === 'Delete Item') {
                handleDeleteItem(itemId, itemName, tableRow);
            }
        });
    });

    // Global variables for manage stock - store in window for access from inventory.php
    window.currentManageStockRow = null;
    window.currentManageStockItemId = null;
    window.currentManageStockItemName = null;

    // Function to handle Manage Stock action
    function handleManageStock(itemId, itemName, row) {
        window.currentManageStockRow = row;
        window.currentManageStockItemId = itemId;
        window.currentManageStockItemName = itemName;
        
        // Get current stock value
        const stockCell = row.querySelector('td:nth-child(5)');
        const stockSpan = stockCell.querySelector('span');
        const currentStockNum = stockSpan ? parseInt(stockSpan.textContent.trim()) || 0 : parseInt(stockCell.textContent.replace(/[^0-9]/g, '')) || 0;
        const unit = row.querySelector('td:nth-child(6)').textContent.trim();
        
        // Open manage stock modal
        if (window.openManageStockModal) {
            window.openManageStockModal(itemName, currentStockNum, unit);
        }
    }

    // Global variables for edit item - store in window for access from inventory.php
    window.currentEditItemRow = null;
    window.currentEditItemId = null;
    window.currentEditItemOriginalName = null;

    // Function to handle Edit Item action
    function handleEditItem(itemId, itemName, row) {
        // Store row reference globally for use in modal
        window.currentEditItemRow = row;
        window.currentEditItemId = itemId;
        window.currentEditItemOriginalName = itemName;
        
        // Get current item data
        const nameCell = row.querySelector('td:nth-child(3)');
        const nameSpan = nameCell.querySelector('span');
        const currentName = nameSpan ? nameSpan.textContent.trim() : nameCell.textContent.trim();
        const currentCategory = row.querySelector('td:nth-child(4)').textContent.trim();
        const stockCell = row.querySelector('td:nth-child(5)');
        const stockSpan = stockCell.querySelector('span');
        const currentStock = stockSpan ? parseInt(stockSpan.textContent.trim()) || 0 : parseInt(stockCell.textContent.replace(/[^0-9]/g, '')) || 0;
        const currentUnit = row.querySelector('td:nth-child(6)').textContent.trim();
        
        // Check if item has "New Item" badge
        const nameBadges = nameCell.querySelectorAll('.badge-new');
        const isNewItem = nameBadges.length > 0;
        
        // Open edit item modal using global function
        if (window.openEditItemModal) {
            window.openEditItemModal(itemName, currentName, currentCategory, currentStock, currentUnit, isNewItem);
        } else {
            console.error('openEditItemModal function not found');
        }
    }

    // Global variables for delete item - store in window for access from inventory.php
    window.currentDeleteItemRow = null;
    window.currentDeleteItemId = null;
    window.currentDeleteItemName = null;

    // Function to handle Delete Item action
    function handleDeleteItem(itemId, itemName, row) {
        // Store row reference globally for use in modal
        window.currentDeleteItemRow = row;
        window.currentDeleteItemId = itemId;
        window.currentDeleteItemName = itemName;
        
        // Open delete item modal using global function
        if (window.openDeleteItemModal) {
            window.openDeleteItemModal(itemId, itemName);
        } else {
            console.error('openDeleteItemModal function not found');
        }
    }

    // Function to update stock badges based on stock level
    function updateStockBadges(stockCell, stockValue) {
        // Remove existing stock badges
        const existingBadges = stockCell.querySelectorAll('.badge-low-stock, .badge-out-stock');
        existingBadges.forEach(function(badge) {
            badge.remove();
        });
        
        // Add appropriate badges
        const stockSpan = stockCell.querySelector('span');
        if (stockValue === 0) {
            stockSpan.insertAdjacentHTML('afterend', '<span class="badge badge-out-stock">Out of Stock</span>');
        } else if (stockValue < 15) {
            stockSpan.insertAdjacentHTML('afterend', '<span class="badge badge-low-stock">Low Stock</span>');
        }
    }

    // Function to update stock badges with threshold
    function updateStockBadgesWithThreshold(stockCell, stockValue, threshold) {
        // Remove existing stock badges
        const existingBadges = stockCell.querySelectorAll('.badge-low-stock, .badge-out-stock');
        existingBadges.forEach(function(badge) {
            badge.remove();
        });
        
        // Add appropriate badges based on threshold
        const stockSpan = stockCell.querySelector('span');
        if (stockValue === 0) {
            stockSpan.insertAdjacentHTML('afterend', '<span class="badge badge-out-stock">Out of Stock</span>');
        } else if (threshold > 0 && stockValue < threshold) {
            stockSpan.insertAdjacentHTML('afterend', '<span class="badge badge-low-stock">Low Stock</span>');
        } else if (threshold === 0 && stockValue < 15) {
            // Default threshold of 15 if no threshold set
            stockSpan.insertAdjacentHTML('afterend', '<span class="badge badge-low-stock">Low Stock</span>');
        }
    }

    // Function to update total items count
    function updateTotalItemsCount() {
        const totalItemsCard = document.querySelector('.card-total-items .card-number');
        if (totalItemsCard) {
            const tableRows = document.querySelectorAll('.items-table tbody tr');
            const newCount = tableRows.length;
            totalItemsCard.textContent = newCount;
        }
    }

    // Function to show notifications
    function showNotification(message, type) {
        // Remove existing notification if any
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'notification notification-' + type;
        notification.textContent = message;
        
        // Add styles
        const bgColor = type === 'success' ? '#4caf50' : (type === 'error' ? '#f44336' : '#2196f3');
        notification.style.cssText = `
            position: fixed;
            top: 70px;
            right: 20px;
            background-color: ${bgColor};
            color: white;
            padding: 15px 20px;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            z-index: 10001;
            animation: slideIn 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        `;
        
        // Add animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
        
        document.body.appendChild(notification);
        
        // Remove notification after 3 seconds
        setTimeout(function() {
            notification.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Inventory Page Specific Functions
    const filterInput = document.querySelector('.filter-input');
    const categorySelect = document.getElementById('categoryFilter') || document.querySelectorAll('.filter-select')[0];
    const statusSelect = document.getElementById('statusFilter') || document.querySelectorAll('.filter-select')[1];
    const itemsTable = document.querySelector('.items-table tbody');
    const paginationBtns = document.querySelectorAll('.pagination-btn');
    const rowsSelect = document.querySelector('.rows-select');

    // Initialize counts on page load
    if (itemsTable) {
        updateLowStockCount();
        updateNewItemsCount();
        updateTotalItemsCount();
        updateTableInfo();
    }

    // Search/Filter functionality
    if (filterInput) {
        filterInput.addEventListener('input', function() {
            filterTable();
            updateClearButton();
            // Update pagination after filter
            if (typeof updatePaginationDisplay === 'function') {
                setTimeout(function() {
                    if (typeof currentPage !== 'undefined') {
                        currentPage = 1; // Reset to first page
                    }
                    updatePaginationDisplay();
                }, 50);
            }
        });
    }

    // Category filter
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            filterTable();
            updateClearButton();
            // Add visual feedback
            if (this.value !== 'All Categories') {
                this.style.backgroundColor = '#e3f2fd';
                this.style.borderColor = '#2196f3';
            } else {
                this.style.backgroundColor = 'white';
                this.style.borderColor = '#ddd';
            }
        });
    }

    // Status filter
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            filterTable();
            updateClearButton();
            // Add visual feedback
            if (this.value !== 'All Status') {
                this.style.backgroundColor = '#e3f2fd';
                this.style.borderColor = '#2196f3';
            } else {
                this.style.backgroundColor = 'white';
                this.style.borderColor = '#ddd';
            }
        });
    }

    // Update clear button visibility
    function updateClearButton() {
        const clearBtn = document.getElementById('clearFilterBtn');
        if (clearBtn) {
            const hasFilters = (categorySelect && categorySelect.value !== 'All Categories') ||
                             (statusSelect && statusSelect.value !== 'All Status') ||
                             (filterInput && filterInput.value.trim() !== '');
            clearBtn.style.display = hasFilters ? 'inline-block' : 'none';
        }
    }

    // Filter table function
    function filterTable() {
        if (!itemsTable) return;
        
        const searchTerm = filterInput ? filterInput.value.toLowerCase().trim() : '';
        const category = categorySelect ? categorySelect.value : 'All Categories';
        const status = statusSelect ? statusSelect.value : 'All Status';
        
        const rows = itemsTable.querySelectorAll('tr');
        let visibleCount = 0;
        
        rows.forEach(function(row) {
            // Get cell values
            const itemIdCell = row.querySelector('td:nth-child(2)');
            const itemNameCell = row.querySelector('td:nth-child(3)');
            const itemCategoryCell = row.querySelector('td:nth-child(4)');
            const stockCell = row.querySelector('td:nth-child(5)');
            
            if (!itemIdCell || !itemNameCell || !itemCategoryCell || !stockCell) {
                return;
            }
            
            // Get text content, removing badges
            const itemId = itemIdCell.textContent.toLowerCase().trim();
            const itemNameSpan = itemNameCell.querySelector('span');
            const itemName = itemNameSpan ? itemNameSpan.textContent.toLowerCase().trim() : itemNameCell.textContent.toLowerCase().trim();
            const itemCategory = itemCategoryCell.textContent.trim();
            const stockText = stockCell.textContent.toLowerCase();
            
            // Check search match
            const matchesSearch = !searchTerm || itemId.includes(searchTerm) || itemName.includes(searchTerm);
            
            // Check category match
            const matchesCategory = category === 'All Categories' || category === '' || itemCategory === category;
            
            // Check status match
            let matchesStatus = true;
            if (status !== 'All Status' && status !== '') {
                if (status === 'In Stock') {
                    // In Stock means no low stock or out of stock badges
                    matchesStatus = !stockText.includes('low stock') && !stockText.includes('out of stock');
                } else if (status === 'Low Stock') {
                    matchesStatus = stockText.includes('low stock');
                } else if (status === 'Out of Stock') {
                    matchesStatus = stockText.includes('out of stock');
                }
            }
            
            // Show or hide row
            if (matchesSearch && matchesCategory && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update table info and renumber rows
        updateTableInfo();
        renumberRows();
        
        // Show filter feedback
        if (category !== 'All Categories' || status !== 'All Status' || searchTerm) {
            showFilterFeedback(category, status, visibleCount);
        } else {
            hideFilterFeedback();
        }
    }

    // Show filter feedback
    function showFilterFeedback(category, status, count) {
        let feedbackText = '';
        if (category !== 'All Categories') {
            feedbackText += `Category: ${category}`;
        }
        if (status !== 'All Status') {
            if (feedbackText) feedbackText += ' | ';
            feedbackText += `Status: ${status}`;
        }
        feedbackText += ` (${count} items)`;
        
        // Remove existing feedback if any
        let feedbackEl = document.querySelector('.filter-feedback');
        if (!feedbackEl) {
            feedbackEl = document.createElement('div');
            feedbackEl.className = 'filter-feedback';
            const filterBar = document.querySelector('.filter-bar');
            if (filterBar) {
                filterBar.parentNode.insertBefore(feedbackEl, filterBar.nextSibling);
            }
        }
        feedbackEl.textContent = feedbackText;
        feedbackEl.style.display = 'block';
    }

        // Hide filter feedback
    function hideFilterFeedback() {
        const feedbackEl = document.querySelector('.filter-feedback');
        if (feedbackEl) {
            feedbackEl.style.display = 'none';
        }
    }

    // Make filterTable and related functions available globally
    window.filterTable = filterTable;
    window.updateClearButton = updateClearButton;

    // Add New Item functionality
    const addItemBtn = document.querySelector('.btn-add-item');
    const addItemModal = document.getElementById('addItemModal');
    const addItemForm = document.getElementById('addItemForm');
    const closeModalBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');

    if (addItemBtn) {
        addItemBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openAddItemModal();
        });
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            closeAddItemModal();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            closeAddItemModal();
        });
    }

    // Close modal when clicking outside
    if (addItemModal) {
        addItemModal.addEventListener('click', function(e) {
            if (e.target === addItemModal) {
                closeAddItemModal();
            }
        });
    }

    // Handle form submission
    if (addItemForm) {
        addItemForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAddNewItem();
        });
    }

    function openAddItemModal() {
        if (addItemModal) {
            addItemModal.classList.add('show');
            // Reset form
            if (addItemForm) {
                addItemForm.reset();
            }
        }
    }

    function closeAddItemModal() {
        if (addItemModal) {
            addItemModal.classList.remove('show');
            // Reset form
            if (addItemForm) {
                addItemForm.reset();
            }
        }
    }

    function handleAddNewItem() {
        // Get form values
        const itemNameInput = document.getElementById('itemName');
        const itemCategoryInput = document.getElementById('itemCategory');
        const initialStockInput = document.getElementById('initialStock');
        const itemUnitInput = document.getElementById('itemUnit');
        const isNewItemInput = document.getElementById('isNewItem');

        if (!itemNameInput || !itemCategoryInput || !initialStockInput || !itemUnitInput) {
            return;
        }

        const itemName = itemNameInput.value.trim();
        const itemCategory = itemCategoryInput.value;
        const initialStock = initialStockInput.value;
        const itemUnit = itemUnitInput.value;
        const isNewItem = isNewItemInput ? isNewItemInput.checked : false;

        // Validate form
        if (!itemName || !itemCategory || !initialStock || !itemUnit) {
            showNotification('Please fill in all required fields', 'error');
            return;
        }

        const stockNum = parseInt(initialStock) || 0;
        
        // Get the next item number
        const existingRows = itemsTable ? itemsTable.querySelectorAll('tr') : [];
        const nextItemNum = existingRows.length + 1;
        
        // Generate next Item ID
        const nextItemId = 'ITM' + String(nextItemNum).padStart(3, '0');
        
        // Create new table row
        const newRow = document.createElement('tr');
        
        // Determine stock display and badges
        let stockDisplay = '<span>' + stockNum + '</span>';
        if (stockNum === 0) {
            stockDisplay += '<span class="badge badge-out-stock">Out of Stock</span>';
        } else if (stockNum < 15) {
            stockDisplay += '<span class="badge badge-low-stock">Low Stock</span>';
        }

        // Determine name display with New badge if checked
        let nameDisplay = '<span>' + itemName + '</span>';
        if (isNewItem) {
            nameDisplay += '<span class="badge badge-new">New</span>';
        }
        
        newRow.innerHTML = `
            <td>${nextItemNum}</td>
            <td>${nextItemId}</td>
            <td>${nameDisplay}</td>
            <td>${itemCategory}</td>
            <td>${stockDisplay}</td>
            <td>${itemUnit}</td>
            <td>
                <div class="actions-menu">
                    <button class="actions-btn">⋮</button>
                    <div class="actions-dropdown">
                        <a href="#">Manage Stock</a>
                        <a href="#">Edit Item</a>
                        <a href="#">Delete Item</a>
                    </div>
                </div>
            </td>
        `;
        
        // Add event listeners to new row actions
        const newActionsBtn = newRow.querySelector('.actions-btn');
        const newActionsItems = newRow.querySelectorAll('.actions-dropdown a');
        
        // Attach actions button event
        newActionsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.closest('.actions-menu');
            const dropdown = menu.querySelector('.actions-dropdown');
            const isActive = menu.classList.contains('active');
            
            document.querySelectorAll('.actions-menu').forEach(function(m) {
                if (m !== menu) {
                    m.classList.remove('active');
                    m.querySelector('.actions-dropdown').classList.remove('show');
                }
            });
            
            if (isActive) {
                menu.classList.remove('active');
                dropdown.classList.remove('show');
            } else {
                menu.classList.add('active');
                dropdown.classList.add('show');
            }
        });
        
        // Attach action menu items
        newActionsItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const menu = this.closest('.actions-menu');
                const tableRow = menu.closest('tr');
                const itemId = tableRow.querySelector('td:nth-child(2)').textContent.trim();
                const itemName = tableRow.querySelector('td:nth-child(3)').textContent.trim();
                const action = this.textContent.trim();
                
                menu.classList.remove('active');
                menu.querySelector('.actions-dropdown').classList.remove('show');
                
                if (action === 'Manage Stock') {
                    handleManageStock(itemId, itemName, tableRow);
                } else if (action === 'Edit Item') {
                    handleEditItem(itemId, itemName, tableRow);
                } else if (action === 'Delete Item') {
                    handleDeleteItem(itemId, itemName, tableRow);
                }
            });
        });
        
        // Add row to table
        if (itemsTable) {
            itemsTable.appendChild(newRow);
        }
        
        // Update card counts
        updateTotalItemsCount();
        updateLowStockCount();
        updateNewItemsCount();
        
        // Update table info
        updateTableInfo();
        
        // Close modal
        closeAddItemModal();
        
        showNotification(`New item "${itemName}" added successfully!`, 'success');
        
        // Renumber all rows
        renumberRows();
    }

    // Renumber table rows (only visible rows)
    function renumberRows() {
        if (!itemsTable) return;
        const rows = itemsTable.querySelectorAll('tr');
        let visibleIndex = 1;
        rows.forEach(function(row) {
            if (row.style.display !== 'none' && row.style.display !== '') {
                // Row is hidden, skip it
                return;
            }
            row.querySelector('td:first-child').textContent = visibleIndex;
            visibleIndex++;
        });
    }

    // Pagination functionality
    if (paginationBtns.length > 0) {
        paginationBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const btnText = this.textContent.trim();
                
                if (btnText === 'Previous' || btnText === '‹') {
                    handlePreviousPage();
                } else if (btnText === 'Next' || btnText === '›') {
                    handleNextPage();
                } else if (!isNaN(btnText)) {
                    handlePageClick(parseInt(btnText));
                }
            });
        });
    }

    let currentPage = 1;
    const itemsPerPage = 10;

    function handlePreviousPage() {
        if (currentPage > 1) {
            currentPage--;
            updatePagination();
            showNotification(`Showing page ${currentPage}`, 'success');
        }
    }

    function handleNextPage() {
        const totalRows = itemsTable ? itemsTable.querySelectorAll('tr:not([style*="display: none"])').length : 0;
        const totalPages = Math.ceil(totalRows / itemsPerPage);
        
        if (currentPage < totalPages) {
            currentPage++;
            updatePagination();
            showNotification(`Showing page ${currentPage}`, 'success');
        }
    }

    function handlePageClick(page) {
        currentPage = page;
        updatePagination();
    }

    function updatePagination() {
        // Remove active class from all pagination buttons
        paginationBtns.forEach(function(btn) {
            btn.classList.remove('active');
        });
        
        // Add active class to current page button
        paginationBtns.forEach(function(btn) {
            if (btn.textContent.trim() === currentPage.toString()) {
                btn.classList.add('active');
            }
        });
    }

    // Rows per page functionality
    if (rowsSelect) {
        rowsSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            showNotification(`Rows per page changed to ${selectedValue}`, 'success');
        });
    }

    // Update table info function
    function updateTableInfo() {
        const tableInfo = document.querySelector('.table-info');
        if (tableInfo && itemsTable) {
            const visibleRows = itemsTable.querySelectorAll('tr:not([style*="display: none"])').length;
            const totalRows = itemsTable.querySelectorAll('tr').length;
            tableInfo.textContent = `Showing 1-${Math.min(visibleRows, itemsPerPage)} of ${visibleRows} items`;
        }
    }

    // Update Low Stock count
    function updateLowStockCount() {
        const lowStockCard = document.querySelector('.card-low-stock-alert .card-number');
        if (lowStockCard && itemsTable) {
            const lowStockRows = itemsTable.querySelectorAll('tr');
            let lowStockCount = 0;
            lowStockRows.forEach(function(row) {
                const stockCell = row.querySelector('td:nth-child(5)');
                if (stockCell && stockCell.textContent.includes('Low Stock')) {
                    lowStockCount++;
                }
            });
            lowStockCard.textContent = lowStockCount;
        }
    }

    // Update New Items count
    function updateNewItemsCount() {
        const newItemsCard = document.querySelector('.card-new-items .card-number');
        if (newItemsCard && itemsTable) {
            const newRows = itemsTable.querySelectorAll('tr');
            let newCount = 0;
            newRows.forEach(function(row) {
                const nameCell = row.querySelector('td:nth-child(3)');
                if (nameCell && nameCell.querySelector('.badge-new')) {
                    newCount++;
                }
            });
            newItemsCard.textContent = newCount;
        }
    }
});

