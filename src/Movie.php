<?php

namespace JSON2Video;

class Movie extends Base {
    private $api_url = 'https://api.json2video.com/v2/movies';
    protected $properties = ['comment', 'draft', 'width', 'height', 'resolution', 'exports', 'quality', 'fps', 'cache'];

    private $apikey = null;

    protected $object = [];

    public function setAPIKey($apikey) {
        $this->apikey = $apikey;
    }

    public function addScene($scene=null) {
        if ($scene && is_a($scene, 'JSON2Video\Scene')) {
            if (!isset($this->object['scenes'])) $this->object['scenes'] = [];
            $this->object['scenes'][] = $scene->getObject();
            return true;
        }
        else throw new \Exception('Invalid scene');
        return false;
    }

    private function fetch(string $method, string $url, string $body, array $headers = []) {
        $context = stream_context_create([
            "http" => [
                "method"        => $method,
                "header"        => implode("\r\n", $headers),
                "content"       => $body,
                "ignore_errors" => true,
            ],
        ]);
    
        $response = file_get_contents($url, false, $context);
    
        $status_line = $http_response_header[0];
    
        preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
    
        $status = $match[1];
    
        return [
            'status' => $status,
            'message' => $status_line,
            'response' => $response
        ];
    }

    public function render() {

        if (empty($this->apikey)) throw new \Exception('Invalid API Key');

        $postdata = json_encode($this->object);
        if (is_null($postdata)) {
            throw new \Exception('Invalid movie settings');
        }
        
        $response = $this->fetch('POST', $this->api_url, $postdata, [
            "Content-Type: application/json",
            "x-api-key: {$this->apikey}"
        ]);

        if ($response) {
            if ($response['status']=='200') {
                $render = json_decode($response['response'], true);
                if ($render['success']??false && !empty($render['project'])) $this->object['project'] = $render['project'];
                else throw new \Exception("Render didn't return a project ID");
                return $render;
            }
            elseif ($response['status']=='400') {
                $api_response = json_decode($response['response'], true);
                throw new \Exception('JSON Syntax error: ' . ($api_response['message'] ?? 'Unknown error'));
            }
            else {
                
                throw new \Exception('API error: ' . ($response['message'] ?? 'Unknown error'));
            }
        }
        else throw new \Error('SDK error');

        return false;
    }

    public function getStatus($project=null) {

        if (!$project) $project = $this->object['project'] ?? null;

        if (empty($this->apikey)) throw new \Exception('Invalid API Key');
        if (!$project) throw new \Exception('Project ID not set');

        $url = $this->api_url . '?project=' . $project;

        $status = $this->fetch('GET', $url, '', [
            "x-api-key: {$this->apikey}"
        ]);

        if ($status) {
            if ($status['status']=='200') return json_decode($status['response'], true);
            else {
                throw new \Exception('API error: ' . ($status['message'] ?? 'Unknown error'));
            }
        }
        else throw new \Error('SDK error');

        return false;
    }

    public function waitToFinish($delay=5, $callback=null) {

        $max_loops = 60;  // loop up to 60 times
        $loops = 0;

        while ($loops<$max_loops) {
            $response = $this->getStatus();  // get the movie rendering status
            
            if ($response && ($response['success']??false) && !empty($response['movie'])) {
                // if the API returns a valid response

                if (is_callable($callback)) {
                    // if the callback function is set, use it
                    $callback($response['movie'], $response['remaining_quota']);
                }
                else {
                    // if not, print the status
                    $this->printStatus($response['movie'], $response['remaining_quota']);
                }

                if (!empty($response['movie']['status'])) {
                    // if the response has a status (it should), check what is the status...

                    if ($response['movie']['status']=='done') {
                        // if the movie is done
                        return $response;
                    }
                    
                    if ($response['movie']['status']=='error') {
                        // if the movie rendering has failed
                        throw new \Exception($response['movie']['message']);
                    }
                }
            }
            else {
                // if the API doesn't return a valid response
                throw new \Error('Invalid API response');
            }

            sleep($delay);  // wait for $delay
            $loops++;
        }

        throw new \Error('The rendering process took more than expected or maybe failed');
    }

    public function printStatus($response, $quota) {
        // print the status
        echo 'Status: ', $response['status']??'', ' / ', $response['message']??'', PHP_EOL;

        // if the movie is done
        if ($response['status']=='done') {
            // print the URL and remaining quota
            echo PHP_EOL, 'Movie URL: ', $response['url']??'No URL', PHP_EOL;
            echo 'Remaining time quota: ', $quota['time']??'No quota', ' seconds', PHP_EOL, PHP_EOL;
        }
    }
}