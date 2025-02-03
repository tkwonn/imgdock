<?php
/**
 * @var array $posts
 */
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <!-- Sort Options -->
    <div class="position-relative d-inline-block">
        <select
            id="sortSelect"
            class="form-select bg-gray text-light border-0 btn-custom-select pe-5"
            aria-label="Sort Select"
        >
            <option value="newest" selected>NEWEST</option>
            <option value="popular">POPULAR</option>
        </select>
        <i class="bi bi-chevron-down text-light position-absolute top-50 end-0 translate-middle-y me-3" style="filter: drop-shadow(1px 0 currentColor); cursor:pointer;"></i>
    </div>

    <!-- Grid or Waterfall -->
    <div class="btn-group">
        <button type="button" class="btn bg-gray text-light btn-view active" data-view="grid" title="Grid view">
            <i class="bi bi-grid-3x3"></i>
        </button>
        <button type="button" class="btn bg-gray text-light btn-view" data-view="waterfall" title="Waterfall view">
            <i class="bi bi-columns-gap"></i>
        </button>
    </div>
</div>

<!-- Post Grid -->
<div id="postsContainer">
    <div id="postsGrid" class="mb-5 grid-view">
        <div class="grid-sizer"></div>
        <?php foreach ($posts as $post): ?>
            <div class="grid-item">
                <div class="card bg-gray">
                    <a href="<?= htmlspecialchars($post['post_url']) ?>" class="text-decoration-none">
                        <img src="/api/images/<?= htmlspecialchars($post['s3_key']) ?>"
                             class="card-img-top card-img-cover-300"
                             alt="<?= htmlspecialchars($post['title']) ?>">
                        <div class="card-body p-2">
                            <div class="card-title">
                                <h3 class="fs-6 text-light p-2"><?= htmlspecialchars($post['title']) ?></h3>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="muted-text">
                                    <i class="bi bi-eye"></i> <?= number_format($post['view_count']) ?>
                                </small>
                                <small class="muted-text">
                                    <?= htmlspecialchars($post['created_at']) ?>
                                </small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- End of Content Message -->
    <div id="endOfContent" class="text-center text-light" style="display: none;">
        End of content
    </div>
</div>

<!-- Loading Spinner -->
<div id="loadingSpinner" class="text-center my-3">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>