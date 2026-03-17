<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$stmt = $pdo->query("
    SELECT 
        posts.id,
        posts.title,
        posts.caption,
        posts.created_at,
        users.username,
        (
            SELECT image_path
            FROM images
            WHERE images.post_id = posts.id
            ORDER BY sort_order ASC, id ASC
            LIMIT 1
        ) AS cover_image
    FROM posts
    INNER JOIN users ON users.id = posts.user_id
    ORDER BY posts.created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <div class="eyebrow">Visual Journal</div>
            <h1>Quiet frames, intimate stories.</h1>
            <p>
                A minimalist photo gallery and short-form blog space for curated image series,
                calm editorial layouts, and personal storytelling.
            </p>
            <div class="button-row">
                <a href="/visionary/pages/register.php" class="btn">Start Creating</a>
                <a href="#latest" class="btn secondary">Explore Posts</a>
            </div>
        </div>

        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80" alt="Hero image">
        </div>
    </div>
</section>

<section class="section" id="latest">
    <div class="container">
        <div class="section-head">
            <div>
                <h2>Latest Posts</h2>
                <p>Photo stories with stronger focus on image series, captions, and clean spacing.</p>
            </div>
        </div>

        <?php if (!$posts): ?>
            <div class="empty-state">No posts yet. Create the first one from your dashboard.</div>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <a href="/visionary/pages/post.php?id=<?= (int)$post['id'] ?>">
                            <div class="post-card-media">
                                <img src="/visionary/<?= e($post['cover_image'] ?: 'assets/placeholder.svg') ?>" alt="<?= e($post['title']) ?>">
                            </div>
                            <div class="post-card-body">
                                <h3 class="post-card-title"><?= e($post['title']) ?></h3>
                                <div class="post-card-meta">
                                    by <?= e($post['username']) ?> · <?= date('M d, Y', strtotime($post['created_at'])) ?>
                                </div>
                                <p class="post-card-caption">
                                    <?= e(mb_strimwidth((string)$post['caption'], 0, 110, '...')) ?>
                                </p>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="footer-space"></div>
</body>
</html>
