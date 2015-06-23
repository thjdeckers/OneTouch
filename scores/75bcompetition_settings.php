<?php
session_start();
//include stylesheet for formatting
//include("http://localhost/php/stylesheet.html");
//include("http://localhost/php/gen_view_ddmenu.html");
//include("stylesheet.php");
include("config.php");
include("mysql.php");
//include("prefs.php");
//include("filter.php");
//include("auth.php");
//include("login.php");
/*include("functions.php");*/
/*include("view.php");*/
//include("gen_header.php");
//include("menu.php");
/*include("new_pagina.php");*/
/*include("mod_pagina.php");*/
//include("gen_form.php");
//include("gen_view.php");
//include("view_sort.php");
//include("upload_class.php");
//include("list_pictures.php");
//include_once("php_functions.php");

$cmd = '';
if (isset($_REQUEST['register'])) {
  $cmd  = $_REQUEST['register'];
} else if (isset($_REQUEST['create'])) {
  $cmd  = $_REQUEST['create'];
} else if (isset($_REQUEST['logout'])) {
  $cmd  = $_REQUEST['logout'];
}

$mysql = new CMysql();
$mysql->connect( $hostname, $mysql_user, $mysql_pass);
$mysql->usedb( $dbname);

$mysql->set_main_table($main_table);
$mysql->set_main_form($main_form);
$mysql->set_coll_cns($coll_cns);


mysql_select_db($dbname) or die("Kan database $dbname niet openen");

print "<html>";
print "<body>";

$competition_logged_in = 0;
$jury_logged_in = 0;
$jury_nr = 0;
#print "cmd: $cmd<BR>";

