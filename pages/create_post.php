<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$title = '';
$caption = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $caption = trim($_POST['caption'] ?? '');
    $files = normalizePostImagesArray($_FILES['images'] ?? []);

    if ($title === '') {
        $error = 'Title is required.';
    } elseif (!$files || !array_filter($files, fn($file) => ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE)) {
        $error = 'Please upload at least one image.';
    } else {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024;
        $uploadDir = __DIR__ . '/../uploads/';
        $savedCount = 0;

        try {
            $pdo->beginTransaction();

            $postStmt = $pdo->prepare('INSERT INTO posts (user_id, title, caption) VALUES (?, ?, ?)');
            $postStmt->execute([currentUserId(), $title, $caption]);
            $postId = $pdo->lastInsertId();

            foreach ($files as $index => $file) {
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
                    $imgStmt = $pdo->prepare('INSERT INTO images (post_id, image_path, sort_order) VALUES (?, ?, ?)');
                    $imgStmt->execute([$postId, $dbPath, $index]);
                    $savedCount++;
                }
            }

            if ($savedCount === 0) {
                throw new RuntimeException('No valid images were uploaded.');
            }

            $pdo->commit();
            setFlash('success', 'Post created successfully.');
            redirect('/visionary/pages/edit_post.php?id=' . $postId);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Something went wrong while creating the post.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel-shell">
    <div class="panel-card">
        <h1>Create Post</h1>
        <p class="form-note">
            Upload a photo series. The first image will be used as the cover on cards,
            and the full post page will display the whole series in a carousel.
        </p>

        <?php if ($error): ?>
            <div class="error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <label>Title</label>
                <input type="text" name="title" value="<?= e($title) ?>" required>
            </div>

            <div class="form-row">
                <label>Mini blog / caption</label>
                <textarea name="caption" placeholder="Write a short story, observation, or feeling..."><?= e($caption) ?></textarea>
            </div>

            <div class="form-row">
                <label>Upload images</label>
                <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple required>
                <div class="upload-hint">Select multiple images at once. Supported: JPG, PNG, WEBP.</div>
            </div>

            <button type="submit">Publish Post</button>
        </form>
    </div>
</div>

</body>
</html>
