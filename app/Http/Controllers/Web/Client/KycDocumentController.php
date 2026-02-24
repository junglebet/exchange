<?php

namespace App\Http\Controllers\Web\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\User\KycFormRequest;
use App\Http\Resources\Country\CountryCollection;
use App\Http\Resources\KycDocument\KycDocument;
use App\Mail\KycDocuments\AdminKycReceived;
use App\Mail\Withdrawals\AdminWithdrawalReceived;
use App\Repositories\Country\CountryRepository;
use App\Repositories\KycDocument\KycDocumentRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Setting;

class KycDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $kycStatus = Setting::get('general.kyc_status');

        if(!$kycStatus) {
            return Inertia::render('Kyc/Index', [
                'isVerified' => auth()->user()->kyc_verified_at ? true : false,
                'countries' => [],
                'pendingDocument' => null,
                'rejectedDocument' => null,
            ]);
        }

        $countries = new CountryCollection((new CountryRepository())->get());

        $kycDocumentRepository = new KycDocumentRepository();

        $user_id = auth()->user()->getAuthIdentifier();

        $kycPendingDocument = $kycDocumentRepository->getByStatus(KYC_DOCUMENT_STATUS_PENDING, $user_id);

        $kycRejectedDocument = $kycDocumentRepository->getByStatus(KYC_DOCUMENT_STATUS_REJECTED, $user_id);

        return Inertia::render('Kyc/Index', [
            'isVerified' => auth()->user()->kyc_verified_at ? true : false,
            'countries' => $countries,
            'pendingDocument' => $kycPendingDocument ? new KycDocument($kycPendingDocument) : null,
            'rejectedDocument' => $kycRejectedDocument ? new KycDocument($kycRejectedDocument) : null,
        ]);
    }

    /**
     * Store the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(KycFormRequest $request)
    {
        $data = $request->only([
            'first_name',
            'last_name',
            'middle_name',
            'country_id',
            'document_type',
            'selfie_id',
            'back_id',
            'front_id',
        ]);

        $data['user_id'] = auth()->user()->getAuthIdentifier();
        $data['status'] = KYC_DOCUMENT_STATUS_PENDING;

        (new KycDocumentRepository())->store($data);

        // Admin Email Notification
        $adminEmail = Setting::get('notification.admin_email', false);
        $notificationAllowed = Setting::get('notification.kyc_received', false);

        if($adminEmail && $notificationAllowed) {
            $route = route('admin.kyc.documents') . "?search=" . auth()->user()->email;
            Mail::to($adminEmail)->queue(new AdminKycReceived(auth()->user()->email, $route));
        }
        // END Admin Email Notification

        return Redirect::route('user.kyc');
    }
}
