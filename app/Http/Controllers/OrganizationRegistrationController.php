<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Organization;
use App\Models\OrganizationDocument;
use App\Models\User;
use App\Services\NotificationApiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrganizationRegistrationController extends Controller
{
    /**
     * Show the organization registration form.
     */
    public function create()
    {
        $countries = Country::orderBy('name')->get();

        return view('organizations.register', compact('countries'));
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
            'country_id'            => 'required|exists:countries,id',
            'account_type'          => 'required|in:organization,developer',
            'document_names'        => 'required|array|size:4',
            'document_names.*'      => 'required|string|max:255',
            'document_files'        => 'required|array|size:4',
            'document_files.*'      => 'required|file|mimes:pdf|max:10240', // 10MB max per file
        ], [
            'document_files.required' => 'All four documents are required.',
            'document_files.size'    => 'All four documents must be uploaded.',
            'document_files.*.required' => 'Each document file is required.',
            'document_files.*.mimes' => 'Only PDF files are allowed.',
            'document_files.*.max'   => 'Each document must not exceed 10MB.',
            'account_type.required'  => 'Please select your account type.',
            'account_type.in'        => 'Invalid account type selected.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('organizations.register')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Currency is always TZS
            $currency = Currency::where('code', 'TZS')->firstOrFail();

            // Create organization
            $organization = Organization::create([
                'name'                => $request->name,
                'phone'               => $request->phone,
                'email'               => $request->email,
                'tin_number'          => $request->tin_number,
                'registration_number' => $request->registration_number,
                'currency'            => [$currency->code],
                'currency_id'         => $currency->id,
                'country_id'          => $request->country_id,
                'timezone'            => 'Africa/Dar_es_Salaam',
                'status'              => 'active',
                'account_type'        => $request->account_type,
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

            // Create admin user account for the organization
            $tempPassword = Str::password(12, letters: true, numbers: true, symbols: false);

            $user = User::create([
                'organization_id' => $organization->id,
                'name'            => $request->name,
                'email'           => $request->email,
                'role'            => 'admin',
                'sex'             => 'O',
                'status'          => 'active',
                'activated_at'    => now(),
                'password'        => $tempPassword,
            ]);

            DB::commit();

            Log::info('Organization registered via web form', [
                'organization_id' => $organization->id,
                'user_id'         => $user->id,
            ]);

            // Send welcome email with credentials (outside transaction so DB is committed first)
            $notifier = new NotificationApiService();

            $appName    = config('app.name');
            $loginUrl   = route('login');
            $orgName    = $organization->name;

            // --- Email ---
            $emailSubject = "Welcome to {$appName} – Your Account is Ready";
            $emailMessage = "
                <p>Hello <strong>{$orgName}</strong>,</p>
                <p>Your organization has been successfully registered on <strong>{$appName}</strong>.</p>
                <p><strong>Login Email:</strong> {$user->email}<br>
                <strong>Temporary Password:</strong> {$tempPassword}</p>
                <p><a href='{$loginUrl}'>Click here to login</a></p>
                <p>⚠️ Please change your password after your first login.</p>
                <p>Welcome aboard,<br>The {$appName} Team</p>
            ";

            $emailSent = $notifier->sendEmail($user->email, $emailSubject, $emailMessage);

            // --- WhatsApp ---
            $whatsappMessage = "Hello {$orgName}!\n\n"
                . "Your {$appName} account is ready.\n\n"
                . "Login Email: {$user->email}\n"
                . "Temporary Password: {$tempPassword}\n\n"
                . "Login here: {$loginUrl}\n\n"
                . "⚠️ Please change your password after first login.";

            $whatsappSent = $notifier->sendWhatsApp($request->phone, $whatsappMessage);

            Log::info('Welcome notifications dispatched', [
                'organization_id' => $organization->id,
                'email_sent'      => $emailSent,
                'whatsapp_sent'   => $whatsappSent,
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
