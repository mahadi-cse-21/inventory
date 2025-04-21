<?php
class UtilityHelper {
    /**
     * Format date in the specified format
     */
    public static function formatDate($date, $format = 'Y-m-d') {
        if (empty($date)) return '';
        $dateObj = new DateTime($date);
        return $dateObj->format($format);
    }
    
    /**
     * Format date for display (more human readable)
     */
    public static function formatDateForDisplay($date, $format = 'long') {
        if (empty($date)) return '';
        
        $dateObj = new DateTime($date);
        
        switch ($format) {
            case 'long':
                return $dateObj->format('F j, Y');
            case 'short':
                return $dateObj->format('M j, Y');
            case 'datetime':
                return $dateObj->format('M j, Y g:i A');
            default:
                return $dateObj->format('Y-m-d');
        }
    }
    
    /**
     * Format currency
     */
    public static function formatCurrency($amount, $symbol = '$') {
        return $symbol . number_format($amount, 2);
    }
    
    /**
     * Generate a unique ID with prefix
     */
    public static function generateUniqueId($prefix = '', $length = 8) {
        $uniqueId = uniqid();
        $randomStr = bin2hex(random_bytes(4));
        $uniqueId = $prefix . substr($uniqueId . $randomStr, 0, $length);
        return $uniqueId;
    }
    
    /**
     * Calculate date difference in days
     */
    public static function dateDiffInDays($startDate, $endDate) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $diff = $start->diff($end);
        return $diff->days;
    }
    
    /**
     * Paginate results
     */
    public static function paginate($totalItems, $currentPage = 1, $itemsPerPage = ITEMS_PER_PAGE) {
        $totalPages = ceil($totalItems / $itemsPerPage);
        $currentPage = max(1, min($currentPage, $totalPages > 0 ? $totalPages : 1));
        
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        return [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'itemsPerPage' => $itemsPerPage,
            'totalItems' => $totalItems,
            'offset' => $offset,
            'hasNextPage' => $currentPage < $totalPages,
            'hasPreviousPage' => $currentPage > 1
        ];
    }
    
    /**
     * Generate pagination HTML
     */
    public static function paginationLinks($pagination, $baseUrl) {
        $html = '<div class="pagination">';
        
        // Previous button
        if ($pagination['hasPreviousPage']) {
            $prevPage = $pagination['currentPage'] - 1;
            $html .= '<a href="' . $baseUrl . '?page=' . $prevPage . '" class="page-btn"><i class="fas fa-angle-left"></i></a>';
        } else {
            $html .= '<span class="page-btn disabled"><i class="fas fa-angle-left"></i></span>';
        }
        
        // Page numbers
        $startPage = max(1, $pagination['currentPage'] - 2);
        $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $pagination['currentPage']) {
                $html .= '<span class="page-btn active">' . $i . '</span>';
            } else {
                $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="page-btn">' . $i . '</a>';
            }
        }
        
        // Next button
        if ($pagination['hasNextPage']) {
            $nextPage = $pagination['currentPage'] + 1;
            $html .= '<a href="' . $baseUrl . '?page=' . $nextPage . '" class="page-btn"><i class="fas fa-angle-right"></i></a>';
        } else {
            $html .= '<span class="page-btn disabled"><i class="fas fa-angle-right"></i></span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Upload file
     */
    public static function uploadFile($file, $destinationPath, $allowedTypes = [], $maxFileSize = MAX_FILE_SIZE) {
        // Create directory if it doesn't exist
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        
        // Validate file type if specified
        if (!empty($allowedTypes) && !in_array($file['type'], $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)
            ];
        }
        
        // Validate file size if specified
        if ($maxFileSize > 0 && $file['size'] > $maxFileSize) {
            return [
                'success' => false,
                'message' => 'File size exceeds the maximum allowed size.'
            ];
        }
        
        // Generate unique filename
        $filename = pathinfo($file['name'], PATHINFO_FILENAME);
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueFilename = $filename . '_' . uniqid() . '.' . $extension;
        $targetFile = $destinationPath . '/' . $uniqueFilename;
        
        // Upload file
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return [
                'success' => true,
                'filename' => $uniqueFilename,
                'path' => $targetFile
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to upload file.'
            ];
        }
    }
    
    /**
     * Sanitize filename
     */
    public static function sanitizeFilename($filename) {
        // Remove any character that is not a-z, A-Z, 0-9, dash, underscore or dot
        $filename = preg_replace('/[^\w\-\.]/', '', $filename);
        // Remove any trailing dots
        $filename = preg_replace('/\.+$/', '', $filename);
        return $filename;
    }
    
    /**
     * Log activity
     */
    public static function logActivity($userId, $action, $entityType = null, $entityId = null, $description = null) {
        $conn = getDbConnection();
        
        $sql = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        return $conn->lastInsertId();
    }
    
    /**
     * Check if a value exists in database
     */
    public static function valueExists($table, $column, $value, $excludeId = null, $idColumn = 'id') {
        $conn = getDbConnection();
        
        $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
        $params = [$value];
        
        if ($excludeId !== null) {
            $sql .= " AND $idColumn != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn() > 0;
    }
}