<?php
class Template {
    public $template, $theme, $vars;

    public function __construct($theme) {
        $this->theme = $theme;
        $this->template = $this->loadTemplate("default");
    }

    function loadTemplate($temp) {
        $template = "core/templates/{$this->theme}/{$temp}.html";
        if(!file_exists($template)){
            die("There was an error. The template {$template} doesn't exist.");
        }
        $data[0][0] = fopen($template, "r");
        $data[0][1] = fread($data[0][0], filesize($template));
        fclose($data[0][0]);
        $data[0][1] = empty($this->vars) ? $data[0][1] : str_replace(array_keys($this->vars), array_values($this->vars), $data[0][1]);
        return $data[0][1];
    }

    function assignVar($var, $content) {
        if(is_array($var) || is_object($var) && is_null($content)) {
            $this->vars += $var;
            while(list($key, $value) = each($this->Variables)) {
                $this->vars["{{$key}}"] = $content;
            }
        } else if(is_string($var) && strlen($var) > 0 && !is_null($content)) {
            $this->vars["{{$var}}"] = $content;
        }
    }

    function content() {
        $this->template = empty($this->vars) ? $this->template : str_replace(array_keys($this->vars), array_values($this->vars), $this->template);
        print($this->template);
    }
}
?>
