<?php
require_once 'db.php';
require_once 'auth.php';
require_auth();

// Fetch posts sorted by newest first
$posts_stmt = $pdo->query("
    SELECT p.*, u.first_name, u.last_name 
    FROM posts p 
    JOIN users u ON p.created_by = u.id 
    ORDER BY p.created_at DESC
");
$posts = $posts_stmt->fetchAll();

// Fetch all comments mapped by post_id
$comments_stmt = $pdo->query("
    SELECT c.*, u.first_name, u.last_name 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    ORDER BY c.created_at ASC
");
$all_comments = $comments_stmt->fetchAll();

$comments_by_post = [];
foreach ($all_comments as $comment) {
    $comments_by_post[$comment['post_id']][] = $comment;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>News Feed</title>
    <style>
        .post { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .comment { background: #f9f9f9; padding: 8px; margin: 5px 0 5px 20px; border-left: 3px solid #007bff; }
        .edited-tag { color: #888; font-size: 0.85em; font-style: italic; }
        .author { font-weight: bold; }
        .time { color: #555; font-size: 0.85em; }
    </style>
</head>
<body>
    <header>
        <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
        <a href="logout.php">Logout</a>
    </header>
    <hr>

    <h3>Create a Post</h3>
    <form method="POST" action="post_actions.php">
        <input type="hidden" name="action" value="create">
        <textarea name="content" rows="3" cols="50" required placeholder="What's on your mind?"></textarea><br>
        <button type="submit">Publish Post</button>
    </form>

    <hr>
    <h3>News Feed</h3>

    <?php foreach ($posts as $post): ?>
        <div class="post">
            <span class="author"><?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></span>
            <span class="time">• <?= $post['created_at'] ?></span>
            
            <?php if (!empty($post['updated_at'])): ?>
                <span class="edited-tag">(Edited)</span>
            <?php endif; ?>

            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

            <!-- Post Controls for Author -->
            <?php if ($post['created_by'] == $_SESSION['user_id']): ?>
                <button onclick="toggleElement('edit-post-<?= $post['id'] ?>')">Edit Post</button>
                <form method="POST" action="post_actions.php" style="display:inline;" onsubmit="return confirm('Delete this post?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <button type="submit">Delete</button>
                </form>

                <div id="edit-post-<?= $post['id'] ?>" style="display:none; margin-top:10px;">
                    <form method="POST" action="post_actions.php">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <textarea name="content" rows="2" cols="40" required><?= htmlspecialchars($post['content']) ?></textarea><br>
                        <button type="submit">Save Edit</button>
                    </form>
                </div>
            <?php endif; ?>

            <hr>
            <h4>Comments</h4>
            
            <?php if (isset($comments_by_post[$post['id']])): ?>
                <?php foreach ($comments_by_post[$post['id']] as $comment): ?>
                    <div class="comment">
                        <span class="author"><?= htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']) ?></span>
                        <span class="time">• <?= $comment['created_at'] ?></span>
                        
                        <?php if (!empty($comment['updated_at'])): ?>
                            <span class="edited-tag">(Edited)</span>
                        <?php endif; ?>

                        <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>

                        <!-- Comment Controls for Author -->
                        <?php if ($comment['user_id'] == $_SESSION['user_id']): ?>
                            <button onclick="toggleElement('edit-comment-<?= $comment['id'] ?>')">Edit</button>
                            <form method="POST" action="comment_actions.php" style="display:inline;" onsubmit="return confirm('Delete this comment?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                <button type="submit">Delete</button>
                            </form>

                            <div id="edit-comment-<?= $comment['id'] ?>" style="display:none; margin-top:5px;">
                                <form method="POST" action="comment_actions.php">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <textarea name="content" rows="2" cols="30" required><?= htmlspecialchars($comment['content']) ?></textarea><br>
                                    <button type="submit">Update Comment</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Add Comment Form -->
            <form method="POST" action="comment_actions.php" style="margin-top:10px; margin-left:20px;">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <input type="text" name="content" placeholder="Write a comment..." required style="width:70%;">
                <button type="submit">Comment</button>
            </form>
        </div>
    <?php endforeach; ?>

    <script>
    function toggleElement(id) {
        const el = document.getElementById(id);
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    }
    </script>
</body>
</html>