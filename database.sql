USE sans;
CREATE TABLE IF NOT EXISTS `sans`.`service_category` ( `svc_category_id` INT(2) NOT NULL AUTO_INCREMENT, `svc_category` VARCHAR(20) NOT NULL , PRIMARY KEY (`svc_category_id`)) ENGINE = InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `sans`.`service_problem` (`svc_problem_id` int(2) NOT NULL AUTO_INCREMENT,`svc_problem` VARCHAR(20) NOT NULL, PRIMARY KEY (`svc_problem_id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `sans`.`service` (`svc_port` int(5) NOT NULL,`svc_acronym` varchar(15) NOT NULL,`svc_service` varchar(30) NOT NULL,`svc_category_id` int(2) NOT NULL,`svc_problem_id` int(2) NOT NULL, PRIMARY KEY (`svc_port`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `sans`.`vulnerability_impact` (`vul_impact_id` int(1) NOT NULL AUTO_INCREMENT,`vul_impact` varchar(69) NOT NULL, PRIMARY KEY (`vul_impact_id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `sans`.`vulnerability_severity` (`vul_severity_id` int(1) NOT NULL AUTO_INCREMENT,`vul_severity` set('Low','Medium','High','Critical') NOT NULL, PRIMARY KEY (`vul_severity_id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `sans`.`vulnerability_type` (`vul_type_id` int(2) NOT NULL AUTO_INCREMENT,`vul_type` set('Denial of Service','Execute Code','Overflow','Directory Traversal','Bypass Something','Gain Information','Gain Privilege','SQL Injection','File Inclusion','Memory Corruption','CSRF','HTTP Response Splitting') NOT NULL, PRIMARY KEY (`vul_type_id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `sans`.`vulnerability` ( `vul_id` INT(6) NOT NULL AUTO_INCREMENT, `vul_port` INT(5) NOT NULL , `vul_software` VARCHAR(50) NOT NULL , `vul_banner` VARCHAR(255) NOT NULL , `vul_exploitable` BOOLEAN NOT NULL , `vul_type_id` INT(2) NOT NULL , `vul_impact_id` INT(1) NOT NULL , `vul_severity_id` INT(1) NOT NULL , PRIMARY KEY (`vul_id`)) ENGINE = InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `sans`.`scan` (`scn_id` int(5) NOT NULL AUTO_INCREMENT,`scn_date` date NOT NULL, PRIMARY KEY (`scn_id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `sans`.`ports` (`por_id` int(16) NOT NULL AUTO_INCREMENT,`por_ip` varchar(15) NOT NULL,`por_port` int(5) NOT NULL,`por_status` tinyint(1) NOT NULL,`por_probe` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`por_id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `sans`.`ports_scan` (`por_scn_id` int(16) NOT NULL AUTO_INCREMENT,`por_id` int(16) NOT NULL,`scn_id` int(5) NOT NULL, PRIMARY KEY (`por_scn_id`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `sans`.`banners` (`scn_id` int(5) NOT NULL,`por_ip` varchar(15) NOT NULL,`por_port` int(5) NOT NULL,`ban_banner` varchar(255) NOT NULL, PRIMARY KEY (`scn_id`, `por_ip`, `por_port`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TRIGGER `Update port_scan table por_id and scan date id` AFTER INSERT ON `sans`.`ports` FOR EACH ROW INSERT INTO `ports_scan` (`por_scn_id`,`por_id`,`scn_id`) VALUES (NULL,(SELECT MAX(`por_id`) FROM `ports`), (SELECT `scn_id` FROM `scan` WHERE `scn_date` = CURDATE()));
CREATE TRIGGER `Update scan table with new date` BEFORE INSERT ON `sans`.`ports` FOR EACH ROW INSERT INTO scan (scn_id, scn_date) SELECT (COALESCE(MAX(scn_id),0)+1), CURDATE() FROM scan HAVING COUNT(CASE WHEN scan.scn_date = CURDATE() THEN 1 end) = 0;

ALTER TABLE `sans`.`ports_scan` ADD INDEX(`por_id`);
ALTER TABLE `sans`.`ports_scan` ADD INDEX(`scn_id`);
ALTER TABLE `sans`.`ports_scan` ADD CONSTRAINT `con1` FOREIGN KEY (`por_id`) REFERENCES `sans`.`ports`(`por_id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `ports_scan` ADD CONSTRAINT `con2` FOREIGN KEY (`scn_id`) REFERENCES `sans`.`scan`(`scn_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sans`.`service` ADD CONSTRAINT `cat1` FOREIGN KEY (`svc_category_id`) REFERENCES `sans`.`service_category`(`svc_category_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `sans`.`service` ADD CONSTRAINT `pro1` FOREIGN KEY (`svc_problem_id`) REFERENCES `sans`.`service_problem`(`svc_problem_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `service` ADD INDEX(`svc_category_id`);
ALTER TABLE `service` ADD INDEX(`svc_problem_id`);
ALTER TABLE `sans`.`vulnerability` ADD CONSTRAINT `type1` FOREIGN KEY (`vul_type_id`) REFERENCES `sans`.`vulnerability_type`(`vul_type_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `sans`.`vulnerability` ADD CONSTRAINT `impact1` FOREIGN KEY (`vul_impact_id`) REFERENCES `sans`.`vulnerability_impact`(`vul_impact_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `sans`.`vulnerability` ADD CONSTRAINT `severity1` FOREIGN KEY (`vul_severity_id`) REFERENCES `sans`.`vulnerability_severity`(`vul_severity_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `sans`.`vulnerability` ADD INDEX(`vul_type_id`);
ALTER TABLE `sans`.`vulnerability` ADD INDEX(`vul_impact_id`);
ALTER TABLE `sans`.`vulnerability` ADD INDEX(`vul_severity_id`);
ALTER TABLE `sans`.`ports` ADD INDEX `index_ip` (`por_ip`);
ALTER TABLE `sans`.`ports` ADD INDEX `index_port` (`por_port`);
ALTER TABLE `sans`.`banners` ADD CONSTRAINT `id2` FOREIGN KEY (`scn_id`) REFERENCES `sans`.`scan`(`scn_id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `sans`.`banners` ADD CONSTRAINT `ip1` FOREIGN KEY (`por_ip`) REFERENCES `sans`.`ports`(`por_ip`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `sans`.`banners` ADD CONSTRAINT `por1` FOREIGN KEY (`por_port`) REFERENCES `sans`.`ports`(`por_port`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT IGNORE INTO `service_category` (`svc_category_id`, `svc_category`) VALUES (1, 'File Transfer'),(2, 'Console'),(3, 'Management'),(4, 'Remote'),(5, 'Web'),(6, 'Mail'),(7, 'Database'),(8, 'Logging'),(9, 'VOIP'),(10, 'Gaming'),(11, 'Chat');
INSERT IGNORE INTO `service_problem` (`svc_problem_id`, `svc_problem`) VALUES (1, 'Un-Encrypted'),(2, 'Un-Patched'),(3, 'Un-Supported'),(4, 'Management'),(5, 'PUP');
INSERT IGNORE INTO `service` (`svc_port`, `svc_acronym`, `svc_service`, `svc_category_id`, `svc_problem_id`) VALUES (21, 'FTP', 'File Transfer Protocol', 1, 1), (23, 'Telnet', 'Telnet', 2, 1), (68, 'DHCP', 'Dynamic Host Configuration Protocol', 3, 4), (69, 'TFTP', 'Trivial File Transfer Protocol', 1, 1), (79, 'Finger', 'Finger Protocol', 3, 4), (80, 'HTTP', 'HyperText Transfer Protocol', 5, 1), (109, 'POP2', 'Post Office Protocol version 2', 6, 1), (110, 'POP3', 'Post Office Protocol version 3', 6, 1), (111, 'RPC', 'Remote Procedure Call', 3, 4), (113, 'Auth', 'Auth Protocol', 3, 4), (119, 'NNTP', 'Network News Transfer Protocol', 3, 4), (135, 'RPC', 'Remote Procedure Call', 3, 4), (137, 'NetBios', 'NetBios', 3, 4), (138, 'NetBios', 'NetBios', 1, 4), (139, 'NetBios', 'NetBios', 3, 4), (143, 'IMAP', 'Internet Message Access Protocol', 6, 1), (161, 'SNMP', 'Simple Network Management Prot', 3, 4), (162, 'SNMP', 'Simple Network Management Prot', 1, 4), (389, 'LDAP', 'LDAP Server', 3, 4), (445, 'SMB', 'SMB server', 1, 1), (512, 'Rexec', 'Remote Process Execution', 2, 4), (513, 'Rlogin', 'Remote Login', 2, 4), (514, 'rsh', 'Remote Shell', 2, 4), (515, 'LPD', 'Line Printer Daemon', 3, 4), (1025, 'AD', 'Active Directory', 3, 4), (1026, 'SOCKS Proxy', 'SOCKS Proxy', 4, 5), (1433, 'MSSQL', 'MSSQL DBMS', 7, 4), (1521, 'Oracle', 'Oracle DBMS', 7, 4), (2049, 'NFS', 'Network File Share', 1, 1), (5900, 'VNC', 'Virtual Network Computing Serv', 4, 4), (5901, 'VNC', 'Virtual Network Computing Serv', 4, 4), (5902, 'VNC', 'Virtual Network Computing Serv', 4, 4), (6667, 'IRC', 'Internet Relay Chat Server', 11, 5), (8080, 'Router', 'Firewall or Router', 3, 1);
INSERT IGNORE INTO `vulnerability_impact` (`vul_impact_id`, `vul_impact`) VALUES (1, 'Confidentiality/Disclosure'),(2, 'Integrity/Alteration'),(3, 'Availability/Denial'),(4, 'Confidentiality/Disclosure, Integrity/Alteration'),(5, 'Availability/Denial, Confidentiality/Disclosure'),(6, 'Integrity/Alteration, Availability/Denial'),(7, 'Confidentiality/Disclosure, Integrity/Alteration, Availability/Denial');
INSERT IGNORE INTO `vulnerability_severity` (`vul_severity_id`, `vul_severity`) VALUES (1, 'Low'),(2, 'Medium'),(3, 'High'),(4, 'Critical');
INSERT IGNORE INTO `vulnerability_type` (`vul_type_id`, `vul_type`) VALUES (1, 'Denial of Service'),(2, 'Execute Code'),(3, 'Overflow'),(4, 'Directory Traversal'),(5, 'Bypass Something'),(6, 'Gain Information'),(7, 'Gain Privilege'),(8, 'SQL Injection'),(9, 'File Inclusion'),(10, 'Memory Corruption'),(11, 'CSRF'),(12, 'HTTP Response Splitting');
INSERT IGNORE INTO `vulnerability` (`vul_id`, `vul_port`, `vul_software`, `vul_banner`, `vul_exploitable`, `vul_type_id`, `vul_impact_id`, `vul_severity_id`) VALUES (1, 22, 'FreeSSHD', 'SSH-2.0-WeOnlyDo 2.1.3', 1, 7, 7, 4),(2, 21, 'FreeFloat', 'FreeFloat Ftp Server (Version 1.00)', 1, 3, 7, 4),(3, 21, '3Com 3CDaemon', '3Com 3CDaemon FTP Serrver Version 2.0', 1, 3, 7, 3),(4, 21, 'Ability', 'Ability Server 2.34', 1, 3, 7, 3),(5, 21, 'Sami', 'Sami FTP Server 2.0.2', 1, 3, 7, 3),(6, 21, 'ProFTPD', '220 ProFTPD 1.3.5rc3 Server\r\n', 1, 2, 7, 4),(7, '21', 'PCMan FTP Server', 'PCMan FTP Server v2.0.7\r\n', '1', '2', '7', '4'),(8, '21', 'Bftpd', 'BFTPD Bftpd 2.3\r\n', '1', '1', '3', '1'),(9, '21', 'Bftpd', 'BFTPD Bftpd 2.2.1\r\n', '1', '1', '3', '1'),(10, '21', 'Bftpd', 'BFTPD Bftpd 1.6.6\r\n', '1', '1', '3', '1'),(11, '21', 'Bftpd', 'BFTPD Bftpd 1.8\r\n', '1', '1', '3', '1'),(12, '21', 'vsFTPd', '200 (vsFTPd 2.3.4)\r\n', '1', '1', '3', '1'),(13, '22', 'OpenSSH', 'SSH-2.0-OpenSSH_4.7p1 Debian-8ubuntu1\r\n', '0', '1', '3', '2'),(14, '22', 'Cisco', 'SSH-2.0-Cisco-1.25\n', '0', '1', '3', '2'),(15, '22', 'Dropbear SSH', 'SSH-2.0-dropbear_2012.55\r\n', '1', '2', '7', '3'),(16, '21', 'vsFTPd', '220 (vsFTPd 2.0.5)\r\n', '0', '1', '3', '1');

ALTER TABLE `banners` ADD CONSTRAINT `id2` FOREIGN KEY (`scn_id`) REFERENCES `sans`.`scan`(`scn_id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `banners` ADD CONSTRAINT `ip1` FOREIGN KEY (`por_ip`) REFERENCES `sans`.`ports`(`por_ip`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `banners` ADD CONSTRAINT `por1` FOREIGN KEY (`por_port`) REFERENCES `sans`.`ports`(`por_port`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `banners` ADD INDEX(`por_ip`);
ALTER TABLE `banners` ADD INDEX(`por_port`);