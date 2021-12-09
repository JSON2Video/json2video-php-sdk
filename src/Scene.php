<?php

namespace JSON2Video;

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