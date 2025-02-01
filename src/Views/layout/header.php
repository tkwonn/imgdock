<?php
/**
 * @var string $pageTitle
 * @var string $logoTitle
 * @var bool   $isUploadPage
 */
use Helpers\Settings;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href="<?= htmlspecialchars(Settings::env('VITE_BASE_URL')) ?>css/app.css" rel="stylesheet">
    <?php if ($isUploadPage): ?>
        <link href="<?= htmlspecialchars(Settings::env('VITE_BASE_URL')) ?>css/upload.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body style="min-height: 100vh; display: flex; flex-direction: column;">
<nav class="navbar navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a class="navbar-brand fw-bold px-5 m-0" href="/"><?php echo htmlspecialchars($logoTitle); ?></a>
            <?php if (!$isUploadPage): ?>
                <a href="/upload" id="uploadBtn" class="btn btn-success text-light">
                    <i class="fas fa-plus"></i> New post
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>