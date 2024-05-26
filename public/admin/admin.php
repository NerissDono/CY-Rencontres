<?php
session_start();

// Fonction pour récupérer tous les utilisateurs à partir des fichiers
function getAllUsers() {
    $users = array();

    // Chemin du dossier contenant les fichiers utilisateurs
    $usersDirectory = '../../data/users';

    // Vérifie si le dossier des utilisateurs existe
    if (is_dir($usersDirectory)) {
        // Parcours des fichiers dans le dossier des utilisateurs
        $userFiles = scandir($usersDirectory);
        foreach ($userFiles as $file) {
            // Ignorer les fichiers spéciaux
            if ($file == '.' || $file == '..') {
                continue;
            }

            // Vérifier si le dossier utilisateur doit être exclu
            if ($file === 'admin@example.com') {
                continue;
            }

            // Lire le fichier profile.txt pour obtenir les informations de l'utilisateur
            $profileFile = $usersDirectory . '/' . $file . '/profile.txt';
            if (file_exists($profileFile)) {
                // Lire le contenu du fichier
                $profileData = file_get_contents($profileFile);
                // Diviser les données en lignes
                $lines = explode("\n", $profileData);
                // Créer un tableau associatif avec les informations
                $userData = array(
                    'lastname' => $lines[2],
                    'firstname' => $lines[3],
                    'gender' => $lines[1],
                    'birthdate' => $lines[4],
                    'email' => $lines[5],
                    'password' => $lines[6]
                );
                // Ajouter les informations de l'utilisateur au tableau
                $users[] = $userData;
            }
        }
    }

    return $users;
}

// Fonction pour supprimer un utilisateur
function deleteUser($email) {
    $userDirectory = '../../data/users/' . $email;

    // Vérifie si le dossier utilisateur existe
    if (is_dir($userDirectory)) {
        // Supprime le dossier utilisateur
        $success = rrmdir($userDirectory);
        if (!$success) {
            // Gérer l'échec de la suppression
            return false;
        }
    }

    return true;
}

// Fonction récursive pour supprimer un répertoire et son contenu
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object)) {
                    rrmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        rrmdir($dir);
    }
}

// Traitement des actions administratives
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    switch ($action) {
        case 'delete_user':
            if (isset($_POST['user_email'])) {
                $user_email = $_POST['user_email'];
                deleteUser($user_email); // Supprimer l'utilisateur
            }
            break;
        // Ajouter d'autres actions administratives ici si nécessaire
    }
}

// Récupérer la liste des utilisateurs enregistrés
$users = getAllUsers();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Gestion des utilisateurs</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="../../src/element/header.css">
</head>
<body>
    <?php
    include('../../src/element/headerAdmin.html');
    ?>
    <header>

    <main>
        <section id="user-list">
            <h2>Liste des utilisateurs</h2>
            <table>
                <tr>
                    <th>Email</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Genre</th>
                    <th>Date de naissance</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['lastname']; ?></td>
                        <td><?php echo $user['firstname']; ?></td>
                        <td><?php echo $user['gender']; ?></td>
                        <td><?php echo $user['birthdate']; ?></td>
                        <td>
                            <form action="" method="post">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_email" value="<?php echo $user['email']; ?>">
                                <button type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>
    </main>
</body>
</html>