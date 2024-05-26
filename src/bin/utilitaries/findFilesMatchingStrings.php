<?php

function findFilesMatchingString(array $searchStrings) {
    $baseDir = $_SERVER['DOCUMENT_ROOT']. '/data/users/';
    $profileFile = 'profile.txt';
    $matchingFiles = [];

    // Open the directory
    if ($handle = opendir($baseDir)) {
        // Iterate through the files and directories within the base directory
        while (false !== ($entry = readdir($handle))) {
            // Skip the special . and .. directories
            if ($entry != "." && $entry != "..") {
                // Construct the path to the profile.txt file
                $filePath = $baseDir . $entry . '/' . $profileFile;

                // Check if the profile.txt file exists
                if (file_exists($filePath)) {
                    // Read the file content
                    $fileContent = file_get_contents($filePath);

                    // Check if the file content contains all search strings
                    $containsAllStrings = true;
                    foreach ($searchStrings as $string) {
                        if (strpos($fileContent, $string) === false) {
                            $containsAllStrings = false;
                            break;
                        }
                    }

                    // If the file contains all the search strings, add its relative path to the results
                    if ($containsAllStrings) {
                        // Calculate the relative path from the site root
                        $relativePath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath($filePath));
                        $matchingFiles[] = $relativePath;
                    }
                }
            }
        }

        // Close the directory handle
        closedir($handle);
    }

    return $matchingFiles;
}