if ($cmd == "Aanmaken") {

	   if ($_REQUEST['wedstrijd_naam'] == '') {
	      print "Voer eerst een naam in<BR>";
	   } else {
              $query = "SELECT wedstrijd_id, naam FROM wedstrijd";
              $query = $query . " WHERE naam =\"" . $_REQUEST['wedstrijd_naam'] . "\"";
              $result = $mysql->query($query);
              $number = mysql_numrows($result);

              if ($number > 0) {
                 print "Wedstrijd " . $_REQUEST['wedstrijd_naam'] . " bestaat al<BR>";
                 print "Aanmelden met wedstrijd nr " . mysql_result($result, 0, "wedstrijd_id") . " en bijbehorend wachtwoord.<BR>";
              } else {
                 $password = createRandomPassword();
                 $query = "INSERT INTO `wedstrijd` (naam, is_aktief, is_locked, password) values('" . $_REQUEST['wedstrijd_naam'] . "','1','0','$password')";
                 $result = $mysql->query($query);
                 $number = mysql_numrows($result);
                 $wedstrijd_id = mysql_insert_id();
		 $naam = $_REQUEST['wedstrijd_naam'];
                 $competition_logged_in = 1;
                 print "wedstrijd id: $wedstrijd_id<BR>";
                 print "password: $password<BR>";
	      }
	   }
}
if ($cmd == "Inloggen" || $cmd == "Einloggen" || $cmd == "Login" || $cmd == "Iniciar sesión" || $cmd == "Connectez") {

           if ($_REQUEST['competition_logged_in'] == 1 && $_REQUEST['jury_logged_in'] == 1) {
               show_registered($_REQUEST['wedstrijd_id'],'');
               return;
           }
           $query = "SELECT wedstrijd_id, naam, password, jury_registered FROM wedstrijd";
           $query = $query . " WHERE wedstrijd_id =\"" . $_REQUEST['wedstrijd_id'] . "\" and password =\"" . $_REQUEST['password'] . "\"";
           $result = $mysql->query($query);
           $number = mysql_numrows($result);

	   if ($_REQUEST['jury_logged_in'] == 0) {
             if ($number == 1) {
              # wedstrijd with id found
	      $naam            = mysql_result($result, 0, "naam");
	      $wedstrijd_id    = mysql_result($result, 0, "wedstrijd_id");
	      $password        = mysql_result($result, 0, "password");
	      $jury_registered = mysql_result($result, 0, "jury_registered");
              $competition_logged_in = 1;
              if ($_REQUEST['session_id'] == '' || $_REQUEST['session_id'] == '0') {
                  $session_id = session_id();
                  $may_release = get_expired($jury_registered,$_REQUEST['wedstrijd_id']);
              } else {
                  $session_id = $_REQUEST['session_id'];
		  $old_registered = 0;
		  # see if session id exists for any jury nr
		  for ($i=1; $i <= 11; $i++) {
                      $query = "SELECT wedstrijd_id, jury_" . $i . " FROM wedstrijd";
                      $query = $query . " WHERE wedstrijd_id =\"" . $_REQUEST['wedstrijd_id'] . "\" and jury_" . $i . " =\"" . $session_id . "\"";
                      $result = $mysql->query($query);
		      $number = mysql_numrows($result);
		      if ($number > 0) {
			# session id exists: use this jury nr as default
			 $old_registered = $i;
		      }
		  }
		  if ($old_registered != 0) {
                     # session id was already registered for judge $old_registered
                     # release $old_registered and use it as preferred

		     #$_REQUEST['jury_nr_select'] = $old_registered;
		     $jury_nr = $old_registered;

                     # remove old registered
                     $new_jury_registered = unregister_judge($jury_nr,$jury_registered);
		     $jury_registered = $new_jury_registered;

		     #$new_jury_registered = $new_jury_registered . "," . $val;
		     $query = "UPDATE `wedstrijd` SET `jury_registered`= '" . $new_jury_registered . "',`jury_" . $jury_nr ."` = ''";
                     $query = $query . " WHERE `wedstrijd_id`=\"" . $wedstrijd_id ."\" LIMIT 1";
                     $result = $mysql->query($query);
		  }

                  $may_release = get_expired($jury_registered,$_REQUEST['wedstrijd_id']);
              }
              if ($jury_nr == 0) {
                      $jury_nr = select_free($jury_registered);
              }
              if ($jury_nr == 0) {
                      $jury_nr = select_expired($may_release);
              }
	      if ($_REQUEST['competition_logged_in'] == 1) {
		    # = second login: register jury

                  $may_release = get_expired($jury_registered,$_REQUEST['wedstrijd_id']);

                  if (! strstr($jury_registered, $_REQUEST['jury_nr_select']) || strstr($may_release, $_REQUEST['jury_nr_select'])) {
                    # jury login
                    $jury_nr = $_REQUEST['jury_nr_select'];
                    $jury_registered = unregister_judge($jury_nr,$jury_registered);
                    $may_release     = unregister_judge($jury_nr,$may_release);
		    if ($jury_registered == '') {
	                $new_jury_registered = $jury_nr;
		    } else {
	                $new_jury_registered = $jury_registered . "," . $jury_nr;
		    }

		    $query = "UPDATE `wedstrijd` SET `jury_registered`= '" . sort_registered($new_jury_registered) . "',`jury_" . $jury_nr . "`='" . $session_id . "'";
                    $query = $query . ",`jury_" .  $jury_nr . "_last_uploaded`=NOW() ";
                    $query = $query . " WHERE `wedstrijd_id`=\"" . $wedstrijd_id ."\" LIMIT 1";
                    $result = $mysql->query($query);
                    $jury_logged_in = 1;
                  } else {
                    print "Jury " . $_REQUEST['jury_nr_select'] . " is al aangemeld<BR>";
		  }
	      }
	     } else {
	        print "Onjuist wachtwoord of wedstrijd nummer<BR>";
	     }
	   } else {
	        $competition_logged_in = $_REQUEST['competition_logged_in'];
		$jury_logged_in = 1;
           }

}

if ($cmd == "Uitloggen" || $cmd == "Ausloggen" || $cmd == "Logout" || $cmd == "Desconectarse" || $cmd == "Déconnectez") {
           $query = "SELECT wedstrijd_id, naam, password, jury_registered FROM wedstrijd";
           $query = $query . " WHERE wedstrijd_id =\"" . $_REQUEST['wedstrijd_id'] . "\" and password =\"" . $_REQUEST['password'] . "\"";
           $result = $mysql->query($query);
           $number = mysql_numrows($result);

           if ($number == 1) {
	      $naam            = mysql_result($result, 0, "naam");
	      $wedstrijd_id    = mysql_result($result, 0, "wedstrijd_id");
	      $password        = mysql_result($result, 0, "password");
	      $jury_registered = mysql_result($result, 0, "jury_registered");
              if ($_REQUEST['session_id'] == '') {
                  $session_id = session_id();
              } else {
                  $session_id = $_REQUEST['session_id'];
              }
	      if ($_REQUEST['jury_logged_in'] == 1) {
                $competition_logged_in = 1;
                if ( strstr($jury_registered, $_REQUEST['jury_nr_select'])) {
                    # jury login
                    $jury_nr = $_REQUEST['jury_nr_select'];
                    $new_jury_registered = unregister_judge($jury_nr,$jury_registered);
		    $query = "UPDATE `wedstrijd` SET `jury_registered`= '" . sort_registered($new_jury_registered) . "'";
		    #$query = "UPDATE `wedstrijd` SET `jury_registered`= '" . $new_jury_registered . "',`jury_" . $jury_nr ."` = ''";
                    $query = $query . " WHERE `wedstrijd_id`=\"" . $wedstrijd_id ."\" LIMIT 1";
                    $result = $mysql->query($query);
                    #$jury_nr = 0;
                    $jury_logged_in = 0;
                } else {
                    print "Jury " . $_REQUEST['jury_nr_select'] . " is niet aangemeld<BR>";
                }
	      } else {
                   $competition_logged_in = 0;
              }
	   } else {
	      print "Onjuist wachtwoord of wedstrijd nummer<BR>";
	   }
}

