<?php

Lib::Import(array('config', 'response'));

class ViewResponse extends Response {
    public $view = '';

    public $layout = 'default';

    public $variables = array();

    public function __construct($properties = array()) {
        parent::__construct($properties);

        Config::Import('application');
    }

    protected function contents($view, $variables) {
        $file = file_exists(Config::$Configs['application']['paths']['application'] . 'views/' . $view . '.php') ?
            Config::$Configs['application']['paths']['application'] . 'views/' . $view . '.php' :
            (file_exists(Config::$Configs['application']['paths']['framework'] . 'views/' . $view . '.php') ?
                Config::$Configs['application']['paths']['framework'] . 'views/' . $view . '.php' : false);

        if(false === $file) {
            throw new Exception("View not found: {$view}");
        }

        return $this->extract_and_include($variables, $file);
    }

    public function render() {
        $contents = $this->contents($this->view, $this->variables);

        if(empty($this->layout)) {
            echo $contents;

            return;
        }

        if(is_string($this->layout)) {
            echo $this->contents("layouts/{$this->layout}", array_merge($this->variables, array(
                'content_for_layout' => $contents,
                'title' => isset($this->title) ? $this->title : '',
            )));

            return;
        }

        foreach($this->layout as $layout) {
            $contents = $this->contents("layouts/{$layout}", array_merge($this->variables, array(
                'content_for_layout' => $contents,
                'title' => isset($this->title) ? $this->title : '',
            )));
        }

        echo $contents;
    }

    protected function extract_and_include($__variables, $__file) {
        extract(array_merge($__variables, array('this' => $this)), EXTR_OVERWRITE);

        ob_start();

        include($__file);

        $__contents = ob_get_contents();

        ob_end_clean();

        return $__contents;
    }

    public function element($element, $variables = array()) {
        return $this->contents("elements/{$element}", $variables);
    }

    public function js($file) {
        Config::Import('application');

        $version = strstr($file, '?')
            ? '&v=' . Config::$Configs['application']['version']
            : '?v=' . Config::$Configs['application']['version'];

        return "<script type=\"text/javascript\" src=\"$file$version\"></script>\n";
    }

    public function css($file) {
        Config::Import('application');

        $version = strstr($file, '?')
            ? '&v=' . Config::$Configs['application']['version']
            : '?v=' . Config::$Configs['application']['version'];

        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"$file$version\" />\n";
    }
}