<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';

$pdo = getPDO();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT v.*, COALESCE(v.participants_count, 0) AS participants_count, COALESCE(v.average_rating, 0) AS average_rating
     FROM view_events_summary v
     WHERE v.organizer_id = ?
     ORDER BY v.start_at DESC'
);
$stmt->execute([$user_id]);
$organised_events = $stmt->fetchAll();

$stmt2 = $pdo->prepare(
    'SELECT v.*, ep.status AS participation_status, COALESCE(v.participants_count, 0) AS participants_count, COALESCE(v.average_rating, 0) AS average_rating
     FROM view_events_summary v
     INNER JOIN event_participants ep ON ep.event_id = v.id AND ep.user_id = ?
     WHERE v.organizer_id != ? AND ep.status = \'accepted\'
     ORDER BY v.start_at DESC'
);
$stmt2->execute([$user_id, $user_id]);
$registered_events = $stmt2->fetchAll();

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
    <title>VentRush</title>
</head>

<body>
    <header>
        <h1>VentRush</h1>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="create-event.php">Créer un événement</a></li>
                <li><a href="account.php">Compte</a></li>
                <li><a href="about.php">À propos</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="main-content">
            <h2><strong>Bienvenue, <?= htmlspecialchars($_SESSION['first_name']) ?></strong></h2>
            <br>
            <h3>Evénements organisés par vous</h3><br>
            <section class="organise">
                <?php if (empty($organised_events)): ?>
                    <p>Vous n'avez pas encore créé d'événement.</p>
                <?php else: ?>
                    <?php foreach ($organised_events as $event): ?>
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

                                <div id="buttonModifierEvent" onclick="window.location.href='edit-event.php?id=<?= (int)$event['id'] ?>'">
                                    <span>Modifier</span>
                                </div>

                                <div id="buttonSupprimerEvent" onclick="window.location.href='delete-event.php?id=<?= (int)$event['id'] ?>'">
                                    <span>Supprimer</span>
                                </div>
                            </nav>
                        </figure>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
            <br><br>
            <h3>Evénements inscrits</h3><br>
            <section class="inscrits">
                <?php if (empty($registered_events)): ?>
                    <p>Vous n'êtes inscrit à aucun événement.</p>
                <?php else: ?>
                    <?php foreach ($registered_events as $event): ?>
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

                                <div id="buttonQuitterEvent" onclick="window.location.href='leave-event.php?id=<?= (int)$event['id'] ?>'">
                                    <span>Quitter</span>
                                </div>
                            </nav>
                        </figure>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <footer>
        <p>2026, VentRush - Marwan, Tom, Aleksandr</p>
    </footer>
</body>

</html>
