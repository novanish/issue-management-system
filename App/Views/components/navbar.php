<?php
$navbarLinks = [
    ["path" => "/issues", "label" => "Issues"],
    ["path" => "/issues/create", "label" => "Create Issue"],
    ["path" => "/auth/change-password", "label" => "Change Password"]
];

?>

<header>
    <nav class="navbar">
        <h1>
            <a href="/">
                Issue Tracker
            </a>
        </h1>

        <?php

        use Core\Http\Request;
        use Core\Http\Session;

        if (Session::get('user')) : ?>
            <ul class="nav-links center">
                <?php foreach ($navbarLinks as $navLink) : ?>
                    <li>
                        <a href="<?= $navLink['path'] ?>" <?= Request::getCurrentPath() === $navLink['path'] ? ' class="active"' : '' ?>><?= $navLink['label'] ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <ul class="nav-links">
                <li>
                    <form action="/auth/signout?redirectTo=<?= Request::getRequestURI() ?>" method="POST">
                        <button class="primary-btn signout-btn">Sign Out</button>
                    </form>
                </li>
            </ul>
        <?php else : ?>
            <ul class="nav-links">
                <li><a href="/auth/signin">Sign In</a></li>
                <li><a href="/auth/signup">Sign Up</a></li>
            </ul>
        <?php endif; ?>
    </nav>
</header>