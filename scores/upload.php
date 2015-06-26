<?php
session_start();
include("config.php");
include("mysql.php");

if (isset($_REQUEST['topcmd'])) {
  $topcmd  = $_REQUEST['topcmd'];
 } else {
  $topcmd = '';
 }

$mysql = new CMysql();
$mysql->connect( $hostname, $mysql_user, $mysql_pass);
$mysql->usedb( $dbname);

$mysql->set_main_table($main_table);
$mysql->set_main_form($main_form);
#$mysql->set_coll_cns($coll_cns);


mysql_select_db($dbname) or die("Kan database $dbname niet openen");

print "<html>";
print "<body>";

# if current session already exists 
#   if wedstrijd_nr exists
#      if other date
#         use new wedstrijd_nr
#         insert
#         return new wedstrijd_nr
#      else
#         // same date
#         if jury_nr exists
#            update
#         else
#            insert
#      endif
#   else
#       insert
#   endif
# else
#   // new session
#   if wedstrijd_nr already exists
#      if other date
#         use new wedstrijd_nr
#         insert
#         return new_wedstrijd_nr
#      else
#         // same date
#         if other jury_nr
#            insert
#         else
#            // same jurynr,wedstrijdnr, different session 
#            restore existing session
#            insert
#         endif
#      endif
#   else
#      // new wedstrijd_nr
#      insert
#   endif

