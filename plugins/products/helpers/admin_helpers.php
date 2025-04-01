<?php
/**
 * Funções auxiliares para ações administrativas
 */

/**
 * Log an admin activity
 * 
 * @param string $activity_type Type of activity
 * @param string $details Details of the activity
 * @return bool Success status
 */
function log_admin_activity($activity_type, $details) {
    try {
        // Verifique se existe a função para obter o ID do admin
        $admin_id = function_exists('get_current_admin_id') ? get_current_admin_id() : 
                   (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0);
        
        if ($admin_id <= 0) {
            return false; // Não há admin logado
        }
        
        $pdo = getConnection();
        
        // Verifique se a tabela existe
        $stmt = $pdo->prepare("
            SELECT 1 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
            AND table_name = 'admin_activity_logs'
        ");
        $stmt->execute();
        
        if ($stmt->fetchColumn()) {
            // Tabela existe, registre a atividade
            $stmt = $pdo->prepare("
                INSERT INTO admin_activity_logs (
                    admin_id, 
                    activity_type, 
                    details, 
                    ip_address, 
                    user_agent, 
                    activity_time
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $admin_id,
                $activity_type,
                $details,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'],
                date('Y-m-d H:i:s')
            ]);
            
            return true;
        }
        
        return false; // Tabela não existe
        
    } catch (Exception $e) {
        error_log('Error logging admin activity: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get current admin ID from session
 * 
 * @return int Admin ID or 0 if not logged in
 */
function get_current_admin_id() {
    return isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;
}