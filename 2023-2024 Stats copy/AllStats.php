<?php
$jsonOptions = [
    'A1: faac1044-0ab6-4ffa-875f-c3474c909a9a',
    'A2: 3dc5d900-292f-4c1a-8122-f315fa66edb7',
    'B1: eb7777d1-2a81-47ad-8859-3aec15022bb9',
    'B2: 67117794-a5f8-4767-b86b-9e9801a55269',
    'B3: a56c4725-d12c-4455-a559-f0b9d20adf1b',
    'B4: e334c797-d7ef-4f59-abf1-aa879772c8ab',
    'C1: 7fafa153-8e28-43ce-bd24-272bf9b0a26a',
];

$selectedOption = isset($_POST['json_option']) ? $_POST['json_option'] : 'faac1044-0ab6-4ffa-875f-c3474c909a9a'; // Default grade_id

$selectedEndpoint = isset($_POST['endpoint']) ? $_POST['endpoint'] : 'getBattingStats'; // Default API endpoint

$selectedFinals = isset($_POST['finals_option']) ? $_POST['finals_option'] : ''; // Default finals option

$selectedMatchType = isset($_POST['match_type_option']) ? $_POST['match_type_option'] : ''; // Default match type option

$endpoints = [
    'getBattingStats' => 'Batting Stats',
    'getBowlingStats' => 'Bowling Stats',
    'getFieldingStats' => 'Fielding Stats',
];

$finalsOptions = [
    'All Stats' => '',
    'Without Finals' => 'false',
    'Only Finals' => 'true',
];

$matchTypeOptions = [
    'All Match Types' => '',
    '2 Day Only' => '1',
    'One Day Only' => '2',
];

