<?php
$jsonOptions = [
    'A1: faac1044-0ab6-4ffa-875f-c3474c909a9a',
    'A2: 3dc5d900-292f-4c1a-8122-f315fa66edb7',
    'B1: eb7777d1-2a81-47ad-8859-3aec15022bb9',
    'B2: 67117794-a5f8-4767-b86b-9e9801a55269',
    'B3: a56c4725-d12c-4455-a559-f0b9d20adf1b',
    'B4: e334c797-d7ef-4f59-abf1-aa879772c8ab',
    'B5: 14d12ef7-57db-4fcc-be09-7a8158d66df1',
    'C1: 7fafa153-8e28-43ce-bd24-272bf9b0a26a',
];

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

$selectedOption = isset($_POST['json_option']) ? $_POST['json_option'] : 'faac1044-0ab6-4ffa-875f-c3474c909a9a'; // Default grade_id
$selectedEndpoint = isset($_POST['endpoint']) ? $_POST['endpoint'] : 'getBattingStats'; // Default API endpoint
$selectedFinals = isset($_POST['finals_option']) ? $_POST['finals_option'] : ''; // Default finals option
$selectedMatchType = isset($_POST['match_type_option']) ? $_POST['match_type_option'] : ''; // Default match type option

// Initialize an array to store player statistics by name
$playersData = [];

// Initialize an array to store player statistics by grade
$gradeStats = [];

