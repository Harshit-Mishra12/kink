<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Report</title>
</head>
<body>
    <h1>User Report</h1>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Total Score</th>
                <th>Total Possible Score</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report as $category)
                <tr>
                    <td>{{ $category['category_name'] }}</td>
                    <td>{{ $category['total_score'] }}</td>
                    <td>{{ $category['total_possible_score'] }}</td>
                    <td>{{ number_format($category['percentage_score'], 2) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
