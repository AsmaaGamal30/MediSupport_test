<?php

namespace App\Http\Controllers\Contact;

use App\Models\Contact;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Contact\ContactResource;
use App\Http\Requests\ContectRequests\ContactRequest;

class ContactController extends Controller
{
    use ApiResponse;

    public function store(ContactRequest $request)
    {
        // Check if the request contains any data
        if (!$request->hasAny(['username', 'email', 'message'])) {
            return $this->error('No data provided', 400);
        }

        // Validate the incoming request data
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);


        $submission = Contact::create([
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'message' => $validatedData['message'],
        ]);

        return $this->success('Contact is Sent Successfully', 200);
    }

    public function index()
    {
        return ContactResource::collection(Contact::query()->paginate(10));
    }

    public function getFirstEightContacts()
    {
        // Check if the user is authenticated as an admin
        if (!Auth::guard('admin')->check()) {
            return $this->error('Unauthorized', 401);
        }

        // Retrieve the first 8 contacts from the database
        $contacts = Contact::take(8)->get();

        // Return the contact resource collection
        return ContactResource::collection($contacts);
    }

    }