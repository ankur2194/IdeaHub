<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Idea Submitted</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1f2937;
        }
        .idea-card {
            background-color: #f9fafb;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .idea-title {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 10px 0;
        }
        .idea-meta {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        .idea-description {
            color: #374151;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #667eea;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #5568d3;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 New Idea Submitted</h1>
        </div>

        <div class="content">
            <p class="greeting">Hi {{ $approverName }},</p>

            <p>A new idea has been submitted to IdeaHub and is awaiting your review.</p>

            <div class="idea-card">
                <h2 class="idea-title">{{ $ideaTitle }}</h2>
                <div class="idea-meta">
                    <strong>Submitted by:</strong> {{ $authorName }}<br>
                    <strong>Category:</strong> {{ $categoryName }}
                </div>
                <div class="idea-description">
                    {{ Str::limit($ideaDescription, 200) }}
                </div>
            </div>

            <p>As an approver, please review this idea and provide your feedback.</p>

            <div style="text-align: center;">
                <a href="{{ $ideaUrl }}" class="button">Review This Idea</a>
            </div>

            <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
                You can approve or reject ideas from your IdeaHub dashboard.
            </p>
        </div>

        <div class="footer">
            <p>This is an automated notification from IdeaHub.</p>
            <p>
                <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}">Visit IdeaHub</a> |
                <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/settings">Manage Notifications</a>
            </p>
        </div>
    </div>
</body>
</html>
