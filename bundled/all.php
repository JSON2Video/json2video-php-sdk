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
    protected $properties = ['comment', 'background_color', 'duration', 'cache'];

    protected $object = [];

    public function setTransition($style=null, $duration=null, $type=null) {
        if ($style || $duration || $type) {
            if (!isset($this->object['transition'])) $this->object['transition'] = [];
            if (!is_null($style)) $this->object['transition']['style'] = $style;
            if (!is_null($duration)) $this->object['transition']['duration'] = $duration;
            if (!is_null($type)) $this->object['transition']['type'] = $type;
        }
    }
    
}

class Movie extends Base {
    private $api_url = 'https://api.json2video.com/v1/movies';
    protected $properties = ['comment', 'project', 'width', 'height', 'resolution', 'quality', 'fps', 'cache'];

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
            if ($response['status']=='200') return json_decode($response['response'], true);
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

    public function getStatus() {

        if (empty($this->apikey)) throw new \Exception('Invalid API Key');
        if (empty($this->object['project'])) throw new \Exception('Project ID not set');

        $url = $this->api_url . '?project=' . $this->object['project'];

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
}