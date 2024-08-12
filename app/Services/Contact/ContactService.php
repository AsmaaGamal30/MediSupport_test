<?php

namespace App\Services\Contact;

use App\Models\Contact;

class ContactService
{
    public function storeContact($validatedData)
    {
        return Contact::create([
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'message' => $validatedData['message'],
        ]);
    }

    public function getFirstEightContacts()
    {
        return Contact::take(8)->get();
    }

    public function getAllContacts($perPage = 10)
    {
        return Contact::paginate($perPage);
    }
}
