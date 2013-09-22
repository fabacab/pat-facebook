<nav class="GlobalNavigation">
    <ul>
        <li<?php if ($_SERVER['PHP_SELF'] === '/index.php') : ?> class="active"<?php endif;?>><a href="/">Dashboard</a></li>
        <li<?php if ($_SERVER['PHP_SELF'] === '/reports.php' && $_GET['action'] === 'lookup') : ?> class="active"<?php endif;?>><a href="/reports.php?action=lookup">Search</a></li>
        <li<?php if ($_SERVER['PHP_SELF'] === '/reports.php' && $_GET['action'] === 'new') : ?> class="active"<?php endif;?>><a href="/reports.php?action=new">Share</a></li>
        <li<?php if ($_SERVER['PHP_SELF'] === '/preferences.php') : ?> class="active"<?php endif;?>><a href="/preferences.php">Preferences</a></li>
        <li><a href="https://github.com/meitar/pat-facebook/wiki#user-manual" target="_blank">Help</a></li>
    </ul>
</nav>
