<?php
$footerYear = date('Y');
?>
<style>
    html {
        height: 100%;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    body > *:not(.site-footer) {
        flex: 0 0 auto;
    }

    .site-footer {
        margin-top: auto;
        background: #1f4d3b;
        color: #e8f4ee;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
    }

    .site-footer .footer-inner {
        min-height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 12px 0;
        font-size: 14px;
        font-weight: 600;
    }
</style>
<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?php echo $footerYear; ?> RaBu. All rights reserved.</span>
    </div>
</footer>
