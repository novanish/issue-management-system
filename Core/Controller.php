<?php

namespace Core;

/**
 * Abstract base class for controllers.
 */
abstract class Controller
{
    /**
     * @var array Holds scripts for the <head> section of an HTML document.
     */
    private array $headScripts = [];

    /**
     * @var array Holds scripts for the <body> section of an HTML document.
     */
    private array $bodyScripts = [];

    /**
     * @var array Holds stylesheets for an HTML document.
     */
    private array $styles = [];

    /**
     * @var string|null The default page title.
     */
    public function __construct(private ?string $pageTitle = 'Issue Management System')
    {
    }

    /**
     * Renders a view.
     *
     * @param string $viewTemplate The template file for the view.
     * @param array|null $data Data to be used in the view.
     */
    public function renderView(string $viewTemplate, ?array $data = [])
    {
        extract($data);
        extract(get_object_vars($this));
        $mainLayoutContent = view($viewTemplate);
        require view('/mainLayout');
    }


    /**
     * Renders a partial view.
     *
     * @param string $partialView The partial view file.
     * @param array|null $data Data to be used in the partial view.
     */
    public function renderPartialView(string $partialView, ?array $data = [])
    {
        extract($data);
        extract(get_object_vars($this));
        require view($partialView);
    }

    /**
     * Sets the page title.
     *
     * @param string $pageTitle The new page title.
     */
    public function setPageTitle(string $pageTitle)
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    /**
     * Adds styles to the styles array.
     *
     * @param string|array ...$styles Styles to be added.
     */
    public function addStyle(string|array ...$styles)
    {
        array_push($this->styles, ...$styles);

        return $this;
    }

    /**
     * Adds scripts to the headScripts array.
     *
     * @param string|array ...$scripts Scripts for the <head> section.
     */
    public function addScriptInHead(string|array ...$scripts)
    {
        array_push($this->headScripts, ...$scripts);
        return $this;
    }

    /**
     * Adds scripts to the bodyScripts array.
     *
     * @param array ...$scripts Scripts for the <body> section.
     */
    public function addScriptInBody(string|array ...$scripts)
    {
        array_push($this->bodyScripts, ...$scripts);
        return $this;
    }
}
