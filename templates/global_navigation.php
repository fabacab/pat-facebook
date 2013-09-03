<nav class="GlobalNavigation">
    <ul>
        <li<?php if ($_SERVER['PHP_SELF'] === '/index.php') : ?> class="active"<?php endif;?>><a href="/">Dashboard</a></li>
        <li<?php if ($_SERVER['PHP_SELF'] === '/reports.php' && $_GET['action'] === 'lookup') : ?> class="active"<?php endif;?>><a href="/reports.php?action=lookup">Find reports</a></li>
        <li<?php if ($_SERVER['PHP_SELF'] === '/reports.php' && $_GET['action'] === 'new') : ?> class="active"<?php endif;?>><a href="/reports.php?action=new">File a report</a></li>
    </ul>
</nav>
