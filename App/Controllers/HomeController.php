<?php

namespace App\Controllers;


class HomeController extends \Core\Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        $this
            ->setPageTitle('Home Page')
            ->addStyle(css('/home'))
            ->renderView('/home');
    }
}
