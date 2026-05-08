<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
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
                <figure id="event">
                    <img id="miniatureEvent" src="./images/fraise.jpg">
                    <figcaption id="informationsEvent">
                        <p id="nomEvent"><strong>Nom</strong> : Exemple</p>
                        <p id="nombreParticipantsEvent"><strong>Participants</strong> : 25/68</p>
                        <p id="lieuEvent"><strong>Lieu</strong> : Tomsk</p>
                        <p id="accesEvent"><span id="acces">Publique</span></p>
                    </figcaption>

                    <nav id="actionsEvent">
                        <p id="dateEvent">24/04/2026</p>

                        <!-- mettre les étoiles ici -->
                        <div class="noteEvent">
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                        </div>

                        <div id="buttonModifierEvent" onclick="window.location.href='index.html'">
                            <span>Modifier</span>
                        </div>

                        <div id="buttonSupprimerEvent" onclick="window.location.href='index.html'">
                            <span>Supprimer</span>
                        </div>
                    </nav>
                </figure>

                <figure id="event">
                    <img id="miniatureEvent" src="./images/fraise.jpg">
                    <figcaption id="informationsEvent">
                        <p id="nomEvent"><strong>Nom</strong> : Exemple</p>
                        <p id="nombreParticipantsEvent"><strong>Participants</strong> : 25/68</p>
                        <p id="lieuEvent"><strong>Lieu</strong> : Tomsk</p>
                        <p id="accesEvent"><span id="acces">Publique</span></p>
                    </figcaption>
                    
                    <nav id="actionsEvent">
                        <p id="dateEvent">24/04/2026</p>

                        <!-- mettre les étoiles ici -->
                        <div class="noteEvent">
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                        </div>

                        <div id="buttonModifierEvent" onclick="window.location.href='index.html'">
                            <span>Modifier</span>
                        </div>

                        <div id="buttonSupprimerEvent" onclick="window.location.href='index.html'">
                            <span>Supprimer</span>
                        </div>
                    </nav>
                </figure>


            </section>
            <br><br>
            <h3>Evénements inscrits</h3><br>
            <section class="inscrits">
                <figure id="event">
                    <img id="miniatureEvent" src="./images/fraise.jpg">
                    <figcaption id="informationsEvent">
                        <p id="nomEvent"><strong>Nom</strong> : Exemple</p>
                        <p id="nombreParticipantsEvent"><strong>Participants</strong> : 25/68</p>
                        <p id="lieuEvent"><strong>Lieu</strong> : Tomsk</p>
                        <p id="accesEvent"><span id="acces">Publique</span></p>
                    </figcaption>

                    <nav id="actionsEvent">

                        <p id="dateEvent">24/04/2026</p>
                        <div class="noteEvent">
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                            <span class="star" data-rating="1" style="color: #facc15;">★</span>
                        </div>
                        <!-- mettre les étoiles ici -->

                        <!-- <div id="buttonParticiperEvent" onclick="window.location.href='index.html'">
                            <span>Participer</span>
                        </div> -->

                        <div id="buttonDetaisEvent" onclick="window.location.href='index.html'">
                            <span>Détails</span>
                        </div>

                        <div id="buttonQuitterEvent" onclick="window.location.href='index.html'">
                            <span>Quitter</span>
                        </div>

                    </nav>
                </figure>
            </section>
        </div>
    </main>
    <footer>
        <p>2026, VentRush - Marwan, Tom, Aleksandr</p>
    </footer>
</body>

</html>