<?php
/**
 * Modals Component
 * 
 * Provides reusable modal dialog components for the admin plugin
 */

/**
 * Display a confirmation modal
 * 
 * @param string $id Modal ID (must be unique)
 * @param string $title Modal title
 * @param string $message Modal message/content
 * @param string $confirm_text Text for confirm button
 * @param string $cancel_text Text for cancel button
 * @param string $confirm_action JavaScript action on confirm
 * @param string $size Modal size ('sm', 'lg', 'xl' or empty for default)
 * @return void
 */
function confirmation_modal($id, $title, $message, $confirm_text = 'Confirm', $cancel_text = 'Cancel', $confirm_action = '', $size = '') {
    $size_class = !empty($size) ? " modal-{$size}" : "";
    $confirm_btn_class = strpos(strtolower($confirm_text), 'delete') !== false ? 'btn-danger' : 'btn-primary';
    
    echo '
    <div class="modal fade" id="' . $id . '" tabindex="-1" aria-labelledby="' . $id . 'Label" aria-hidden="true">
        <div class="modal-dialog' . $size_class . '">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="' . $id . 'Label">' . htmlspecialchars($title) . '</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ' . $message . '
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . htmlspecialchars($cancel_text) . '</button>
                    <button type="button" class="btn ' . $confirm_btn_class . '" ' . (!empty($confirm_action) ? 'onclick="' . $confirm_action . '"' : '') . '>' . htmlspecialchars($confirm_text) . '</button>
                </div>
            </div>
        </div>
    </div>';
}

/**
 * Display a form modal
 * 
 * @param string $id Modal ID (must be unique)
 * @param string $title Modal title
 * @param string $form_content HTML content for the form
 * @param string $form_action Form action URL
 * @param string $submit_text Text for submit button
 * @param string $cancel_text Text for cancel button
 * @param string $method Form method (GET/POST)
 * @param string $size Modal size ('sm', 'lg', 'xl' or empty for default)
 * @return void
 */
function form_modal($id, $title, $form_content, $form_action = '', $submit_text = 'Submit', $cancel_text = 'Cancel', $method = 'post', $size = '') {
    $size_class = !empty($size) ? " modal-{$size}" : "";
    
    echo '
    <div class="modal fade" id="' . $id . '" tabindex="-1" aria-labelledby="' . $id . 'Label" aria-hidden="true">
        <div class="modal-dialog' . $size_class . '">
            <div class="modal-content">
                <form action="' . $form_action . '" method="' . strtolower($method) . '">
                    <div class="modal-header">
                        <h5 class="modal-title" id="' . $id . 'Label">' . htmlspecialchars($title) . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ' . $form_content . '
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . htmlspecialchars($cancel_text) . '</button>
                        <button type="submit" class="btn btn-primary">' . htmlspecialchars($submit_text) . '</button>
                    </div>
                </form>
            </div>
        </div>
    </div>';
}

/**
 * Display a success modal
 * 
 * @param string $id Modal ID (must be unique)
 * @param string $title Modal title
 * @param string $message Success message/content
 * @param string $button_text Text for close button
 * @return void
 */
function success_modal($id, $title, $message, $button_text = 'Close') {
    echo '
    <div class="modal fade" id="' . $id . '" tabindex="-1" aria-labelledby="' . $id . 'Label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="' . $id . 'Label">' . htmlspecialchars($title) . '</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                    </div>
                    <div class="text-center">' . $message . '</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">' . htmlspecialchars($button_text) . '</button>
                </div>
            </div>
        </div>
    </div>';
}

/**
 * Display an error modal
 * 
 * @param string $id Modal ID (must be unique)
 * @param string $title Modal title
 * @param string $message Error message/content
 * @param string $button_text Text for close button
 * @return void
 */
function error_modal($id, $title, $message, $button_text = 'Close') {
    echo '
    <div class="modal fade" id="' . $id . '" tabindex="-1" aria-labelledby="' . $id . 'Label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="' . $id . 'Label">' . htmlspecialchars($title) . '</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-exclamation-circle text-danger" style="font-size: 64px;"></i>
                    </div>
                    <div class="text-center">' . $message . '</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . htmlspecialchars($button_text) . '</button>
                </div>
            </div>
        </div>
    </div>';
}