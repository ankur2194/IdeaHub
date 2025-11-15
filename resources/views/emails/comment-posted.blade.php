<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Comment on Your Idea</title>
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
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
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
        .idea-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 20px 0 10px 0;
        }
        .comment-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .comment-author {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 10px;
        }
        .comment-text {
            color: #374151;
            line-height: 1.6;
            margin: 0;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #3b82f6;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #2563eb;
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
            color: #3b82f6;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 New Comment</h1>
        </div>

        <div class="content">
            <p class="greeting">Hi there!</p>

            <p><strong>{{ $commenterName }}</strong> commented on your idea:</p>

            <p class="idea-title">{{ $ideaTitle }}</p>

            <div class="comment-box">
                <div class="comment-author">{{ $commenterName }} wrote:</div>
                <p class="comment-text">{{ $commentContent }}</p>
            </div>

            <p>This is a great opportunity to engage with the community and continue the discussion.</p>

            <div style="text-align: center;">
                <a href="{{ $ideaUrl }}" class="button">Reply to Comment</a>
            </div>

            <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
                Keep the conversation going and collaborate with others!
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
