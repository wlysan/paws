<?php
/**
 * Alerts Component
 * 
 * Provides reusable alert/notification components for the admin plugin
 */

/**
 * Display a success alert with the provided message
 * 
 * @param string $message The message to display
 * @param bool $dismissible Whether the alert can be dismissed (default: true)
 * @return void
 */
function show_success_alert($message, $dismissible = true) {
    $dismiss_class = $dismissible ? 'alert-dismissible fade show' : '';
    $dismiss_button = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';
    
    echo '<div class="alert alert-success ' . $dismiss_class . '" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <div>' . htmlspecialchars($message) . '</div>
            </div>
            ' . $dismiss_button . '
          </div>';
}

/**
 * Display an error alert with the provided message
 * 
 * @param string $message The message to display
 * @param bool $dismissible Whether the alert can be dismissed (default: true)
 * @return void
 */
function show_error_alert($message, $dismissible = true) {
    $dismiss_class = $dismissible ? 'alert-dismissible fade show' : '';
    $dismiss_button = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';
    
    echo '<div class="alert alert-danger ' . $dismiss_class . '" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div>' . htmlspecialchars($message) . '</div>
            </div>
            ' . $dismiss_button . '
          </div>';
}

/**
 * Display a warning alert with the provided message
 * 
 * @param string $message The message to display
 * @param bool $dismissible Whether the alert can be dismissed (default: true)
 * @return void
 */
function show_warning_alert($message, $dismissible = true) {
    $dismiss_class = $dismissible ? 'alert-dismissible fade show' : '';
    $dismiss_button = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';
    
    echo '<div class="alert alert-warning ' . $dismiss_class . '" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>' . htmlspecialchars($message) . '</div>
            </div>
            ' . $dismiss_button . '
          </div>';
}

/**
 * Display an info alert with the provided message
 * 
 * @param string $message The message to display
 * @param bool $dismissible Whether the alert can be dismissed (default: true)
 * @return void
 */
function show_info_alert($message, $dismissible = true) {
    $dismiss_class = $dismissible ? 'alert-dismissible fade show' : '';
    $dismiss_button = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';
    
    echo '<div class="alert alert-info ' . $dismiss_class . '" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle me-2"></i>
                <div>' . htmlspecialchars($message) . '</div>
            </div>
            ' . $dismiss_button . '
          </div>';
}

/**
 * Display alerts from session variables and clear them
 * 
 * @return void
 */
function show_session_alerts() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['success_message'])) {
        show_success_alert($_SESSION['success_message']);
        unset($_SESSION['success_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        show_error_alert($_SESSION['error_message']);
        unset($_SESSION['error_message']);
    }
    
    if (isset($_SESSION['warning_message'])) {
        show_warning_alert($_SESSION['warning_message']);
        unset($_SESSION['warning_message']);
    }
    
    if (isset($_SESSION['info_message'])) {
        show_info_alert($_SESSION['info_message']);
        unset($_SESSION['info_message']);
    }
}