if ($competition_logged_in == 1) {
   $jury_registered = show_registered($wedstrijd_id,$may_release);
}

print "<script type=\"text/javascript\">";
print "function appendOption(num) {";
print "  var elOptNew = parent.document.createElement('option');";
print "  elOptNew.text = num;";
print "  elOptNew.value = num;";
print "  var elSel = parent.document.getElementById('jury_nr_select');";
print "  try {";
print "     elSel.add(elOptNew, null);";
print "  }";
print "  catch(ex) {";
print "     elSel.add(elOptNew);";
print "  }";
print "}";

print "  var password         = parent.document.getElementById('password');";
print "  var wedstrijd_id     = parent.document.getElementById('wedstrijd_id');";
print "  var wedstrijd_naam   = parent.document.getElementById('wedstrijd_naam');";
print "  var session_id       = parent.document.getElementById('session_id');";
print "  var jury_nr_select   = parent.document.getElementById('jury_nr_select');";
print "  var jury_nr_label    = parent.document.getElementById('jury_nr_label');";
print "  var jury_name_input  = parent.document.getElementById('h_jury_name');";
print "  var jury_name_label  = parent.document.getElementById('input_jury_name');";
print "  var get_participants = parent.document.getElementById('get_participants');";
print "  var competition_logged_in = parent.document.getElementById('competition_logged_in');";
print "  var jury_logged_in        = parent.document.getElementById('jury_logged_in');";

print "  password.value     = '" . $password . "';";
print "  wedstrijd_id.value = '" . $wedstrijd_id . "';";
print "  wedstrijd_naam.value = '" . $naam . "';";
print "  session_id.value   = '" . $session_id . "';";
print "  var jury_registered = '" . $jury_registered . "';";
print "  var may_release     = '" . $may_release     . "';";
if ($competition_logged_in == 1) {
     # enable jury login
     print "  jury_nr_select.style.visibility = 'visible';";
     print "  jury_nr_label.style.visibility = 'visible';";
     print "  competition_logged_in.value = 1;";
     # remove remaining jury numbers from select
     print "  for (ix = jury_nr_select.options.length - 1; ix >= 0; ix--) {";
     print "          jury_nr_select.removeChild(jury_nr_select.options[ix]);";
     print "  }";
     if ($jury_logged_in == 1) {
         print "  get_participants.style.visibility = 'visible';";
         print "  if (jury_name_input) { jury_name_input.style.visibility = 'visible';";
         print "  jury_name_label.style.visibility = 'visible'; }";
         # add registered jury number
         print "  jury_logged_in.value = 1;";
         print "  appendOption(" . $jury_nr . ");";
     }
     if ($_REQUEST['jury_logged_in'] == 1 && $jury_logged_in == 0) {
         # jury logout
         print "  jury_logged_in.value = 0;";
         print "  get_participants.style.visibility = 'hidden';";
         print "  if (jury_name_input) { jury_name_input.style.visibility  = 'hidden';";
         print "  jury_name_label.style.visibility  = 'hidden'; }";
     }
     if ($jury_logged_in == 0) {
         # add all jury numbers to select
         print "  for (ix = 0; ix < 11; ix++) {";
         print "          appendOption(ix+1);";
         print "  }";
         # remove registered jury numbers
         print "  ix = 0;";
         print "  while (ix <  jury_nr_select.options.length) {";
         print "      if (jury_registered.search(jury_nr_select.options[ix].text) > -1 && may_release.search(jury_nr_select.options[ix].text) == -1) {";
         print "          jury_nr_select.removeChild(jury_nr_select.options[ix]);";
         print "      } else {";
         #              select $jury_nr as preferred
	 print "        if (jury_nr_select.options[ix].text == " . $jury_nr . ") {";
	 print "            jury_nr_select.selectedIndex = ix;";
	 print "        }";
         print "        ix++;";
         print "      }";
         print "  }";
     }
} else {
     print "  competition_logged_in.value = 0;";
     # disable jury login
     print "  jury_nr_select.style.visibility = 'hidden';";
     print "  jury_nr_label.style.visibility = 'hidden';";
     print "  get_participants.visibility = 'hidden';";
}
print "</script>";

