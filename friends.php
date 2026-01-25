<?php
// Denna sida visar användarens vänner, vänförfrågningar och deras senaste betyg.
// Funktioner:
// - Visa accepterade vänner (tvåvägsrelationer)
// - Hantera inkommande vänförfrågningar (acceptera/avslå)
// - Visa vännernas senaste betyg (med titel, affisch och betyg)
// - Möjlighet att skicka vänförfrågan genom att ange ett användarnamn
// Kräver att användaren är inloggad
require_once "models/session_user.php";
requireLoginOrRedirect();

require_once "models/db.php";



$userId = $_SESSION['user_id'];
$pdo = getPDO();

// Hämta accepterade vänner (båda håll)
$stmt = $pdo->prepare("
    SELECT u.id, u.username
    FROM users u
    JOIN friends f ON (u.id = f.friend_id AND f.user_id = ?) OR (u.id = f.user_id AND f.friend_id = ?)
    WHERE f.status = 'accepted' AND u.id != ?
    GROUP BY u.id
");
$stmt->execute([$userId, $userId, $userId]);
$friends = $stmt->fetchAll();

// Hämta vänförfrågningar (där jag är mottagare och status = pending)
$stmtReq = $pdo->prepare("
    SELECT f.user_id AS requester_id, u.username AS requester_name, f.requested_at
    FROM friends f
    JOIN users u ON f.user_id = u.id
    WHERE f.friend_id = ? AND f.status = 'pending'
    ORDER BY f.requested_at DESC
");
$stmtReq->execute([$userId]);
$friendRequests = $stmtReq->fetchAll();

// Hämta vänners betyg (samma som tidigare)
$friendsRatings = [];
if (!empty($friends)) {
    $friendIds = implode(',', array_column($friends, 'id'));
    $stmtRatings = $pdo->prepare("
        SELECT r.*, m.title, m.poster_url, r.user_id
        FROM ratings r
        JOIN media m ON r.movie_id = m.id
        WHERE r.user_id IN ($friendIds)
        ORDER BY r.date_rated DESC
        LIMIT 30
    ");
    $stmtRatings->execute();
    $friendsRatings = $stmtRatings->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8" />
    <title>FRDb – Friends</title>
    <link rel="stylesheet" href="css/topbar.css" />
    <link rel="stylesheet" href="css/friends.css" />
    <script src="JS/friends.js"></script>
</head>
<body>

<?php include 'views/topbar.php'; ?>

<main class="friends-container">
    <h1>Your Friends</h1>

    <?php if (count($friendRequests) > 0): ?>
        <section class="friend-requests">
            <h2>Friend Requests</h2>
            <ul>
                <?php foreach ($friendRequests as $req): ?>
                    <li>
                        <?= htmlspecialchars($req['requester_name']) ?> 
                        <form method="POST" action="processes/respond_friend_request.php" style="display:inline;">
                            <input type="hidden" name="requester_id" value="<?= $req['requester_id'] ?>">
                            <button type="submit" name="action" value="accept">Accept</button>
                            <button type="submit" name="action" value="decline">Decline</button>
                        </form>

                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if (count($friends) > 0): ?>
        <ul class="friend-list">
            <?php foreach ($friends as $friend): ?>
                <li><?= htmlspecialchars($friend['username']) ?></li>
            <?php endforeach; ?>
        </ul>

        <h2>Recent Ratings by Friends</h2>
        <?php if (!empty($friendsRatings)): ?>
            <div class="friends-ratings-grid">
                <?php foreach ($friendsRatings as $rating): ?>
                    <div class="rating-card">
                        <img src="<?= htmlspecialchars($rating['poster_url']) ?>" alt="<?= htmlspecialchars($rating['title']) ?>" class="rating-poster" />
                        <div class="rating-info">
                            <strong><?= htmlspecialchars($rating['title']) ?></strong>
                            <div>Rating: <?= htmlspecialchars($rating['score']) ?>/10</div>
                            <div>
                              By: 
                              <?php
                                // Hitta vännamnet från ID
                                $friendUsernames = array_column($friends, 'username', 'id');
                                echo htmlspecialchars($friendUsernames[$rating['user_id']] ?? 'Unknown');
                              ?>
                            </div>
                        
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No recent ratings by your friends.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>You have no friends yet. Find and add some!</p>
    <?php endif; ?>

    <section class="add-friend-section">
        <h2>Add a Friend</h2>
        <form method="POST" action="processes/add_friend.php">
            <label for="friend_username">Friend's Username:</label>
            <input type="text" id="friend_username" name="friend_username" required />
            <button type="submit">Send Friend Request</button>
        </form>
    </section>
</main>

</body>
</html>
