<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$postStmt = $pdo->prepare("
    SELECT posts.*, users.username
    FROM posts
    INNER JOIN users ON users.id = posts.user_id
    WHERE posts.id = ?
");
$postStmt->execute([$postId]);
$post = $postStmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die('Post not found.');
}

$imgStmt = $pdo->prepare("
    SELECT *
    FROM images
    WHERE post_id = ?
    ORDER BY sort_order ASC, id ASC
");
$imgStmt->execute([$postId]);
$images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

$isOwner = isLoggedIn() && (int)$post['user_id'] === (int)currentUserId();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="post-detail-wrap">
    <div class="post-detail-grid">
        <div>
            <div class="carousel" data-carousel>
                <?php foreach ($images as $index => $image): ?>
                    <div class="slide<?= $index === 0 ? ' active' : '' ?>">
                        <img src="/visionary/<?= e($image['image_path']) ?>" alt="Post image <?= $index + 1 ?>">
                    </div>
                <?php endforeach; ?>

                <?php if (count($images) > 1): ?>
                    <div class="carousel-controls">
                        <button class="carousel-btn" type="button" data-prev>‹</button>
                        <button class="carousel-btn" type="button" data-next>›</button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (count($images) > 1): ?>
                <div class="thumb-row">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="thumb<?= $index === 0 ? ' active' : '' ?>">
                            <img src="/visionary/<?= e($image['image_path']) ?>" alt="Thumbnail <?= $index + 1 ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="post-side">
            <div class="eyebrow">Photo Series</div>
            <h1 class="post-page-title"><?= e($post['title']) ?></h1>
            <div class="post-author">by <?= e($post['username']) ?></div>
            <div class="post-date"><?= date('F d, Y', strtotime($post['created_at'])) ?></div>

            <div class="post-caption"><?= e($post['caption']) ?></div>

            <?php if ($isOwner): ?>
                <div class="button-row compact top-gap">
                    <a class="btn secondary" href="/visionary/pages/edit_post.php?id=<?= (int)$post['id'] ?>">Edit Post</a>
                    <form method="POST" action="/visionary/pages/delete_post.php" onsubmit="return confirm('Delete this post and all its images?');">
                        <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                        <button type="submit" class="btn danger">Delete Post</button>
                    </form>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</div>

</body>
</html>
