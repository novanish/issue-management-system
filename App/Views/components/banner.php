<?php

use Core\Http\Session;
?>
<div class="banner <?= Session::getFlash('flash_message')['type'] ?>">
    <p class="banner-message"><?= Session::getFlash('flash_message')['message'] ?></p>
    <button class="close-button" aria-label="Close" onclick="closeAlert()">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
        </svg>
    </button>
</div>

<script>
    function closeAlert() {
        const alert = document.querySelector('.banner');
        alert.style.transform = 'translateY(-200%)';
    }

    setTimeout(closeAlert, 2000)
</script>