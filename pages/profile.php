<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$userId = currentUserId();

$userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

$postStmt = $pdo->prepare("
    SELECT 
        posts.id,
        posts.title,
        posts.caption,
        posts.created_at,
        (
            SELECT image_path
            FROM images
            WHERE images.post_id = posts.id
            ORDER BY sort_order ASC, id ASC
            LIMIT 1
        ) AS cover_image
    FROM posts
    WHERE posts.user_id = ?
    ORDER BY posts.created_at DESC
");
$postStmt->execute([$userId]);
$posts = $postStmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="profile-wrap">
    <div class="profile-card">
        <div class="profile-top">
            <div>
                <div class="eyebrow">Profile</div>
                <h1 class="profile-title"><?= e($user['username']) ?></h1>
                <div class="profile-sub"><?= e($user['email']) ?></div>
            </div>

            <div>
                <a href="/visionary/pages/create_post.php" class="btn">New Post</a>
            </div>
        </div>
    </div>

    <div class="profile-card">
        <div class="section-head">
            <div>
                <h2>Your Posts</h2>
                <p>Your visual archive, now with edit and delete actions.</p>
            </div>
        </div>

        <?php if (!$posts): ?>
            <div class="empty-state">You have not published any posts yet.</div>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card managed-card">
                        <a href="/visionary/pages/post.php?id=<?= (int)$post['id'] ?>">
                            <div class="post-card-media">
                                <img src="/visionary/<?= e($post['cover_image'] ?: 'assets/placeholder.svg') ?>" alt="<?= e($post['title']) ?>">
                            </div>
                            <div class="post-card-body">
                                <h3 class="post-card-title"><?= e($post['title']) ?></h3>
                                <div class="post-card-meta"><?= date('M d, Y', strtotime($post['created_at'])) ?></div>
                                <p class="post-card-caption"><?= e(mb_strimwidth((string)$post['caption'], 0, 100, '...')) ?></p>
                            </div>
                        </a>

                        <div class="card-actions">
                            <a class="btn secondary small" href="/visionary/pages/edit_post.php?id=<?= (int)$post['id'] ?>">Edit</a>
                            <form method="POST" action="/visionary/pages/delete_post.php" onsubmit="return confirm('Delete this post and all its images?');">
                                <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                <button type="submit" class="btn danger small">Delete</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
