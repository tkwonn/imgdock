<?php
/**
 * @var array $posts
 * @var array $tags
 */
$pageTitle = 'imgdock: Free Image Hosting Service';
$logoTitle = 'imgdock';
$isUploadPage = false;
require __DIR__ . '/layout/header.php';
use Helpers\Settings;
?>

<main class="container-fluid px-5 py-4" style="margin-top: 56px; color: white;">
    <div class="px-5">
        <!-- EXPLORE TAGS -->
        <div class="mb-5">
            <h2 class="fs-6 text-light mb-3">EXPLORE TAGS</h2>
            <div class="tag-scroll-container position-relative">
                <div id="tagContainer" class="d-flex gap-3" style="overflow-x: auto;">
                    <?php foreach ($tags as $tag): ?>
                        <div style="min-width: 200px;">
                            <div class="card bg-gray h-100">
                                <a href="<?= htmlspecialchars($tag['page_url']) ?>" class="text-decoration-none">
                                    <img
                                            src="/api/images/<?= htmlspecialchars($tag['s3_key']) ?>"
                                            loading="lazy"
                                            class="card-img-top card-img-cover-100 mb-1"
                                            alt="<?= htmlspecialchars($tag['name']) ?>"
                                    >
                                    <div class="card-body p-2">
                                        <h3 class="card-title mb-1 text-light fs-6"><?= htmlspecialchars($tag['name']) ?></h3>
                                        <small class="muted-text"><?= htmlspecialchars($tag['post_count']) ?> Posts</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Left Arrow -->
                <button class="scroll-arrow scroll-arrow-left translate-middle-y bg-transparent border-0 text-light">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <!-- Right Arrow -->
                <button class="scroll-arrow scroll-arrow-right translate-middle-y bg-transparent border-0 text-light">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <?php require __DIR__ . '/components/postGrid.php'; ?>
    </div>
</main>

<script type="module" src="<?= htmlspecialchars(Settings::env('VITE_BASE_URL')) ?>js/home.js"></script>