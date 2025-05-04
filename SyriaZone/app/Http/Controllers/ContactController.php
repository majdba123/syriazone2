<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        // Determine if the user is a vendor or regular user
        $contactable = $user->vendor ?? $user;

        // Create the contact
        $contact = $contactable->contacts()->create([
            'subject' => $request->subject,
            'status' => 'pending',
        ]);



        return response()->json([
            'success' => true,
            'message' => 'Contact created successfully',
            'data' => [
                'contact' => $contact,
                'contactable_type' => $contactable instanceof \App\Models\Vendor ? 'vendor' : 'user',
                'contactable_id' => $contactable->id
            ]
        ], 201);
    }



    public function myContacts()
    {
        $user = Auth::user();

        // Get the contactable entity (vendor if exists, otherwise user)
        $contactable = $user->vendor ?? $user;

        // Get contacts with replies, ordered by latest first
        $contacts = $contactable->contacts()
            ->with('replies')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contacts,
            'contactable_type' => $contactable instanceof \App\Models\Vendor ? 'vendor' : 'user',
            'contactable_id' => $contactable->id
        ]);
    }


    public function storeReply(Request $request, $contact_id)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            // Find the contact with error handling
            $contact = Contact::find($contact_id);
            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found',
                    'errors' => ['contact_id' => ['The specified contact does not exist']]
                ], 404);
            }

            // Check if user is authorized to reply to this contact


            // Create the reply
            $reply = $contact->replies()->create([
                'message' => $validated['message'],
            ]);

            // Update contact status
            $contact->update(['status' => 'replied']);

            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully',
                'data' => [
                    'reply' => $reply,
                    'contact_status' => 'replied'
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()->toArray()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }



    public function allContacts(Request $request)
    {
        try {

            // Validate filter parameters
            $validated = $request->validate([
                'status' => 'nullable|in:pending,replied',
                'type' => 'nullable|in:user,vendor',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            // Base query
            $query = Contact::with('replies')->latest();

            // If type filter is not provided, show only current user's/vendor's contacts
            if (!$request->has('type')) {

            } else {
                // Apply type filter
                if ($validated['type'] === 'vendor') {
                    $query->where('contactable_type', \App\Models\Vendor::class);
                } else {
                    $query->where('contactable_type', \App\Models\User::class);
                }

                // For admin viewing all contacts, no additional restrictions
                // For regular users, restrict to their own contacts

            }

            // Apply status filter if provided
            if ($request->has('status')) {
                $query->where('status', $validated['status']);
            }

            // Pagination
            $perPage = $validated['per_page'] ?? 15;
            $contacts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $contacts,
                'filters' => [
                    'status' => $request->status,
                    'type' => $request->type,
                    'per_page' => $perPage
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()->toArray()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }



    public function destroy($contact_id)
    {
        try {
            $user = Auth::user();
            $contact = Contact::find($contact_id);

            // Check if contact exists
            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found',
                    'errors' => ['contact_id' => ['The specified contact does not exist']]
                ], 404);
            }

            // Delete the contact and its replies (using cascading delete if set up in database)
            $contact->replies()->delete();
            $contact->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contact and its replies deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting contact',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }


}
