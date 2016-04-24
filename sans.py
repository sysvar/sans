#!/usr/bin/python
# -*- coding: utf-8 -*-
from socket import *                        # Networking
import threading as th                      # Parallel Probes
import sys                                  # Accept paramaters
import argparse                             # CLI Menu
import MySQLdb                              # Database Intergration
from datetime import datetime               # Start/Stop Time of Scan
from time import sleep, gmtime, strftime    # Calculating Scan Time
import smtplib                              # SMTP Sending Emails
from email.mime.text import MIMEText        # Email MIME Message Type

class colour:
    RED = '\033[91m'
    YELLOW = '\033[93m'
    GREEN = '\033[92m'
    WHITE = '\033[0m'

class setup:
    # Please update these variables appropriately

    # Troubleshooting
    webDashboardURL     = 'http://127.0.0.1/sans'
    webServerAddress    = '127.0.0.1'
    webServerPort       = 80
    internetCheck       = '216.58.208.142' # Google

    # MySQL Database
    mysqlServerIP       = '127.0.0.1'
    mysqlUser           = 'root'
    mysqlPass           = 'sans'
    mysqlDB             = 'sans'

    # Email Alerting
    emailServerIP       = 'smtp.example.com'
    emailServerPort     = 587
    emailServerUser     = 'sans@example.com'
    emailServerPass     = "sans"
    emailSenderEmail    = 'sans@example.com'
    emailRecipient      = 'sans@example.com'

def banner():
    print colour.YELLOW + """

   ¦¦¦¦¦¦¦+ ¦¦¦¦¦+ ¦¦¦+   ¦¦+¦¦¦¦¦¦¦+
   ¦¦+----+¦¦+--¦¦+¦¦¦¦+  ¦¦¦¦¦+----+
   ¦¦¦¦¦¦¦+¦¦¦¦¦¦¦¦¦¦+¦¦+ ¦¦¦¦¦¦¦¦¦¦+
   +----¦¦¦¦¦+--¦¦¦¦¦¦+¦¦+¦¦¦+----¦¦¦
   ¦¦¦¦¦¦¦¦¦¦¦  ¦¦¦¦¦¦ +¦¦¦¦¦¦¦¦¦¦¦¦¦
   +------++-+  +-++-+  +---++------+

     """ + colour.WHITE

    print """   Created by Ray Welland

   This script checks for open ports on a range of targets, if supported it then downloads
   the associated service banner, and stores all data in the MySQL database. Analytics are
   performed on the dataset and the results dashboard is accessable via web server"""

def troubleshooting():
    try:
        websiteCon = socket(AF_INET, SOCK_STREAM)
        websiteCon.connect((setup.webServerAddress, setup.webServerPort))
        websiteConStatus = "PASS"
    except Exception, e:
        websiteConStatus = "FAIL"
    try:
        internetCon = socket(AF_INET, SOCK_STREAM)
        internetCon.connect((setup.internetCheck, 80))
        internetConStatus = "PASS"
    except Exception, e:
        internetConStatus = "FAIL"
    try:
        databaseCon1 = MySQLdb.connect(setup.mysqlServerIP,setup.mysqlUser,setup.mysqlPass,setup.mysqlDB)
        databaseConCheck = databaseCon1.cursor()
        databaseConCheck.execute("SELECT VERSION()")
        results = databaseConCheck.fetchone()
        databaseVer = results[0]
        databaseCon1.commit()
        databaseCon1.close()
        if (databaseVer is None):
            databaseConStatus = "FAIL"
        else:
            databaseConStatus = "PASS"
    except Exception, e:
        databaseConStatus = "FAIL"
    except KeyboardInterrupt:
        sys.exit()
    finally:
        websiteCon.close()
        internetCon.close()

    print "_" * 96
    print ""
    print ("   Database Connection: " + (colour.GREEN if databaseConStatus == "PASS" else colour.RED) + databaseConStatus + colour.WHITE + "   |"
          "   Dashboard Connection: " + (colour.GREEN if websiteConStatus == "PASS" else colour.RED) + websiteConStatus + colour.WHITE + "   |"
          "   Internet Connection: " + (colour.GREEN if internetConStatus == "PASS" else colour.RED) + internetConStatus + colour.WHITE)
    print "_" * 96
    print "\n"