# see if current session was already stored
$all_scores = array();
$all_pairs = preg_split("/\,/",$_REQUEST['deductions_upload'],-1,PREG_SPLIT_NO_EMPTY);
foreach ($all_pairs as $idx => $val)
 {
#    # gymnast=1,exercise=1,deductions=0:1:0:1:0:1:1:0:3:1,jumps=10,max_jump=10,score=9.2,extra_deduction=0,gymnast=2,deductions=3:4:1:4:0:1:3:4:5:4,jumps=10,max_jump=10,score=7.1,extra_deduction=0,gymnast=3,deductions=3:1:3:4:0:1:4:3:4:1,jumps=10,max_jump=10,score=7.6,extra_deduction=0 
     $pair = preg_split("/=/",$val,-1,PREG_SPLIT_NO_EMPTY);
     if ($pair[0] == "gymnast_nr") {
         $gymnast_nr = $pair[1];
         print "gymnast_nr: $gymnast_nr<BR>";
         $gymnast_id = 0;
     }
     if ($pair[0] == "gymnast_id") {
         $gymnast_id = $pair[1];
     }
     if ($pair[0] == "exercise") {
         $exercise = $pair[1];
     }
     if ($pair[0] == "deductions") {
         $deductions = $pair[1];
     }
     if ($pair[0] == "jumps") {
         $jumps = $pair[1];
     }
     if ($pair[0] == "max_jump") {
         $max_jump = $pair[1];
     }
     if ($pair[0] == "score") {
         $single_score = $pair[1];
     }
     if ($pair[0] == "extra_deduction") {
         $extra_deduction = $pair[1];

         $score_array = array(
                "score" => $single_score,
                "deductions" => $deductions,
                "extra_deduction" => $extra_deduction, 
                "jumps" => $jumps, 
                "max_jump" => $max_jump
         );
         $all_scores[$gymnast_nr] = array($exercise => $score_array);
         $gymnast_ids[$gymnast_nr] = $gymnast_id;
     }
}

 foreach ($all_scores as $gymnast_nr => $scores) {
       foreach ($scores as $exercise => $score) {
	 #if ($gymnast_nr == $_REQUEST['current_gymnast'] && $exercise == $_REQUEST['current_exercise']) {
            #print "update<BR>";
            $query = "SELECT session_id,ref_jury_nr,ref_wedstrijd_nr,ref_gymnast,gymnast_nr,exercise,score_id FROM scores";
            $query = $query . " WHERE ref_jury_nr = \"" . $_REQUEST['ref_jury_nr'] . "\" and ref_wedstrijd_nr = \"" . $_REQUEST['ref_wedstrijd_nr'] . "\" and gymnast_nr = \"" . $gymnast_nr . "\" and exercise = \"" . $exercise . "\"";
            #$query = $query . " WHERE session_id =\"" . session_id() . "\" and ref_jury_nr = \"" . $_REQUEST['ref_jury_nr'] . "\" and ref_wedstrijd_nr = \"" . $_REQUEST['ref_wedstrijd_nr'] . "\" and gymnast_nr = \"" . $gymnast_nr . "\" and exercise = \"" . $exercise . "\"";
            $result = $mysql->query($query);
            $number = mysql_numrows($result);
 
            #print "number: $number<BR>";
            if ($number > 0) {
                $new_id = mysql_result($result, 0, "score_id");
                $new_query = "UPDATE `scores` SET `deductions`='" . $score['deductions'] . "',`extra_deduction`='" . $score['extra_deduction'] . "',`jumps`='" . $score['jumps'] . "',`max_jump`='" . $score['max_jump'] . "',`upload_time`=NOW(),`score`=" . $score['score'] . ",`ref_gymnast`=" . $gymnast_ids[$gymnast_nr];
                #$new_query = $new_query . " WHERE `session_id`=\"" . session_id() . "\" AND ref_jury_nr =\"" . $_REQUEST['ref_jury_nr'] . "\" AND ref_wedstrijd_nr =\"" . $_REQUEST['ref_wedstrijd_nr'] . "\" AND gymnast_nr = \"" . $gymnast_nr ."\" LIMIT 1";
                $new_query = $new_query . " WHERE ref_jury_nr =\"" . $_REQUEST['ref_jury_nr'] . "\" AND ref_wedstrijd_nr =\"" . $_REQUEST['ref_wedstrijd_nr'] . "\" AND gymnast_nr = \"" . $gymnast_nr ."\" LIMIT 1";
                $new_result = $mysql->query($new_query);
                #print "q: $new_query<BR>";
            } else {
                $new_query = "INSERT INTO `scores` (session_id,upload_time,ref_wedstrijd_nr,ref_jury_nr,jury_name,ref_gymnast,gymnast_nr,exercise,score,deductions,extra_deduction,jumps,max_jump) values('" . session_id() . "',NOW(),'" . $_REQUEST['ref_wedstrijd_nr'] . "','" . $_REQUEST['ref_jury_nr'] . "','" . $_REQUEST['jury_name'] . "','" . $gymnast_ids[$gymnast_nr] . "','" . $gymnast_nr . "','" . $exercise . "','" . $score['score'] . "','" . $score['deductions'] . "','" . $score['extra_deduction'] . "','" . $score['jumps'] . "','" . $score['max_jump'] . "')";
                #print "q: $new_query<BR>";
                $new_result = $mysql->query($new_query);
                $new_id = mysql_insert_id();
                #print "new_id: $new_id<BR>";
            }
            $ok = 0;
            if ($new_result) {
                   $upload_ack = "OK: $new_id";
	           # succesful: update last_uploaded record
                   $query = "UPDATE `wedstrijd` SET `jury_" .  $_REQUEST['ref_jury_nr'] . "_last_uploaded`=NOW()";
                   $query = $query . " WHERE `wedstrijd_id`=\"" . $_REQUEST['ref_wedstrijd_nr'] . "\" LIMIT 1";
                   $result = $mysql->query($query);
                   $ok = 1;
            } else {
                   $upload_ack = "FOUT: $new_id";
            }
	 #}
       }
}

print "<script type=\"text/javascript\">";
print "  var upload_ack  = parent.document.getElementById('upload_ack');";
print "  var uploaded_ok = parent.document.getElementById('uploaded_ok');";
print "  if (upload_ack) {";
print "     upload_ack.value = '$upload_ack';";
print "  }";
print "  if (uploaded_ok) {";
print "     uploaded_ok.value = '" . $ok . "';";
print "  }";

print "</script>";

print "</body>";
print "</html>";
