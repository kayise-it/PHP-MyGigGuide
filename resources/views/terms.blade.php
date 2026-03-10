@extends('layouts.app')

@section('title', 'Terms and Conditions - My Gig Guide')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-8">Terms and Conditions</h1>
    
    <div class="prose prose-lg max-w-none">
        <p class="text-gray-600 mb-6">
            <strong>Last updated:</strong> {{ date('F j, Y') }}
        </p>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">1. Acceptance of Terms</h2>
            <p class="text-gray-700 mb-4">
                By accessing and using My Gig Guide ("the Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">2. Use License</h2>
            <p class="text-gray-700 mb-4">
                Permission is granted to temporarily download one copy of My Gig Guide per device for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4">
                <li>modify or copy the materials</li>
                <li>use the materials for any commercial purpose or for any public display (commercial or non-commercial)</li>
                <li>attempt to decompile or reverse engineer any software contained on My Gig Guide</li>
                <li>remove any copyright or other proprietary notations from the materials</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">3. User Accounts</h2>
            <p class="text-gray-700 mb-4">
                When you create an account with us, you must provide information that is accurate, complete, and current at all times. You are responsible for safeguarding the password and for maintaining the confidentiality of your account.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">4. Content Policy</h2>
            <p class="text-gray-700 mb-4">
                You are responsible for the content you post on My Gig Guide. You agree not to post content that:
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4">
                <li>is unlawful, harmful, threatening, abusive, harassing, defamatory, vulgar, obscene, or invasive of another's privacy</li>
                <li>infringes on any patent, trademark, trade secret, copyright, or other proprietary rights</li>
                <li>contains software viruses or any other computer code, files, or programs designed to interrupt, destroy, or limit the functionality of any computer software or hardware</li>
                <li>is spam, chain letters, pyramid schemes, or any other form of solicitation</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">5. Event Listings</h2>
            <p class="text-gray-700 mb-4">
                My Gig Guide provides a platform for event organizers to list their events. We do not guarantee the accuracy of event information, and we are not responsible for events that are cancelled, postponed, or changed without notice.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">6. Privacy Policy</h2>
            <p class="text-gray-700 mb-4">
                Your privacy is important to us. Please review our Privacy Policy, which also governs your use of the Service, to understand our practices.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">7. Disclaimers</h2>
            <p class="text-gray-700 mb-4">
                The information on this website is provided on an "as is" basis. To the fullest extent permitted by law, this Company:
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4">
                <li>excludes all representations and warranties relating to this website and its contents</li>
                <li>excludes all liability for damages arising out of or in connection with your use of this website</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">8. Governing Law</h2>
            <p class="text-gray-700 mb-4">
                These terms and conditions are governed by and construed in accordance with the laws of South Africa and you irrevocably submit to the exclusive jurisdiction of the courts in that State or location.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">9. Changes to Terms</h2>
            <p class="text-gray-700 mb-4">
                My Gig Guide reserves the right to revise these terms at any time without notice. By using this web site you are agreeing to be bound by the then current version of these terms and conditions of use.
            </p>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">10. Contact Information</h2>
            <p class="text-gray-700 mb-4">
                If you have any questions about these Terms and Conditions, please contact us at:
            </p>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-700">
                    <strong>Email:</strong> legal@mygigguide.co.za<br>
                    <strong>Address:</strong> 123 Music Street, Cape Town, South Africa<br>
                    <strong>Phone:</strong> +27 74 660 9752
                </p>
            </div>
        </section>
    </div>
</div>
@endsection
