<!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js ie6" dir="ltr" lang="da-DK"><![endif]-->
<!--[if IE 7]><html class="no-js ie7" dir="ltr" lang="da-DK"><![endif]-->
<!--[if IE 8]><html class="no-js ie8" dir="ltr" lang="da-DK"><![endif]-->
<!--[if IE 9]><html class="no-js ie9" dir="ltr" lang="da-DK"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html class="no-js" dir="ltr" lang="da-DK"><!--<![endif]-->

<head>
<title>Liste</title> 
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, maximum-scale=1"/>
<link rel="stylesheet" id="swim-calculator-css" href="style.css" type="text/css" media="all" />
<!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>

<body>
<?php setlocale( LC_ALL, 'da_DK' );

$club_code = $_REQUEST['club_code']; // get club code

$event_source = $_REQUEST['source_gr']; // get entries file
if ( $event_source == '' || $event_source == NULL )
	$event_source = 'GR120204.txt'; // use this file then
	/* How the file is constructed:
	$event_data[0] = Event no.
	$event_data[1] = Female/Male (LD = Relay female, LH = Relay male, LX = Relay mixed)
	$event_data[2] = Event name
	$event_data[3] = ? (25 = max age? / Masters or not?)
	$event_data[4] = Age group?
	$event_data[5] = ?
	$event_data[6] = ?
	$event_data[7] = ?
	$event_data[8] = ?
	$event_data[9] = ?
	$event_data[9] = ?
	$event_data[9] = ? (55)
	$event_data[9] = ? (56)
	$event_data[9] = ? (95) */

$entries_source = $_REQUEST['source_an']; // get entries file
if ( $entries_source == '' || $entries_source == NULL )
	$entries_source = 'AN120204Nord.TXT'; // use this file then
	/* How the file is constructed:
	$entries_data[0] = Licens no.
	$entries_data[1] = 
	$entries_data[2] = Name/Relay name
	$entries_data[3] = Year
	$entries_data[4] = Female/Male (LD = Relay female, LH = Relay male, LX = Relay mixed)
	$entries_data[5] = Event no.
	$entries_data[6] = ?
	$entries_data[7] = Date
	$entries_data[8] = Time
	$entries_data[9] = City of PB? */

$result_source = $_REQUEST['source_re']; // get result file
if ( $result_source == '' || $result_source == NULL )
	$result_source = 'RE120204.txt'; // use this file then
	/* How the file is constructed:
	$entries_data[0] = Club code
	$entries_data[1] = Licens no.
	$entries_data[2] = ?
	$entries_data[3] = Name/Relay name
	$entries_data[4] = Year
	$entries_data[5] = Female/Male (LD = Relay female, LH = Relay male, LX = Relay mixed)
	$entries_data[6] = Event no.
	$entries_data[7] = ?
	$entries_data[8] = Final? (A = ?, P = Preliminary?, F = Final, FL = Relay final)
	$entries_data[9] = Date
	$entries_data[10] = Time
	$entries_data[11] = Rank
	$entries_data[12] = Licens no. */

if ( !file_exists($entries_source) )
	die( 'mangler anmeldelsesfilen (' . $entries_source . ')' );
if ( !file_exists($result_source) )
	die( 'mangler resultatfilen (' . $result_source . ')' );

$search = array( 'æ', 'ø', 'å', ' ', 'Æ', 'Ø', 'Å' ); // characters to search for
$replace = array( 'ae', 'o', 'aa', '', 'ae', 'o', 'aa' ); // characters to replace with

// Process the event file
$event_array = array(); // array for the event
if ( file_exists($event_source) ) {
	$event_file = fopen( $event_source, 'r' );
	while ( ( $event_data = fgetcsv( $event_file, 1000, ';' ) ) !== FALSE ) {
		$eventno = str_replace( $search, $replace, $event_data[0] );
		$gender = str_replace( $search, $replace, $event_data[1] );
		$name = utf8_encode( trim( $event_data[2] ) );
		
		// Insert data to the array - NOT THE RIGHT WAY TO DETECT
		if ( trim( $event_data[3] ) == '25' ) {
			$event_array[] = array( 'eventno' => $eventno, 'gender' => $gender, 'name' => $name );
		}
	}
	fclose( $event_file );
} else {
	echo 'mangler st&aelig;vnefilen (' . $event_source . ')';
}