$json_url = 'https://stats-community.cricket.com.au/api/' . $selectedEndpoint . '?grade_id=' . $selectedOption . '&options_type=' . $selectedFinals . '&match_type_id=' . $selectedMatchType;
$json_data = file_get_contents($json_url);
$data = json_decode($json_data, true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cricket Stats</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        h1 {
            margin-top: 20px;
        }
        #filterInputContainer {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }

        #filterInputLabel {
            margin-right: 10px;
        }

        #filterInput {
            flex: 1;
        }
        #container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 20px;
        }
        #controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 10px;
        }
        #statsTableContainer {
            width: 100%;
            overflow-x: auto; /* Add horizontal scroll for small screens */
        }
        #statsTable {
            min-width: 600px; /* Set a minimum width to avoid collapsing on small screens */
        }

        #statsTable th,
        #statsTable td {
            padding: 8px;
        }
		
        @media (max-width: 600px) {
            #controls {
                flex-direction: column;
            }

            .input-container {
                display: flex;
                flex-direction: row;
                align-items: center;
                margin: 5px 0;
            }

            .input-container label,
            .input-container select {
                margin: 0 5px;
            }
        }
    </style>
    <script>
        function filterTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("filterInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("statsTable");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</head>
<body>
    <h2 style="padding-top:10px">2023-2024 SSCA Season Statistics</h2>
	<p>Below is a quick overview of the stats from the 2023-2024 season, the stats can be downloaded from the official PlayHQ website <a href="https://stats-community.cricket.com.au/">here</a></p>
    <div id="container">
        <form method="post" id="controls">
		    <div class="input-container">
				<label for="endpoint">Stats:</label>
				<select name="endpoint" id="endpoint">
					<?php
					foreach ($endpoints as $endpointKey => $endpointLabel) {
						echo '<option value="' . $endpointKey . '" ' . ($selectedEndpoint === $endpointKey ? 'selected' : '') . '>' . $endpointLabel . '</option>';
					}
					?>
				</select>
			</div>
			<div class="input-container">
            <label for="json_option">Grade:</label>
            <select name="json_option" id="json_option">
                <?php
                foreach ($jsonOptions as $option) {
                    $parts = explode(': ', $option);
                    $optionValue = count($parts) > 1 ? trim($parts[1]) : '';
                    echo '<option value="' . $optionValue . '" ' . ($selectedOption === $optionValue ? 'selected' : '') . '>' . $parts[0] . '</option>';
                }
                ?>
            </select>
			</div>
						<div class="input-container">
            <label for="finals_option">Finals:</label>
            <select name="finals_option" id="finals_option">
                <?php
                foreach ($finalsOptions as $optionLabel => $optionValue) {
                    echo '<option value="' . $optionValue . '" ' . ($selectedFinals === $optionValue ? 'selected' : '') . '>' . $optionLabel . '</option>';
                }
                ?>
            </select>
			</div>
									<div class="input-container">
            <label for="match_type_option">Match Type:</label>
            <select name="match_type_option" id="match_type_option">
                <?php
                foreach ($matchTypeOptions as $optionLabel => $optionValue) {
                    echo '<option value="' . $optionValue . '" ' . ($selectedMatchType === $optionValue ? 'selected' : '') . '>' . $optionLabel . '</option>';
                }
                ?>
            </select>
			</div>
            <input type="submit" value="Load Data">
        </form>
        
		
		<?php
        if ($data !== null) {
		echo '<h3 style="padding-top:20px">' . $endpoints[$selectedEndpoint] . '</h3>';
		}
        ?>
		
		<div id="filterInputContainer"  style="flex: 1; width: 100%;">
			<label for="filterInput" id="filterInputLabel">Search by Player name:</label>
			<input type="text" id="filterInput" onkeyup="filterTable()" placeholder="Search for names..">
		</div>

        <?php
        if ($data !== null && count($data) > 0) {
            //echo '<h2>' . $endpoints[$selectedEndpoint] . '</h2>';
            echo '<table id="statsTable" border="1">
                <tr>
                    <th>Name</th>
                    <th>Club</th>
                    <th>Matches</th>';
            if ($selectedEndpoint === "getBattingStats") {
                echo '
                    <th>Runs</th>
                    <th>High Score</th>
                    <th>Average</th>';
            } elseif ($selectedEndpoint === "getBowlingStats") {
                echo '
                    <th>Wickets</th>
                    <th>Average</th>
                    <th>Best Bowling</th>';
            } elseif ($selectedEndpoint === "getFieldingStats") {
                echo '
                    <th>Catches</th>
                    <th>Run Outs</th>
                    <th>WK Catches</th>
                    <th>Stumpings</th>';
            }
            // Add more headers based on the selected endpoint
            echo '</tr>';

            foreach ($data as $item) {
                echo '<tr>';
                echo '<td>' . esc_html($item['Name']) . '</td>';
                echo '<td>' . esc_html($item['Organisation']['Name']) . '</td>';
                echo '<td>' . esc_html($item['Statistics']['Matches']) . '</td>';
                if ($selectedEndpoint === "getBattingStats") {
                    echo '<td>' . esc_html($item['Statistics']['BattingAggregate']) . '</td>';
                    echo '<td>' . esc_html($item['Statistics']['BattingHighScore']) . '</td>';
                    echo '<td>' . esc_html($item['Statistics']['BattingAverage']) . '</td>';
                } elseif ($selectedEndpoint === "getBowlingStats") {
                    echo '<td>' . esc_html($item['Statistics']['BowlingWickets']) . '</td>';
                    echo '<td>' . esc_html($item['Statistics']['BowlingAverage']) . '</td>';
                    echo '<td>' . esc_html($item['Statistics']['BowlingBestInnings']) . '</td>';
                } elseif ($selectedEndpoint === "getFieldingStats") {
                    echo '<td>' . esc_html($item['Statistics']['FieldingCatchesNonWK']) . '</td>';
                    echo '<td>' . esc_html($item['Statistics']['FieldingRunOuts']) . '</td>';
                    echo '<td>' . esc_html($item['Statistics']['FieldingCatchesWK']) . '</td>';
                    echo '<td>' . esc_html($item['Statistics']['FieldingStumpings']) . '</td>';
                }
                // Add more columns based on the selected endpoint
                echo '</tr>';
            }

            echo '</table>';
        } else {
            echo 'No results found.';
        }
        ?>
    </div>
</body>
</html>