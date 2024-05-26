<?php
session_start();
include_once '../../src/bin/utilitaries/getRecentProfiles.php';
include_once '../../src/bin/utilitaries/findFilesMatchingStrings.php';

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

// Filtrer les profils récents en fonction du terme de recherche
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($searchTerm)) {
    $recentProfiles = array_filter($recentProfiles, function($profile) use ($searchTerm) {
        return stripos($profile['username'], $searchTerm) !== false || stripos($profile['bio'], $searchTerm) !== false;
    });
}

// Prend la valeur true si l'utilisateur est abonné, false sinon
$isSubscribed = false;
if (file_exists($profileFile)) {
    $profileData = file($profileFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array('subscribed', $profileData)) {
        $isSubscribed = true;
    }
}
// Traitement du formulaire d'abonnement pour mettre à jour la mention 'subscribed' dans son profile.txt
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subscription'])) {
    $subscription = $_POST['subscription'];
    // Mettre à jour le fichier profile.txt
    if (!in_array('subscribed', $profileData)) {
        file_put_contents($profileFile, "\nsubscribed", FILE_APPEND);
    }
    // Redirection pour éviter la resoumission du formulaire
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fonction qui lit les messages à partir du fichier messages.txt
function readMessages($filePath) {
}

// Fonction pour ajouter une conversation à la liste des conversations de l'utilisateur connecté
function addConversation($email1, $email2) {
    $dir1 = '../../data/users/' . $email1;
    $dir2 = '../../data/users/' . $email2;
    $file1 = $dir1 . '/conversations.txt';
    $file2 = $dir2 . '/conversations.txt';

    // Vérifiez d'abord si les fichiers existent
    $file1Exists = file_exists($file1);
    $file2Exists = file_exists($file2);

    // Ouvrir les fichiers après la vérification
    $conversationsFile1 = fopen($file1, 'a'); // Utilisez 'a' pour ajouter au fichier
    $conversationsFile2 = fopen($file2, 'a'); // Utilisez 'a' pour ajouter au fichier

    if ($file1Exists && $file2Exists) {
        fwrite($conversationsFile1, $email2 . "\n");
        fwrite($conversationsFile2, $email1 . "\n");
    } else {
        echo "<p>Erreur dans l'ajout des conversations dans la liste</p>";
    }

    fclose($conversationsFile1);
    fclose($conversationsFile2);
}




// Variables pour utiliser les fonctions de conversations et messages

