<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idea Review Update</title>
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
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
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
            border-left: 4px solid #6b7280;
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
        .feedback-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .feedback-title {
            font-weight: 600;
            color: #78350f;
            margin: 0 0 10px 0;
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
            <h1>📝 Idea Review Update</h1>
        </div>

        <div class="content">
            <p class="greeting">Hi there,</p>

            <p>We wanted to update you on the status of your idea submission.</p>

            <div class="idea-card">
                <h2 class="idea-title">{{ $ideaTitle }}</h2>
                <p>{{ Str::limit($ideaDescription, 150) }}</p>
            </div>

            <p>After careful consideration, <strong>{{ $approverName }}</strong> has decided not to move forward with this idea at this time.</p>

            @if($reason)
                <div class="feedback-box">
                    <p class="feedback-title">💡 Feedback:</p>
                    <p style="margin: 0; color: #78350f;">{{ $reason }}</p>
                </div>
            @endif

            <p>Don't be discouraged! We encourage you to:</p>
            <ul>
                <li>Review the feedback and consider refining your idea</li>
                <li>Submit new ideas that align with current priorities</li>
                <li>Engage with other ideas in the community</li>
            </ul>

            <div style="text-align: center;">
                <a href="{{ $ideaUrl }}" class="button">View Your Idea</a>
            </div>

            <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
                Thank you for your contribution. We value your participation in our innovation community.
            </p>
        </div>

        <div class="footer">
            <p>This is an automated notification from IdeaHub.</p>
            <p>
                <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}">Visit IdeaHub</a> |
                <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/ideas/create">Submit a New Idea</a>
            </p>
        </div>
    </div>
</body>
</html>
