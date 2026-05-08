<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';

$error   = '';
$success = '';
$values  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [
        'title'       => trim($_POST['title']       ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'location'    => trim($_POST['location']    ?? ''),
        'start_date'  => $_POST['start_date']       ?? '',
        'start_time'  => $_POST['start_time']       ?? '',
        'end_date'    => $_POST['end_date']          ?? '',
        'end_time'    => $_POST['end_time']          ?? '',
        'capacity'    => $_POST['capacity']          ?? '',
        'is_public'   => $_POST['is_public']         ?? '1',
        'status'      => $_POST['status']            ?? 'published',
    ];

    if ($values['title'] === '' || $values['location'] === '' || $values['start_date'] === '' || $values['start_time'] === '') {
        $error = 'Veuillez remplir les champs obligatoires.';
    } elseif ((int)$values['capacity'] < 1) {
        $error = 'La capacité doit être supérieure à 0.';
    } else {
        $start_at = $values['start_date'] . ' ' . $values['start_time'] . ':00';
        $end_at   = ($values['end_date'] !== '' && $values['end_time'] !== '')
                    ? $values['end_date'] . ' ' . $values['end_time'] . ':00'
                    : null;

        // slug : title → lowercase, tirets
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $values['title']));
        $slug = trim($slug, '-');

        // upload miniature
        $thumbnail = null;
        if (!empty($_FILES['thumbnail']['name'])) {
            $ext       = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            $allowed   = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Format d\'image non supporté (jpg, jpeg, png, webp).';
            } elseif ($_FILES['thumbnail']['size'] > 5 * 1024 * 1024) {
                $error = 'L\'image ne doit pas dépasser 5 Mo.';
            } else {
                $upload_dir = __DIR__ . '/images/events/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $filename  = uniqid('event_') . '.' . $ext;
                move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_dir . $filename);
                $thumbnail = 'images/events/' . $filename;
            }
        }

        if ($error === '') {
            try {
                $pdo = getPDO();

                // unicité du slug par organisateur
                $check = $pdo->prepare('SELECT id FROM events WHERE organizer_id = ? AND slug = ?');
                $check->execute([$_SESSION['user_id'], $slug]);
                if ($check->fetch()) {
                    $slug .= '-' . time();
                }

                $pdo->prepare(
                    'INSERT INTO events (organizer_id, title, slug, thumbnail, description, start_at, end_at, location, capacity, is_public, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                )->execute([
                    $_SESSION['user_id'],
                    $values['title'],
                    $slug,
                    $thumbnail,
                    $values['description'],
                    $start_at,
                    $end_at,
                    $values['location'],
                    (int)$values['capacity'],
                    (int)$values['is_public'],
                    $values['status'],
                ]);

                $success = 'Événement créé avec succès !';
                $values  = [];
            } catch (PDOException $e) {
                $error = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Créer un événement – VentRush</title>
</head>

<body>
    <header>
        <h1>VentRush</h1>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="account.php">Compte</a></li>
                <li><a href="about.php">À propos</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="main-content">
            <h2><strong>Créer un événement</strong></h2>
            <br>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form class="event-form" method="post" action="" enctype="multipart/form-data" novalidate>

                <div class="form-group">
                    <label for="title">Nom de l'événement <span class="required">*</span></label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($values['title'] ?? '') ?>"
                        placeholder="Ex : Tournoi de foot"
                        required
                        autofocus>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="Décrivez votre événement..."><?= htmlspecialchars($values['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="location">Lieu <span class="required">*</span></label>
                    <input
                        type="text"
                        id="location"
                        name="location"
                        value="<?= htmlspecialchars($values['location'] ?? '') ?>"
                        placeholder="Ex : Genève, Parc des Bastions"
                        required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Date de début <span class="required">*</span></label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="<?= htmlspecialchars($values['start_date'] ?? '') ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="start_time">Heure de début <span class="required">*</span></label>
                        <input
                            type="time"
                            id="start_time"
                            name="start_time"
                            value="<?= htmlspecialchars($values['start_time'] ?? '') ?>"
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="end_date">Date de fin</label>
                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="<?= htmlspecialchars($values['end_date'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="end_time">Heure de fin</label>
                        <input
                            type="time"
                            id="end_time"
                            name="end_time"
                            value="<?= htmlspecialchars($values['end_time'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="capacity">Capacité max <span class="required">*</span></label>
                        <input
                            type="number"
                            id="capacity"
                            name="capacity"
                            value="<?= htmlspecialchars($values['capacity'] ?? '') ?>"
                            min="1"
                            placeholder="Ex : 50"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="is_public">Accès</label>
                        <select id="is_public" name="is_public">
                            <option value="1" <?= ($values['is_public'] ?? '1') === '1' ? 'selected' : '' ?>>Publique</option>
                            <option value="0" <?= ($values['is_public'] ?? '1') === '0' ? 'selected' : '' ?>>Privé</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Statut</label>
                        <select id="status" name="status">
                            <option value="published" <?= ($values['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Publié</option>
                            <option value="draft"     <?= ($values['status'] ?? 'published') === 'draft'     ? 'selected' : '' ?>>Brouillon</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="thumbnail">Miniature (jpg, png, webp – max 5 Mo)</label>
                        <input type="file" id="thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                </div>

                <div class="event-form-actions">
                    <a href="index.php" class="btn-secondary">Annuler</a>
                    <button type="submit" class="btn-primary">Créer l'événement</button>
                </div>

            </form>
        </div>
    </main>

    <footer>
        <p>2026, VentRush - Marwan, Tom, Aleksandr</p>
    </footer>
</body>

</html>
