<?php
function getRecentProfiles($directory, $limit = 20) {
    $profiles = [];

    if ($handle = opendir($directory)) {
        while (false !== ($entry = readdir($handle))) {
            $userDir = $directory . '/' . $entry;
            if ($entry != "." && $entry != ".." && is_dir($userDir)) {
                $profileFile = $userDir . '/profile.txt';
                if (file_exists($profileFile)) {
                    $lines = file($profileFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    if (count($lines) >= 4) {
                        $username = $lines[0];
                        $gender = $lines[1];
                        $birthdate = $lines[4]; // Assuming the birthdate is on the fourth line
                        $year = explode('-', $birthdate)[0];

                        // Get the creation time of the profile file
                        $creationTime = filemtime($profileFile);

                        $profiles[] = [
                            'username' => $username,
                            'gender' => $gender,
                            'year' => $year,
                            'creation_time' => $creationTime,
                        ];
                    }
                }
            }
        }
        closedir($handle);
    }

    // Sort the profiles by creation time in descending order
    usort($profiles, function($a, $b) {
        return $b['creation_time'] - $a['creation_time'];
    });

    // Return the most recent profiles up to the limit
    return array_slice($profiles, 0, $limit);
}
?>
