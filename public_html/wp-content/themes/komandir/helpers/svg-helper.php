<?php

class SVGHelper
{
    private $sprite;

    public function init_sprite($sprite) {
        $this->sprite = $sprite;
    }
    
    public function view_from_sprite($data = array()) {
        if (isset($data['title'])) {
            $output = '<svg';

            if (isset($data['class'])) {
                $output.= ' class='.$data['class'];
            }
            
            $output.= isset($data['width']) ? ' width="' . $data['width'] . '"' : '';
            $output.= isset($data['height']) ? ' height="' . $data['height'] . '"' : '';
            $output.= ">\n";
            $output.= '<use xlink:href="' .$this->sprite.'#';
            $output.= $data['title']."\"></use>\n</svg>\n";
        } else {
            $output = 'Отсутствует название';
        }

        return $output;
    }
}
