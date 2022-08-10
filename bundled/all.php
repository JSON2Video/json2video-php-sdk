<?php

/*

    JSON2Video PHP SDK

    This simple SDK is a wrapper for calling JSON2Video API
    JSON2Video API allows you to create and edit videos programmatically

    Documentation: https://json2video.com/docs/sdk

*/

namespace JSON2Video;

class Base {

    protected $object, $properties;

    public function __get($property) {
        $property = strtolower($property);
        if (in_array($property, $this->properties) && isset($this->object[$property])) {
            return $this->object[$property];
        }

        return null;
    }

    public function __set($property, $value) {
        $property = strtolower($property);
        if (in_array($property, $this->properties)) {
            $property = strtolower(str_replace('_', '-', $property));
            $this->object[$property] = $value;
            return $value;
        }

        return null;
    }

    public function addElement($element=null) {
        if ($element && is_array($element)) {
            if (!isset($this->object['elements'])) $this->object['elements'] = [];
            $this->object['elements'][] = $element;
            return true;
        }
        return false;
    }

    public function getJSON() {
        return json_encode($this->object, JSON_PRETTY_PRINT);
    }

    public function getObject() {
        return $this->object;
    }
}


class Scene extends Base {
    protected $properties = ['comment', 'background_color', 'transition', 'duration', 'cache'];
    protected $object = [];
}

class Movie extends Base {
    private $api_url = 'https://api.json2video.com/v2/movies';
    protected $properties = ['comment', 'draft', 'width', 'height', 'resolution', 'quality', 'fps', 'cache'];

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

        $max_loops = 60;
        $loops = 0;

        while ($loops<$max_loops) {
            $response = $this->getStatus();
            
            if ($response && ($response['success']??false) && !empty($response['movie'])) {

                if (is_callable($callback)) $callback($response['movie'], $response['remaining_quota']);
                else $this->printStatus($response['movie'], $response['remaining_quota']);

                if (!empty($response['movie']['status']) && $response['movie']['status']=='done') {
                    return $response;
                }
            }
            else {
                throw new \Error('Invalid API response');
            }

            sleep($delay);
            $loops++;
        }
    }

    public function printStatus($response, $quota) {
        echo 'Status: ', $response['status'], ' / ', $response['message'], PHP_EOL;
        if ($response['status']=='done') {
            echo PHP_EOL, 'Movie URL: ', $response['url'], PHP_EOL;
            echo 'Remaining quota: movies(', $quota['movies'], ') and drafts(', $quota['drafts'], ')', PHP_EOL, PHP_EOL;
        }
    }
}