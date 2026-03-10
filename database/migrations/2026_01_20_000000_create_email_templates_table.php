<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->longText('body_html');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed initial "Claim Your Account" template (artist/venue claim invitation)
        if (!DB::table('email_templates')->where('key', 'claim_invitation')->exists()) {
            DB::table('email_templates')->insert([
                'key' => 'claim_invitation',
                'name' => 'Claim Your Artist / Venue Profile',
                'subject' => '🎵 Claim Your Artist Profile on My Gig Guide!',
                'description' => 'Invitation for artists / venues / organisers to claim their auto-created profile.',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Your Artist Profile</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #111827;
            max-width: 640px;
            margin: 0 auto;
            padding: 24px;
            background-color: #f3f4f6;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.12);
        }
        .hero {
            text-align: center;
            padding: 24px;
            border-radius: 16px;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 45%, #ec4899 100%);
            color: #ffffff;
            margin-bottom: 24px;
        }
        .hero-title {
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 8px 0;
        }
        .hero-subtitle {
            margin: 0;
            opacity: 0.95;
        }
        .cta-button {
            display: inline-block;
            margin-top: 18px;
            padding: 14px 32px;
            border-radius: 999px;
            background: #fbbf24;
            color: #1f2937;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
        }
        .section-title {
            font-size: 18px;
            margin: 24px 0 12px;
            font-weight: 700;
            color: #111827;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 8px 0 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .feature-row {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            background: #ffffff;
        }
        .feature-row:nth-child(even) {
            background: #f9fafb;
        }
        .feature-icon {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #eef2ff;
            margin-right: 10px;
            font-size: 18px;
        }
        .feature-label {
            font-weight: 600;
            margin-right: 4px;
        }
        .how-to {
            background: #f5f3ff;
            border-radius: 12px;
            padding: 16px 18px;
            margin-top: 20px;
            border: 1px solid #e5e7eb;
        }
        .how-to ol {
            padding-left: 20px;
            margin: 8px 0 0;
        }
        .footer-note {
            font-size: 13px;
            color: #6b7280;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="hero">
            <p class="hero-title">🎵 Claim Your Artist Profile!</p>
            <p class="hero-subtitle">
                We’ve created a profile for <strong>{{ $entityName }}</strong> on 
                <strong>My Gig Guide</strong> – South Africa's premier music discovery platform.
            </p>
            <a href="{{ $registerUrl }}" class="cta-button">Claim Your Profile</a>
        </div>

        <p>Hi {{ $contactName ?? 'there' }},</p>

        <p>
            We're excited to let you know that we've created a {{ $entityType ?? 'artist' }} profile for 
            <strong>{{ $entityName }}</strong> on My Gig Guide. We’d love for you to help us test the features you can use. <em>(*blush*)</em>
        </p>

        <p>You can:</p>
        <ul>
            <li>Manage your public profile – pictures, videos, contact information, and more.</li>
            <li>Create and manage events that you're doing.</li>
            <li>Select from hundreds of artists already on the platform or add missing collaborators.</li>
            <li>Add YouTube videos to your events or profile.</li>
            <li>Share events to Facebook to increase your reach and exposure.</li>
        </ul>

        <p>
            We're rolling this out to a small group of passionate artists and venues as a pilot. 
            Some features may still be a bit rough around the edges, and your feedback will help us polish them.
        </p>

        <h3 class="section-title">✨ What You Get When You Claim Your Profile:</h3>
        <div class="feature-list">
            <div class="feature-row">
                <div class="feature-icon">🎨</div>
                <div><span class="feature-label">Complete Control</span> – Update all your information, add photos, videos, and media.</div>
            </div>
            <div class="feature-row">
                <div class="feature-icon">🚀</div>
                <div><span class="feature-label">Boost Visibility</span> – Get discovered by music lovers, event organisers, and industry professionals.</div>
            </div>
            <div class="feature-row">
                <div class="feature-icon">📅</div>
                <div><span class="feature-label">Promote Events</span> – Create and manage events, and build your following.</div>
            </div>
            <div class="feature-row">
                <div class="feature-icon">💼</div>
                <div><span class="feature-label">Professional Presence</span> – Showcase your portfolio to artists, festivals, and booking agents.</div>
            </div>
            <div class="feature-row">
                <div class="feature-icon">💬</div>
                <div><span class="feature-label">Connect &amp; Engage</span> – Build relationships with fans and the music community.</div>
            </div>
            <div class="feature-row">
                <div class="feature-icon">📊</div>
                <div><span class="feature-label">Track Performance</span> – Monitor views, engagement, and see what's working.</div>
            </div>
        </div>

        <div class="how-to">
            <strong>🎯 How to Claim Your Profile:</strong>
            <ol>
                <li>Click the <strong>“Claim Your Profile”</strong> button above or visit <a href="https://www.mygigguide.co.za/login">https://www.mygigguide.co.za/login</a>.</li>
                <li>Sign up or log in using this email address <strong>{{ $entity->getClaimEmail() ?? '' }}</strong>.</li>
                <li>Verify your email when prompted.</li>
                <li>Your profile is already linked to <strong>{{ $entityName }}</strong> – start updating your information right away.</li>
            </ol>
        </div>

        <p>
            We created this profile using publicly available information to help showcase your gigs to a wider audience. 
            If you have any questions or prefer not to claim this profile, you can simply ignore this email.
        </p>

        <p class="footer-note">
            Best regards,<br>
            <strong>The My Gig Guide Team</strong><br>
            South Africa's Premier Music Discovery Platform
        </p>
    </div>
</body>
</html>
HTML,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};

