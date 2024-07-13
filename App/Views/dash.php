<aside>
    <ul>Link</ul>
    <ul>Link</ul>
    <ul>Link</ul>
</aside>

<div>

    <h1><?= $dashboardData['name'] ?></h1>
    <?php extract($dashboardContentData);
    require $dashboardContent ?>

</div>