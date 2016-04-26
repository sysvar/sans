<?php
    require('./assets/inc/mysqli_connect.php');
    
    # Finding Hosts with Unwanted Services Yesterday
    $query1 = "
        SELECT
          ports.por_ip AS `IP Address`,
          ports.por_port AS Port,
          service_category.svc_category AS Type,
          service_problem.svc_problem AS Problem
        FROM service
          INNER JOIN service_problem
            ON service.svc_problem_id = service_problem.svc_problem_id
          INNER JOIN service_category
            ON service.svc_category_id = service_category.svc_category_id
          INNER JOIN ports
            ON ports.por_port = service.svc_port
          INNER JOIN ports_scan
            ON ports.por_id = ports_scan.por_id
          INNER JOIN scan
            ON ports_scan.scn_id = scan.scn_id
        WHERE ports.por_status = TRUE
        AND scan.scn_date = SUBDATE(CURDATE(), 1)
    ";

    $response1 = @mysqli_query($dbc, $query1);          # Store response from database
    while ($row = mysqli_fetch_array($response1)) {     # Fetch results as array
        $array1[] = $row;                               # Store results in array1
    }

    # Finding Hosts with Unwanted Services Today
    $query2 = "
        SELECT
          ports.por_ip AS `IP Address`,
          ports.por_port AS Port,
          service_category.svc_category AS Type,
          service_problem.svc_problem AS Problem
        FROM service
          INNER JOIN service_problem
            ON service.svc_problem_id = service_problem.svc_problem_id
          INNER JOIN service_category
            ON service.svc_category_id = service_category.svc_category_id
          INNER JOIN ports
            ON ports.por_port = service.svc_port
          INNER JOIN ports_scan
            ON ports.por_id = ports_scan.por_id
          INNER JOIN scan
            ON ports_scan.scn_id = scan.scn_id
        WHERE ports.por_status = TRUE
        AND scan.scn_date = CURDATE()
    ";

    $response2 = @mysqli_query($dbc, $query2);          # Store response from database
    while ($row = mysqli_fetch_array($response2)) {     # Fetch results as array
        $array2[] = $row;                               # Store results in array2
    }

    # Function to check the difference
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
                <div class="col-1-2">
                    <div class="white shadow fade">
                        <div class="table-header">
                            <h2>Unwanted Service Summary Today</h2>
                        </div>
                        <div class="content-hosts">
                            <?php
                            // Create a query for the database
                            $query = "
                                        SELECT
                                          service.svc_port AS Port,
                                          service.svc_acronym AS Acronym,
                                          service.svc_service AS Service,
                                          COUNT(p.por_port) AS Affected
                                        FROM service
                                          LEFT OUTER JOIN ports p
                                            ON service.svc_port = p.por_port
                                          INNER JOIN ports_scan
                                            ON p.por_id = ports_scan.por_id
                                          INNER JOIN scan
                                            ON ports_scan.scn_id = scan.scn_id
                                        WHERE p.por_status = TRUE
                                        AND scan.scn_date = CURDATE()
                                        GROUP BY service.svc_port
                                    ";

                            // Get a response from the database by sending the connection and the query
                            $response = @mysqli_query($dbc, $query);

                            // If the query executed properly, proceed
                            if ($response) {
                                echo '<table align="left" cellspacing="6" cellpadding="3">

                                    <tr><td align="left"><b>Port</b></td>
                                    <td align="left"><b>Acronym</b></td>
                                    <td align="left"><b>Service</b></td>
                                    <td align="left"><b>Affected</b></td>';

                                // mysqli_fetch_array will return a row of data from the query until no further data is available
                                while ($row = mysqli_fetch_array($response)) {
                                    echo '<tr><td align="left">' .
                                    $row['Port'] . '</td><td align="left">' .
                                    $row['Acronym'] . '</td><td align="left">' .
                                    $row['Service'] . '</td><td align="left">' .
                                    $row['Affected'] . '</td><td align="left">';
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
                    <div class="white shadow fade">
                        <div class="table-header">
                            <h2>Hosts with Unwanted Services Patched</h2>
                        </div>
                        <div class="content-hosts">
                            <?php
                            $response = check_diff_multi($array1, $array2);
                            if ($response) {
                                ?><table align="left" cellspacing="6" cellpadding="3">
                                    <tr>
                                        <td align="left"><b>IP Address</b></td>
                                        <td align="left"><b>Port</b></td>
                                        <td align="left"><b>Type</b></td>
                                        <td align="left"><b>Problem</b></td>
                                    </tr>
                                    <?php
                                    foreach ($response as $row) {
                                        echo '<tr><td align="left">' .
                                        $row['IP Address'] . '</td><td align="left">' .
                                        $row['Port'] . '</td><td align="left">' .
                                        $row['Type'] . '</td><td align="left">' .
                                        $row['Problem'] . '</td><td align="left">';
                                        echo '</tr>';
                                    }
                                    echo '</table>';
                                } else {
                                    echo "Comparison found no changes in results<br />";
                                    echo mysqli_error($dbc);
                                }
                                ?> 
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-pad">
                <div class="col-1-2">
                    <div class="white shadow fade">
                        <div class="table-header">
                            <h2>Hosts with Unwanted Services Yesterday</h2>
                        </div>
                        <div class="content-hosts">
                            <?php
                            if ($response1) {
                                echo '<table align="left" cellspacing="6" cellpadding="3">
                                    <tr><td align="left"><b>IP Address</b></td>
                                    <td align="left"><b>Port</b></td>
                                    <td align="left"><b>Type</b></td>
                                    <td align="left"><b>Problem</b></td>';

                                // mysqli_fetch_array will return a row of data from the query until no further data is available
                                foreach ($array1 as $row) {
                                    echo '<tr><td align="left">' .
                                    $row['IP Address'] . '</td><td align="left">' .
                                    $row['Port'] . '</td><td align="left">' .
                                    $row['Type'] . '</td><td align="left">' .
                                    $row['Problem'] . '</td><td align="left">';
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
                    <div class="white shadow fade">
                        <div class="table-header">
                            <h2>Hosts with Unwanted Services Today</h2>
                        </div>
                        <div class="content-hosts">
                            <?php
                            if ($response2) {
                                echo '<table align="left" cellspacing="6" cellpadding="3">
                                    <tr><td align="left"><b>IP Address</b></td>
                                    <td align="left"><b>Port</b></td>
                                    <td align="left"><b>Type</b></td>
                                    <td align="left"><b>Problem</b></td>';

                                // mysqli_fetch_array will return a row of data from the query until no further data is available
                                foreach ($array2 as $row) {
                                    echo '<tr><td align="left">' .
                                    $row['IP Address'] . '</td><td align="left">' .
                                    $row['Port'] . '</td><td align="left">' .
                                    $row['Type'] . '</td><td align="left">' .
                                    $row['Problem'] . '</td><td align="left">';
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


