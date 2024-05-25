<?php
session_start();
include_once '../../src/bin/utilitaries/getRecentProfiles.php';

/*include_once '../../src/bin/utilitaries/searchUsername.php';

// Vérification des paramètres de la requête GET
if (isset($_GET['username'])) {
    // Désactiver la sortie des erreurs pour éviter les espaces ou les messages d'erreur avant les headers
    ob_start();

    $username = trim($_GET['username']);
    if ($username !== '') {
        $searchDir = '../../data/users'; // Répertoire de base pour la recherche
        $matchedProfiles = searchUsernameInProfiles($searchDir, $username);
        header('Content-Type: application/json');
        echo json_encode($matchedProfiles);
    } else {
        echo json_encode([]);
    }

    // Nettoyer le tampon de sortie et désactiver la capture
    ob_end_flush();
    exit; // Assurez-vous de terminer le script après l'envoi de la réponse JSON
}*/

// Affectation des champs publics du profil
$pseudo = $_SESSION['id'];
$password = $_SESSION['password'];
$birthdate = $_SESSION['birthdate'];
$gender = $_SESSION['gender'];
if (isset($_SESSION['height'])) { $height = $_SESSION['height']; }
if (isset($_SESSION['bio'])) { $bio = $_SESSION['bio']; }

// Affectation des champs privés du profil
$lastname = $_SESSION['lastname'];
$name = $_SESSION['name'];

// Récupération des profils les plus récents
$recentProfiles = getRecentProfiles(__DIR__ . '/../../data/users');

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Utilisateur</title>
    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="../../src/element/header.css">
</head>
<body>
    <?php
        include ('../../src/element/header.html');
    ?>
    <main>
        <section id="profil">
            <h2>Mon Profil</h2>
            <div id="publicSection">
                <h3>Informations Publiques</h3>
                <form id="publicForm">
                    <img id="logo" src="../../data/img/logo.png" alt="mehdi"/><br>
                    <label for="pseudo">Pseudonyme:</label>
                    <input type="text" id="pseudo" name="pseudo" value="<?php echo htmlspecialchars($pseudo); ?>" disabled><br>
                    <label for="sexe">Genre</label>
                    <input type="text" id="sexe" name="sexe" value="<?php echo htmlspecialchars($gender);?>" disabled><br>
                    <label for="naissance">Date de naissance:</label>
                    <input type="text" id="naissance" name="naissance" value="<?php echo htmlspecialchars($birthdate);?>" disabled><br>
                    <label for="taille">Taille (cm):</label>
                    <input type="number" id="taille" name="taille" disabled><br>
                    <label for="bio">Bio (Décrivez-vous) :</label>
                    <textarea id="bio" name="bio" disabled><?php echo htmlspecialchars($bio); ?></textarea><br>
                    <input type="button" value="Modifier" onclick="modifierProfil('publicForm')">
                    <input type="submit" value="Enregistrer" name="editPublic" style="display: none;">
                </form>
            </div>
            <div id="privateSection" style="display: none;">
                <h3>Informations Privées</h3>
                <form id="privateForm">
                    <label for="lastname">Nom:</label>
                    <input type="text" id="nom" name="lastname" value="<?php echo htmlspecialchars($lastname)?>" disabled><br>
                    <label for="name">Prénom:</label>
                    <input type="text" id="prénom" name="name" value="<?php echo htmlspecialchars($name)?>" disabled><br>
                    <label for="password">Mot de Passe:</label>
                    <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password)?>" disabled><br>
                    <input type="button" value="Modifier" onclick="modifierProfil('privateForm')">
                    <input type="submit" value="Enregistrer les informations privées" style="display: none;">
                </form>
            </div>
            <button onclick="togglePrivateSection()">Afficher les Informations Privées</button>
        </section>

        <section id="recentProfiles">
            <h2>Profils les Plus Récents</h2>
            <div id="recentProfilesContainer">
                <?php foreach ($recentProfiles as $profile): ?>
                    <div class="profile-box">
                        <strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($profile['username']); ?><br>
                        <strong>Genre :</strong> <?php echo htmlspecialchars($profile['gender']); ?><br>
                        <strong>Année de naissance :</strong> <?php echo htmlspecialchars($profile['year']); ?><br>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="placeholder">
            <p>Section de messages privés (à venir)</p>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 CY Meet</p>
    </footer>

    <script>
        function modifierProfil(formId) {
            // Activer les champs pour permettre la modification
            document.querySelectorAll(`#${formId} input, #${formId} textarea`).forEach(input => {
                input.disabled = false;
            });
            // Modifier le texte du bouton
            document.querySelector(`#${formId} input[type="button"]`).style.display = "none";
            document.querySelector(`#${formId} input[type="submit"]`).style.display = "inline-block";
            // Ajouter un événement pour enregistrer les modifications
            document.getElementById(formId).addEventListener('submit', function(event) {
                event.preventDefault();
                sauvegarderProfil(formId);
            });
        }

        function togglePrivateSection() {
            var privateSection = document.getElementById('privateSection');
            var button = document.querySelector('#profil button');
            
            if (privateSection.style.display === 'none') {
                privateSection.style.display = 'block';
                button.textContent = 'Masquer les Informations Privées';
            } else {
                privateSection.style.display = 'none';
                button.textContent = 'Afficher les Informations Privées';
            }
        }
    </script>
</body>
</html>
