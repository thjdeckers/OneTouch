<?php
session_start();
include("config.php");
include("mysql.php");

ini_set('default_charset', 'UTF-8');

$cmd = '';
if (isset($_REQUEST['settings_cmd'])) {
  $cmd  = $_REQUEST['settings_cmd'];
}

$mysql = new CMysql();
$mysql->connect( $hostname, $mysql_user, $mysql_pass);
$mysql->usedb( $dbname);

$mysql->set_main_table($main_table);
$mysql->set_main_form($main_form);
$mysql->set_coll_cns($coll_cns);

$participants_list = "";

mysql_select_db($dbname) or die("Kan database $dbname niet openen");
print "<html>";
print "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head>";
print "<body>";

           $wedstrijd_id = $_REQUEST['wedstrijd_id'];
           $c_query = "SELECT naam from wedstrijd WHERE wedstrijd_id = " . $wedstrijd_id . " LIMIT 1";
           $c_result = $mysql->query($c_query);
           $c_number = mysql_numrows($c_result);

           if ($c_number > 0) {
              print "Deelnemers  " . mysql_result($c_result, 0, "naam") . "<BR>";

              $query = "SELECT participant.participant_id,participant.ref_gymnast,participant.is_aktief,participant.ref_level,participant.toestel,participant.blok,ref_wedstrijd,start_nr_1,start_nr_2,start_nr_3,gymnast_id,gymnast.naam,gymnast.is_aktief,gymnast.ref_club,gymnast.year_of_birth,club.naam,club.land,club.is_aktief,level_id,level.is_aktief,level.omschrijving,level.max_moeilijkheid FROM participant,gymnast,club,level WHERE ref_wedstrijd = '" . $wedstrijd_id . "' AND participant.ref_gymnast = gymnast.gymnast_id AND gymnast.ref_club = club.club_id AND participant.ref_level = level.level_id AND gymnast.is_aktief = '1' AND club.is_aktief = '1' AND level.is_aktief = '1' ORDER BY participant.participant_id,start_nr_1";
              $mysql->query("SET names 'utf8'");
              $result = $mysql->query($query);
              $number = mysql_numrows($result);

              /*print "<table>";*/
              for ($nr = 0; $nr < $number; $nr++) {
	       $is_aktief             = mysql_result($result, $nr, "participant.is_aktief");
	       $toestel               = mysql_result($result, $nr, "participant.toestel");
	       $blok                  = mysql_result($result, $nr, "participant.blok");
	       $start_nr_1            = mysql_result($result, $nr, "start_nr_1");
	       $start_nr_2            = mysql_result($result, $nr, "start_nr_2");
	       $start_nr_3            = mysql_result($result, $nr, "start_nr_3");
	       $gymnast_id            = mysql_result($result, $nr, "gymnast.gymnast_id");
	       $gymnast_naam          = mysql_result($result, $nr, "gymnast.naam");
	       $gymnast_year_of_birth = mysql_result($result, $nr, "gymnast.year_of_birth");
	       $club_naam             = mysql_result($result, $nr, "club.naam");
	       $club_land             = mysql_result($result, $nr, "club.land");
	       $level_id              = mysql_result($result, $nr, "level.level_id");
	       $niveau                = mysql_result($result, $nr, "level.omschrijving");
	       $max_mh                = mysql_result($result, $nr, "level.max_moeilijkheid");
               /*print "<tr>";
               print "<td>" . $start_nr_1            . "</td>";
               print "<td>" . $gymnast_id            . "</td>";
               print "<td>" . $gymnast_naam          . "</td>";
               print "<td>" . $gymnast_year_of_birth . "</td>";
               print "<td>" . $club_naam             . "</td>";
               print "<td>" . $club_land             . "</td>";
               print "</tr>";*/
               if ($club_land == '') {
                   $club_land = "Nederland";
               }
	       $new_participant = "$is_aktief|toestel:$toestel|blok:$blok|$start_nr_1|$start_nr_2|$start_nr_3|$gymnast_id|$gymnast_naam|$gymnast_year_of_birth|$club_naam|$club_land|$level_id|$niveau|$max_mh||";
	       if ($participants_list == "") {
		   $participants_list = "is_aktief|toestel|blok|start_nr_1|start_nr_2|start_nr_3|gymnast_id|ts_gymnast|jaar|club|land|level_id|niveau|max_mh||" . $new_participant;
	       } else {
		   $participants_list .= $new_participant;
	       }
              }
              /*print "</table>";*/
	   }

	   $values_list = "{\"values\":[{\"cmd\":\"" . $cmd . "\", \"participants\":\"" . htmlspecialchars($participants_list,ENT_COMPAT,"UTF-8") . "\"}]}";

print "<script type=\"text/javascript\">";

//print "var win = parent.document.getElementById(\"download_frame\").contentWindow;";
//print "top.postMessage('" . htmlspecialchars($values_list,ENT_COMPAT,"UTF-8") . "','*');";
print "top.postMessage('" .  $values_list . "','*');";

//print "  var stored_values   = parent.document.getElementById('stored_values');";
//print "  stored_values.value = '" . htmlspecialchars($values_list,ENT_COMPAT,"UTF-8") . "';";
print "</script>";

/*
print "<script type=\"text/javascript\">";

print "  var participants_list = parent.document.getElementById('participants_list');";
print "  participants_list.value     = '" . htmlspecialchars($participants_list,ENT_COMPAT,"UTF-8") . "';";
print "</script>";
 */

print "</body>";
print "</html>";
?>
