<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fonction pour récupérer tous les utilisateurs à partir des fichiers
function getAllUsers() {
    $users = array();
    $usersDirectory = $_SERVER['DOCUMENT_ROOT'] . '/data/users';

    if (file_exists($usersDirectory) && is_dir($usersDirectory)) {
        $userFiles = scandir($usersDirectory);
        foreach ($userFiles as $file) {
            if ($file == '.' || $file == '..' || $file === 'admin1@cupidquest.fr') {
                continue;
            }

            $profileFile = $usersDirectory . '/' . $file . '/profile.txt';
            if (file_exists($profileFile)) {
                $profileData = file_get_contents($profileFile);
                $lines = explode("\n", $profileData);

                $bioFile = $usersDirectory . '/' . $file . '/bio.txt';
                $bio = file_exists($bioFile) ? file_get_contents($bioFile) : '';

                $userData = array(
                    'pseudo' => isset($lines[0]) ? trim($lines[0]) : '',
                    'lastname' => isset($lines[2]) ? trim($lines[2]) : '',
                    'firstname' => isset($lines[3]) ? trim($lines[3]) : '',
                    'gender' => isset($lines[1]) ? trim($lines[1]) : '',
                    'birthdate' => isset($lines[4]) ? trim($lines[4]) : '',
                    'email' => isset($lines[5]) ? trim($lines[5]) : '',
                    'password' => isset($lines[6]) ? trim($lines[6]) : '',
                    'creation_time' => filemtime($profileFile),
                    'bio' => $bio
                );

                $users[] = $userData;
            }
        }

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
        $userFiles = scandir($usersDirectory);
        foreach ($userFiles as $file) {
            if ($file == '.' || $file == '..' || $file === 'admin1@cupidquest.fr') {
                continue;
            }

            $profileFile = $usersDirectory . '/' . $file . '/profile.txt';
            if (file_exists($profileFile)) {
                $profileData = file_get_contents($profileFile);
                $lines = explode("\n", $profileData);
                $userEmail = trim($lines[5]);

                if ($userEmail === $email) {
                    $userDirectory = $usersDirectory . '/' . $file;

                    if (is_dir($userDirectory)) {
                        $deleted = true;
                        $files = glob("$userDirectory/*");
                        foreach ($files as $filename) {
                            if (!unlink($filename)) {
                                $deleted = false;
                            }
                        }

                        if ($deleted && rmdir($userDirectory)) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        }
    }

    return false;
}

// Fonction pour bannir un utilisateur
function banUser($email) {
    $banDirectory = $_SERVER['DOCUMENT_ROOT'] . '/data/ban';
    $banFile = $banDirectory . '/bannedAccount.txt';

    if (!file_exists($banDirectory)) {
        mkdir($banDirectory, 0755, true);
    }

    if ($handle = fopen($banFile, 'a')) {
        fwrite($handle, $email . "\n");
        fclose($handle);
        return true;
    } else {
        return false;
    }
}

// Fonction pour mettre à jour un utilisateur
function updateUser($email, $userData) {
    $usersDirectory = $_SERVER['DOCUMENT_ROOT'] . '/data/users';

    if (file_exists($usersDirectory) && is_dir($usersDirectory)) {
        $userFiles = scandir($usersDirectory);
        foreach ($userFiles as $file) {
            if ($file == '.' || $file == '..' || $file === 'admin1@cupidquest.fr') {
                continue;
            }

            $profileFile = $usersDirectory . '/' . $file . '/profile.txt';
            if (file_exists($profileFile)) {
                $profileData = file_get_contents($profileFile);
                $lines = explode("\n", $profileData);
                $userEmail = trim($lines[5]);

                if ($userEmail === $email) {
                    $newProfileData = implode("\n", [
                        $userData['pseudo'],
                        $userData['gender'],
                        $userData['lastname'],
                        $userData['firstname'],
                        $userData['birthdate'],
                        $userEmail,
                        $lines[6]
                    ]);
                    file_put_contents($profileFile, $newProfileData);

                    $bioFile = $usersDirectory . '/' . $file . '/bio.txt';
                    file_put_contents($bioFile, $userData['bio']);
                    return true;
                }
            }
        }
    }
    return false;
}

// Si une action de suppression, de bannissement ou de modification d'utilisateur est demandée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Suppression d'utilisateur
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user' && isset($_POST['user_email'])) {
        $userEmailToDelete = $_POST['user_email'];
        if (deleteUser($userEmailToDelete)) {
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
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo '<script>alert("Erreur lors du bannissement de l\'utilisateur.");</script>';
        }
    }

    // Mise à jour d'utilisateur
    if (isset($_POST['action']) && $_POST['action'] === 'update_user' && isset($_POST['user_email'])) {
        $userEmailToUpdate = $_POST['user_email'];
        $userData = [
            'pseudo' => $_POST['pseudo'],
            'lastname' => $_POST['lastname'],
            'firstname' => $_POST['firstname'],
            'gender' => $_POST['gender'],
            'birthdate' => $_POST['birthdate'],
            'bio' => $_POST['bio']
        ];

        if (updateUser($userEmailToUpdate, $userData)) {
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo '<script>alert("Erreur lors de la mise à jour de l\'utilisateur.");</script>';
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
    <title>Cupid Quest</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="src/element/header.css">
</head>
<body>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/src/element/headerAdmin.html'); ?>
    
    <main>
        <section id="user-list">
            <h2>Liste des Utilisateurs</h2>
            <table>
                <tr>
                    <th>Pseudo</th>
                    <th>Email</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Genre</th>
                    <th>Date de naissance</th>
                    <th>Bio</th>
                    <th>Action</th>
                    <th>Bannir</th>
                </tr>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['pseudo']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($user['firstname']); ?></td>
                        <td><?php echo htmlspecialchars($user['gender']); ?></td>
                        <td><?php echo htmlspecialchars($user['birthdate']); ?></td>
                        <td class="bio">
                            <div class="bio-content"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></div>
                        </td>
                        <td>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                <button type="submit">Supprimer</button>
                            </form>
                            <br>
                            <button onclick="showEditForm('<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['pseudo']); ?>', '<?php echo htmlspecialchars($user['lastname']); ?>', '<?php echo htmlspecialchars($user['firstname']); ?>', '<?php echo htmlspecialchars($user['gender']); ?>', '<?php echo htmlspecialchars($user['birthdate']); ?>', `<?php echo htmlspecialchars($user['bio']); ?>`)">Modifier</button>
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

        <section id="edit-user" style="display: none;">
            <h2>Modifier l'utilisateur</h2>
            <form id="edit-user-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_email" id="edit-email">

                <label for="edit-pseudo">Pseudo:</label>
                <input type="text" name="pseudo" id="edit-pseudo" required>

                <label for="edit-lastname">Nom:</label>
                <input type="text" name="lastname" id="edit-lastname" required>

                <label for="edit-firstname">Prénom:</label>
                <input type="text" name="firstname" id="edit-firstname" required>

                <label for="edit-gender">Genre:</label>
                <input type="text" name="gender" id="edit-gender" required>

                <label for="edit-birthdate">Date de naissance:</label>
                <input type="date" name="birthdate" id="edit-birthdate" required>

                <label for="edit-bio">Bio:</label>
                <textarea name="bio" id="edit-bio" required></textarea>

                <button type="submit">Mettre à jour</button>
                <button type="button" onclick="hideEditForm()">Annuler</button>
            </form>
        </section>
    </main>

    <script>
        function showEditForm(email, pseudo, lastname, firstname, gender, birthdate, bio) {
            document.getElementById('edit-user').style.display = 'block';
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-pseudo').value = pseudo;
            document.getElementById('edit-lastname').value = lastname;
            document.getElementById('edit-firstname').value = firstname;
            document.getElementById('edit-gender').value = gender;
            document.getElementById('edit-birthdate').value = birthdate;
            document.getElementById('edit-bio').value = bio;
        }

        function hideEditForm() {
            document.getElementById('edit-user').style.display = 'none';
        }
    </script>
</body>
</html>