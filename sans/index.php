<?php require('./assets/inc/mysqli_connect.php'); ?>
<!doctype html>
<html>
	<head>
        <?php require('./assets/inc/head.php'); ?>
	</head>
    
	<body>
        <?php require('./assets/inc/header.php'); ?>
        
        <main class="page-width">
            
            <div class="spacer"></div>
                    
            <div class="grid grid-pad">
                <div class="col-1-4 box4">
                   <div class="content white shadow tblack boxpad">
                       <div class="tot-info">Total Hosts Scanned</div>
                       <div class="tot-stat">
                            <?php
                                // Create a query for the database
                                $query = "
                                            SELECT
                                              COUNT(DISTINCT p.por_ip) AS Scanned
                                            FROM ports p
                                              INNER JOIN ports_scan s
                                                ON p.por_id = s.por_id
                                              INNER JOIN scan sc
                                                ON sc.scn_id = s.scn_id
                                                AND sc.scn_date = CURDATE()
                                        ";

                                // Get a response from the database by sending the connection and the query
                                $response = @mysqli_query($dbc, $query);

                                // If the query executed properly, proceed
                                if($response){
                                    $row = mysqli_fetch_assoc($response);
                                    $total = $row['Scanned'];
                                    echo $row['Scanned'];
                                }
                                else {
                                    echo "Couldn't issue database query<br />";
                                    echo mysqli_error($dbc);
                                }
                            ?>                       
                       </div>
                   </div>
                </div>
                <div class="col-1-4 box4">
                   <div class="content white shadow tblack boxpad">
                       <div class="tot-info">Total Ports Scanned</div>
                       <div class="tot-stat">
                            <?php
                                $query = "
                                            SELECT
                                              COUNT(ports.por_status) AS Scanned
                                            FROM ports
                                              INNER JOIN ports_scan
                                                ON ports.por_id = ports_scan.por_id
                                              INNER JOIN scan
                                                ON ports_scan.scn_id = scan.scn_id
                                            WHERE scan.scn_date = CURDATE()
                                        ";                                
                                $response = @mysqli_query($dbc, $query);
                                if($response){
                                    $row = mysqli_fetch_assoc($response);
                                    echo $row['Scanned'];
                                }
                                else {
                                    echo "Couldn't issue database query<br />";
                                    echo mysqli_error($dbc);
                                }
                            ?>
                       </div>
                   </div>
                </div>
                <div class="col-1-4 box4">
                   <div class="content white shadow tblack boxpad">
                       <div class="tot-info">Unwanted Services</div>
                       <div class="tot-stat">
                            <?php
                                $query = "
                                            SELECT
                                              COUNT(ports.por_status) AS Scanned
                                            FROM ports
                                              INNER JOIN ports_scan
                                                ON ports.por_id = ports_scan.por_id
                                              INNER JOIN scan
                                                ON ports_scan.scn_id = scan.scn_id
                                              RIGHT OUTER JOIN service
                                                ON ports.por_port = service.svc_port
                                            WHERE scan.scn_date = CURDATE()
                                            AND ports.por_status = TRUE
                                        ";                                
                                $response = @mysqli_query($dbc, $query);
                                if($response){
                                    $row = mysqli_fetch_assoc($response);
                                    echo $row['Scanned'];
                                }
                                else {
                                    echo "Couldn't issue database query<br />";
                                    echo mysqli_error($dbc);
                                }
                            ?>
                       </div>
                   </div>
                </div>
                <div class="col-1-4 box4">
                   <div class="content white shadow tblack boxpad">
                       <div class="tot-info">Vulnerable Services</div>
                       <div class="tot-stat">
                            <?php
                                $query = "
                                            SELECT
                                              COUNT(banners.ban_banner) AS Scanned
                                            FROM banners
                                              INNER JOIN scan
                                                ON banners.scn_id = scan.scn_id
                                              INNER JOIN vulnerability
                                                ON banners.ban_banner = vulnerability.vul_banner
                                            WHERE scan.scn_date = CURDATE()
                                        ";                                
                                $response = @mysqli_query($dbc, $query);
                                if($response){
                                    $row = mysqli_fetch_assoc($response);
                                    echo $row['Scanned'];
                                }
                                else {
                                    echo "Couldn't issue database query<br />";
                                    echo mysqli_error($dbc);
                                }
                            ?>
                       </div>
                   </div>
                </div>
            </div>
            
            <div class="spacer"></div>
                
            <div class="grid grid-pad">
                <div class="col-1-4 box4">
                   <div class="content white shadow tblack boxpad">
                       <div class="tot-info">Hosts Online</div>
                       <div class="tot-stat">
                            <?php
                                $query = "
                                            SELECT
                                              COUNT(DISTINCT ports.por_ip) AS Scanned
                                            FROM ports
                                              INNER JOIN ports_scan
                                                ON ports.por_id = ports_scan.por_id
                                              INNER JOIN scan
                                                ON ports_scan.scn_id = scan.scn_id
                                            WHERE scan.scn_date = CURDATE()
                                            AND ports.por_status = TRUE
                                        ";                                
                                $response = @mysqli_query($dbc, $query);
                                if($response){
                                    $row = mysqli_fetch_assoc($response);
                                    $online = $row['Scanned'];
                                    echo $row['Scanned'];
                                }
                                else {
                                    echo "Couldn't issue database query<br />";
                                    echo mysqli_error($dbc);
                                }
                            ?> 
                        </div>
                   </div>
                </div>
                <div class="col-1-4 box4">
                   <div class="content white shadow tblack boxpad">
                       <div class="tot-info">Hosts Offline</div>
                       <div class="tot-stat">
                            <?php
                                $query = "SELECT COUNT(*) FROM (SELECT DISTINCT por_ip 
                                          FROM `ports` WHERE por_status IS FALSE) AS c";                                
                                $response = @mysqli_query($dbc, $query);
                                if($response){
                                    $row = mysqli_fetch_assoc($response);
                                    $hostsoffline = ($total - $online);
                                    echo $hostsoffline;
                                }
                                else {
                                    echo "Couldn't issue database query<br />";
                                    echo mysqli_error($dbc);
                                }
                            ?> 
                       </div>
                   </div>
                </div>
                <div class="col-1-4 box4">
                   <div class="content white shadow tblack boxpad">
                       <div class="tot-info">Ports Open</div>
                       <div class="tot-stat">
                            <?php
                                $query = "
                                            SELECT
                                              COUNT(ports.por_status) AS Scanned
                                            FROM ports
                                              INNER JOIN ports_scan
                                                ON ports.por_id = ports_scan.por_id
                                              INNER JOIN scan
                                                ON ports_scan.scn_id = scan.scn_id
                                            WHERE scan.scn_date = CURDATE()
                                            AND ports.por_status = TRUE
                                        ";                                
                                $response = @mysqli_query($dbc, $query);
                                if($response){
                                    $row = mysqli_fetch_assoc($response);
                                    echo $row['Scanned'];
                                }
                                else {
                                    echo "Couldn't issue database query<br />";
                                    echo mysqli_error($dbc);
                                }
                            ?>
                       </div>
                   </div>
                </div>
                <div class="col-1-4 box4">
                   <div class="content white shadow tblack boxpad">
                       <div class="tot-info">Ports Closed</div>
                       <div class="tot-stat">
                            <?php
                                $query = "
                                            SELECT
                                              COUNT(ports.por_status) AS Scanned
                                            FROM ports
                                              INNER JOIN ports_scan
                                                ON ports.por_id = ports_scan.por_id
                                              INNER JOIN scan
                                                ON ports_scan.scn_id = scan.scn_id
                                            WHERE scan.scn_date = CURDATE()
                                            AND ports.por_status = FALSE
                                        ";                                
                                $response = @mysqli_query($dbc, $query);
                                if($response){
                                    $row = mysqli_fetch_assoc($response);
                                    echo $row['Scanned'];
                                }
                                else {
                                    echo "Couldn't issue database query<br />";
                                    echo mysqli_error($dbc);
                                }
                            ?>
                       </div>
                   </div>
                </div>
            </div>
            
            <div class="spacer"></div>
               
        </main>
        
        <?php require('./assets/inc/footer.php'); ?>
        
	</body>
    
</html>
<?php mysqli_close($dbc); ?>