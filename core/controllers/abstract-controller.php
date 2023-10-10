<?php

/**
 * The abstract class for all Controllers.
 * 
 * @package 
 */
abstract class Controller
{
    protected $plugin_path;

    protected $partials_path;

    /**
     * 
     * @param string $partials_path 
     * @return void 
     */
    public function __construct(string $partials_path)
    {
        $this->partials_path = $partials_path;
    }

    /**
     * Get the content of a partial file.
     * 
     * @param string $partial_path 
     * @param null|array $args 
     * @return string 
     */
    protected function Partial(string $partial_path, ?array $args = null): string
    {
        if ($args !== null) extract($args);

        ob_start();
        include $this->plugin_path . $this->partials_path . $partial_path;
        $content = ob_get_clean();

        return is_string($content) ? $content : "";
    }
}