// Process the entries file
$entries_array = array(); // array for the entries
$entries_file = fopen( $entries_source, 'r' );
while ( ( $entries_data = fgetcsv( $entries_file, 1000, ';' ) ) !== FALSE ) {
	$licens = str_replace( $search, $replace, $entries_data[0] );
	$name = utf8_encode( trim( $entries_data[2] ) );
	$gender = str_replace( $search, $replace, $entries_data[4] );
	$eventno = str_replace( $search, $replace, $entries_data[5] );
	$time = str_replace( $search, $replace, $entries_data[8] );
	$time = str_replace( '.', ',', $time );
	
	// Get club code from the entries file
	if ( ( $club_code == '' || $club_code == NULL ) && strlen( $licens ) == 4 ) {
		$club_code = $licens; // use this code then
		$event = utf8_encode( trim( $entries_data[3] ) ); // event name
	}
	
	// Insert data to the array
	if ( ( trim( $entries_data[1] ) == '' || trim( $entries_data[1] ) == '0' ) && $licens != '' ) {
		$entries_array[] = array( 'gender' => $gender, 'name' => $name, 'licensno' => $licens, 'eventno' => $eventno, 'time' => $time );
	}
}
fclose( $entries_file );

// Process the result file
$result_array = array(); // array for the results
$result_file = fopen( $result_source, 'r' );
while ( ( $result_data = fgetcsv( $result_file, 1000, ';' ) ) !== FALSE ) {
	$club = str_replace( $search, $replace, $result_data[0] );
	$licens = str_replace( $search, $replace, $result_data[1] );
	$name = utf8_encode( trim( $result_data[3] ) );
	$gender = str_replace( $search, $replace, $result_data[5] );
	$eventno = str_replace( $search, $replace, $result_data[6] );
	$time = str_replace( $search, $replace, $result_data[10] );
	if ( $time == 'DISK.' ) {
		$time = str_replace( '.', '', $time );
	} else {
		$time = str_replace( '.', ',', $time );
	}
	$rank = str_replace( $search, $replace, $result_data[11] );
	
	// Insert data to the array
	if ( $club == $club_code && $licens != '' && $result_data[8] != 'FL' ) {
		$result_array[] = array( 'gender' => $gender, 'name' => $name, 'licensno' => $licens, 'eventno' => $eventno, 'time' => $time, 'rank' => $rank );
	}
}
fclose( $result_file );

// Set the order of data to sort by
function compare_data( $a, $b ) {
	$retval = strnatcmp( $a['gender'], $b['gender'] );
	if ( !$retval ) $retval = strnatcmp( $a['name'], $b['name'] );
	if ( !$retval ) return strnatcmp( $a['eventno'], $b['eventno'] );
	return $retval;
}

usort( $entries_array, 'compare_data'); // sort entries data alphabetically by gender, name and eventno
usort( $result_array, 'compare_data'); // sort result data alphabetically by gender, name and eventno

// Detect if the two arrays have the same length
if ( count( $entries_array ) != count( $result_array ) ) {
	echo '<p>Der er ikke lige mange poster (' . count( $entries_array ) . ' i anmeldelse og ' . count( $result_array ) . ' i slut)</p>';
} else {
	echo '<p>Det burde passe! (' . count( $entries_array ) . ' i anmeldelse og ' . count( $result_array ) . ' i slut)</p>';
}

