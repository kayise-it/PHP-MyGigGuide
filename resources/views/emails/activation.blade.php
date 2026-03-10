<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Your Account - My Gig Guide</title>
    <style>
        /* Reset styles */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
        }

        /* Main styles */
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            min-width: 100%;
            height: 100%;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        .header {
            background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .logo {
            color: #ffffff;
            font-size: 28px;
            font-weight: bold;
            margin: 0;
            text-decoration: none;
        }

        .content {
            padding: 40px 30px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin: 0 0 20px 0;
            text-align: center;
        }

        .subtitle {
            font-size: 16px;
            color: #6b7280;
            margin: 0 0 30px 0;
            text-align: center;
        }

        .message {
            font-size: 16px;
            color: #374151;
            margin: 0 0 30px 0;
            line-height: 1.6;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
            transition: all 0.3s ease;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 30px 0;
        }

        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
        }

        .footer-text {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 10px 0;
        }

        .footer-links {
            margin: 20px 0 0 0;
        }

        .footer-links a {
            color: #8b5cf6;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .social-links {
            margin: 20px 0 0 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #6b7280;
            text-decoration: none;
        }

        .security-note {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #92400e;
        }

        .security-note strong {
            color: #92400e;
        }

        /* Responsive styles */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            .header, .content, .footer {
                padding: 20px !important;
            }
            .title {
                font-size: 20px !important;
            }
            .cta-button {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <a href="{{ config('app.url') }}" class="logo">My Gig Guide</a>
        </div>

        <!-- Main Content -->
        <div class="content">
            <h1 class="title">Welcome to My Gig Guide!</h1>
            <p class="subtitle">Please activate your account to get started</p>
            
            <div class="message">
                <p>Hi {{ $user->name }},</p>
                <p>Thank you for joining My Gig Guide! We're excited to have you as part of our music community.</p>
                <p>To complete your registration and start discovering amazing events, artists, and venues, please click the button below to activate your account:</p>
            </div>

            <div class="button-container">
                <a href="{{ $activationUrl }}" class="cta-button">Activate My Account</a>
            </div>

            <div class="security-note">
                <strong>Security Note:</strong> This activation link will expire in 24 hours for your security. If you didn't create an account with us, please ignore this email.
            </div>

            <div class="message">
                <p>Once activated, you'll be able to:</p>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Discover amazing events in your area</li>
                    <li>Follow your favorite artists and venues</li>
                    <li>Rate and review events and artists</li>
                    <li>Get personalized recommendations</li>
                    <li>Connect with the music community</li>
                </ul>
            </div>

            <div class="divider"></div>

            <div class="message">
                <p>If the button above doesn't work, you can copy and paste this link into your browser:</p>
                <p style="word-break: break-all; background-color: #f3f4f6; padding: 10px; border-radius: 6px; font-family: monospace; font-size: 14px;">{{ $activationUrl }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-text">Made with ❤️ for music lovers everywhere</p>
            <p class="footer-text">&copy; {{ date('Y') }} My Gig Guide. All rights reserved.</p>
            
            <div class="footer-links">
                <a href="{{ config('app.url') }}">Home</a>
                <a href="{{ config('app.url') }}/events">Events</a>
                <a href="{{ config('app.url') }}/artists">Artists</a>
                <a href="{{ config('app.url') }}/venues">Venues</a>
                <a href="{{ config('app.url') }}/contact">Contact</a>
            </div>

            <div class="social-links">
                <a href="#">Twitter</a>
                <a href="#">Facebook</a>
                <a href="#">Instagram</a>
            </div>
        </div>
    </div>
</body>
</html>

