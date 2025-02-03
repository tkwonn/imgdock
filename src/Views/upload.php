<?php
/**
 * @var array $tags
 */
$pageTitle = 'imgdock: Free Image Hosting Service';
$logoTitle = 'imgdock';
$isUploadPage = true;
require __DIR__ . '/layout/header.php';
use Helpers\Settings;

?>

<main style="margin-top: 56px; flex: 1; display: flex;">
    <div class="container my-auto" style="max-width: 800px;">
        <div class="row w-100">
            <div class="col-12">
                <div class="card bg-gray">
                    <div class="card-body p-4">
                        <div class="row">
                            <!-- File Upload Area -->
                            <div class="col-12 mb-3">
                                <div id="uppy" class="w-100"></div>
                            </div>

                            <!-- Input Forms & Buttons -->
                            <div class="col-12" id="detailsStep">
                                <form id="postDetailsForm">
                                    <!-- Tags -->
                                    <div class="mb-3">
                                        <label class="form-label text-light">Add Tags</label>
                                        <div id="selectedTagsContainer" class="d-flex flex-wrap align-items-center gap-2 mb-2"></div>
                                        <div class="dropdown">
                                            <button
                                                    class="btn btn-outline-secondary rounded text-light"
                                                    type="button"
                                                    id="showTagsDropdownBtn"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false"
                                                    style="position: relative;"
                                            >
                                                <i class="bi bi-plus me-1"></i> Tag
                                            </button>
                                            <ul
                                                    class="dropdown-menu"
                                                    aria-labelledby="showTagsDropdownBtn"
                                                    id="tagsDropdown"
                                            >
                                                <?php foreach ($tags as $tag): ?>
                                                    <li>
                                                        <button
                                                                type="button"
                                                                class="dropdown-item tag-list-item"
                                                                data-tag-id="<?= $tag['id'] ?>"
                                                                data-tag-name="<?= htmlspecialchars($tag['name']) ?>"
                                                        >
                                                            <?= htmlspecialchars($tag['name']) ?>
                                                        </button>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Title & Description -->
                                    <div class="mb-3">
                                        <label for="title" class="form-label text-light">
                                            Title
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control bg-dark text-light border-0" id="title" name="title" maxlength="100" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label text-light">Description</label>
                                        <textarea class="form-control bg-dark text-light border-0" id="description" name="description" rows="3"></textarea>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" id="cancelButton">Cancel</button>
                                        <button type="submit" class="btn btn-success" id="submitButton" disabled>Post</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Upload Status Overlay -->
<div id="uploadStatus" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-90 d-none" style="z-index: 1050">
    <div class="position-absolute top-50 start-50 translate-middle text-center text-light">
        <div id="spinner" class="spinner-border mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h5 id="statusText">Processing...</h5>
    </div>
</div>

<script type="module" src="<?= htmlspecialchars(Settings::env('VITE_BASE_URL')) ?>js/upload.js"></script>