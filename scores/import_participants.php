<?php
session_start();
include("config.php");
include("mysql.php");

//error_reporting(E_ALL);
//ini_set("display_errors", 1); 

// http://www.practicalweb.co.uk/blog/08/05/18/reading-unicode-excel-file-php
ini_set('default_charset', 'UTF-8');

function fopen_utf8($filename){
    $encoding='';
    $handle = fopen($filename, 'r');
    $bom = fread($handle, 2);
//    fclose($handle);
    rewind($handle);

    if($bom === chr(0xff).chr(0xfe)  || $bom === chr(0xfe).chr(0xff)){
            // UTF16 Byte Order Mark present
            $encoding = 'UTF-16';
    } else {
        $file_sample = fread($handle, 1000) + 'e'; //read first 1000 bytes
        // + e is a workaround for mb_string bug
        rewind($handle);
   
        $encoding = mb_detect_encoding($file_sample , 'UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');
    }
    if ($encoding){
        stream_filter_append($handle, 'convert.iconv.'.$encoding.'/UTF-8');
    }
    return  ($handle);
}

if (isset($_REQUEST['topcmd'])) {
  $topcmd  = $_REQUEST['topcmd'];
 } else {
  $topcmd = '';
 }

if (isset($_REQUEST['delimiter'])) {
  $delimiter = $_REQUEST['delimiter'];
} else {
  print "<html><body>Fout: geen scheidingsteken voor CSV file opgegeven<BR></body></html>\n";
  exit;
}


