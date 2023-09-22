<?php
$jsonOptions = [
    'A1' => 'faac1044-0ab6-4ffa-875f-c3474c909a9a',
    'A2' => '3dc5d900-292f-4c1a-8122-f315fa66edb7',
    'B1' => 'eb7777d1-2a81-47ad-8859-3aec15022bb9',
    'B2' => '67117794-a5f8-4767-b86b-9e9801a55269',
    'B3' => 'a56c4725-d12c-4455-a559-f0b9d20adf1b',
    'B4' => 'e334c797-d7ef-4f59-abf1-aa879772c8ab',
    'C1' => '7fafa153-8e28-43ce-bd24-272bf9b0a26a'
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

$topRecords = array_slice($combinedData, 0, 10);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Top Batsmen by Batting Aggregate</title>
</head>
<body>
    <h2>Top Batsmen by Batting Aggregate</h2>

    <?php if (!empty($topRecords)) : ?>
        <table border="1">
            <tr>
                <th>Count</th>
                <th>Name</th>
                <th>Grade</th>
                <th>Club</th>
                <th>Matches</th>
                <th>Runs</th>
                <th>High Score</th>
                <th>Average</th>
            </tr>
            <?php foreach ($topRecords as $count => $item) : ?>
                <tr>
                    <td><?= $count + 1 ?></td>
                    <td><?= esc_html($item['Name']) ?></td>
                    <td><?= esc_html($item['Grade']) ?></td>
                    <td><?= esc_html($item['Organisation']['Name']) ?></td>
                    <td><?= esc_html($item['Statistics']['Matches']) ?></td>
                    <td><?= esc_html($item['Statistics']['BattingAggregate']) ?></td>
                    <td><?= esc_html($item['Statistics']['BattingHighScore']) ?></td>
                    <td><?= esc_html($item['Statistics']['BattingAverage']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <p>No results found.</p>
    <?php endif; ?>
</body>
</html>