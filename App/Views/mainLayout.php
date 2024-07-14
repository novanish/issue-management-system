<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= css('/reset') ?>">
    <link rel="stylesheet" href="<?= css('/variables') ?>">
    <link rel="stylesheet" href="<?= css('/global') ?>">
    <?php foreach ($styles as $style) : ?>
        <link rel="stylesheet" <?= is_array($style) ? arrayToAttributesString($style) : "href=\"{$style}\"" ?> />
    <?php endforeach; ?>

    <?php foreach ($headScripts as $headScript) : ?>
        <script <?= is_array($headScript) ? arrayToAttributesString($headScript) : "src=\"{$headScript}\"" ?>></script>
    <?php endforeach; ?>
    <title><?= $pageTitle ?></title>
</head>

<body>


    <?php

    use Core\Http\Session;

    if (Session::getFlash('flash_message') !== null)  require view('/components/banner');
    require view('/components/navbar') ?>

    <main>
        <?php
        require $mainLayoutContent ?>
    </main>

    <?php foreach ($bodyScripts as $bodyScript) : ?>
        <script <?= is_array($bodyScript) ? arrayToAttributesString($bodyScript) : "src=\"{$bodyScript}\"" ?>></script>
    <?php endforeach; ?>
</body>

</html>