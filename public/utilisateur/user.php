<?php
session_start();
include_once '../../src/bin/utilitaries/getRecentProfiles.php';

// Chemin vers le fichier profile.txt et bio.txt
$profileFile = '../../data/users/' . $_SESSION['email'] . '/profile.txt';
$bioFile = '../../data/users/' . $_SESSION['email'] . '/bio.txt';
$profilePicDir = '../../data/users/' . $_SESSION['email'] . '/';
$profilePicFile = $profilePicDir . 'profile_pic';

// Lecture des informations du fichier profile.txt
if (!file_exists($profileFile)) {
    die('Fichier de profil non trouvé.');
}

$profileData = file($profileFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Lecture des informations du fichier bio.txt
if (!file_exists($bioFile)) {
    $bio = '';
} else {
    $bio = file_get_contents($bioFile);
}

// Charger les informations du profil dans les variables
$pseudo = $profileData[0];
$gender = $profileData[1];
$birthdate = $profileData[4];
$lastname = $profileData[2];
$name = $profileData[3];
$hashedPassword = $profileData[6];
$height = isset($profileData[7]) ? $profileData[7] : '';

// Convertir la date de naissance au format YYYY-MM-DD si elle n'est pas vide
if (!empty($birthdate)) {
    $birthdate = date('Y-m-d', strtotime($birthdate));
}

// Récupération des profils les plus récents
$recentProfiles = getRecentProfiles(__DIR__ . '/../../data/users');

// Initialiser le message d'erreur
$error = '';

// Traitement du formulaire si les données sont soumises via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['editPublic'])) {
        // Récupération des données du formulaire public
        $height = $_POST['taille'];
        $bio = $_POST['bio'];
        $birthdate = $_POST['naissance'];

        // Mise à jour du tableau avec les nouvelles informations
        $profileData[4] = $birthdate;
        $profileData[7] = $height;

        // Écriture des données dans le fichier profile.txt
        if (!is_writable($profileFile)) {
            die('Impossible d\'écrire dans le fichier de profil.');
        }

        $newProfileData = implode("\n", $profileData);
        file_put_contents($profileFile, $newProfileData);

        // Écriture de la biographie dans le fichier bio.txt
        if (!is_writable($bioFile)) {
            die('Impossible d\'écrire dans le fichier de biographie.');
        }

        file_put_contents($bioFile, str_replace("\r\n", "\n", $bio)); // Normaliser les sauts de ligne

        // Gestion du téléchargement de l'image
        if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] == UPLOAD_ERR_OK) {
            $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
            $fileType = $_FILES['profilePic']['type'];
            if (in_array($fileType, $allowedTypes)) {
                // Déplacer le fichier téléchargé vers le dossier utilisateur
                $extension = pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION);
                $destination = $profilePicFile . '.' . $extension;
                move_uploaded_file($_FILES['profilePic']['tmp_name'], $destination);

                // Supprimer les anciennes images de profil
                foreach (glob($profilePicFile . '.*') as $oldFile) {
                    if ($oldFile != $destination) {
                        unlink($oldFile);
                    }
                }
            } else {
                $error = 'Type de fichier non autorisé. Veuillez sélectionner une image (png, jpg, gif).';
            }
        }

        // Redirection pour éviter la resoumission du formulaire
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['editPrivate'])) {
        // Récupération des données du formulaire privé
        $lastname = $_POST['lastname'];
        $name = $_POST['name'];
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];

        // Vérifier le mot de passe actuel
        if (password_verify($currentPassword, $hashedPassword)) {
            // Mise à jour du tableau avec les nouvelles informations
            $profileData[2] = $lastname;
            $profileData[3] = $name;
            $profileData[4] = $birthdate; // Mettre à jour la date de naissance

            // Hacher le nouveau mot de passe avant de le stocker
            if (!empty($newPassword)) {
                $profileData[6] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            // Écriture des données dans le fichier profile.txt
            if (!is_writable($profileFile)) {
                die('Impossible d\'écrire dans le fichier de profil.');
            }

            $newProfileData = implode("\n", $profileData);
            file_put_contents($profileFile, $newProfileData);

            // Redirection pour éviter la resoumission du formulaire
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = 'Mot de passe actuel incorrect.';
        }
    }
}
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
        // si l'utilisateur connecté est l'admin alors il a accès au dashboard administrateur dans la navbar
        if ($_SESSION['email'] == "admin1@cupidquest.fr")
        {
            include('../../src/element/headerAdmin.html');
        }
        else
        {
            include('../../src/element/header.html');
        }
    ?>
    <main>
        <section id="profil">
            <h2>Mon Profil</h2>
            <div id="publicSection">
                <h3>Informations Publiques</h3>
                <form id="publicForm" method="post" enctype="multipart/form-data">
                    <label>Photo de profil:</label><br>
                    <?php
                        $userProfilePic = glob($profilePicFile . '.*');
                        if (!empty($userProfilePic)) {
                            $profilePic = $userProfilePic[0];
                            $profilePicUrl = '../../data/users/' . $_SESSION['email'] . '/' . basename($profilePic);
                            echo '<img id="profilePic" src="' . htmlspecialchars($profilePicUrl) . '" alt="Profile Picture" onerror="this.onerror=null;this.src=\'../../data/img/defaultpfp.jpeg\';" /><br>';
                        } else {
                            echo '<p id="noProfilePic">Aucune image de profil</p><br>';
                        }
                    ?>
                    <input type="file" id="profilePicUpload" name="profilePic" accept="image/png, image/jpeg, image/gif" style="display:none;"><br>
                    <label for="pseudo">Pseudonyme:</label>
                    <input type="text" id="pseudo" name="pseudo" value="<?php echo htmlspecialchars($pseudo); ?>" disabled><br>
                    <label for="sexe">Genre</label>
                    <input type="text" id="sexe" name="sexe" value="<?php echo htmlspecialchars($gender); ?>" disabled><br>
                    <label for="naissance">Date de naissance:</label>
                    <input type="date" id="naissance" name="naissance" value="<?php echo htmlspecialchars($birthdate); ?>" disabled><br>
                    <label for="taille">Taille (cm):</label>
                    <input type="number" id="taille" name="taille" value="<?php echo htmlspecialchars($height); ?>" disabled><br>
                    <label for="bio">Bio (Décrivez-vous) :</label>
                    <textarea id="bio" name="bio" disabled><?php echo htmlspecialchars($bio); ?></textarea><br>
                    <input type="button" value="Modifier" onclick="modifierProfil('publicForm')">
                    <input type="submit" value="Enregistrer" name="editPublic" style="display:none;">
                </form>
            </div>
            <div id="privateSection" style="display: none;">
                <h3>Informations Privées</h3>
                <form id="privateForm" method="post">
                    <label for="lastname">Nom:</label>
                    <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" disabled><br>
                    <label for="name">Prénom:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" disabled><br>
                    <label for="currentPassword">Mot de Passe Actuel:</label>
                    <input type="password" id="currentPassword" name="currentPassword" required disabled><br>
                    <label for="newPassword">Nouveau Mot de Passe:</label>
                    <input type="password" id="newPassword" name="newPassword" disabled><br>
                    <?php if (!empty($error)): ?>
                        <p style="color: red; background-color: aliceblue;"><?php echo $error; ?></p>
                    <?php endif; ?>
                    <input type="button" value="Modifier" onclick="modifierProfil('privateForm')">
                    <input type="submit" value="Enregistrer les informations privées" name="editPrivate" style="display:none;">
                </form>
            </div>
            <button class="toggle-button" onclick="togglePrivateSection()">Afficher les Informations Privées</button>
        </section>

        <section id="recentProfiles">
            <h2>Profils les Plus Récents</h2>
            <div id="recentProfilesContainer">
                <?php foreach ($recentProfiles as $profile): ?>
                    <div class="profile-box">
                        <?php if (!empty($profile['profilePic'])): ?>
                            <img src="<?php echo htmlspecialchars($profile['profilePic']); ?>" alt="Profile Picture" style="width: 100px; height: 100px;"><br>
                        <?php else: ?>
                            <p>Aucune image de profil</p>
                        <?php endif; ?>
                        <strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($profile['username']); ?><br>
                        <strong>Genre :</strong> <?php echo htmlspecialchars($profile['gender']); ?><br>
                        <strong>Année de naissance :</strong> <?php echo htmlspecialchars($profile['year']); ?><br>
                        <strong>Taille :</strong> <?php echo htmlspecialchars($profile['height']); ?> cm<br>
                        <strong>Bio :</strong> <?php echo nl2br(htmlspecialchars($profile['bio'])); ?><br>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="placeholder">
            <p>Section de messages privés (à venir)</p>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Cupid Quest</p>
    </footer>

    <script>
        function modifierProfil(formId) {
            // Activer les champs pour permettre la modification
            document.querySelectorAll(`#${formId} input, #${formId} textarea`).forEach(input => {
                input.disabled = false;
            });
            // Afficher le champ de téléchargement de fichier si c'est le formulaire public
            if (formId === 'publicForm') {
                document.getElementById('profilePicUpload').style.display = 'block';
            }
            // Modifier le texte du bouton
            document.querySelector(`#${formId} input[type="button"]`).style.display = "none";
            document.querySelector(`#${formId} input[type="submit"]`).style.display = "inline-block";
        }

        function togglePrivateSection() {
            var privateSection = document.getElementById('privateSection');
            var button = document.querySelector('.toggle-button');
            
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
