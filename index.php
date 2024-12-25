<?php
require 'includes/db.php'; // Include database connection

// Fetch all posts
$postQuery = "SELECT * FROM posts ORDER BY created_at DESC";
$postResult = $conn->query($postQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blogging Website</title>
    <link rel="stylesheet" href="style.css"> <!-- Include your CSS file -->
    <style>
        .comment {
            position: relative;
            padding: 10px;
            margin: 10px 0;
            background-color: #f9f9f9;
        }

        .comment:hover .delete-btn {
            display: inline;
        }

        .delete-btn {
            display: none;
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: red;
            color: white;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Blog</h1>

        <!-- Loop through each post -->
        <?php while ($post = $postResult->fetch_assoc()): ?>
            <div class="post">
                <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>

                <!-- Display Post Media -->
                <?php if (!empty($post['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" style="max-width:100%; height:auto;">
                <?php endif; ?>
                <?php if (!empty($post['video'])): ?>
                    <video controls style="max-width:100%;">
                        <source src="uploads/<?php echo htmlspecialchars($post['video']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>

                <!-- Like Section -->
                <?php
                $postId = $post['id'];
                $ipAddress = $_SERVER['REMOTE_ADDR'];

                // Check if the user has liked the post
                $likeQuery = "SELECT * FROM likes WHERE post_id = $postId AND ip_address = '$ipAddress'";
                $likeResult = $conn->query($likeQuery);
                $isLiked = $likeResult->num_rows > 0;

                // Count total likes
                $likeCountQuery = "SELECT COUNT(*) AS total_likes FROM likes WHERE post_id = $postId";
                $likeCountResult = $conn->query($likeCountQuery);
                $likeCount = $likeCountResult->fetch_assoc()['total_likes'];

                // Ensure the number displays like "1K" for 1000 likes
                $formattedLikes = ($likeCount >= 1000) ? number_format($likeCount / 1000, 1) . 'K' : $likeCount;
                ?>

                <form action="like.php" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                    <button type="submit">
                        <?php echo $isLiked ? 'Unlike' : 'Like'; ?>
                    </button>
                    <span><?php echo $formattedLikes; ?> Likes</span> <!-- Display likes -->
                </form>

                <!-- Comments Section -->
                <div class="comments">
                    <h4>Comments</h4>
                    <?php
                    $commentQuery = "SELECT * FROM comments WHERE post_id = $postId ORDER BY created_at DESC";
                    $commentResult = $conn->query($commentQuery);
                    while ($comment = $commentResult->fetch_assoc()):
                        $commenter = !empty($comment['guest_name']) ? $comment['guest_name'] : 'Anonymous';
                        $commentId = $comment['id'];
                    ?>
                        <div class="comment">
                            <p><strong><?php echo htmlspecialchars($commenter); ?>:</strong> <?php echo htmlspecialchars($comment['content']); ?></p>

                            <!-- Delete Button (Only visible on hover) -->
                            <form action="delete_comment.php" method="POST" class="delete-btn">
                                <input type="hidden" name="comment_id" value="<?php echo $commentId; ?>">
                                <button type="submit" style="background-color: red; color: white;">Delete</button>
                            </form>
                        </div>
                    <?php endwhile; ?>

                    <!-- Comment Form -->
                    <form action="comment.php" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                        <input type="text" name="guest_name" placeholder="Your name (optional)">
                        <textarea name="content" placeholder="Write a comment..." required></textarea>
                        <button type="submit">Post Comment</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
