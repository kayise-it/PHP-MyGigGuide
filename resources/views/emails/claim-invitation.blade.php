<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Your {{ ucfirst($entityType) }} Profile</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .highlight-box {
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
        }
        .highlight-box h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .button {
            display: inline-block;
            background: #7c3aed;
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 0;
            font-size: 16px;
        }
        .button:hover {
            background: #6d28d9;
        }
        .info-box {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        .feature-list li {
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list .icon {
            margin-right: 12px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #7c3aed;">🎵 Claim Your {{ ucfirst($entityType) }} Profile!</h1>
        
        <p>Hello,</p>
        
        <p>We're excited to let you know that we've created a {{ $entityType }} profile for <strong>{{ $entityName }}</strong> on My Gig Guide, South Africa's premier music discovery platform!</p>
        
        <div class="highlight-box">
            <h2>{{ $entityName }}</h2>
            <p style="margin: 0; opacity: 0.9;">Your {{ $entityType }} profile is ready for you to claim and complete</p>
        </div>

        <p><strong>We created this profile using publicly available information to help showcase your work on our platform.</strong> Now it's your turn to take control and make it shine!</p>

        <h3>✨ What You Get When You Claim Your Profile:</h3>
        <ul class="feature-list">
            <li><span class="icon">🎨</span> <strong>Complete Control</strong> - Update all your information, add photos, videos, and media</li>
            <li><span class="icon">🚀</span> <strong>Boost Visibility</strong> - Get discovered by music lovers, event organisers, and industry professionals</li>
            <li><span class="icon">📅</span> <strong>Promote Events</strong> - Create and manage events, sell tickets, and build your audience</li>
            <li><span class="icon">💼</span> <strong>Professional Presence</strong> - Showcase your portfolio to venues, festivals, and booking agents</li>
            <li><span class="icon">💬</span> <strong>Connect & Engage</strong> - Build relationships with fans and the music community</li>
            <li><span class="icon">📊</span> <strong>Track Performance</strong> - Monitor views, engagement, and see what's working</li>
        </ul>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $registerUrl }}" class="button">Claim Your Profile →</a>
        </div>

        <div class="info-box">
            <p style="margin: 0 0 15px 0; font-weight: 600; color: #7c3aed;">🎯 How to Claim Your Profile:</p>
            <ol style="margin: 0; padding-left: 20px; line-height: 1.8;">
                <li><strong>Click "Claim Your Profile"</strong> above to get started</li>
                <li><strong>Create your free account</strong> using this email address{{ $entity->getClaimEmail() ? ' (' . $entity->getClaimEmail() . ')' : '' }}</li>
                <li><strong>Verify your email</strong> - we'll send you a quick verification link</li>
                <li><strong>Your profile is automatically linked!</strong> Start updating your information right away</li>
            </ol>
        </div>

        <p style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; border-radius: 4px; margin: 25px 0;">
            <strong>💡 What happens next?</strong><br>
            Once you claim your profile, you'll have full access to manage all your information, upload photos, promote events, and connect with the South African music community. It only takes a few minutes to get started!
        </p>

        <p style="color: #6b7280; font-size: 14px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            <em>We created this profile to help showcase {{ $entityType == 'artist' ? 'your music' : ($entityType == 'venue' ? 'your venue' : ($entityType == 'event' ? 'your event' : 'your organisation')) }} to a wider audience. If you have any questions or prefer not to claim this profile, feel free to ignore this email.</em><br><br>
            Best regards,<br>
            <strong>The My Gig Guide Team</strong><br>
            <small style="color: #9ca3af;">South Africa's Premier Music Discovery Platform</small>
        </p>
    </div>
</body>
</html>


