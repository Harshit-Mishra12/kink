<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        h1 {
            text-align: center;
            color: #444;
            font-size: 24px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        thead {
            background-color: #007bff;
            color: #fff;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            font-weight: bold;
            font-size: 14px;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e9ecef;
        }
        tbody td {
            font-size: 13px;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>User Report</h1>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="center">Total Score</th>
                <th class="center">Average Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report as $category)
                <tr>
                    <td>{{ $category['category_name'] }}</td>
                    <td class="center">{{ $category['total_score'] }}</td>
                    <td class="center">{{ $category['average_percentage']  }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
