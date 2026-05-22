<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';

$pdo = getPDO();

$search   = trim($_GET['search']   ?? '');
$location = trim($_GET['location'] ?? '');
$access   = $_GET['access']        ?? '';
$sort     = $_GET['sort']          ?? 'date_asc';

$where  = ["v.status = 'published'"];
$params = [];

if ($search !== '') {
    $where[]  = 'v.title LIKE ?';
    $params[] = '%' . $search . '%';
}

if ($location !== '') {
    $where[]  = 'v.location LIKE ?';
    $params[] = '%' . $location . '%';
}

if ($access === '1' || $access === '0') {
    $where[]  = 'v.is_public = ?';
    $params[] = (int)$access;
}

$order_map = [
    'date_asc'     => 'v.start_at ASC',
    'date_desc'    => 'v.start_at DESC',
    'rating_desc'  => 'v.average_rating DESC',
    'participants' => 'v.participants_count DESC',
];
$order = $order_map[$sort] ?? 'v.start_at ASC';

$sql  = 'SELECT v.*, u.first_name, u.last_name FROM view_events_summary v ';
$sql .= 'INNER JOIN users u ON u.id = v.organizer_id ';
$sql .= 'WHERE ' . implode(' AND ', $where) . ' ';
$sql .= 'ORDER BY ' . $order;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

function renderStars(float $rating): string {
    $html = '<div class="noteEvent">';
    for ($i = 1; $i <= 5; $i++) {
        $color = $i <= round($rating) ? '#facc15' : '#ccc';
        $html .= '<span class="star" style="color: ' . $color . ';">★</span>';
    }
    $html .= '</div>';
    return $html;
}

function formatDate(string $datetime): string {
    return date('d/m/Y', strtotime($datetime));
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Événements – VentRush</title>
</head>

<body>
    <header>
        <h1>VentRush</h1>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="events.php">Événements</a></li>
                <li><a href="create-event.php">Créer un événement</a></li>
                <li><a href="account.php">Compte</a></li>
                <li><a href="about.php">À propos</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="main-content main-content-wide">
            <h2><strong>Tous les événements</strong></h2>
            <br>

            <form class="events-filters" method="get" action="">
                <div class="filter-group">
                    <input
                        type="text"
                        name="search"
                        placeholder="Rechercher un événement..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="filter-group">
                    <input
                        type="text"
                        name="location"
                        placeholder="Lieu..."
                        value="<?= htmlspecialchars($location) ?>">
                </div>

                <div class="filter-group">
                    <select name="access">
                        <option value=""  <?= $access === ''  ? 'selected' : '' ?>>Tous les accès</option>
                        <option value="1" <?= $access === '1' ? 'selected' : '' ?>>Publique</option>
                        <option value="0" <?= $access === '0' ? 'selected' : '' ?>>Privé</option>
                    </select>
                </div>

                <div class="filter-group">
                    <select name="sort">
                        <option value="date_asc"     <?= $sort === 'date_asc'     ? 'selected' : '' ?>>Date ↑</option>
                        <option value="date_desc"    <?= $sort === 'date_desc'    ? 'selected' : '' ?>>Date ↓</option>
                        <option value="rating_desc"  <?= $sort === 'rating_desc'  ? 'selected' : '' ?>>Mieux notés</option>
                        <option value="participants" <?= $sort === 'participants' ? 'selected' : '' ?>>Plus populaires</option>
                    </select>
                </div>

                <button type="submit" class="btn-filter">Filtrer</button>
                <a href="events.php" class="btn-reset">Réinitialiser</a>
            </form>

            <p class="events-count"><?= count($events) ?> événement<?= count($events) !== 1 ? 's' : '' ?> trouvé<?= count($events) !== 1 ? 's' : '' ?></p>
            <br>

            <?php if (empty($events)): ?>
                <p>Aucun événement ne correspond à votre recherche.</p>
            <?php else: ?>
                <section class="events-grid">
                    <?php foreach ($events as $event): ?>
                        <figure id="event">
                            <img id="miniatureEvent" src="<?= $event['thumbnail'] ? htmlspecialchars($event['thumbnail']) : './images/fraise.jpg' ?>">
                            <figcaption id="informationsEvent">
                                <p id="nomEvent"><strong>Nom</strong> : <?= htmlspecialchars($event['title']) ?></p>
                                <p id="nombreParticipantsEvent"><strong>Participants</strong> : <?= (int)$event['participants_count'] ?>/<?= (int)$event['capacity'] ?></p>
                                <p id="lieuEvent"><strong>Lieu</strong> : <?= htmlspecialchars($event['location']) ?></p>
                                <p id="accesEvent"><span id="acces"><?= $event['is_public'] ? 'Publique' : 'Privé' ?></span></p>
                            </figcaption>

                            <nav id="actionsEvent">
                                <p id="dateEvent"><?= formatDate($event['start_at']) ?></p>

                                <?= renderStars((float)$event['average_rating']) ?>

                                <div id="buttonDetaisEvent" onclick="window.location.href='event.php?id=<?= (int)$event['id'] ?>'">
                                    <span>Détails</span>
                                </div>

                                <?php if ($event['organizer_id'] !== $_SESSION['user_id']): ?>
                                    <div id="buttonParticiperEvent" onclick="window.location.href='join-event.php?id=<?= (int)$event['id'] ?>'">
                                        <span>Participer</span>
                                    </div>
                                <?php endif; ?>
                            </nav>
                        </figure>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>2026, VentRush - Marwan, Tom, Aleksandr</p>
    </footer>
</body>

</html>