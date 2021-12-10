# Create videos programmatically in PHP
Create and edit videos: add watermarks, resize videos, create slideshows, add soundtrack, automate the creation of videos in multiple languages, add voice-over, add text animations.

JSON2Video API is the easiest way to create, edit and customise videos programmatically. Its dead simple approach, close to the web development mindset, makes it the ultimate solution for developers that want to create or customise videos in an automated way.

Additionally, the simple integration of real HTML5+CSS elements, the already built-in text animations and voice generation (TTS) converts JSON2Video in the best solution in its category.

Use cases
* Automate the production of promotional videos for your e-commerce products
* Automate publication of social media videos created directly from your news feed
* Customize your advertising campaigns with different images, videos, texts and create tens or hundreds of different options
* From weather forecasts to traffic bulletins or financial reports, if you have a data source you can create an audiovisual experience
* Convert your text, pictures and information into engaging videos of your real estate properties
* Add watermarks, bumpers, titles; Concatenate different videos into one; Add voice-over or music; Create photo slideshows; …


## Get your FREE API Key
JSON2Video is free to use. Get your API Key at [JSON2Video.com](https://json2video.com)

## Documentation
The [API Specification](https://json2video.com/docs/api/) will provide you with all the details of the JSON payload and the endpoints.

For a step by step guide, read the [Tutorial](https://json2video.com/docs/tutorial/) that will introduce you through all features with code examples.

## PHP SDK installation
You can use JSON2Video PHP SDK as a Composer package or with a simple require_once.

### Using require_once
The simplest way :-)

1) Download all.php from the /bundled folder into your project directory
2) Import the library:

```php
<?php
    require_once 'path/to/the/sdk/all.php';

```

### Using Composer
The SDK has no external dependencies on other packages.

1) Open the terminal and cd to your project directory
2) Use composer:

```
$ composer require json2video/json2video-php-sdk
```

## Hello world
JSON2Video makes video creation easy as a piece of cake:

```php
<?php
    // Create a new movie
    $movie = new Movie;

    // Set your API key
    // Get your free API key at https://json2video.com
    $movie->setAPIKey(YOUR_API_KEY);

    // Set a project ID
    $movie->project = 'myproj';

    // Set movie quality: low, medium, high
    $movie->quality = 'high';

    // Create a new scene
    $scene = new Scene;

    // Set the scene background color
    $scene->background_color = '#4392F1';

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

    // Add the scene to the movie
    $movie->addScene($scene);

    // Call the API and render the movie
    $movie->render();
```

This is the resulting video:

https://assets.json2video.com/mkt/github/hello-world.mp4
