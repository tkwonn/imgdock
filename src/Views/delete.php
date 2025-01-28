<?php
/**
 * @var string $title The title of the post
 */
$pageTitle = 'Post Deleted - imgdock';
$logoTitle = 'imgdock';
$isUploadPage = false;
require __DIR__ . '/layout/header.php';
?>

<div class="container min-vh-100 d-flex align-items-center py-5" style="max-width: 800px;">
    <div class="row w-100">
        <div class="col-12">
            <div class="card bg-gray">
                <div class="card-body p-4">
                    <div class="text-center">
                        <h1 class="h4 mb-4 text-light">Post Deleted Successfully</h1>

                        <div class="alert alert-success d-inline-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-check2-circle me-2"></i>
                            Your image "<?= htmlspecialchars($title) ?>" has been deleted.
                        </div>

                        <div>
                            <a href="/" class="btn btn-success">
                                Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

