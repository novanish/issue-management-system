<h1><?= $subDashboardData['name'] ?></h1>
<?php
extract($subDashboardContentData);

require $subDashboardContent;
?>