<?php
/**
 * @var array $post
 */
$pageTitle = $post['title'];
$logoTitle = 'imgdock';
$isUploadPage = false;
require __DIR__ . '/layout/header.php';
use Helpers\Settings;

?>

<main class="post-layout">
    <div class="post-wrapper"
         id="postTitle"
         data-post-title="<?= htmlspecialchars($post['title']) ?>"
    >
        <div class="post-container">
            <div class="position-relative">
                <a href="/" class="btn btn-outline-light btn-sm back-button">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div class="card bg-gray">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="action-buttons">
                                <a href="/api/images/<?= htmlspecialchars($post['s3_key']) ?>"
                                   download="<?= htmlspecialchars($post['title'] . '.' . $post['extension']) ?>"
                                   class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-download"></i>
                                </a>
                                <button type="button" class="btn btn-outline-light btn-sm" data-action="share-x">
                                    <i class="bi bi-twitter-x"></i>
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm" data-action="copy-url">
                                    <i class="bi bi-link-45deg"></i>
                                </button>
                            </div>

                            <div class="post-content">
                                <h1 class="h5 mb-1 text-light"><?= htmlspecialchars($post['title']) ?></h1>
                                <div class="post-meta muted-text mb-3">
                                    <?= number_format($post['view_count']) ?> Views
                                    • <?= htmlspecialchars($post['created_at']) ?>
                                    • <?= htmlspecialchars($post['size']) ?>
                                </div>

                                <div class="post-img-container">
                                    <img src="/api/images/<?= htmlspecialchars($post['s3_key']) ?>"
                                         alt="<?= htmlspecialchars($post['title']) ?>"
                                         class="img-fluid rounded"
                                    >
                                </div>

                                <?php if (!empty($post['description'])): ?>
                                    <div class="text-light">
                                        <?= nl2br(htmlspecialchars($post['description'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Toast Notification -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
    <div id="toast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                Link copied to clipboard!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script type="module" src="<?= htmlspecialchars(Settings::env('VITE_BASE_URL')) ?>js/post.js"></script>