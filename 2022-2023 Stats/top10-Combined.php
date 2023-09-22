<?php
$jsonOptions = [
    'A1' => 'f3a5cd22-3e0f-4015-a83f-524041c02604',
    'A2' => '45c5bdc7-7522-4b4f-b1fe-3cf6b0388fdc',
    'B1' => '36d25d27-6f0e-46c7-b08b-32674e6d5072',
    'B2' => '2d115353-0146-483e-b582-1366b6369f41',
    'B3' => '5563b108-f19c-4a2f-9a0a-930ec645e11b',
    'B4' => '4a3e16d1-ce1d-4632-b9e5-b198cd95f2bc',
    'C1' => 'ff9cd850-ae40-46b2-83f7-5123127804cc'
];

$combinedData = [];

foreach ($jsonOptions as $grade => $grade_id) {
    $json_url = 'https://stats-community.cricket.com.au/api/getBattingStats?grade_id=' . $grade_id;
    $json_data = file_get_contents($json_url);
    $data = json_decode($json_data, true);

    if (!empty($data)) {
        foreach ($data as $item) {
            $item['Grade'] = $grade;
            $combinedData[] = $item;
        }
    }
}

usort($combinedData, function ($a, $b) {
    return $b['Statistics']['BattingAggregate'] - $a['Statistics']['BattingAggregate'];
});

$topBatsmen = array_slice($combinedData, 0, 10);

// Reset combinedData for bowlers
$combinedData = [];

foreach ($jsonOptions as $grade => $grade_id) {
    $json_url = 'https://stats-community.cricket.com.au/api/getBowlingStats?grade_id=' . $grade_id;
    $json_data = file_get_contents($json_url);
    $data = json_decode($json_data, true);

    if (!empty($data)) {
        foreach ($data as $item) {
            $item['Grade'] = $grade;
            $combinedData[] = $item;
        }
    }
}

usort($combinedData, function ($a, $b) {
    return $b['Statistics']['BowlingWickets'] - $a['Statistics']['BowlingWickets'];
});

$topBowlers = array_slice($combinedData, 0, 10);

// Reset combinedData for fielders
$combinedData = [];

foreach ($jsonOptions as $grade => $grade_id) {
    $json_url = 'https://stats-community.cricket.com.au/api/getFieldingStats?grade_id=' . $grade_id;
    $json_data = file_get_contents($json_url);
    $data = json_decode($json_data, true);

    if (!empty($data)) {
        foreach ($data as $item) {
            $item['Grade'] = $grade;
            $combinedData[] = $item;
        }
    }
}

usort($combinedData, function ($a, $b) {
    $catchesDiff = $b['Statistics']['FieldingCatchesNonWK'] - $a['Statistics']['FieldingCatchesNonWK'];
    if ($catchesDiff !== 0) {
        return $catchesDiff;
    }
    return $b['Statistics']['FieldingRunOuts'] - $a['Statistics']['FieldingRunOuts'];
});

$topFielders = array_slice($combinedData, 0, 10);
?>

<!DOCTYPE html>
<html>
<head>
    <script>
        function redirectToTopPerformers() {
            window.location.href = 'https://sutherlandshireca.com.au/22-23-top-performers/';
        }
    </script>
        <style>
        body {
            font-family: Arial, sans-serif;
        }

        /* Add a media query for mobile screens */
        @media (max-width: 768px) {
            .performers-container {
                flex-direction: column;
            }

            .performers-container > div {
                margin-bottom: 20px;
            }
        }

        /* Rest of your existing styles */
        /* ... */

    </style>
</head>
<body>
    <h2>2022 - 2023 Top Performers</h2>

    <div class="performers-container" style="display: flex; justify-content: space-between; width: 100%;">

        <div onclick="redirectToTopPerformers()" style="cursor:pointer">
            <h3>Top Batsmen by Runs</h3>
            <?php if (!empty($topBatsmen)) : ?>
                <table border="1">
                    <tr>
                        <th>Count</th>
                        <th>Name</th>
                        <th>Grade</th>
                        <th>Runs</th>
                    </tr>
                    <?php foreach ($topBatsmen as $count => $item) : ?>
                        <tr>
                            <td><?= $count + 1 ?></td>
                            <td><?= esc_html($item['Name']) ?></td>
                            <td><?= esc_html($item['Grade']) ?></td>
                            <td><?= esc_html($item['Statistics']['BattingAggregate']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else : ?>
                <p>No results found.</p>
            <?php endif; ?>
        </div>

        <div onclick="redirectToTopPerformers()" style="cursor:pointer">
            <h3>Top Bowlers by Wickets</h3>
            <?php if (!empty($topBowlers)) : ?>
                <table border="1">
                    <tr>
                        <th>Count</th>
                        <th>Name</th>
                        <th>Grade</th>
                        <th>Wickets</th>
                    </tr>
                    <?php foreach ($topBowlers as $count => $item) : ?>
                        <tr>
                            <td><?= $count + 1 ?></td>
                            <td><?= esc_html($item['Name']) ?></td>
                            <td><?= esc_html($item['Grade']) ?></td>
                            <td><?= esc_html($item['Statistics']['BowlingWickets']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else : ?>
                <p>No results found.</p>
            <?php endif; ?>
        </div>

        <div onclick="redirectToTopPerformers()" style="cursor:pointer">
            <h3>Top Fielders by Catches</h3>
            <?php if (!empty($topFielders)) : ?>
                <table border="1">
                    <tr>
                        <th>Count</th>
                        <th>Name</th>
                        <th>Grade</th>
                        <th>Catches</th>
                    </tr>
                    <?php foreach ($topFielders as $count => $item) : ?>
                        <tr>
                            <td><?= $count + 1 ?></td>
                            <td><?= esc_html($item['Name']) ?></td>
                            <td><?= esc_html($item['Grade']) ?></td>
                            <td><?= esc_html($item['Statistics']['FieldingCatchesNonWK']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else : ?>
                <p>No results found.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>