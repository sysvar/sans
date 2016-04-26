<?php
    require('./assets/inc/mysqli_connect.php');

    # Finding Hosts with Vulnerable Services Yesterday
    $query1 = "
        SELECT
          banners.por_ip AS Host,
          banners.por_port AS Port,
          vulnerability.vul_software AS Software,
          banners.ban_banner AS Banner
        FROM banners
          INNER JOIN vulnerability
            ON banners.ban_banner = vulnerability.vul_banner
          INNER JOIN scan
            ON banners.scn_id = scan.scn_id
        WHERE scan.scn_date = SUBDATE(CURDATE(), 1)
        GROUP BY vulnerability.vul_software,
                 banners.por_port,
                 banners.ban_banner,
                 banners.por_ip
    ";

    $response1 = @mysqli_query($dbc, $query1);          # Store response from database
    while ($row = mysqli_fetch_array($response1)) {     # Fetch results as array
        $array1[] = $row;                               # Store results in array1
    }

    # Finding Hosts with Vulnerable Services Today
    $query2 = "         
        SELECT
          banners.por_ip AS Host,
          banners.por_port AS Port,
          vulnerability.vul_software AS Software,
          banners.ban_banner AS Banner
        FROM banners
          INNER JOIN vulnerability
            ON banners.ban_banner = vulnerability.vul_banner
          INNER JOIN scan
            ON banners.scn_id = scan.scn_id
        WHERE scan.scn_date = CURDATE()
        GROUP BY vulnerability.vul_software,
                 banners.por_port,
                 banners.ban_banner,
                 banners.por_ip
    ";

    $response2 = @mysqli_query($dbc, $query2);          # Store response from database
    while ($row = mysqli_fetch_array($response2)) {     # Fetch results as array
        $array2[] = $row;                               # Store results in array2
    }

    function check_diff_multi($a1, $a2) {       # Get array 1 and 2
        $result = array();                      # Initialise Output Variable as an array containing all values present in array 1 but absent in array 2
        $i = 0;                                 # Initialise Variable
        foreach ($a1 as $ka1 => $va1) {         # Loop through array 1
            $found = false;
            foreach ($a2 as $ka2 => $va2) {     # Loop through array 2 in which we compare each value of array 1 with all the values of array 2 one at a time
                $i++;
                $x = array_diff($va1, $va2);    # array_diff — Computes the difference of arrays
                if (empty($x)) {                # I.e. values match
                    $found = true;              
                    unset($a2[$ka2], $a1[$ka1]);# Remove that value from array 2 and continue to next value
                }
            }
            if (!$found) {                      # If no value in array 2 matches value in array 1
                $result[] = $va1;               # Add that value of array 1 in result
            }
        }
        return $result;
    }
?>
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
                <div class="col-1-8 no-pad">
                    <div class="content blue ser-box">
                        <div class="ser-name">FTP</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content blue ser-box">
                        <div class="ser-stat">
                            <?php
                            // Create a query for the database
                            $query = "
                                        SELECT
                                          COUNT(ports.por_port) AS Scanned
                                        FROM ports
                                          INNER JOIN ports_scan
                                            ON ports.por_id = ports_scan.por_id
                                          INNER JOIN scan
                                            ON ports_scan.scn_id = scan.scn_id
                                        WHERE scan.scn_date = CURDATE()
                                        AND ports.por_status = TRUE
                                        AND ports.por_port = 21
                                    ";

                            // Get a response from the database by sending the connection and the query
                            $response = @mysqli_query($dbc, $query);

                            // If the query executed properly, proceed
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Open</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content blue ser-box">
                        <div class="ser-stat">
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
                                        AND vulnerability.vul_port = 21
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Vuln</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content blue ser-box">
                        <div class="ser-stat">
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
                                        AND vulnerability.vul_port = 21
                                        AND vulnerability.vul_exploitable = TRUE
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Expl</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content green ser-box">
                        <div class="ser-stat">                            
                            <?php
                            $query = "
                                        SELECT
                                          COUNT(banners.ban_banner) AS Scanned
                                        FROM banners
                                          INNER JOIN scan
                                            ON banners.scn_id = scan.scn_id
                                          INNER JOIN vulnerability
                                            ON banners.ban_banner = vulnerability.vul_banner
                                          INNER JOIN vulnerability_severity
                                            ON vulnerability.vul_severity_id = vulnerability_severity.vul_severity_id
                                        WHERE scan.scn_date = CURDATE()
                                        AND vulnerability_severity.vul_severity = 'Low'
                                        AND vulnerability.vul_port = 21
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Low</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content yellow ser-box">
                        <div class="ser-stat">
                            <?php
                            $query = "
                                        SELECT
                                          COUNT(banners.ban_banner) AS Scanned
                                        FROM banners
                                          INNER JOIN scan
                                            ON banners.scn_id = scan.scn_id
                                          INNER JOIN vulnerability
                                            ON banners.ban_banner = vulnerability.vul_banner
                                          INNER JOIN vulnerability_severity
                                            ON vulnerability.vul_severity_id = vulnerability_severity.vul_severity_id
                                        WHERE scan.scn_date = CURDATE()
                                        AND vulnerability_severity.vul_severity = 'Medium'
                                        AND vulnerability.vul_port = 21
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Medium</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content orange ser-box">
                        <div class="ser-stat">
                            <?php
                            $query = "
                                        SELECT
                                          COUNT(banners.ban_banner) AS Scanned
                                        FROM banners
                                          INNER JOIN scan
                                            ON banners.scn_id = scan.scn_id
                                          INNER JOIN vulnerability
                                            ON banners.ban_banner = vulnerability.vul_banner
                                          INNER JOIN vulnerability_severity
                                            ON vulnerability.vul_severity_id = vulnerability_severity.vul_severity_id
                                        WHERE scan.scn_date = CURDATE()
                                        AND vulnerability_severity.vul_severity = 'High'
                                        AND vulnerability.vul_port = 21
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">High</div>
                    </div>
                </div>
                <div class="col-1-8">
                    <div class="content red ser-box">
                        <div class="ser-stat">
                            <?php
                            $query = "
                                        SELECT
                                          COUNT(banners.ban_banner) AS Scanned
                                        FROM banners
                                          INNER JOIN scan
                                            ON banners.scn_id = scan.scn_id
                                          INNER JOIN vulnerability
                                            ON banners.ban_banner = vulnerability.vul_banner
                                          INNER JOIN vulnerability_severity
                                            ON vulnerability.vul_severity_id = vulnerability_severity.vul_severity_id
                                        WHERE scan.scn_date = CURDATE()
                                        AND vulnerability_severity.vul_severity = 'Critical'
                                        AND vulnerability.vul_port = 21
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Critical</div>
                    </div>
                </div>
            </div>

            <div class="spacer"></div>

            <!-- SSH Grid -->

            <div class="grid grid-pad">
                <div class="col-1-8 no-pad">
                    <div class="content blue ser-box">
                        <div class="ser-name">SSH</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content blue ser-box">
                        <div class="ser-stat">
                            <?php
                            $query = "
                                        SELECT
                                          COUNT(ports.por_port) AS Scanned
                                        FROM ports
                                          INNER JOIN ports_scan
                                            ON ports.por_id = ports_scan.por_id
                                          INNER JOIN scan
                                            ON ports_scan.scn_id = scan.scn_id
                                        WHERE scan.scn_date = CURDATE()
                                        AND ports.por_status = TRUE
                                        AND ports.por_port = 22
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Open</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content blue ser-box">
                        <div class="ser-stat">
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
                                        AND vulnerability.vul_port = 22
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Vuln</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content blue ser-box">
                        <div class="ser-stat">
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
                                        AND vulnerability.vul_port = 22
                                        AND vulnerability.vul_exploitable = TRUE
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Expl</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content green ser-box">
                        <div class="ser-stat">
                            <?php
                            $query = "
                                        SELECT
                                          COUNT(banners.ban_banner) AS Scanned
                                        FROM banners
                                          INNER JOIN scan
                                            ON banners.scn_id = scan.scn_id
                                          INNER JOIN vulnerability
                                            ON banners.ban_banner = vulnerability.vul_banner
                                          INNER JOIN vulnerability_severity
                                            ON vulnerability.vul_severity_id = vulnerability_severity.vul_severity_id
                                        WHERE scan.scn_date = CURDATE()
                                        AND vulnerability_severity.vul_severity = 'Low'
                                        AND vulnerability.vul_port = 22
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Low</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content yellow ser-box">
                        <div class="ser-stat">
                            <?php
                            $query = "
                                        SELECT
                                          COUNT(banners.ban_banner) AS Scanned
                                        FROM banners
                                          INNER JOIN scan
                                            ON banners.scn_id = scan.scn_id
                                          INNER JOIN vulnerability
                                            ON banners.ban_banner = vulnerability.vul_banner
                                          INNER JOIN vulnerability_severity
                                            ON vulnerability.vul_severity_id = vulnerability_severity.vul_severity_id
                                        WHERE scan.scn_date = CURDATE()
                                        AND vulnerability_severity.vul_severity = 'Medium'
                                        AND vulnerability.vul_port = 22
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Medium</div>
                    </div>
                </div>
                <div class="col-1-8 no-pad">
                    <div class="content orange ser-box">
                        <div class="ser-stat">
                            <?php
                            $query = "
                                        SELECT
                                          COUNT(banners.ban_banner) AS Scanned
                                        FROM banners
                                          INNER JOIN scan
                                            ON banners.scn_id = scan.scn_id
                                          INNER JOIN vulnerability
                                            ON banners.ban_banner = vulnerability.vul_banner
                                          INNER JOIN vulnerability_severity
                                            ON vulnerability.vul_severity_id = vulnerability_severity.vul_severity_id
                                        WHERE scan.scn_date = CURDATE()
                                        AND vulnerability_severity.vul_severity = 'High'
                                        AND vulnerability.vul_port = 22
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">High</div>
                    </div>
                </div>
                <div class="col-1-8">
                    <div class="content red ser-box">
                        <div class="ser-stat">
                            <?php
                            $query = "
                                        SELECT
                                          COUNT(banners.ban_banner) AS Scanned
                                        FROM banners
                                          INNER JOIN scan
                                            ON banners.scn_id = scan.scn_id
                                          INNER JOIN vulnerability
                                            ON banners.ban_banner = vulnerability.vul_banner
                                          INNER JOIN vulnerability_severity
                                            ON vulnerability.vul_severity_id = vulnerability_severity.vul_severity_id
                                        WHERE scan.scn_date = CURDATE()
                                        AND vulnerability_severity.vul_severity = 'Critical'
                                        AND vulnerability.vul_port = 22
                                    ";
                            $response = @mysqli_query($dbc, $query);
                            if ($response) {
                                $row = mysqli_fetch_assoc($response);
                                echo $row['Scanned'];
                            } else {
                                echo "Couldn't issue database query<br />";
                                echo mysqli_error($dbc);
                            }
                            ?>
                        </div>
                        <div class="ser-info">Critical</div>
                    </div>
                </div>
            </div>
    
            <div class="grid grid-pad">
                <div class="col-1-2">
                    <div class="white shadow">
                        <div class="table-header">
                            <h2>Vulnerabilities Analysed</h2>
                        </div>
                        <div class="content-hosts">
                            <?php
                                $query = "  
                                            SELECT
                                              vulnerability.vul_port AS Ports,
                                              COUNT(banners.por_ip) AS Hosts,
                                              vulnerability.vul_software AS Software,
                                              vulnerability.vul_banner AS Banner,
                                              vulnerability_type.vul_type AS Type,
                                              vulnerability_impact.vul_impact AS Impact
                                            FROM banners
                                              INNER JOIN vulnerability
                                                ON banners.ban_banner = vulnerability.vul_banner
                                              INNER JOIN vulnerability_impact
                                                ON vulnerability.vul_impact_id = vulnerability_impact.vul_impact_id
                                              INNER JOIN vulnerability_type
                                                ON vulnerability.vul_type_id = vulnerability_type.vul_type_id
                                            WHERE banners.scn_id = (SELECT
                                                MAX(scn_id) AS scanid
                                              FROM scan)
                                            GROUP BY vulnerability.vul_port,
                                                     vulnerability.vul_software,
                                                     vulnerability_type.vul_type,
                                                     vulnerability_impact.vul_impact
                                            ";
                                $response = @mysqli_query($dbc, $query);
                                if($response){                            
                                    echo '<table align="left" cellspacing="6" cellpadding="3">
                                    <tr><td align="left"><b>Port</b></td>
                                    <td align="left"><b>Hosts</b></td>
                                    <td align="left"><b>Software</b></td>
                                    <td align="left"><b>Banner</b></td>
                                    <td align="left"><b>Type</b></td>
                                    <td align="left"><b>Impact</b></td>';

                                    // mysqli_fetch_array will return a row of data from the query until no further data is available
                                    while($row = mysqli_fetch_array($response)){
                                        echo '<tr><td align="left">' . 
                                        $row['Ports'] . '</td><td align="left">' . 
                                        $row['Hosts'] . '</td><td align="left">' .
                                        $row['Software'] . '</td><td align="left">' .
                                        $row['Banner'] . '</td><td align="left">' .
                                        $row['Type'] . '</td><td align="left">' .
                                        $row['Impact'] . '</td><td align="left">';
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
                            <h2>Vulnerable Services Patched</h2>
                        </div>
                        <div class="content-hosts">
                            <?php
                            $response = check_diff_multi($array1, $array2);
                            if ($response) {
                                echo '<table align="left" cellspacing="6" cellpadding="3">
                                    <tr><td align="left"><b>Host</b></td>
                                    <td align="left"><b>Port</b></td>
                                    <td align="left"><b>Software</b></td>
                                    <td align="left"><b>Banner</b></td>';

                                foreach ($response as $row) {
                                    echo '<tr><td align="left">' .
                                    $row['Host'] . '</td><td align="left">' .
                                    $row['Port'] . '</td><td align="left">' .
                                    $row['Software'] . '</td><td align="left">' .
                                    $row['Banner'] . '</td><td align="left">';
                                    echo '</tr>';
                                }
                                echo '</table>';
                            } else {
                                echo "Comparison found no changes in results<br />";
                                echo mysqli_error($dbc);
                            }
                            ?> 
                        </div>
                    </div>
                </div>
            </div>            
            
            <div class="grid grid-pad">
                <div class="col-1-2">
                    <div class="white shadow">
                        <div class="table-header">
                            <h2>Vulnerable Services Yesterday</h2>
                        </div>
                        <div class="content-hosts">
                            <?php
                            if ($response1) {
                                echo '<table align="left" cellspacing="6" cellpadding="3">
                                    <tr><td align="left"><b>Host</b></td>
                                    <td align="left"><b>Port</b></td>
                                    <td align="left"><b>Software</b></td>
                                    <td align="left"><b>Banner</b></td>';

                                // mysqli_fetch_array will return a row of data from the query until no further data is available
                                foreach ($array1 as $row) {
                                    echo '<tr><td align="left">' .
                                    $row['Host'] . '</td><td align="left">' .
                                    $row['Port'] . '</td><td align="left">' .
                                    $row['Software'] . '</td><td align="left">' .
                                    $row['Banner'] . '</td><td align="left">';
                                    echo '</tr>';
                                }
                                echo '</table>';
                            } else {
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
                            <h2>Vulnerable Services Today</h2>
                        </div>
                        <div class="content-hosts">
                            <?php
                            $response = @mysqli_query($dbc, $query);
                            if ($response2) {
                                echo '<table align="left" cellspacing="6" cellpadding="3">
                                    <tr><td align="left"><b>Host</b></td>
                                    <td align="left"><b>Port</b></td>
                                    <td align="left"><b>Software</b></td>
                                    <td align="left"><b>Banner</b></td>';

                                // mysqli_fetch_array will return a row of data from the query until no further data is available
                                foreach ($array2 as $row) {
                                    echo '<tr><td align="left">' .
                                    $row['Host'] . '</td><td align="left">' .
                                    $row['Port'] . '</td><td align="left">' .
                                    $row['Software'] . '</td><td align="left">' .
                                    $row['Banner'] . '</td><td align="left">';
                                    echo '</tr>';
                                }
                                echo '</table>';
                            } else {
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


