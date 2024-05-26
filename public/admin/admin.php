<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fonction pour récupérer tous les utilisateurs à partir des fichiers
function getAllUsers() {
    $users = array();

    // Chemin du dossier contenant les fichiers utilisateurs
    $usersDirectory = $_SERVER['DOCUMENT_ROOT'] . '/data/users';

    // Vérifie si le dossier des utilisateurs existe
    if (file_exists($usersDirectory) && is_dir($usersDirectory)) {
        // Parcours des fichiers dans le dossier des utilisateurs
        $userFiles = scandir($usersDirectory);
        foreach ($userFiles as $file) {
            // Ignorer les fichiers spéciaux
            if ($file == '.' || $file == '..' || $file === 'admin1@cupidquest.fr') {
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
                    'lastname' => isset($lines[2]) ? trim($lines[2]) : '',
                    'firstname' => isset($lines[3]) ? trim($lines[3]) : '',
                    'gender' => isset($lines[1]) ? trim($lines[1]) : '',
                    'birthdate' => isset($lines[4]) ? trim($lines[4]) : '',
                    'email' => isset($lines[5]) ? trim($lines[5]) : '',
                    'password' => isset($lines[6]) ? trim($lines[6]) : '',
                    'creation_time' => filemtime($profileFile) // Ajouter le temps de création du fichier
                );
                // Ajouter les informations de l'utilisateur au tableau
                $users[] = $userData;
            }
        }

        // Trier les utilisateurs par ordre de création (plus récents en premier)
        usort($users, function($a, $b) {
            return $b['creation_time'] - $a['creation_time'];
        });
    }

    return $users;
}

// Fonction pour supprimer un utilisateur
function deleteUser($email) {
    $usersDirectory = $_SERVER['DOCUMENT_ROOT'] . '/data/users';

    if (file_exists($usersDirectory) && is_dir($usersDirectory)) {
        // Parcours des fichiers dans le dossier des utilisateurs
        $userFiles = scandir($usersDirectory);
        foreach ($userFiles as $file) {
            // Ignorer les fichiers spéciaux
            if ($file == '.' || $file == '..' || $file === 'admin1@cupidquest.fr') {
                continue;
            }

            // Lire le fichier profile.txt pour obtenir les informations de l'utilisateur
            $profileFile = $usersDirectory . '/' . $file . '/profile.txt';
            if (file_exists($profileFile)) {
                // Lire le contenu du fichier
                $profileData = file_get_contents($profileFile);
                // Diviser les données en lignes
                $lines = explode("\n", $profileData);
                // Extraire l'adresse email de l'utilisateur
                $userEmail = trim($lines[5]);

                // Vérifier si l'adresse email correspond à celle fournie
                if ($userEmail === $email) {
                    // Chemin du répertoire de l'utilisateur
                    $userDirectory = $usersDirectory . '/' . $file;

                    // Vérifier si c'est bien un dossier
                    if (is_dir($userDirectory)) {
                        // Supprimer tous les fichiers dans le répertoire de l'utilisateur
                        $deleted = true;
                        $files = glob("$userDirectory/*");
                        foreach ($files as $filename) {
                            if (!unlink($filename)) {
                                $deleted = false;
                            }
                        }

                        // Supprimer le répertoire de l'utilisateur
                        if ($deleted && rmdir($userDirectory)) {
                            return true; // Utilisateur supprimé avec succès
                        } else {
                            return false; // Échec de la suppression des fichiers ou du répertoire
                        }
                    }
                }
            }
        }
    }

    return false; // Aucun utilisateur trouvé ou erreur lors de la suppression
}

// Fonction pour bannir un utilisateur
function banUser($email) {
    $banDirectory = $_SERVER['DOCUMENT_ROOT'] . '/data/ban';
    $banFile = $banDirectory . '/bannedAccount.txt';

    // Vérifie si le répertoire de bannissement existe, sinon le crée
    if (!file_exists($banDirectory)) {
        mkdir($banDirectory, 0755, true);
    }

    // Ajoute l'email banni dans le fichier
    if ($handle = fopen($banFile, 'a')) {
        fwrite($handle, $email . "\n");
        fclose($handle);
        return true;
    } else {
        return false;
    }
}

// Si une action de suppression ou de bannissement d'utilisateur est demandée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Suppression d'utilisateur
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user' && isset($_POST['user_email'])) {
        $userEmailToDelete = $_POST['user_email'];
        if (deleteUser($userEmailToDelete)) {
            // Rediriger vers la même page après la suppression
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo '<script>alert("Erreur lors de la suppression de l\'utilisateur.");</script>';
        }
    }

    // Bannissement d'utilisateur
    if (isset($_POST['action']) && $_POST['action'] === 'ban_user' && isset($_POST['user_email'])) {
        $userEmailToBan = $_POST['user_email'];
        if (banUser($userEmailToBan)) {
            deleteUser($userEmailToBan);
            // Rediriger vers la même page après le bannissement
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo '<script>alert("Erreur lors du bannissement de l\'utilisateur.");</script>';
        }
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
    <link rel="stylesheet" href="src/element/header.css">
</head>
<body>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/src/element/headerAdmin.html'); ?>
    
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
                    <th>Bannir</th>
                </tr>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($user['firstname']); ?></td>
                        <td><?php echo htmlspecialchars($user['gender']); ?></td>
                        <td><?php echo htmlspecialchars($user['birthdate']); ?></td>
                        <td>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                <button type="submit">Supprimer</button>
                            </form>
                        </td>
                        <td>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                <input type="hidden" name="action" value="ban_user">
                                <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                <button type="submit">Bannir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>
    </main>
</body>
</html>