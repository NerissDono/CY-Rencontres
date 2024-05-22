<?php 

session_start();

//affectation des champs publics du profil

$pseudo = $_SESSION['id'];
$password = $_SESSION['password'];
$birthdate = $_SESSION['birthdate'];
$gender = $_SESSION['gender'];
if (isset($_SESSION['height'])) { $hegiht = $_SESSION['height']; }
if (isset($_SESSION['bio'])) { $bio = $_SESSION['bio']; }

//affectation des champs privés du profil

$lastname = $_SESSION['lastname'];
$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Utilisateur</title>
</head>
<body>
    
    <header>
        <h1>Bienvenue sur notre site de rencontres</h1>
    </header>

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
                    <input type="text" id="sexe" name="sexe" value="<?php echo htmlspecialchars($gender);?>"disabled><br>
                    <label for="naissance">Date de naissance:</label>
                    <input type="text" id="naissance" name="naissance" value="<?php echo htmlspecialchars($birthdate);?>" disabled><br>
                    <label for="taille">Taille:</label>
                    <input type="text" id="taille" name="taille" disabled><br>
                    <label for="bio">Bio:</label>
                    <textarea id="bio" name="bio" disabled></textarea><br>
                    <input type="button" value="Modifier" onclick="modifierProfil('publicForm')">
                    <input type="submit" value="Enregistrer" style="display: none;">
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

        <section id="recherche">
            <h2>Recherche de Profils</h2>
            <form action="#" method="get">
                <label for="keywords">Mots-clés :</label>
                <input type="text" id="keywords" name="keywords"><br>
                <input type="submit" value="Rechercher">
            </form>
            <div id="resultats">
                <!-- Affichage des résultats de la recherche -->
            </div>
        </section>

        <section id="abonnements">
            <h2>Offres d'Abonnement</h2>
            <ul>
                <li>Abonnement Mensuel: 8€/mois</li>
                <li>Abonnement Trimestriel: 20€/trimestre</li>
                <li>Abonnement Annuel: 50€/an</li>
                <li>Version d'Essai: 1.50€ pour 24h d'accès complet</li>
            </ul>
            <button onclick="choisirAbonnement()">Choisir un Abonnement</button>
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

        function sauvegarderProfil(formId) {
            // Insérer ici la logique pour sauvegarder les modifications du profil
            // Une fois les modifications sauvegardées, désactivez les champs et réinitialisez le bouton
            document.querySelectorAll(`#${formId} input, #${formId} textarea`).forEach(input => {
                input.disabled = true;
            });
            document.querySelector(`#${formId} input[type="button"]`).style.display = "inline-block";
            document.querySelector(`#${formId} input[type="submit"]`).style.display = "none";
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