<?php

/*

    Simple "Hello world" example demonstrating the usage of the JSON2Video PHP SDK

    Documentation: https://json2video.com/docs/sdk

*/

require 'vendor/autoload.php';

use JSON2Video\Movie;
use JSON2Video\Scene;

// Create a new movie
$movie = new Movie;

// Set your API key
// Get your free API key at https://json2video.com
$movie->setAPIKey(YOUR_API_KEY);

// Set movie quality: low, medium, high
$movie->quality = 'high';
$movie->draft = true;

// Create a new scene
$scene = new Scene;

// Set the scene background color
$scene->background_color = '#4392F1';

// Add a text element printing "Hello world" in a fancy way (basic/006)
// The element is 10 seconds long and starts 2 seconds from the scene start
// Element's vertical position is 50 pixels from the top
$scene->addElement([
    'type' => 'text',
    'style' => '003',
    'text' => 'Hello world',
    'duration' => 10,
    'start' => 2
]);

// Add the scene to the movie
$movie->addScene($scene);

// Call the API and start rendering the movie
$result = $movie->render();

// Wait for the render to finish
$movie->waitToFinish();