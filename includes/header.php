<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    <!-- External CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/inventory-styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <?php if (isset($extraCSS)): ?>
        <?php foreach ($extraCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo ASSETS_URL . '/css/' . $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <style>
    /* Flash Message Styling */
    .flash-message {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        align-items: center;
        max-width: 400px;
        padding: 16px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        animation: flash-slide-in 0.4s ease-out forwards;
        transition: opacity 0.3s, transform 0.3s;
    }

    .flash-message.dismissing {
        opacity: 0;
        transform: translateX(30px);
    }

    .flash-icon {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 14px;
        font-size: 20px;
    }

    .flash-content {
        flex-grow: 1;
        font-size: 14px;
        line-height: 1.5;
    }

    .flash-close {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        background: transparent;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 10px;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s, transform 0.2s;
    }

    .flash-close:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    /* Flash types */
    .flash-success {
        background-color: #ecfdf5;
        border-left: 4px solid #10b981;
        color: #065f46;
    }

    .flash-success .flash-icon {
        color: #10b981;
    }

    .flash-error, .flash-danger {
        background-color: #fef2f2;
        border-left: 4px solid #ef4444;
        color: #b91c1c;
    }

    .flash-error .flash-icon, .flash-danger .flash-icon {
        color: #ef4444;
    }

    .flash-warning {
        background-color: #fffbeb;
        border-left: 4px solid #f59e0b;
        color: #b45309;
    }

    .flash-warning .flash-icon {
        color: #f59e0b;
    }

    .flash-info {
        background-color: #eff6ff;
        border-left: 4px solid #3b82f6;
        color: #1e40af;
    }

    .flash-info .flash-icon {
        color: #3b82f6;
    }

    /* Animation */
    @keyframes flash-slide-in {
        0% {
            transform: translateX(100%);
            opacity: 0;
        }
        100% {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Progress bar */
    .flash-message::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: rgba(255, 255, 255, 0.5);
        width: 100%;
        transform-origin: left;
        animation: flash-progress 5s linear forwards;
    }

    @keyframes flash-progress {
        0% {
            transform: scaleX(1);
        }
        100% {
            transform: scaleX(0);
        }
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .flash-message {
            top: 10px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
    }
</style>


</head>
<body <?php echo isset($bodyClass) ? 'class="' . $bodyClass . '"' : ''; ?>>
    <div class="container">
        <!-- Sidebar -->
        <?php if (!isset($hideSidebar) || !$hideSidebar): ?>
            <?php include 'includes/sidebar.php'; ?>
        <?php endif; ?>
        
        <!-- Main Content -->
        <div class="main <?php echo isset($hideSidebar) && $hideSidebar ? 'full-width' : ''; ?>">
            <!-- Top Bar -->
            <?php if (!isset($hideTopbar) || !$hideTopbar): ?>
                <?php include 'includes/topbar.php'; ?>
            <?php endif; ?>
            
          <!-- Flash Messages -->
<?php $flashMessage = getFlashMessage(); ?>
<?php if ($flashMessage): ?>
    <div id="flash-message" class="flash-message flash-<?php echo $flashMessage['type']; ?>" data-auto-dismiss>
        <div class="flash-icon">
            <?php if ($flashMessage['type'] === 'success'): ?>
                <i class="fas fa-check-circle"></i>
            <?php elseif ($flashMessage['type'] === 'error' || $flashMessage['type'] === 'danger'): ?>
                <i class="fas fa-exclamation-circle"></i>
            <?php elseif ($flashMessage['type'] === 'warning'): ?>
                <i class="fas fa-exclamation-triangle"></i>
            <?php else: ?>
                <i class="fas fa-info-circle"></i>
            <?php endif; ?>
        </div>
        <div class="flash-content">
            <?php echo $flashMessage['message']; ?>
        </div>
        <button class="flash-close" onclick="dismissFlash()">
            <i class="fas fa-times"></i>
        </button>
    </div>
<?php endif; ?>
            
            <!-- Content Container -->
            <div class="content">