// Traitement du formulaire d'envoi des messages
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sendMessage'])) {
    $recipient = $_POST['recipient'];
    $messageContent = $_POST['messageContent'];
    $currentDate = date('Y-m-d H:i:s');
    
    // Append the new message to the messages.txt file
    $newMessage = $_SESSION['email'] . ';' . $recipient . ';' . $messageContent . ';' . $currentDate . "\n";
    file_put_contents($messagesFile, $newMessage, FILE_APPEND);

    // Redirection pour éviter la resoumission du formulaire
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupid Quest</title>
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
            include('../../src/element/headeruser.html');
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
            <form method="get">
                <input type="text" name="search" placeholder="Recherche par mots clés" value="<?php echo htmlspecialchars($searchTerm); ?>">
                <input type="submit" value="Rechercher">
            </form>
            <div id="recentProfilesContainer">
                <?php if (empty($recentProfiles)): ?>
                    <p>Aucun profil trouvé.</p>
                <?php else: ?>
                    <?php foreach ($recentProfiles as $profile): ?>
                        <?php if($profile['email'] != $_SESSION['email']) :?>
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
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section id="placeholder">
            <?php if ($isSubscribed): ?>
                <h2>Vos Messages Privés</h2>
                <div id="conv-starter">
                    <h2>Commencez à discuter avec des utilisateurs</h2>
                    <form id="start-conversation" method="post">
                        <input type="text" name="conv-name" placeholder="avec qui voulez vous commencer à parler ?">
                        <input type="submit" value="Démarrer une conversation" name="startConversation" >
                    </form>
                    <?php 
                    // Traitement du formulaire pour initialiser une conversation avec un utilisateur
                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['startConversation'])) {
                    // Vérifier si le champ "conv-name" est défini dans le formulaire
                    if (isset($_POST['conv-name']) && !empty($_POST['conv-name'])) {
                    // Récupérer le pseudo saisi dans le champ de texte du formulaire
                    $pseudo = $_POST['conv-name'];

                    // Utiliser le pseudo comme chaîne de recherche pour trouver les fichiers correspondants
                    $matchingFiles = findFilesMatchingString([$pseudo]);

                    // Afficher les résultats ou effectuer toute autre action souhaitée
                    if (!empty($matchingFiles)) {
                    echo "<p>Les fichiers correspondants pour le pseudo \"$pseudo\" sont :</p>";
                    foreach ($matchingFiles as $file) {
                    echo "<p>$file</p>";
                    }
                    } else {
                    echo "<p>Aucun fichier correspondant trouvé pour le pseudo \"$pseudo\".</p>";
                    }
                    } else {
                    echo "<p>Le champ \"conv-name\" n'est pas défini ou est vide.</p>";
                    }
                    }
                    ?>

                </div>
                <div id="conversations">
                    <h2>Conversations</h2>
                    <?php if (empty($userConversations)): ?>
                        <p>Aucune conversation trouvée.</p>
                    <?php else: ?>
                    <?php foreach ($userConversations as $conversations): ?>
                    <div class="conversations-box">
                        
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div id="sendMessage">
                    <form method="post" id="sendMessageForm">
                        <label for="recipient">Destinataire:</label>
                        <input type="email" id="recipient" name="recipient" required><br>
                        <label for="messageContent">Message:</label>
                        <textarea id="messageContent" name="messageContent" required></textarea><br>
                        <input type="submit" value="Envoyer" name="sendMessage">
                    </form>
                </div>
            <?php else: ?>
                <h2>Accès réservé aux abonnés</h2>
                <div class="subscription-message">
                    <p>Pour accéder à la messagerie privée, veuillez vous abonner :</p>
                </div>
                <form id="subscriptionForm" method="post">
                    <div class="subscription-option">
                        <input type="radio" id="subscription1minute" name="subscription" value="1minute">
                        <label for="subscription1minute">1 minute - Gratuit</label>
                    </div>
                    <div class="subscription-option">
                        <input type="radio" id="subscription1day" name="subscription" value="1day">
                        <label for="subscription1day">1 jour - 1€</label>
                    </div>
                    <div class="subscription-option">
                        <input type="radio" id="subscription1month" name="subscription" value="1month">
                        <label for="subscription1month">1 mois - 5€</label>
                    </div>
                    <div class="subscription-option">
                        <input type="radio" id="subscription1year" name="subscription" value="1year">
                        <label for="subscription1year">1 an - 50€</label>
                    </div>
                    <div class="subscription-option">
                        <input type="radio" id="subscriptionlifetime" name="subscription" value="lifetime">
                        <label for="subscriptionlifetime">À vie - 100€</label>
                    </div>
                    <input type="submit" value="S'abonner">
                </form>
            <?php endif; ?>
        </section>

    </main>
    <footer>
        <p>&copy; 2024 Cupid Quest</p>
    </footer>

    <script>
    function modifierProfil(formId) {
        document.querySelectorAll(`#${formId} input, #${formId} textarea`).forEach(input => {
            input.disabled = false;
        });
        if (formId === 'publicForm') {
            document.getElementById('profilePicUpload').style.display = 'block';
        }
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

    function startConversation(username) {
        // Logique pour démarrer une conversation avec l'utilisateur spécifié
        alert('Démarrer une conversation avec ' + username);
    }
    </script>
</body>
</html>