printLock = th.Lock()

def probe(targetHost, targetPort, databaseTime):
    targetHostSan = str(targetHost)
    targetPortSan = str(targetPort)

    try:
        probe = socket(AF_INET, SOCK_STREAM)
        probe.connect((targetHost, targetPort))
        printLock.acquire()        #Acquire the print lock before printing anything to console
        print colour.GREEN + '   [+] ' + colour.WHITE + targetHost + ' (' + str(targetPort) + '/tcp) open'
        print ""
        databaseCon2 = MySQLdb.connect(setup.mysqlServerIP,setup.mysqlUser,setup.mysqlPass,setup.mysqlDB)
        databaseQuery = databaseCon2.cursor()
        databaseQuery.execute("INSERT INTO `ports` (`por_id`, `por_ip`, `por_port`, `por_status`, `por_probe`) "
                              "VALUES (NULL, '"+ targetHostSan +"', '"+ targetPortSan +"', 1, CURRENT_TIMESTAMP);")
        databaseCon2.commit()

        if targetPort == 21 or targetPort == 22:
            probe.connect((targetHost, targetPort))
            probe.send('Hola\r\n')
            targetBanner = probe.recv(255)
            targetBannerSan = MySQLdb.escape_string(targetBanner);
            print '       ' + str(targetBanner)

            databaseQuery.execute("INSERT INTO `banners` (`scn_id`, `por_ip`, `por_port`, `ban_banner`) "
                                  "VALUES ((SELECT max(scn_id) FROM scan), '"+ targetHostSan +"', '"+ targetPortSan +"', '" + targetBannerSan + "');")
            databaseCon2.commit()
            databaseCon2.close()
        printLock.release()    #Release the print lock, stops jumbled text displayed to console.

    except Exception, e:
        printLock.acquire()
        print colour.RED + '   [-] ' + colour.WHITE + targetHost + ' (' + str(targetPort) + '/tcp) closed, ' + str(e)
        databaseCon3 = MySQLdb.connect(setup.mysqlServerIP,setup.mysqlUser,setup.mysqlPass,setup.mysqlDB)
        databaseQuery = databaseCon3.cursor()
        databaseQuery.execute("INSERT INTO `ports` (`por_id`, `por_ip`, `por_port`, `por_status`, `por_probe`) "
                              "VALUES (NULL, '"+ targetHostSan +"', '"+ targetPortSan +"', 0, CURRENT_TIMESTAMP);")
        databaseCon3.commit()
        databaseCon3.close()
        print
        printLock.release()

    except socket.error:
        printLock.acquire()
        print "Couldn't connect to target"
        printLock.release()
        sys.exit()

    except (KeyboardInterrupt, SystemExit):
        printLock.acquire()
        print '\n      Received keyboard interrupt\n'
        printLock.release()

    finally:
        probe.close()

def scan(targetHosts, targetPorts, targetTimeout, threadCount, databaseTime):
    pairs = [ (host,port) for port in targetPorts for host in targetHosts  ]        #Generate all the pairs of hosts and ports given
    groups = [ pairs[i:i+threadCount] for i in range(0, len(pairs), threadCount) ]  #According to number of threads , divide the pairs generated into groups of n pairs each
    setdefaulttimeout(targetTimeout)                                                #Set the default time out
    for group in groups :                                                           #Iterate over all the groups of pairs
        threads = [ th.Thread(target = probe, args = (pair[0], pair[1], databaseTime)) for pair in group ]   #Create a thread for each pair in the group
        start = [ thread.start() for thread in threads ]                            #Start all the threads created in previous steps
        join = [ thread.join() for thread in threads ]                              #Wait for all the threads to complete their execution before proceeding to next group

