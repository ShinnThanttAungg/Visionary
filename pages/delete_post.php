<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/visionary/pages/profile.php');
}

$postId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$postStmt = $pdo->prepare('SELECT id FROM posts WHERE id = ? AND user_id = ?');
$postStmt->execute([$postId, currentUserId()]);
$post = $postStmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    setFlash('error', 'Post not found or access denied.');
    redirect('/visionary/pages/profile.php');
}

try {
    $pdo->beginTransaction();

    $imgStmt = $pdo->prepare('SELECT image_path FROM images WHERE post_id = ?');
    $imgStmt->execute([$postId]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    $deleteImagesStmt = $pdo->prepare('DELETE FROM images WHERE post_id = ?');
    $deleteImagesStmt->execute([$postId]);

    $deletePostStmt = $pdo->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
    $deletePostStmt->execute([$postId, currentUserId()]);

    $pdo->commit();

    foreach ($images as $image) {
        removeImageFile($image['image_path']);
    }

    setFlash('success', 'Post deleted successfully.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlash('error', 'Unable to delete the post.');
}

redirect('/visionary/pages/profile.php');
