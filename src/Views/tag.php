<?php
/**
 * @var array $tag
 * @var array $posts
 */
$pageTitle = $tag['name'] . ' Images - imgdock';
$logoTitle = 'imgdock';
$isUploadPage = false;
require __DIR__ . '/layout/header.php';
use Helpers\Settings;
?>

<main
        class="container-fluid px-5 pb-4"
        style="margin-top: 56px;"
        id="tagContainer"
        data-tag-id="<?= htmlspecialchars($tag['id']) ?>"
>
    <div class="px-5">
        <div class="mb-5 position-relative">
            <div>
                <img
                        src="/api/images/<?= htmlspecialchars($tag['s3_key']) ?>"
                        alt="<?= htmlspecialchars($tag['name']) ?>"
                        class="page-img-cover-400"
                >
            </div>

            <div class="position-absolute top-50 start-50 translate-middle text-center w-100">
                <div class="container" style="max-width: 700px;">
                    <h1 class="display-4 fw-bold mb-3 text-white">
                        <?= htmlspecialchars($tag['name']) ?>
                    </h1>
                    <p class="lead text-white mb-0">
                        <?= nl2br(htmlspecialchars($tag['description'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <?php require __DIR__ . '/components/postGrid.php'; ?>
    </div>
</main>

<script type="module" src="<?= htmlspecialchars(Settings::env('VITE_BASE_URL')) ?>js/tag.js"></script>
