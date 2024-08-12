<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContectRequests\ContactRequest;
use App\Http\Resources\Contact\ContactResource;
use App\Services\Contact\ContactService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    use ApiResponse;

    protected $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function store(ContactRequest $request)
    {
        // Check if the request contains any data
        if (!$request->hasAny(['username', 'email', 'message'])) {
            return $this->error('No data provided', 400);
        }

        // Validate the incoming request data
        $validatedData = $request->validated();

        // Store the contact using the service
        $this->contactService->storeContact($validatedData);

        return $this->success('Contact is sent successfully', 200);
    }

    public function index()
    {
        // Retrieve all contacts with pagination using the service
        $contacts = $this->contactService->getAllContacts();

        return ContactResource::collection($contacts);
    }

    public function getFirstEightContacts()
    {
        // Check if the user is authenticated as an admin
        if (!Auth::guard('admin')->check()) {
            return $this->error('Unauthorized', 401);
        }

        // Retrieve the first 8 contacts using the service
        $contacts = $this->contactService->getFirstEightContacts();

        return ContactResource::collection($contacts);
    }
}