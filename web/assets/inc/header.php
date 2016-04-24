<header id="header">
    <div class="page-width">
        <div class="grid grid-pad no-pad">
            <div class="col-4-12">
               <div class="content">
                    <div id="logo"></div>
                    <div id="heading">SANS Dashboard</div>
               </div>
            </div>
            <div class="col-8-12">
                <div class="content">
                    <nav id="main-menu">
                        <ul>
                            <li><a href="index.php">Overview</a></li>
                            <li><a href="hosts.php">Host Analysis</a></li>
                            <li><a href="services.php">Services Analysis</a></li>
                            <li><a href="vulnerabilities.php">Vulnerability Analysis</a></li>
                            <li><a href="scanner.php">Scanner</a></li>
                        </ul>
                    </nav>

                    <div id="date" style="display:hidden">
                        <?php
                            date_default_timezone_set('Europe/London');
                            date_default_timezone_get();
                            echo date('l jS F Y g:i A ');
                        ?>
                    </div>
               </div>
            </div>
        </div>
    </div>
</header>