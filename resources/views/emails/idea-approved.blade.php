<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idea Approved!</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        .success-icon {
            text-align: center;
            font-size: 60px;
            margin: 20px 0;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1f2937;
        }
        .idea-card {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
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
        .points-badge {
            display: inline-block;
            background-color: #fbbf24;
            color: #78350f;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #10b981;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #059669;
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
            color: #10b981;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Congratulations!</h1>
        </div>

        <div class="content">
            <div class="success-icon">✅</div>

            <p class="greeting">Great news!</p>

            <p>Your idea has been approved by <strong>{{ $approverName }}</strong>.</p>

            <div class="idea-card">
                <h2 class="idea-title">{{ $ideaTitle }}</h2>
                <p>{{ Str::limit($ideaDescription, 150) }}</p>
            </div>

            <div style="text-align: center;">
                <div class="points-badge">🏆 +{{ $points }} Points Earned</div>
            </div>

            <p>Your idea is now moving forward in our innovation pipeline. We'll keep you updated on its progress.</p>

            <div style="text-align: center;">
                <a href="{{ $ideaUrl }}" class="button">View Your Idea</a>
            </div>

            <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
                Thank you for contributing to innovation at our organization!
            </p>
        </div>

        <div class="footer">
            <p>This is an automated notification from IdeaHub.</p>
            <p>
                <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}">Visit IdeaHub</a> |
                <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/my-ideas">My Ideas</a>
            </p>
        </div>
    </div>
</body>
</html>
