<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        // Save the contact form submission to your database or any other storage mechanism
        // For example, using Eloquent ORM:
        // Assuming you have a ContactFormSubmission model with 'username', 'email', and 'message' fields
        $submission = \App\Models\Contact::create([
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'message' => $validatedData['message'],
        ]);

        // Optionally, you can return a success response or any other response as needed
        return response()->json(['message' => 'Contact form submitted successfully'], 200);
    }
}