for ( $i = 0; $i < count( $entries_array ); $i++ ) {
	// Check for cancellations
	if ( $entries_array[$i][licensno] != $result_array[$i][licensno] ) {
		$first_part = array_slice( $result_array, 0, $i );
		$insert = array( 'gender' => $entries_array[$i][gender], 'name' => $entries_array[$i][name], 'licensno' => $entries_array[$i][licensno], 'eventno' => $entries_array[$i][eventno], 'time' => 'DNS', 'rank' => '' ); 
		$empty_record = array_push( $first_part, $insert );
		$last_part = array_slice( $result_array, $i );
		$result_array = array_merge( $first_part, $last_part );
	}
	// Check for first swimmer in relay
	/*if ( $entries_array[$i][gender] != $result_array[$i][gender] ) {
		$first_part = array_slice( $result_array, 0, $i );
		$last_part = array_slice( $result_array, $i );
		$empty_record = array_shift( $last_part );
		$result_array = array_merge( $first_part, $last_part );
	}*/
} ?>
<header><h1><?php echo $event; ?></h1></header>
<section>
	<table width="750" cellspacing="0" align="center" border="1">
		<thead>
			<tr>
				<th id="name" class="col-name">Navn</th>
				<th id="event" class="col-event">L&oslash;bs nr.</th>
				<th id="entry_time" class="col-entry-time">Tilmeldt</th>
				<th id="result_time" class="col-result-time">Opn&aring;et</th>
				<th id="pr" class="col-pr">PR</th>
				<th id="rank" class="col-rank">Placering</th>
			</tr>
		</thead>
		<tbody>
			<?php for ( $i = 0; $i < count( $entries_array ); $i++ ) {
				$row_class = ' class="alternate"' == $row_class ? '' : ' class="alternate"';
				echo '<tr id="row-' . $i . '"' . $row_class . '>';
				
				echo '<td>';
				if ( $entries_array[$i][name] == $result_array[$i][name] && $entries_array[$i][licensno] == $result_array[$i][licensno] ) {
					echo $entries_array[$i][name];
					//echo ' (' . $entries_array[$i][licensno] . ')';
				} else {
					echo $entries_array[$i][name] . ' (' . $entries_array[$i][licensno] . ')' . '+' . $result_array[$i][name] . ' (' . $result_array[$i][licensno] . ')';
				}
				echo '</td>' . "\n";
				echo '<td>';
				if ( file_exists($event_source) ) {
					if ( $entries_array[$i][eventno] == $result_array[$i][eventno] ) {
						echo $event_array[$entries_array[$i][eventno]-1][name];
						//echo '(' . $entries_array[$i][eventno] . ')';
					} else {
						echo $event_array[$entries_array[$i][eventno]-1][name] . '(' . $entries_array[$i][eventno] . ')' . '+' . $event_array[$result_array[$i][eventno]-1][name] . '(' . $result_array[$i][eventno] . ')';
					}
				} else {
					echo $entries_array[$i][eventno];
				}
				echo '</td>' . "\n";
				echo '<td>' . $entries_array[$i][time] . '</td>' . "\n";
				echo '<td>' . $result_array[$i][time] . '</td>' . "\n";
				
				$entries_time = $entries_array[$i][time];
				$entries_time_formated = explode( ':', $entries_time );
				$entries_minutes = intval( $entries_time_formated[0] );
				$entries_time_formated = explode( ',', $entries_time_formated[1] );
				$entries_seconds = intval( $entries_time_formated[0] );
				$entries_hundreds = intval( $entries_time_formated[1] );
				$entries_time_converted = ( ( $entries_minutes * 60 ) * 100 ) + ( $entries_seconds * 100 ) + $entries_hundreds;
				
				$result_time = $result_array[$i][time];
				$result_time_formated = explode( ':', $result_time );
				$result_minutes = intval( $result_time_formated[0] );
				$result_time_formated = explode( ',', $result_time_formated[1] );
				$result_seconds = intval( $result_time_formated[0] );
				$result_hundreds = intval( $result_time_formated[1] );
				$result_time_converted = ( ( $result_minutes * 60 ) * 100 ) + ( $result_seconds * 100 ) + $result_hundreds;
				
				$pr_time = number_format( ( ( $entries_time_converted - $result_time_converted ) / 100), 2 );
				
				if ( $result_time_converted == 'DISK' || $result_time_converted == 'DNS' || $result_time_converted == 'AVBRÖT' ) {
					$pr_cell_class = 'red';
					$pr_prefix = '';
					
					$pr_minutes = '';
					$pr_seconds = '';
					$pr_hundreds = '';
				} else if ( $entries_time_converted < $result_time_converted && $entries_time_converted != '00:00,00' ) {
					$pr_cell_class = 'red';
					$pr_prefix = '-';
					
					$pr_time = number_format( -( $pr_time ), 2 );
					
					if ( $pr_time > 100 ) {
						$pr_minutes = floor( $pr_time / 60 ) . ':';
						$pr_seconds = substr( floor( $pr_time / 60 ), -2 ) . ',';
					} else {
						$pr_minutes = 00 . ':';
						if ( $pr_time < 10 ) {
							$pr_seconds = '0' . floor( $pr_time ) . ',';
						} else {
							$pr_seconds = floor( $pr_time ) . ',';
						}
					}
					$pr_hundreds = substr( $pr_time, -2 );
				} else if ( $entries_time_converted < $result_time_converted && $entries_time_converted == '00:00,00' ) {
					$pr_cell_class = 'green';
					$pr_prefix = '';
					
					$pr_minutes = $result_minutes . ':';
					if ( $result_seconds < 10 ) {
						$pr_seconds = '0' . $result_seconds . ',';
					} else {
						$pr_seconds = $result_seconds . ',';
					}
					if ( $result_hundreds < 10 ) {
						$pr_hundreds = '0' . $result_hundreds;
					} else {
						$pr_hundreds = $result_hundreds;
					}
				} else {
					$pr_cell_class = 'green';
					$pr_prefix = '';
					
					if ( $pr_time > 100 ) {
						$pr_minutes = floor( $pr_time / 60 ) . ':';
						$pr_seconds = substr( floor( $pr_time / 60 ), -2 ) . ',';
					} else {
						$pr_minutes = 00 . ':';
						if ( $pr_time < 10 ) {
							$pr_seconds = '0' . floor( $pr_time ) . ',';
						} else {
							$pr_seconds = floor( $pr_time ) . ',';
						}
					}
					$pr_hundreds = substr( $pr_time, -2 );
				}
				
				$pr_time_formated = $pr_prefix . $pr_minutes  . $pr_seconds . $pr_hundreds;
				
				echo '<td class="' . $pr_cell_class . '">' . $pr_time_formated . '</td>' . "\n";
				
				if ( $result_array[$i][rank] == '1' ) {
					$rank_cell_class = 'gold';
				} else if ( $result_array[$i][rank] == '2' ) {
					$rank_cell_class = 'silver';
				} else if ( $result_array[$i][rank] == '3' ) {
					$rank_cell_class = 'bronze';
				} else {
					$rank_cell_class = '';
				}
									
				echo '<td class="' . $rank_cell_class . '">' . $result_array[$i][rank] . '</td>' . "\n";

				echo '</tr>' . "\n";
			} ?>
		</tbody>
	</table>
</section>
<footer>
	<h2>BUGS:</h2>
	<ul>
		<li>Hvis en sv&oslash;mmer har flere l&oslash;b i resultat end i anmeldelse (eks. ved f&oslash;rste svømmer i holdkap) g&aring;r det galt - l&oslash;bet tages ikke med i listen.<br /><em>detaljer: '$result_data[8] != 'FL' - removes first swimmer in relays'</em></li>
		<li>Hvis en sv&oslash;mmer er med i resultat men ikke i anmeldelse (eks. ved sv&oslash;mmer udenfor konkurrence - 'rank' er tomt) g&aring;r det galt.</li>
		<li>Hvis en sv&oslash;mmer er med i resultat men ikke i anmeldelse (eks. ved eftertilmelding) g&aring;r det galt.</li>
	</ul>
</footer>
</body>

</html>