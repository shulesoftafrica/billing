<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationDocument;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class OrganizationRegistrationController extends Controller
{
    /**
     * Show the organization registration form.
     */
    public function create()
    {
        $countries = Country::orderBy('name')->get();
        $currencies = Currency::orderBy('name')->get();

        return view('organizations.register', compact('countries', 'currencies'));
    }

    /**
     * Handle the organization registration submission.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'phone'                 => 'required|string|max:20',
            'email'                 => 'required|email|max:255|unique:organizations,email',
            'tin_number'            => 'nullable|string|max:50',
            'registration_number'   => 'nullable|string|max:50',
            'currency'              => 'required|array|min:1',
            'currency.*'            => 'required|string|max:10',
            'country_id'            => 'required|exists:countries,id',
            'document_names'        => 'required|array|min:1',
            'document_names.*'      => 'required|string|max:255',
            'document_files'        => 'required|array|min:1',
            'document_files.*'      => 'required|file|mimes:pdf|max:10240', // 10MB max per file
        ], [
            'document_names.required' => 'At least one document is required.',
            'document_names.min'     => 'At least one document is required.',
            'document_files.required' => 'At least one document must be uploaded.',
            'document_files.min'     => 'At least one document must be uploaded.',
            'document_files.*.mimes' => 'Only PDF files are allowed.',
            'document_files.*.max'   => 'Each document must not exceed 10MB.',
            'currency.required'      => 'Please select at least one currency.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('organizations.register')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create organization
            $organization = Organization::create([
                'name'                => $request->name,
                'phone'               => $request->phone,
                'email'               => $request->email,
                'tin_number'          => $request->tin_number,
                'registration_number' => $request->registration_number,
                'currency'            => $request->currency,
                'country_id'          => $request->country_id,
                'status'              => 'pending',
            ]);

            // Handle document uploads
            if ($request->hasFile('document_files')) {
                $documentNames = $request->document_names ?? [];
                $documentFiles = $request->file('document_files');

                foreach ($documentFiles as $index => $file) {
                    $documentName = $documentNames[$index] ?? 'Document ' . ($index + 1);

                    // Store file in storage/app/private/organization_documents/{org_id}/
                    $path = $file->store(
                        'organization_documents/' . $organization->id,
                        'local'
                    );

                    OrganizationDocument::create([
                        'organization_id'  => $organization->id,
                        'document_name'    => $documentName,
                        'file_path'        => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type'        => $file->getMimeType(),
                        'file_size'        => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            Log::info('Organization registered via web form', [
                'organization_id' => $organization->id,
            ]);

            return redirect()
                ->route('organizations.register.success')
                ->with('organization', $organization);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Organization web registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('organizations.register')
                ->with('error', 'Registration failed. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show the registration success page.
     */
    public function success()
    {
        return view('organizations.success');
    }
}
