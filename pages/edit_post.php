<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

$postStmt = $pdo->prepare('SELECT * FROM posts WHERE id = ? AND user_id = ?');
$postStmt->execute([$postId, currentUserId()]);
$post = $postStmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    setFlash('error', 'Post not found or access denied.');
    redirect('/visionary/pages/profile.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $caption = trim($_POST['caption'] ?? '');
    $deleteImageIds = $_POST['delete_images'] ?? [];
    $files = normalizePostImagesArray($_FILES['new_images'] ?? []);

    if ($title === '') {
        $error = 'Title is required.';
    } else {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024;
        $uploadDir = __DIR__ . '/../uploads/';

        try {
            $pdo->beginTransaction();

            $updateStmt = $pdo->prepare('UPDATE posts SET title = ?, caption = ? WHERE id = ? AND user_id = ?');
            $updateStmt->execute([$title, $caption, $postId, currentUserId()]);

            if ($deleteImageIds) {
                $deleteImageIds = array_map('intval', $deleteImageIds);
                $placeholders = implode(',', array_fill(0, count($deleteImageIds), '?'));
                $params = array_merge([$postId], $deleteImageIds);

                $imgReadStmt = $pdo->prepare("SELECT id, image_path FROM images WHERE post_id = ? AND id IN ($placeholders)");
                $imgReadStmt->execute($params);
                $imagesToDelete = $imgReadStmt->fetchAll(PDO::FETCH_ASSOC);

                if ($imagesToDelete) {
                    $imgDeleteStmt = $pdo->prepare("DELETE FROM images WHERE post_id = ? AND id IN ($placeholders)");
                    $imgDeleteStmt->execute($params);
                    foreach ($imagesToDelete as $imageRow) {
                        removeImageFile($imageRow['image_path']);
                    }
                }
            }

            $sortStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) FROM images WHERE post_id = ?');
            $sortStmt->execute([$postId]);
            $nextSort = ((int)$sortStmt->fetchColumn()) + 1;

            foreach ($files as $file) {
                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }

                if (($file['size'] ?? 0) > $maxSize) {
                    continue;
                }

                $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExtensions, true)) {
                    continue;
                }

                $newName = uniqid('img_', true) . '.' . $ext;
                $destination = $uploadDir . $newName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $dbPath = 'uploads/' . $newName;
                    $imgInsertStmt = $pdo->prepare('INSERT INTO images (post_id, image_path, sort_order) VALUES (?, ?, ?)');
                    $imgInsertStmt->execute([$postId, $dbPath, $nextSort]);
                    $nextSort++;
                }
            }

            $countStmt = $pdo->prepare('SELECT COUNT(*) FROM images WHERE post_id = ?');
            $countStmt->execute([$postId]);
            if ((int)$countStmt->fetchColumn() === 0) {
                throw new RuntimeException('A post must have at least one image.');
            }

            $pdo->commit();
            setFlash('success', 'Post updated successfully.');
            redirect('/visionary/pages/edit_post.php?id=' . $postId);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage() === 'A post must have at least one image.'
                ? 'A post must have at least one image.'
                : 'Something went wrong while updating the post.';
        }
    }

    $postStmt->execute([$postId, currentUserId()]);
    $post = $postStmt->fetch(PDO::FETCH_ASSOC);
}

$imgStmt = $pdo->prepare('SELECT * FROM images WHERE post_id = ? ORDER BY sort_order ASC, id ASC');
$imgStmt->execute([$postId]);
$images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel-shell wide-shell">
    <div class="panel-card">
        <div class="profile-top">
            <div>
                <h1>Edit Post</h1>
                <p class="form-note">Update the title, caption, remove old images, or add new ones.</p>
            </div>
            <div class="button-row compact">
                <a class="btn secondary" href="/visionary/pages/post.php?id=<?= (int)$postId ?>">View Post</a>
                <a class="btn secondary" href="/visionary/pages/profile.php">Back to Profile</a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <label>Title</label>
                <input type="text" name="title" value="<?= e($post['title']) ?>" required>
            </div>

            <div class="form-row">
                <label>Mini blog / caption</label>
                <textarea name="caption"><?= e($post['caption']) ?></textarea>
            </div>

            <div class="form-row">
                <label>Current Images</label>
                <div class="manage-image-grid">
                    <?php foreach ($images as $image): ?>
                        <label class="manage-image-card">
                            <img src="/visionary/<?= e($image['image_path']) ?>" alt="Post image">
                            <span class="checkbox-row">
                                <input type="checkbox" name="delete_images[]" value="<?= (int)$image['id'] ?>">
                                Remove this image
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="upload-hint">Keep at least one image on the post.</div>
            </div>

            <div class="form-row">
                <label>Add More Images</label>
                <input type="file" name="new_images[]" accept=".jpg,.jpeg,.png,.webp" multiple>
            </div>

            <div class="button-row compact">
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
