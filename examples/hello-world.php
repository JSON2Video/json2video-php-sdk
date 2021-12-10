<?php

/*

    Simple "Hello world" example demonstrating the usage of the JSON2Video PHP SDK

    Documentation: https://json2video.com/docs/sdk

*/

// Import the Movie and Scene classes
use JSON2Video\Movie;
use JSON2Video\Scene;

// Import the SDK
include "../src/json2video-php-sdk.php";

// Get your free API key at https://json2video.com
define ('YOUR_API_KEY', '');

// Create a new movie
$movie = new Movie;

// Set your API key
$movie->setAPIKey(YOUR_API_KEY);

// Set a project ID
$movie->project = "myproj";

// Set movie quality
$movie->quality = "high";

// Create a new scene
$scene = new Scene;

// Set the scene background color
$scene->background_color = "#4392F1";

// Add a text element printing "Hello world" in a fancy way (basic/006)
// The element is 10 seconds long and starts 2 seconds from the scene start
// Element's vertical position is 50 pixels from the top
$scene->addElement([
    'type' => 'text',
    'template' => 'basic/006',
    'items' => [
        [ 'text' => 'Hello world' ]
    ],
    'y' => 50,
    'duration' => 10,
    'start' => 2
]);


try {
    // Add the scene to the movie
    $movie->addScene($scene);

    // Call the API and render the movie
    $movie->render();

    // Check every 2 seconds for the render status
    for ($i=0; $i<60; $i++) {

        // Get the movie status
        $response = $movie->getStatus();
        
        if ($response['success'] && $response['movies'][0]) {
            // Print the status and the last task
            echo '>>> Status: ', $response['movies'][0]['status'], ' --> ', $response['movies'][0]['task'], PHP_EOL;

            // If the render is done, print the video URL and break the loop
            if ($response['movies'][0]['status']=='done') {
                echo PHP_EOL, '>>> The movie is ready at: ', $response['movies'][0]['url'], PHP_EOL, PHP_EOL;
                break;
            }
        }
        else {
            // If there was an invalid API response
            echo '--- ERROR ---', PHP_EOL, $response['message'] ?? 'Unknown error', PHP_EOL, PHP_EOL;
        }
        
        // Sleep for 2 seconds in every loop
        sleep(2);
    }
}
catch(Exception $error) {
    // Print the error message
    echo $error->getMessage(), PHP_EOL;
}



