<?php
require_once 'db.php';
require_once 'auth.php';
require_auth();

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $content = trim($_POST['content'] ?? '');
    if (!empty($content)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO posts (content, created_by) VALUES (?, ?)");
            $stmt->execute([$content, $_SESSION['user_id']]);

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }
} 

elseif ($action === 'edit') {
    $post_id = filter_var($_POST['post_id'] ?? null, FILTER_VALIDATE_INT);
    $content = trim($_POST['content'] ?? '');

    if ($post_id && !empty($content)) {
        try {
            $pdo->beginTransaction();

    
            $stmt = $pdo->prepare("SELECT created_by FROM posts WHERE id = ?");
            $stmt->execute([$post_id]);
            $post = $stmt->fetch();

            if ($post && $post['created_by'] == $_SESSION['user_id']) {
                $update = $pdo->prepare("UPDATE posts SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $update->execute([$content, $post_id]);
            }

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }
} 

elseif ($action === 'delete') {
    $post_id = filter_var($_POST['post_id'] ?? null, FILTER_VALIDATE_INT);

    if ($post_id) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT created_by FROM posts WHERE id = ?");
            $stmt->execute([$post_id]);
            $post = $stmt->fetch();

            if ($post && $post['created_by'] == $_SESSION['user_id']) {
                $delete = $pdo->prepare("DELETE FROM posts WHERE id = ?");
                $delete->execute([$post_id]);
            }

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }
}

header("Location: index.php");
exit;