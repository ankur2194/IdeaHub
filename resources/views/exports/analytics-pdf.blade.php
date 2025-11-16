<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0 0 10px 0;
        }
        .meta {
            color: #666;
            font-size: 10px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background-color: #3b82f6;
            color: white;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-weight: bold;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th {
            background-color: #e0e7ff;
            color: #1e40af;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #c7d2fe;
        }
        table td {
            padding: 6px 8px;
            border: 1px solid #e0e7ff;
        }
        table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .metric-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .metric-card {
            border: 1px solid #e5e7eb;
            padding: 12px;
            background-color: #f9fafb;
        }
        .metric-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 IdeaHub Analytics Report</h1>
        <p class="meta">Generated on {{ $generated_at }}</p>
        @if(isset($data['date_range']))
            <p class="meta">Period: {{ $data['date_range']['from'] }} to {{ $data['date_range']['to'] }}</p>
        @endif
    </div>

    <!-- Overview Section -->
    <div class="section">
        <div class="section-title">📈 Overview</div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Total Ideas</div>
                <div class="metric-value">{{ $data['overview']['total_ideas'] }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Approved Ideas</div>
                <div class="metric-value">{{ $data['overview']['approved_ideas'] }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Pending Ideas</div>
                <div class="metric-value">{{ $data['overview']['pending_ideas'] }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Approval Rate</div>
                <div class="metric-value">{{ $data['overview']['approval_rate'] }}%</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Users</div>
                <div class="metric-value">{{ $data['overview']['total_users'] }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Comments</div>
                <div class="metric-value">{{ $data['overview']['total_comments'] }}</div>
            </div>
        </div>
    </div>

    <!-- Ideas by Category -->
    <div class="section">
        <div class="section-title">📂 Ideas by Category</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th style="text-align: right;">Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['ideas_by_category'] as $item)
                <tr>
                    <td>{{ $item->category ?? 'Uncategorized' }}</td>
                    <td style="text-align: right;">{{ $item->count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Ideas by Status -->
    <div class="section">
        <div class="section-title">📊 Ideas by Status</div>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th style="text-align: right;">Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['ideas_by_status'] as $item)
                <tr>
                    <td style="text-transform: capitalize;">{{ $item->status }}</td>
                    <td style="text-align: right;">{{ $item->count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Top Contributors -->
    <div class="section">
        <div class="section-title">🏆 Top Contributors</div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th style="text-align: center;">Level</th>
                    <th style="text-align: right;">Ideas</th>
                    <th style="text-align: right;">Approved</th>
                    <th style="text-align: right;">Comments</th>
                    <th style="text-align: right;">Points</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['top_contributors'] as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td style="text-align: center;">{{ $user->level }}</td>
                    <td style="text-align: right;">{{ $user->ideas_submitted }}</td>
                    <td style="text-align: right;">{{ $user->ideas_approved }}</td>
                    <td style="text-align: right;">{{ $user->comments_posted }}</td>
                    <td style="text-align: right;"><strong>{{ $user->points }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This report was automatically generated by IdeaHub</p>
        <p>&copy; {{ date('Y') }} IdeaHub - Innovation Management Platform</p>
    </div>
</body>
</html>
