<?php
require_once 'db.php';
require_once 'auth.php';
require_auth();

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $post_id = filter_var($_POST['post_id'] ?? null, FILTER_VALIDATE_INT);
    $content = trim($_POST['content'] ?? '');

    if ($post_id && !empty($content)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$post_id, $_SESSION['user_id'], $content]);

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }
} 

elseif ($action === 'edit') {
    $comment_id = filter_var($_POST['comment_id'] ?? null, FILTER_VALIDATE_INT);
    $content    = trim($_POST['content'] ?? '');

    if ($comment_id && !empty($content)) {
        try {
            $pdo->beginTransaction();

            // Prepared Statement 1: Verify ownership
            $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            $comment = $stmt->fetch();

            if ($comment && $comment['user_id'] == $_SESSION['user_id']) {
                // Prepared Statement 2: Update content
                $update = $pdo->prepare("UPDATE comments SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $update->execute([$content, $comment_id]);
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
    $comment_id = filter_var($_POST['comment_id'] ?? null, FILTER_VALIDATE_INT);

    if ($comment_id) {
        try {
            $pdo->beginTransaction();

            // Prepared Statement 1: Verify ownership
            $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            $comment = $stmt->fetch();

            if ($comment && $comment['user_id'] == $_SESSION['user_id']) {
                // Prepared Statement 2: Delete comment
                $delete = $pdo->prepare("DELETE FROM comments WHERE id = ?");
                $delete->execute([$comment_id]);
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