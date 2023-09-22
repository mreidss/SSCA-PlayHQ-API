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

?>

<!DOCTYPE html>
<html>
<head>
    <title>Top Bowlers by Wickets</title>
</head>
<body>
    <h2>Top Bowlers by Wickets</h2>

    <?php if (!empty($topBowlers)) : ?>
        <table border="1">
            <tr>
                <th>Count</th>
                <th>Name</th>
                <th>Grade</th>
                <th>Club</th>
                <th>Matches</th>
                <th>Wickets</th>
                <th>Average</th>
                <th>Best Bowling</th>
            </tr>
            <?php foreach ($topBowlers as $count => $item) : ?>
                <tr>
                    <td><?= $count + 1 ?></td>
                    <td><?= esc_html($item['Name']) ?></td>
                    <td><?= esc_html($item['Grade']) ?></td>
                    <td><?= esc_html($item['Organisation']['Name']) ?></td>
                    <td><?= esc_html($item['Statistics']['Matches']) ?></td>
                    <td><?= esc_html($item['Statistics']['BowlingWickets']) ?></td>
                    <td><?= esc_html($item['Statistics']['BowlingAverage']) ?></td>
                    <td><?= esc_html($item['Statistics']['BowlingBestInnings']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <p>No results found.</p>
    <?php endif; ?>
</body>
</html>