print "</body>";
print "</html>";

function select_free($lc_jury_registered) {
	 for ($i=1; $i <= 11; $i++) {
             if (! strstr($lc_jury_registered,"$i")) {
                 return($i);
             }
         }
         return(0);
}

function select_expired($lc_may_release) {
         # all in use: use an expired one as default
	 for ($i=1; $i <= 11; $i++) {
             if (strstr($may_release,"$i")) {
                 return($i);
             }
         }
         return(0);
}

function get_expired($lc_jury_registered,$lc_wedstrijd_id) {
         global $mysql;

         $expired = "";
	 if ($lc_jury_registered != '') {
		       $registered =  explode(',',$lc_jury_registered);
		       $still_registered = "";
	               foreach ($registered as $val) {
                                # registered judges have uploaded more recently than 500 seconds?
                                $query = "SELECT TIME_TO_SEC(TIMEDIFF(NOW(),`jury_" . $val . "_last_uploaded`)) FROM wedstrijd";
                                $query = $query . " WHERE wedstrijd_id =\"" . $lc_wedstrijd_id . "\"";
                                $result = $mysql->query($query);
                                $number = mysql_numrows($result);
                                if (mysql_result($result,0) < 500) {
                                    # last registered or uploaded less than 500 seconds ago
 		                    if ($still_jury_registered == '') {
 			                $still_registered = $val;
		                    } else {
			                $still_registered = $still_registered . "," . $val;
	                            }
		                } else {
                                    # probably has left, because registered or uploaded more than 500 seconds ago
                                    #
 		                    if ($expired == '') {
 			                $expired = $val;
		                    } else {
			                $expired = $expired . "," . $val;
	                            }
                                }
                       }
	 }
         return($expired);
}

function show_registered($lc_wedstrijd_id,$lc_may_release) {
   global $mysql;

   $query = "SELECT wedstrijd_id, jury_registered FROM wedstrijd";
   $query = $query . " WHERE wedstrijd_id =\"" . $lc_wedstrijd_id . "\"";
   $result = $mysql->query($query);
   $number = mysql_numrows($result);

   $lc_jury_registered = "";
   if ($number == 1) {
        $lc_jury_registered     = mysql_result($result, 0, "jury_registered");
	if ($lc_jury_registered == '') {
           print "Er zijn geen juryleden aangemeld<BR>";
	} else {
	   if (strlen($lc_jury_registered) > 1) {
	      print "Juryleden: $lc_jury_registered zijn aangemeld<BR>";
              print "Verstreken: $lc_may_release<BR>";
	   } else {
              print "Jurylid: $lc_jury_registered is aangemeld<BR>";
              print "Verstreken: $lc_may_release<BR>";
           }
        }
   }
   return($lc_jury_registered);
}

function unregister_judge($judge,$lc_jury_registered) {

    $lc_new_registered = "";
    $lc_registered =  explode(',',$lc_jury_registered);
    foreach ($lc_registered as $lc_val) {
       if ($lc_val != $judge) {
          if ($lc_new_registered == "") {
              $lc_new_registered = $lc_val;
          } else {
              $lc_new_registered = $lc_new_registered . "," . $lc_val;
          }
       }
    }
    return($lc_new_registered);
}

function sort_registered($lc_jury_registered) {
	 $lc_registered =  explode(',',$lc_jury_registered);
         $lc_new_registered = "";
         sort($lc_registered);
         foreach ($lc_registered as $lc_val) {
            if ($lc_new_registered == "") {
                $lc_new_registered = $lc_val;
            } else {
                $lc_new_registered = $lc_new_registered . "," . $lc_val;
            }
         }
         return($lc_new_registered);
}

function createRandomPassword() {

    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= 7) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;
}
