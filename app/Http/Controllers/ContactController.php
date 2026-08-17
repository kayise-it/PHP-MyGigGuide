<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Show the contact form.
     */
    public function index()
    {
        return view('contact');
    }

    /**
     * Handle contact form submission.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $newsletter = $request->boolean('newsletter');

        try {
            $contactData = $request->only(['name', 'email', 'subject', 'message']);

            // Log the contact form submission
            \Log::info('Contact form submitted', array_merge($contactData, ['newsletter' => $newsletter]));

            // Send email to contact owner using Mailable class
            try {
                Mail::to('dave@mygigguide.co.za')
                    ->send(new ContactFormMail(
                        $contactData['name'],
                        $contactData['email'],
                        $contactData['subject'],
                        $contactData['message'],
                        $newsletter
                    ));
            } catch (\Throwable $mailErr) {
                \Log::error('Failed to send contact email', [
                    'error' => $mailErr->getMessage(),
                    'trace' => $mailErr->getTraceAsString(),
                ]);

                return redirect()->back()
                    ->with('error', 'Sorry, there was an error sending your message. Please try again or contact us directly at dave@mygigguide.co.za.')
                    ->withInput();
            }

            return redirect()->route('contact.index')
                ->with('success', 'Thank you for contacting us! We will get back to you within 24 hours.');

        } catch (\Exception $e) {
            \Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Sorry, there was an error sending your message. Please try again or contact us directly at dave@mygigguide.co.za.')
                ->withInput();
        }
    }
}
