<?php


// fonction qui recherche des chaines de caractères dans les profile.txt et retourne les chemins vers les fichiers concernés
function searchAccount(array $toSearch) {

    $userDir = '../../data/users/';
    $profile = 'profile.txt';
    $matchingFiles = [];


    if ($handle = opendir($userDir)) {

        while (false !== ($input = readdir($handle))) {     
            if ($input != "." && $input != "..") {
                
                $filePath = $userDir . $input . '/' . $profile;

                if (file_exists($filePath)) {
                    
                    $fileContent = file_get_contents($filePath);
                    $containsAllStrings = true;

                    foreach ($toSearch as $string) {
                        if (strpos($fileContent, $string) === false) {

                            $containsAllStrings = false;
                            break;
                        }
                    }
                    if ($containsAllStrings) {

                        $matchingFiles[] = $filePath;
                    }
                }
            }
        }
        closedir($handle);
    }
    return $matchingFiles;
}


