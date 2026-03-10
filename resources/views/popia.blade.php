@extends('layouts.app')

@section('title', 'POPIA Privacy Policy - My Gig Guide')
@section('description', 'Protection of Personal Information Act (POPIA) Privacy Policy for My Gig Guide')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <div class="flex items-center space-x-3 mb-6">
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-3 rounded-xl">
                    <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">POPIA Privacy Policy</h1>
                    <p class="text-gray-600 mt-1">Protection of Personal Information Act Compliance</p>
                </div>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Last Updated:</strong> {{ date('F d, Y') }} | This policy complies with South Africa's Protection of Personal Information Act (POPIA), 2013.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="space-y-8">
            
            <!-- Introduction -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">1</span>
                    Introduction
                </h2>
                <div class="prose prose-gray max-w-none">
                    <p class="text-gray-700 leading-relaxed">
                        My Gig Guide ("we," "our," or "us") is committed to protecting your personal information in accordance with South Africa's Protection of Personal Information Act (POPIA), 2013. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our event discovery platform and related services.
                    </p>
                    <p class="text-gray-700 leading-relaxed mt-4">
                        By using our website and services, you consent to the collection and use of your personal information as described in this policy. If you do not agree with our policies and practices, please do not use our services.
                    </p>
                </div>
            </section>

            <!-- Information We Collect -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">2</span>
                    Information We Collect
                </h2>
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">2.1 Personal Information</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li><strong>Account Information:</strong> Name, email address, phone number, profile picture, and date of birth</li>
                            <li><strong>Event Information:</strong> Event attendance history, preferences, and reviews</li>
                            <li><strong>Artist/Venue Information:</strong> Business details, contact information, and performance history</li>
                            <li><strong>Payment Information:</strong> Billing address and payment method details (processed securely through third-party providers)</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">2.2 Technical Information</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li>IP address, browser type, device information, and operating system</li>
                            <li>Website usage data, including pages visited, time spent, and click patterns</li>
                            <li>Location data (with your permission) for finding nearby events</li>
                            <li>Cookies and similar tracking technologies</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- How We Use Information -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">3</span>
                    How We Use Your Information
                </h2>
                <div class="space-y-4">
                    <ul class="list-disc list-inside space-y-3 text-gray-700">
                        <li><strong>Service Provision:</strong> To provide and maintain our event discovery platform, process bookings, and facilitate transactions</li>
                        <li><strong>Personalization:</strong> To customize content, recommend events, and improve user experience</li>
                        <li><strong>Communication:</strong> To send event updates, newsletters, and important service notifications</li>
                        <li><strong>Analytics:</strong> To analyze usage patterns, improve our services, and develop new features</li>
                        <li><strong>Legal Compliance:</strong> To comply with applicable laws, regulations, and legal processes</li>
                        <li><strong>Security:</strong> To protect against fraud, abuse, and unauthorized access</li>
                    </ul>
                </div>
            </section>

            <!-- Legal Basis for Processing -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">4</span>
                    Legal Basis for Processing (POPIA Compliance)
                </h2>
                <div class="space-y-4">
                    <p class="text-gray-700 leading-relaxed">
                        Under POPIA, we process your personal information based on the following lawful grounds:
                    </p>
                    <ul class="list-disc list-inside space-y-3 text-gray-700">
                        <li><strong>Consent:</strong> When you explicitly agree to the processing of your personal information</li>
                        <li><strong>Contract Performance:</strong> To fulfill our contractual obligations to provide services</li>
                        <li><strong>Legitimate Interest:</strong> To improve our services, prevent fraud, and maintain platform security</li>
                        <li><strong>Legal Obligation:</strong> To comply with applicable South African laws and regulations</li>
                        <li><strong>Vital Interest:</strong> To protect your life, health, or physical integrity in emergency situations</li>
                    </ul>
                </div>
            </section>

            <!-- Information Sharing -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">5</span>
                    Information Sharing and Disclosure
                </h2>
                <div class="space-y-4">
                    <p class="text-gray-700 leading-relaxed">
                        We may share your personal information in the following circumstances:
                    </p>
                    <ul class="list-disc list-inside space-y-3 text-gray-700">
                        <li><strong>Event Organizers:</strong> When you book events, we share necessary information with organizers</li>
                        <li><strong>Service Providers:</strong> With trusted third parties who assist in operating our platform (payment processors, analytics providers)</li>
                        <li><strong>Legal Requirements:</strong> When required by law, court order, or to protect our rights and safety</li>
                        <li><strong>Business Transfers:</strong> In connection with mergers, acquisitions, or asset sales</li>
                        <li><strong>Consent:</strong> With your explicit consent for any other purpose</li>
                    </ul>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                        <p class="text-sm text-yellow-800">
                            <strong>Note:</strong> We never sell your personal information to third parties for marketing purposes.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Data Security -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">6</span>
                    Data Security
                </h2>
                <div class="space-y-4">
                    <p class="text-gray-700 leading-relaxed">
                        We implement appropriate technical and organizational measures to protect your personal information:
                    </p>
                    <ul class="list-disc list-inside space-y-3 text-gray-700">
                        <li>SSL encryption for data transmission</li>
                        <li>Secure servers and databases with access controls</li>
                        <li>Regular security audits and vulnerability assessments</li>
                        <li>Staff training on data protection and privacy</li>
                        <li>Incident response procedures for data breaches</li>
                    </ul>
                </div>
            </section>

            <!-- Your Rights -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">7</span>
                    Your Rights Under POPIA
                </h2>
                <div class="space-y-4">
                    <p class="text-gray-700 leading-relaxed">
                        You have the following rights regarding your personal information:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">Access & Portability</h4>
                            <p class="text-sm text-gray-700">Request access to your personal information and receive it in a portable format</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">Correction</h4>
                            <p class="text-sm text-gray-700">Request correction of inaccurate or incomplete personal information</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">Deletion</h4>
                            <p class="text-sm text-gray-700">Request deletion of your personal information in certain circumstances</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2">Objection</h4>
                            <p class="text-sm text-gray-700">Object to processing based on legitimate interests or for marketing purposes</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">
                            <strong>To exercise your rights:</strong> Contact us at <a href="mailto:privacy@mygigguide.co.za" class="text-blue-600 hover:text-blue-800 underline">privacy@mygigguide.co.za</a> or use the contact form below.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Data Retention -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">8</span>
                    Data Retention
                </h2>
                <div class="space-y-4">
                    <p class="text-gray-700 leading-relaxed">
                        We retain your personal information only for as long as necessary to fulfill the purposes outlined in this policy:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li><strong>Account Information:</strong> Until you delete your account or request deletion</li>
                        <li><strong>Event Data:</strong> For the duration of the event plus 3 years for analytics and legal compliance</li>
                        <li><strong>Payment Records:</strong> As required by South African tax and accounting laws (typically 5 years)</li>
                        <li><strong>Marketing Data:</strong> Until you unsubscribe or object to processing</li>
                    </ul>
                </div>
            </section>

            <!-- Cookies and Tracking -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">9</span>
                    Cookies and Tracking Technologies
                </h2>
                <div class="space-y-4">
                    <p class="text-gray-700 leading-relaxed">
                        We use cookies and similar technologies to enhance your experience:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-semibold text-green-900 mb-2">Essential Cookies</h4>
                            <p class="text-sm text-green-800">Required for basic website functionality and security</p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-900 mb-2">Analytics Cookies</h4>
                            <p class="text-sm text-blue-800">Help us understand how you use our website</p>
                        </div>
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <h4 class="font-semibold text-purple-900 mb-2">Preference Cookies</h4>
                            <p class="text-sm text-purple-800">Remember your settings and preferences</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4">
                        You can manage cookie preferences through your browser settings or our cookie consent banner.
                    </p>
                </div>
            </section>

            <!-- Children's Privacy -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">10</span>
                    Children's Privacy
                </h2>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-gray-700 leading-relaxed">
                        Our services are not directed to children under 18 years of age. We do not knowingly collect personal information from children under 18. If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately.
                    </p>
                </div>
            </section>

            <!-- Changes to Policy -->
            <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">11</span>
                    Changes to This Policy
                </h2>
                <p class="text-gray-700 leading-relaxed">
                    We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy on this page and updating the "Last Updated" date. Your continued use of our services after such changes constitutes acceptance of the updated policy.
                </p>
            </section>

            <!-- Contact Information -->
            <section class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg border border-purple-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="bg-purple-600 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mr-3">12</span>
                    Contact Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Data Protection Officer</h3>
                        <div class="space-y-2 text-gray-700">
                            <p><strong>Email:</strong> <a href="mailto:privacy@mygigguide.co.za" class="text-purple-600 hover:text-purple-800">privacy@mygigguide.co.za</a></p>
                            <p><strong>Phone:</strong> +27 (0) 11 123 4567</p>
                            <p><strong>Address:</strong><br>
                            My Gig Guide (Pty) Ltd<br>
                            Privacy Department<br>
                            Johannesburg, South Africa
                            </p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">General Inquiries</h3>
                        <div class="space-y-2 text-gray-700">
                            <p><strong>Email:</strong> <a href="mailto:info@mygigguide.co.za" class="text-purple-600 hover:text-purple-800">info@mygigguide.co.za</a></p>
                            <p><strong>Website:</strong> <a href="{{ route('home') }}" class="text-purple-600 hover:text-purple-800">www.mygigguide.co.za</a></p>
                            <p><strong>Response Time:</strong> We aim to respond to all privacy inquiries within 48 hours.</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-white rounded-lg border border-purple-200">
                    <p class="text-sm text-gray-600">
                        <strong>Complaints:</strong> If you believe we have not handled your personal information in accordance with POPIA, you have the right to lodge a complaint with the Information Regulator of South Africa at <a href="mailto:inforeg@justice.gov.za" class="text-purple-600 hover:text-purple-800">inforeg@justice.gov.za</a> or visit <a href="https://www.justice.gov.za/inforeg/" target="_blank" class="text-purple-600 hover:text-purple-800">www.justice.gov.za/inforeg/</a>
                    </p>
                </div>
            </section>
        </div>

        <!-- Back to Home -->
        <div class="mt-12 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-medium rounded-lg transition-all duration-300 shadow-sm hover:shadow-md">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection

