<?php require('./assets/inc/mysqli_connect.php'); 

// Clear Database
$query1 = "DROP TABLE `banners`, `ports_scan`, `scan`, `ports`;";
$query2 = "CREATE TABLE IF NOT EXISTS `scanner`.`scan` (`scn_id` int(5) NOT NULL AUTO_INCREMENT,`scn_date` date NOT NULL, PRIMARY KEY (`scn_id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;";
$query3 = "CREATE TABLE IF NOT EXISTS `scanner`.`ports` (`por_id` int(16) NOT NULL AUTO_INCREMENT,`por_ip` varchar(15) NOT NULL,`por_port` int(5) NOT NULL,`por_status` tinyint(1) NOT NULL,`por_probe` 
           timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`por_id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;";
$query4 = "CREATE TABLE IF NOT EXISTS `scanner`.`ports_scan` (`por_scn_id` int(16) NOT NULL AUTO_INCREMENT,`por_id` int(16) NOT NULL,`scn_id` int(5) NOT NULL, PRIMARY KEY (`por_scn_id`)) 
           ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;";
$query5 = "CREATE TABLE IF NOT EXISTS `scanner`.`banners` (`scn_id` int(5) NOT NULL,`por_ip` varchar(15) NOT NULL,`por_port` int(5) NOT NULL,`ban_banner` varchar(255) NOT NULL, 
           PRIMARY KEY (`scn_id`, `por_ip`, `por_port`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;";
$query6 = "CREATE TRIGGER `Update port_scan table por_id and scan date id` AFTER INSERT ON `scanner`.`ports` FOR EACH ROW INSERT INTO `ports_scan` (`por_scn_id`,`por_id`,`scn_id`) 
           VALUES (NULL,(SELECT MAX(`por_id`) FROM `ports`), (SELECT `scn_id` FROM `scan` WHERE `scn_date` = CURDATE()));";
$query7 = "CREATE TRIGGER `Update scan table with new date` BEFORE INSERT ON `scanner`.`ports` FOR EACH ROW INSERT INTO scan (scn_id, scn_date) SELECT (COALESCE(MAX(scn_id),0)+1), CURDATE() 
           FROM scan HAVING COUNT(CASE WHEN scan.scn_date = CURDATE() THEN 1 end) = 0;";
$query8 = "ALTER TABLE `scanner`.`ports` ADD INDEX `index_ip` (`por_ip`);";
$query9 = "ALTER TABLE `scanner`.`ports` ADD INDEX `index_port` (`por_port`);";
$query10 = "ALTER TABLE `scanner`.`banners` ADD CONSTRAINT `id2` FOREIGN KEY (`scn_id`) REFERENCES `scanner`.`scan`(`scn_id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `scanner`.`banners` 
            ADD CONSTRAINT `ip1` FOREIGN KEY (`por_ip`) REFERENCES `scanner`.`ports`(`por_ip`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `scanner`.`banners` ADD CONSTRAINT `por1` FOREIGN KEY
           (`por_port`) REFERENCES `scanner`.`ports`(`por_port`) ON DELETE CASCADE ON UPDATE CASCADE;";
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
                <div class="col-1-1">
                    <div class="white shadow fade">
                        <div class="table-header">
                            <h2>Scanner Scheduler</h2>
                        </div>
                        <div class="content-hosts">
                            <p>Here you can scedule a new scan, these scans will take place at midnight everyday of the week, as well a one-off scan taking place now.</p>
                            <p>Please take local laws into account before initiating any scanning and know that scanning with malicious intent is always illigal.</p>
                            <p class="tred tsmall"><strong>NOTE:</strong> Updating the scan schedule will result in the database being reset and all results will be deleted!</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-pad">
                <div class="col-1-2">
                    <div class="white shadow fade">
                        <div class="table-header">
                            <h2>Schedule a Small Scan</h2>
                        </div>
                        <div class="content-hosts">
                            <p>Input targets and ports via text input box</p>
                            <p class="tsmall"><em>Seperate IP Addresses and Ports with commas, no spaces, no netblock prefixes</em></p>
                            <br />
                            <form action="" method="post">
                                <table id="scanner-table">
                                    <tbody>
                                        <tr>
                                            <td class="table30"><label for="hosts">IP Addresses:</label></td>
                                            <td class="table70"><input id="hosts" class="max input-height" type="text" name="hosts" value="" required /></td>
                                            <td>
                                        </tr>
                                        <tr>
                                            <td class="table30"><label for="ports">Ports:</label></td>
                                            <td class="table70"><input id="ports" class="max input-height" type="text" name="ports" required /></td>
                                        </tr>
                                        <tr>
                                            <td class="table30"><label for="timeout">Timeout:</label></td>
                                            <td class="table70"><input id="timeout" class="max input-height" type="number" name="timeout" value="3" required /></td>
                                        </tr>
                                        <tr>
                                            <td class="table30"><label for="threading">Threading:</label></td>
                                            <td class="table70"><input id="threading" class="max input-height" type="number" name="threading" value="1" required /></td>
                                        </tr>
                                        <tr>
                                            <td class="submit"><input type="submit" name="form1" value="Scan Now and Schedule" /></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>
                            <?php
                                if (isset($_POST['form1'])) {
                                    // Initialise variables
                                    $hosts = ""; $ports = ""; $timeout = ""; $threading = ""; $error = false;
    
                                    // Get input from form
                                    $hosts = isset($_POST['hosts']) ? $_POST['hosts'] : '';
                                    $ports = isset($_POST['ports']) ? $_POST['ports'] : '';
                                    $timeout = isset($_POST['timeout']) ? $_POST['timeout'] : '';
                                    $threading = isset($_POST['threading']) ? $_POST['threading'] : '';
    
                                    // Check IP Address(es) Input Validation
                                    if (!empty($hosts)) {
                                        if (!preg_match("((25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)(,\n|,?$))",$hosts)) {
                                            $errortext = "Invalid IP Address(es)";
                                            $error = true;
                                        }
                                    }
                                    // Check Port(s) Input Validation
                                    if (!empty($ports)) {
                                        if (!preg_match("(^(6553[0-5]|655[0-2][0-9]|65[0-4][0-9]{2}|6[0-4][0-9]{3}|[1-5][0-9]{4}|[1-9][0-9]{1,3}|[0-9])(?:,(?1))*$)",$ports)) {
                                            $errortext = "Invalid Port(s)";
                                            $error = true;
                                        }
                                    }
                                    // Check Timeout Input Validation
                                    if (!empty($timeout)) {
                                        if (!preg_match("([0-9]{1,2})",$timeout)) {
                                            $errortext = "Invalid Timeout Value";
                                            $error = true;
                                        }
                                    }
                                    // Check Threading Input Validation
                                    if (!empty($threading)) {
                                        if (!preg_match("([0-9]{1,2})",$threading)) {
                                            $errortext = "Invalid Threading Value";
                                            $error = true;
                                        }
                                    }
                                    // If error variable equels true then show associated error
                                    if ($error == true) {
                                        echo $errortext;
                                    }
                                    // Run database reset query   
                                    if ($error == false and !empty($hosts) and !empty($ports) and !empty($timeout) and !empty($threading)) {    
                                        @mysqli_query($dbc, $query1);
                                        @mysqli_query($dbc, $query2);
                                        @mysqli_query($dbc, $query3);
                                        @mysqli_query($dbc, $query4);
                                        @mysqli_query($dbc, $query5);
                                        @mysqli_query($dbc, $query6);
                                        @mysqli_query($dbc, $query7);
                                        @mysqli_query($dbc, $query8);
                                        @mysqli_query($dbc, $query9);
                                        @mysqli_query($dbc, $query10);
                                        echo "<br/>";
                                        echo "Database Reset, Schedule Task Modified, Scan Started";
                                        exec("echo '#!/bin/bash \n' > /opt/sans/schedule.sh");
                                        exec("echo '/usr/bin/python2.7 /opt/sans/sans.py -i '$hosts' -p '$ports' -t '$timeout' -c '$threading' >> /var/log/sans.log' >> /opt/sans/schedule.sh");
                                        exec("nohup /opt/sans/schedule.sh > /dev/null 2>&1 &");
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-1-2">
                    <div class="white shadow fade">
                        <div class="table-header">
                            <h2>Schedule a Large Scan</h2>
                        </div>
                        <div class="content-hosts">
                            <p>Input targets and ports via file upload</p>
                            <p class="tsmall"><em>1 IP Address and Port per line, no netblock prefixes, extensions: .txt, .text, .lst</em></p>
                            <br />                                                                  
                            <form action="" method="post" enctype="multipart/form-data">
                                <table id="scanner-table">
                                    <tbody>
                                        <tr>
                                            <td class="table30"><label for="targets">IP Addresses File:</label></td>
                                            <td class="table70"><input class="max" id="hosts" type="file" name="hostsfile" required /></td>
                                            <td>
                                        </tr>
                                        <tr>
                                            <td class="table30"><label for="ports">Ports File:</label></td>
                                            <td class="table70"><input class="max" id="hosts" type="file" name="portsfile" required /></td>
                                        </tr>
                                        <tr>
                                            <td class="table30"><label for="timeout2">Timeout:</label></td>
                                            <td class="table70"><input class="max input-height" type="number" name="timeout2" value="3" required /></td>
                                        </tr>
                                        <tr>
                                            <td class="table30"><label for="threading2">Threading:</label></td>
                                            <td class="table70"><input id="threading2" class="max input-height" type="number" name="threading2" value="1" required /></td>
                                        </tr>
                                        <tr>
                                            <td class="submit"><input type="submit" name="form2" value="Scan Now and Schedule" /></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>
                            <p class="tsmall"><a target="_blank" href="http://www.magic-cookie.co.uk/iplist.html">Helpful List generator</a></p>
                            <?php
                                if (isset($_POST['form2'])) {
                                    $error2 = true;
                                    $allowed_file_types = array('.txt','.text','.lst');
                                    $timeout2 = '';
                                    $timeout2 = isset($_POST['timeout2']) ? $_POST['timeout2'] : '';
                                    $threading2 = '';
                                    $threading2 = isset($_POST['threading2']) ? $_POST['threading2'] : '';
                            
                                    $file_hosts = $_FILES["hostsfile"]["name"];
                                    $name_hosts = substr($file_hosts, 0, strripos($file_hosts, '.')); // Returns the file name
                                    $ext_hosts = substr($file_hosts, strripos($file_hosts, '.')); // Returns the file extension
                                    $size_hosts = $_FILES["hostsfile"]["size"];
                            
                                    if (in_array($ext_hosts,$allowed_file_types) && ($size_hosts < 10485760)) {
                                        // Rename file
                                        $new_hosts = "hosts.txt";
                                        move_uploaded_file($_FILES["hostsfile"]["tmp_name"], "/opt/sans/" . $new_hosts);
                                        echo "Hosts file uploaded successfully. ";
                                        $error2 = false;
                                    }
                                    elseif (empty($name_hosts)) {
                                        // file selection error
                                        echo "Please select a hosts file to upload. ";
                                    }
                                    elseif ($size_hosts > 10485760) {
                                        // file size error
                                        echo "The hosts file you are trying to upload is too large. ";
                                        }
                                    else {
                                        // file type error
                                        echo "Only these file typs are allowed for upload: " . implode(', ',$allowed_file_types);
                                        unlink($_FILES["hostsfile"]["tmp_name"]);
                                    }
                            
                                    $file_ports = $_FILES["portsfile"]["name"];
                                    $name_ports = substr($file_ports, 0, strripos($file_ports, '.')); // Returns the file name
                                    $ext_ports = substr($file_ports, strripos($file_ports, '.')); // Returns the file extension
                                    $size_ports = $_FILES["portsfile"]["size"];
                            
                                    if (in_array($ext_ports,$allowed_file_types) && ($size_ports < 10485760)) {
                                        // Rename file
                                        $new_ports = "ports.txt";
                                        move_uploaded_file($_FILES["portsfile"]["tmp_name"], "/opt/sans/" . $new_ports);
                                        echo "Ports file uploaded successfully.";
                                        $error2 = false;
                                    }
                                    elseif (empty($name_ports)) {
                                        // file selection error
                                        echo "Please select a ports file to upload.";
                                    }
                                    elseif ($size_ports > 10485760) {
                                        // file size error
                                        echo "The ports file you are trying to upload is too large.";
                                            }
                                    else {
                                        // file type error
                                        echo "Only these file typs are allowed for upload: " . implode(', ',$allowed_file_types);
                                        unlink($_FILES["portsfile"]["tmp_name"]);
                                    }
                                    if (!empty($timeout2)) {
                                        if (!preg_match("([0-9]{1,2})",$timeout2)) {
                                            $errortext = "Invalid Timeout Value";
                                            $error = true;
                                        }
                                    }
                                    if (!empty($threading2)) {
                                        if (!preg_match("([0-9]{1,2})",$threading2)) {
                                            $errortext = "Invalid Threading Value";
                                            $error = true;
                                        }
                                    }

                                    if ($error2 == false) { 
                                        // Run database reset query 
                                        @mysqli_query($dbc, $query1);
                                        @mysqli_query($dbc, $query2);
                                        @mysqli_query($dbc, $query3);
                                        @mysqli_query($dbc, $query4);
                                        @mysqli_query($dbc, $query5);
                                        @mysqli_query($dbc, $query6);
                                        @mysqli_query($dbc, $query7);
                                        @mysqli_query($dbc, $query8);
                                        @mysqli_query($dbc, $query9);
                                        @mysqli_query($dbc, $query10);
                                        echo "<br/>";
                                        echo "Database Reset, Schedule Task Modified, Scan Started";
                                    
                                        exec("echo '#!/bin/bash \n' > /opt/sans/schedule.sh");
                                        exec("echo '/usr/bin/python2.7 /opt/sans/sans.py -I '/opt/sans/hosts.txt' -P '/opt/sans/ports.txt' -t '$timeout2' -c '$threading2' >> /var/log/sans.log' >> /opt/sans/schedule.sh");
                                        exec("nohup /opt/sans/schedule.sh > /dev/null 2>&1 &");
                                    }
                                }
                            ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="grid grid-pad">
                <div class="col-1-2">
                    <div class="white shadow fade">
                        <div class="table-header">
                            <h2>Abort Scan &amp; Cancel Schedule</h2>
                        </div>
                        <div class="content-hosts">
                            <form action="" method="post">
                                <span class="submit submit-red">
                                    <input type="submit" name="abort" value="Abort!" />
                                </span>
                            </form>
                            <?php
                                if (isset($_POST['abort'])) {
                                    exec("pkill -f /usr/bin/python");
                                    exec("echo '' >> /opt/sans/schedule.sh");
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