def email():
    databaseTime = strftime("%y/%m/%d-%k:%M:%S", gmtime())
    messageType = 'plain'  # plain, html, xml
    subject="SANS Tracker: " + databaseTime
    message="""
        SANS has completed today's assessment

        Please visit your dashboard to see the updates

        """ + setup.webDashboardURL
    try:
        msg = MIMEText(message, messageType)            # Save message and type
        msg['Subject']= subject                         # Set Subject
        msg['From']   = setup.emailSenderEmail          # Set From Email
        connection = smtplib.SMTP(setup.emailServerIP,setup.emailServerPort) # Connect
        connection.ehlo_or_helo_if_needed()             # Identify ourself
        connection.starttls()                           # Secure email with TLS encryption
        connection.set_debuglevel(False)                # Disable Debugging
        connection.login(setup.emailServerUser, setup.emailServerPass) # Authenticate
        try:
            connection.sendmail(setup.emailSenderEmail, setup.emailRecipient, msg.as_string()) # Send
            print '   Email alert successfully sent to ' + setup.emailRecipient
            print ""
        finally:
            connection.quit()

    except Exception, exc:
        sys.exit( "   Warning: Unable to send alert email; %s" % str(exc) ) # Give error message

def main():
    databaseTime = strftime("%y/%m/%d-%k:%M:%S", gmtime())
    banner()
    troubleshooting()

    menu = argparse.ArgumentParser(prog='sans.py', description='Useful Swtiches Information',
                                   usage='python %(prog)s -i <target IP(s)> -p <target port(s)> -t <timeout in seconds>\n'
                                         '       python %(prog)s -i 192.168.1.1 -p 21 -t 1 -c 1\n'
                                         '       python %(prog)s -i 192.168.1.1,192.168.1.2 -p 21,22 -t 0.3 -c 10',
                                   epilog='Please take into account computer laws in your country before running this program, mass-'
                                          'scanning may be considered a hostile action')
    menu.add_argument('-i', '--targetHosts', nargs= '*', type=str, help="Target host IP(s); Separate by comma")
    menu.add_argument('-p', '--targetPorts', nargs= '*', type=str, help="Target port(s); Separate by comma")
    menu.add_argument("-I", '--hostsFile', type=str, help="Hosts file name")
    menu.add_argument("-P", '--portsFile', type=str, help="Ports file name")
    menu.add_argument("-c", '--threadCount', type=int, help="Number of Threads")
    menu.add_argument("-t", '--targetTimeout', type=float, help="Timeout in seconds")
    menu.add_argument('-v', action='version', version='%(prog)s 2.0')
    args = menu.parse_args()

    if (args.targetHosts != None) | (args.targetPorts != None) | (args.hostsFile != None) | (args.portsFile != None):
        print colour.YELLOW + "   Scan started on:",strftime("%a, %d %b %Y %H:%M:%S", gmtime()),"\n" + colour.WHITE
        timeStart = datetime.now()                      # Save the time the scan started
        targetTimeout = float(args.targetTimeout)       # Move Timeout to variable
        threadCount = int(args.threadCount)             # Move Thread Count to variable

        if (args.hostsFile != None):                    # Formatting: Check between file or command input
            h=open(args.hostsFile)
            targetHosts=h.read().splitlines()
            h.close()
        else:
            targetHosts = args.targetHosts[0].split(',')

        if (args.portsFile != None):                    # Formatting: Check between file or command input
            p=open(args.portsFile)
            targetPorts = p.read().splitlines()
            p.close()
        else:
            targetPorts = args.targetPorts[0].split(',')
        targetPorts = [int(i) for i in targetPorts]

        # Validation: Checking for no input
        if (targetHosts == None) | (targetPorts == None) | (targetTimeout == None) | (threadCount == None):
            print menu.usage
            exit(0)

        if(targetTimeout <= 0) | (threadCount <= 0) :   # Validation: Checking for invalid Value
            print menu.usage
            exit(0)

        scan(targetHosts, targetPorts, targetTimeout, threadCount, databaseTime)        # Scan

        sleep(targetTimeout)
        print colour.YELLOW + "   Scan Ended on:",strftime("%a, %d %b %Y %H:%M:%S", gmtime()),"\n" + colour.WHITE
        timeEnd = datetime.now()                        # Save the time the scan finished
        timeTotal =  timeEnd - timeStart                # Calculate total time of scan
        print colour.YELLOW + "   Scanning Time: ",timeTotal,"" + colour.WHITE
        print ""
        email()                                         # Email User
        print ""
    else:
        print menu.usage
        exit(0)

if __name__ == '__main__':
    main()
