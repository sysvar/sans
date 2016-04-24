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
                
                <div class="col-1-2">
                   <div class="white shadow">
                       <div class="table-header">
                            <h2>Hosts Offline</h2>
                       </div>
                       <div class="content-hosts">
                            <?php
                                // Create a query for the database
                                $query2 = "
                                            SELECT DISTINCT
                                              ports.por_ip
                                            FROM ports
                                              INNER JOIN ports_scan
                                                ON ports.por_id = ports_scan.por_id
                                              INNER JOIN scan
                                                ON ports_scan.scn_id = scan.scn_id
                                            WHERE scan.scn_date = CURDATE()
                                            AND ports.por_status = 0
                                            AND ports.por_ip NOT IN (SELECT
                                                ports.por_ip
                                              FROM ports
                                              WHERE ports.por_status = 1)
                                        ";
                                $query = "
                                            SELECT
                                              ports.por_ip
                                            FROM ports
                                              INNER JOIN ports_scan
                                                ON ports.por_id = ports_scan.por_id
                                              INNER JOIN scan
                                                ON ports_scan.scn_id = scan.scn_id
                                            WHERE scan.scn_date = CURDATE()
                                            GROUP BY ports.por_ip
                                            HAVING COUNT(1) = COUNT(CASE WHEN ports.por_status = 0 THEN 1 END)
                                        ";

                                // Get a response from the database by sending the connection and the query
                                $response = @mysqli_query($dbc, $query);

                                // If the query executed properly, proceed
                                if($response){
                                    echo '<table align="left" cellspacing="6">';
                                    
                                    // mysqli_fetch_array will return a row of data from the query until no further data is available
                                    while($row = mysqli_fetch_array($response)){
                                        echo '<tr><td align="left">' . 
                                        $row['por_ip'] . '</td><td align="left">';
                                        echo '</tr>';
                                    }
                                    echo '</table>';
                                }
                                else {
                                    echo "Couldn't issue database query<br />";
                                    echo mysqli_error($dbc);
                                }                     
                           ?>
                       </div>
                   </div>
                </div>
                
                <div class="col-1-2">
                   <div class="white shadow">
                        <div class="table-header">
                            <h2>Hosts Online</h2>
                        </div>
                        <div class="content-hosts">
                            <?php
                                $query = "
                                            SELECT DISTINCT
                                              ports.por_ip
                                            FROM ports
                                              INNER JOIN ports_scan
                                                ON ports.por_id = ports_scan.por_id
                                              INNER JOIN scan
                                                ON ports_scan.scn_id = scan.scn_id
                                            WHERE ports.por_status IS TRUE
                                            AND scan.scn_date = CURDATE()
                                        ";                                
                                $response = @mysqli_query($dbc, $query);
                                if($response){
                                    echo '<table align="left" cellspacing="6">';
                                    while($row = mysqli_fetch_array($response)){
                                        echo '<tr><td align="left">' . 
                                        $row['por_ip'] . '</td><td align="left">';
                                        echo '</tr>';
                                    }
                                    echo '</table>';
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
        </main>
        
        <?php require('./assets/inc/footer.php'); ?>
        
	</body>

</html>
<?php mysqli_close($dbc); ?>