// Loop through each grade and fetch players
foreach ($jsonOptions as $option) {
    list($grade, $gradeId) = explode(': ', $option);

    // Assuming $selectedEndpoint, $selectedOption, $selectedFinals, $selectedMatchType are set correctly
    $json_url = 'https://stats-community.cricket.com.au/api/' . $selectedEndpoint . '?grade_id=' . $gradeId . '&options_type=' . $selectedFinals . '&match_type_id=' . $selectedMatchType;
    $json_data = file_get_contents($json_url);

    // Decode JSON data
    $data = json_decode($json_data, true);

    // Check if data is received successfully and contains players
    if ($data && isset($data)) {
        // Add players' statistics to the $playersData array
        foreach ($data as $player) {
            $name = $player['Name'];
            if (!isset($playersData[$name])) {
                // If the player doesn't exist in $playersData, add them with their statistics
                $playersData[$name] = [
                    'Grade' => [$grade],
                    'DismissedCount' => $player['Statistics']['BattingInnings'] - $player['Statistics']['BattingNotOuts'],
                    'Club' => $player['Organisation']['Name'],
                    'Matches' => $player['Statistics']['Matches'],
                    'BattingInnings' => $player['Statistics']['BattingInnings'],
                    'Runs' => $player['Statistics']['BattingAggregate'],
                    'HighScore' => $player['Statistics']['BattingHighScore'],
                    'BattingAverage' => isset($player['Statistics']['BattingAverage']) ? $player['Statistics']['BattingAverage'] : null,
                ];
            } else {
                // If the player already exists in $playersData, aggregate their statistics
                $playersData[$name]['Grade'][] = $grade;
                $playersData[$name]['DismissedCount'] += ($player['Statistics']['BattingInnings'] - $player['Statistics']['BattingNotOuts']);
                $playersData[$name]['Matches'] += $player['Statistics']['Matches'];
                $playersData[$name]['BattingInnings'] += $player['Statistics']['BattingInnings'];
                $playersData[$name]['Runs'] += $player['Statistics']['BattingAggregate'];
                $playersData[$name]['HighScore'] = max($playersData[$name]['HighScore'], $player['Statistics']['BattingHighScore']);
                if (isset($player['Statistics']['BattingAverage']) && $player['Statistics']['BattingAverage'] !== '') {
                    if ($playersData[$name]['BattingAverage'] !== null) {
                        $playersData[$name]['BattingAverage'] = round($playersData[$name]['Runs'] / $playersData[$name]['DismissedCount'], 2);
                    } else {
                        $playersData[$name]['BattingAverage'] = $player['Statistics']['BattingAverage'];
                    }
                }
            }

            // Add player statistics to grade-specific stats
            if (!isset($gradeStats[$grade][$name])) {
                $gradeStats[$grade][$name] = [
                    'Matches' => $player['Statistics']['Matches'],
                    'BattingInnings' => $player['Statistics']['BattingInnings'],
                    'Runs' => $player['Statistics']['BattingAggregate'],
                    'HighScore' => $player['Statistics']['BattingHighScore'],
                    'BattingAverage' => isset($player['Statistics']['BattingAverage']) ? $player['Statistics']['BattingAverage'] : null,
                ];
            } else {
                // Aggregate player stats for the same player across different grades
                $gradeStats[$grade][$name]['Matches'] += $player['Statistics']['Matches'];
                $gradeStats[$grade][$name]['BattingInnings'] += $player['Statistics']['BattingInnings'];
                $gradeStats[$grade][$name]['Runs'] += $player['Statistics']['BattingAggregate'];
                $gradeStats[$grade][$name]['HighScore'] = max($gradeStats[$grade][$name]['HighScore'], $player['Statistics']['BattingHighScore']);
                if (isset($player['Statistics']['BattingAverage']) && $player['Statistics']['BattingAverage'] !== '') {
                    if ($gradeStats[$grade][$name]['BattingAverage'] !== null) {
                        $gradeStats[$grade][$name]['BattingAverage'] = round(($gradeStats[$grade][$name]['BattingAverage'] + $player['Statistics']['BattingAverage']) / 2, 2);
                    } else {
                        $gradeStats[$grade][$name]['BattingAverage'] = $player['Statistics']['BattingAverage'];
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Stats</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Function to toggle player stats on row click
            $(".player-row").click(function() {
                $(this).next(".player-stats-row").toggleClass("d-none");
            });

            // Function to filter player stats based on selected grade and club
            $("#filter-btn").click(function() {
                var selectedGrade = $("#grade-filter").val();
                var selectedClub = $("#club-filter").val();

                $(".player-row").each(function() {

                    var grade = $(this).find(".grade").text();
                    var grades = $(this).find(".grade").text();
                    if(grade.includes(","))
                    {
                        grades = grade.split(',');
                    }

                    var club = $(this).find(".club").text();

                    if ((selectedGrade === "All Grades" || grade === selectedGrade || grades.includes(selectedGrade)) && (selectedClub === "All Clubs" || club === selectedClub)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Reset filters
            $("#reset-btn").click(function() {
                $(".player-row").show();
                $("#grade-filter").val("All Grades");
                $("#club-filter").val("All Clubs");
            });
        });
    </script>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center">Player Stats</h1>

        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-md-6">
                <select id="grade-filter" class="form-control">
                    <option value="All Grades">All Grades</option>
                    <option value="A1">A1</option>
                    <option value="A2">A2</option>
                    <option value="B1">B1</option>
                    <option value="B2">B2</option>
                    <option value="B3">B3</option>
                    <option value="B4">B4</option>
                    <option value="B5">B5</option>
                    <option value="C1">C1</option>
                </select>
            </div>
            <div class="col-md-6">
                <select id="club-filter" class="form-control">
                    <option value="All Clubs">All Clubs</option>
                    <?php foreach ($clubs as $club): ?>
                        <option value="<?= $club ?>"><?= $club ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mt-2">
                <button id="filter-btn" class="btn btn-primary">Filter</button>
                <button id="reset-btn" class="btn btn-secondary">Reset</button>
            </div>
        </div>

        <!-- Player Stats Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Name</th>
                        <th>Club</th>
                        <th>Matches</th>
                        <th>Innings</th>
                        <th>Runs</th>
                        <th>h/s</th>
                        <th>Average</th>
                        <th>Dismissed Count</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($playersData as $name => $player): ?>
                        <tr class="player-row">
                            <td class="grade"><?= implode(', ', $player['Grade']) ?></td>
                            <td><?= $name ?></td>
                            <td class="club"><?= $player['Club'] ?></td>
                            <td><?= $player['Matches'] ?></td>
                            <td><?= $player['BattingInnings'] ?></td>
                            <td><?= $player['Runs'] ?></td>
                            <td><?= $player['HighScore'] ?></td>
                            <td><?= $player['BattingAverage'] !== null ? $player['BattingAverage'] : 'N/A' ?></td>
                            <td><?= $player['DismissedCount'] ?></td>
                            <td>
                                <!-- Button to toggle player stats -->
                                <button class="btn btn-sm btn-primary">Grade Stats</button>
                            </td>
                        </tr>
                        <tr class="player-stats-row d-none">
                            <td colspan="7">
                                <!-- Table to display player stats for each grade -->
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Grade</th>
                                            <th>Matches</th>
                                            <th>Batting Innings</th>
                                            <th>Runs</th>
                                            <th>h/s</th>
                                            <th>Average</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($player['Grade'] as $grade): ?>
                                            <tr>
                                                <td><?= $grade ?></td>
                                                <td><?= $gradeStats[$grade][$name]['Matches'] ?></td>
                                                <td><?= $gradeStats[$grade][$name]['BattingInnings'] ?></td>
                                                <td><?= $gradeStats[$grade][$name]['Runs'] ?></td>
                                                <td><?= $gradeStats[$grade][$name]['HighScore'] ?></td>
                                                <td><?= $gradeStats[$grade][$name]['BattingAverage'] !== null ? $gradeStats[$grade][$name]['BattingAverage'] : 'N/A' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
