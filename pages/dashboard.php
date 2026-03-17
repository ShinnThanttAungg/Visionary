<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$userId = currentUserId();

$userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE user_id = ?');
$countStmt->execute([$userId]);
$postCount = (int)$countStmt->fetchColumn();

$recentStmt = $pdo->prepare("
    SELECT 
        posts.id,
        posts.title,
        posts.created_at,
        (
            SELECT image_path FROM images WHERE images.post_id = posts.id ORDER BY sort_order ASC, id ASC LIMIT 1
        ) AS cover_image
    FROM posts
    WHERE posts.user_id = ?
    ORDER BY posts.created_at DESC
    LIMIT 3
");
$recentStmt->execute([$userId]);
$recentPosts = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel-shell">
    <div class="panel-card">
        <div class="dashboard-grid">
            <div>
                <h1>Hello, <?= e($user['username']) ?></h1>
                <p class="form-note">
                    Your dashboard should stay focused on content creation. It now gives you
                    quick access to create, manage, edit, and delete your posts.
                </p>

                <div class="stat-box">
                    <h3><?= $postCount ?></h3>
                    <div class="profile-sub">Total posts published</div>
                </div>
            </div>

            <div>
                <div class="stat-box">
                    <h3>Quick actions</h3>
                    <p class="profile-sub">Create a new post or manage existing ones from your profile page.</p>
                    <div class="button-row compact">
                        <a class="btn" href="/visionary/pages/create_post.php">Create New Post</a>
                        <a class="btn secondary" href="/visionary/pages/profile.php">Manage Posts</a>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($recentPosts): ?>
            <div class="section-block">
                <div class="section-head compact-head">
                    <div>
                        <h2>Recent Posts</h2>
                        <p>Your latest uploads at a glance.</p>
                    </div>
                </div>

                <div class="gallery-grid">
                    <?php foreach ($recentPosts as $post): ?>
                        <article class="post-card">
                            <a href="/visionary/pages/post.php?id=<?= (int)$post['id'] ?>">
                                <div class="post-card-media">
                                    <img src="/visionary/<?= e($post['cover_image'] ?: 'assets/placeholder.svg') ?>" alt="<?= e($post['title']) ?>">
                                </div>
                                <div class="post-card-body">
                                    <h3 class="post-card-title"><?= e($post['title']) ?></h3>
                                    <div class="post-card-meta"><?= date('M d, Y', strtotime($post['created_at'])) ?></div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