if (isset($_FILES['csv_file']['name'])) {
  $csv_file = $_FILES['csv_file']['name'];
  $target_path = preg_replace("!{$_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']);

  $target_path = $target_path . basename( $_FILES['csv_file']['name']); 
  print "tmp: " . $_FILES['csv_file']['tmp_name'] . ", target_path: $target_path<BR>";

  if(move_uploaded_file($_FILES['csv_file']['tmp_name'], $target_path)) {
      echo "Bestand ".  basename( $_FILES['csv_file']['name']).  " OK<BR>";
      $csv_file = $target_path;
  } else{
      print "<html><body>Fout: bestand upload mislukt<BR></body></html>\n";
      exit;
  }
} else {
  print "<html><body>Fout: geen bestandnaam<BR></body></html>\n";
  exit;
}

if (isset($_REQUEST['competition_nr'])) {
  $competition_nr = $_REQUEST['competition_nr'];
} else {
  print "<html><body>Fout: geen wedstrijd nummer opgegeven<BR></body></html>\n";
  exit;
}

if (isset($_REQUEST['password'])) {
  $password = $_REQUEST['password'];
} else {
  print "<html><body>Fout: geen wachtwoord opgegeven<BR></body></html>\n";
  exit;
}

#if ($bottomcmd == "logout")
# {
#  unset($_SESSION['uid']);
#  unset($_SESSION['pwd']);
#
#  $_SESSION = array();
#  session_destroy();
#  //include("http://localhost/php/login.html");
#  exit;
# }

$mysql = new CMysql();
$mysql->connect( $hostname, $mysql_user, $mysql_pass);
$mysql->usedb( $dbname);

$mysql->set_main_table($main_table);
$mysql->set_main_form($main_form);
#$mysql->set_coll_cns($coll_cns);


mysql_select_db($dbname) or die("Kan database $dbname niet openen");

$query = "SELECT wedstrijd_id,password,is_aktief,is_locked,naam FROM wedstrijd WHERE wedstrijd_id = '" . $competition_nr . "' AND password = '" . $password . "' AND is_aktief = '1' AND is_locked = '0' LIMIT 1";
print "query: $query<BR>";
$result = $mysql->query($query);
$number = mysql_numrows($result);
if ($number == 0) {
  print "<html><body>Fout: wedstrijd met nummer $competition_nr en opgegeven wachtwoord niet gevonden, of upload niet (meer) toegestaan<BR></body></html>\n";
  exit;
}

print "<html>";
print "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head>";
print "<body>";

print "deelnemers:<BR>";
// Get a file into an array.  In this example we'll go through HTTP to get
// the HTML source of a URL.
#$lines = file('http://www.savannahdeckers.nl/scores/competition_36.csv');

// Loop through our array, show HTML source as HTML source; and line numbers too.
# ts_gymnast,club,toestel,startnr,positie,niveau,max_mh,b1,b2,b3,b4,b5,a1,tot,b1,b2,b3,b4,b5,a1,tot,b1,b2,b3,b4,b5,a1,tot,eindtotaal
$row = 1;
$header = array();
$error = 0;
$wedstrijd_id = $competition_nr;
$blok      = 0;
$startnr   = 0;
$se_format = 0; # scoreexpress format
$prev_toestel = '';
$skip_line = 0;
#setlocale(LC_ALL, 'en_NL.UTF8');
if (($handle = fopen($csv_file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE && $error == 0) {
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";

	print "data[0]: $data[0]<BR>";
	# read header row
        if ($row == 1) {
           if ($data[0] != 'ts_gymnast' && $data[0] != 'Category' && $data[0] != 'Categorie') {
              print "FOUT: CSV bestand moet header bevatten die begint met ts_gymnast, Category of Categorie<BR>";
              $error = 1;
           } else {
              # store header
              for ($c=0; $c < $num; $c++) {
                  $header[$c] = $data[$c];
                  if ($data[$c] == 'is_aktief') {
                     $entry[$data[$c]] = '1'; # is_aktief = 1 by default
                  }
		  if ($data[$c] == 'Nummer') {
                     $data[$c] = 'Number';
                     $header[$c] = $data[$c];
		  }
              }
	      if ($data[0] == 'Category' || $data[0] == 'Categorie' ) { # at each header row start new blok
		    print "Category header found<BR>";
		    $se_format = 1;
		    $blok++;
		    $startnr = 0;
	      }
           }
        } else {  # read data row
           $entry = array();
	   # read data in order of header labels:
           foreach ($header as $ix => $name) {
	    print "data[" . $ix . " " . $name . "]: " . $data[$ix] . "<BR>";
	    if ($data[$ix] == 'Category' || $data[$ix] == 'Categorie') { # at each header row start new blok
		    print "Category header found<BR>";
		    $se_format = 1;
		    $blok++;
		    $startnr = 0;
		    $skip_line = 1;
	    }

	    if ($se_format == 1) {  # ScoreExpress input file
		if ($name == 'toestel') {
		   if ($data[$ix] != $prev_toestel) {
	               $blok = 1;
	               $prev_toestel = $data[$ix];
		   }
		}
	        $entry['startnr']   = $startnr;
		$entry['blok']      = $blok;
		$entry['is_aktief'] = '1';
            }

            if ($data[$ix] != 'Category'    &&
                $data[$ix] != 'Categorie'   &&
		$data[$ix] != 'Number'      &&
		$data[$ix] != 'Nummer'      &&
		$data[$ix] != 'Participant' &&
		$data[$ix] != 'Deelnemer' &&
		$data[$ix] != 'Club')
	       {
		  //print "old name $name<BR>";
		  switch ($name) {
		   case 'Categorie':
		   case 'Category':
			 $name = 'niveau';
			 break;
		   case 'Club':
			 $name = 'club';
			 break;
                   case 'ts_gymnast':
                         $entry[$name] = $data[$ix];
                         break;
		   case 'Deelnemer': # combine Number and Participant
		   case 'Participant': # combine Number and Participant
			 $name = 'ts_gymnast';
		         $entry[$name] = $entry['Number'] . ' ' . $data[$ix];
			 break;
	           }
		   if ($name != 'ts_gymnast') {
		       $entry[$name] = $data[$ix];
	           }
	           if ($se_format == 1 && $name == 'niveau') {  # ScoreExpress input file
		           $startnr++;
                   }
	       }
           }
           foreach ($entry as $key => $value) { # print values
              switch ($key) {
                case 'ts_gymnast':
                                   print "springer $value<BR>";
                                   break;
                case 'club':
                                   print "club $value<BR>";
                                   break;
                case 'land':
                                   print "land $value<BR>";
                                   break;
                case 'jaar':
                                   print "jaar $value<BR>";
                                   break;
                case 'startnr':
                                   print "startnr $value<BR>";
                                   break;
                case 'niveau':
                                   print "niveau $value<BR>";
                                   break;
                case 'max_mh':
                                   print "max_mh $value<BR>";
                                   break;
                case 'is_aktief':
                                   print "is_aktief $value<BR>";
                                   break;
                case 'toestel':
                                   print "toestel $value<BR>";
                                   break;
                case 'blok':
                                   print "blok $value<BR>";
                                   break;
               }
           }
	   # insert missing clubs
           $query = "SELECT club_id,is_aktief,naam,plaats,land FROM club WHERE naam = '" . $entry['club'] . "' AND plaats = '" . $entry['plaats'] .  "' AND land = '" . $entry['land'] . "' AND is_aktief = '1' LIMIT 1";
           $result = $mysql->query($query);
           $number = mysql_numrows($result);
           print "New club? $query nr: $number<BR>";
           if ($number == 0) {
               print "New;";
               $query = "INSERT INTO `club` (is_aktief,naam,plaats,land) values('1','" . $entry['club'] . "','" . $entry['plaats'] . "','" . $entry['land'] . "')";
	       if ($skip_line == 0) {
                   $result = $mysql->query($query);
                   $number = mysql_numrows($result);
		   $club_id = mysql_insert_id();
                   print "id: $club_id<BR>";
	       }
           } else {
               $club_id = mysql_result($result, 0, "club_id");
               print "Existing; id: $club_id<BR>";
           }

         #  CREATE TABLE `club` $(
         #   `club_id` bigint(20) NOT NULL auto_increment,
         #   `is_aktief` enum('0','1') NOT NULL default '1',
         #   `naam` varchar(32) NOT NULL default '',
         #   `plaats` varchar(32) NOT NULL default '',
         #   `land` varchar(32) NOT NULL default '',

         # `gymnast_id` bigint(20) NOT NULL auto_increment,
         # `is_aktief` enum('0','1') NOT NULL default '1',
         # `naam` varchar(32) NOT NULL default '',
         # `ref_club` bigint(32) NOT NULL default '0',
         # `year_of_birth` year(4) NOT NULL default '0000',

         # INSERT INTO `gymnast` VALUES (1, '1', 'Knöfel, Christina', 1, 1997);

	   # inserting missing gymnasts
           $query = "SELECT gymnast_id,is_aktief,naam,ref_club,year_of_birth FROM gymnast WHERE naam = '" . $entry['ts_gymnast'] . "' AND ref_club = '" . $club_id .  "' AND year_of_birth = '" . $entry['jaar'] . "' AND is_aktief = '1' LIMIT 1";
           $result = $mysql->query($query);
           $number = mysql_numrows($result);
          print "New gymnast? $query nr: $number<BR>";
           if ($number == 0) {
              print "New; ";
               $query = "INSERT INTO `gymnast` (is_aktief,naam,ref_club,year_of_birth) values('1','" . $entry['ts_gymnast'] . "','" . $club_id . "','" . $entry['jaar'] . "')";
	       if ($skip_line == 0) {
                   $result = $mysql->query($query);
                   $number = mysql_numrows($result);
		   $gymnast_id = mysql_insert_id();
                   print "id: $gymnast_id<BR>";
	        }
           } else {
               $gymnast_id = mysql_result($result, 0, "gymnast_id");
               print "Existing; id: $gymnast_id<BR>";
           }

           # `level_id` bigint(20) NOT NULL auto_increment,
           # `is_aktief` enum('0','1') NOT NULL default '1',
           # `omschrijving` varchar(32) NOT NULL default '',
           # `max_moeilijkheid` float NOT NULL default '0',

           # INSERT INTO `level` VALUES (1, '1', 'Klasse 5 - Jg. 97-99 - Schülerin', 0);

	   # insert missing levels
           $query = "SELECT level_id,is_aktief,omschrijving,max_moeilijkheid FROM level WHERE omschrijving = '" . $entry['niveau'] . "' AND max_moeilijkheid = '" . $entry['max_mh'] . "' AND is_aktief = '1' LIMIT 1";
           $result = $mysql->query($query);
           $number = mysql_numrows($result);
           print "New level? $query number: $number<BR>";
           if ($number == 0) {
               print "New; ";
               $query = "INSERT INTO `level` (is_aktief,omschrijving,max_moeilijkheid) values('1','" . $entry['niveau'] . "','" . $entry['max_mh'] . "')";
	       if ($skip_line == 0) {
                   $result = $mysql->query($query);
                   $number = mysql_numrows($result);
		   $level_id = mysql_insert_id();
                   print "id: $level_id<BR>";
	       }
           } else {
               $level_id = mysql_result($result, 0, "level_id");
               print "Existing; id: $level_id<BR>";
           }

           #CREATE TABLE `participant` (
             #`participant_id` bigint(20) NOT NULL auto_increment,
             #`is_aktief` enum('0','1') NOT NULL default '1',
             #`ref_wedstrijd` bigint(20) NOT NULL default '0',
             #`ref_gymnast` bigint(20) NOT NULL default '0',
             #`ref_level` bigint(20) NOT NULL default '0',
             #`start_nr_1` int(11) NOT NULL default '0',
             #`start_nr_2` int(11) NOT NULL default '0',
             #`start_nr_3` int(11) NOT NULL default '0',
             #`total_score_1` float NOT NULL default '0',
             #`total_score_2` float NOT NULL default '0',
             #`total_score_3` float NOT NULL default '0',
             #`ranking_1` int(11) NOT NULL default '0',
             #`ranking_2` int(11) NOT NULL default '0',
             #`ranking_3` int(11) NOT NULL default '0',

             # INSERT INTO `participant` VALUES (1, '1', 33, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0);

	   # insert missing participants
           $query = "SELECT participant_id,is_aktief,ref_wedstrijd,ref_gymnast FROM participant WHERE ref_wedstrijd = '" . $wedstrijd_id . "' AND ref_gymnast = '" . $gymnast_id . "' AND is_aktief = '1' LIMIT 1";
	   if ($skip_line == 0) {
               $result = $mysql->query($query);
               $number = mysql_numrows($result);
               print "New participant? $query nr: $number<BR>";
               if ($number == 0) {
                   print "New; ";
                   $query = "INSERT INTO `participant` (is_aktief,ref_wedstrijd,ref_gymnast,ref_level,start_nr_1,start_nr_2,toestel,blok) values('" . $entry['is_aktief'] . "','" . $wedstrijd_id . "','" . $gymnast_id . "','" . $level_id . "','" . $entry['startnr'] . "','" . $entry['startnr'] . "','" . $entry['toestel'] . "','" . $entry['blok'] ."')";
                   $result = $mysql->query($query);
                   $number = mysql_numrows($result);
                   $participant_id = mysql_insert_id();
                   print "id: $participant_id<BR>";
               } else {
                   # already exists; see if on correct toestel and blok
                   $participant_id = mysql_result($result, 0, "participant_id");
                   print "Existing; id: $participant_id<BR>";
                   $query = "SELECT participant_id,is_aktief,ref_wedstrijd,ref_gymnast,toestel,blok FROM participant WHERE participant_id = '" . $participant_id . "' AND ref_wedstrijd = '" . $wedstrijd_id . "' AND ref_gymnast = '" . $gymnast_id . "' AND toestel = '" . $entry['toestel'] . "' AND blok = '" . $entry['blok'] . "' AND is_aktief = '1' LIMIT 1";
                   $result = $mysql->query($query);
                   $number = mysql_numrows($result);
                   print "On correct apparatus and block? $query nr: $number<BR>";
                  
                   if ($number == 0) {
                     print "Update apparatus and block<BR>";
                     $query = "UPDATE participant SET (is_aktief,toestel,blok) = ('" . $entry['is_aktief'] . "','" . $entry['toestel'] . "','" . $entry['blok'] . "') WHERE participant_id = '" . $participant_id . "' LIMIT 1";
                     $result = $mysql->query($query);
                   } else {
                      $participant_id = mysql_result($result, 0, "participant_id");
                      print "Apparatus and block OK<BR>";
                   }
               }
	   }
        }
	$skip_line = 0;
        $row++;
    }
    fclose($handle);
}

print "</body>";
print "</html>";
