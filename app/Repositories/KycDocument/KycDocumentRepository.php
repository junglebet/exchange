<?php

namespace App\Repositories\KycDocument;

use App\Mail\KycDocuments\KycApproved;
use App\Mail\KycDocuments\KycRejected;
use App\Models\KycDocument\KycDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class KycDocumentRepository
{
    /**
     * @return KycDocument Collection
     */
    public function get() {

        $kycDocument = KycDocument::query();

        $kycDocument->with(['country', 'selfie', 'back', 'front', 'user']);

        $kycDocument->filter(request()->only(['search']));

        return $kycDocument->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();
    }

    /**
     * @return KycDocument Collection
     */
    public function getByStatus($status, $user_id) {

        $kycDocument = KycDocument::query();

        $kycDocument->whereStatus($status);

        $kycDocument->where('user_id', $user_id);

        $kycDocument->orderBy('id', 'desc');

        return $kycDocument->first();
    }

    /**
     * @param $data
     * @return KycDocument
     */
    public function store($data) {

        $kycDocument = (new KycDocument())->create($data);

        return $kycDocument->fresh();
    }

    /**
     * Moderate Kyc Document
     */
    public function moderate($document, $action) {

        if($action == "approve") {
            $document->status = KYC_DOCUMENT_STATUS_APPROVED;
            $document->user->kyc_verified_at = Carbon::now();
            $document->user->name = $document->first_name . " " . $document->last_name;
            $document->user->update();

            $document->update();

            // Notify user
            Mail::to($document->user)->queue(new KycApproved($document->user));

        } else {
            $document->status = KYC_DOCUMENT_STATUS_REJECTED;
            $document->rejected_reason = nl2br(request()->get('reason'));
            $document->save();

            // Notify user
            Mail::to($document->user)->queue(new KycRejected($document->user, $document->rejected_reason));
        }


    }

    public function count() {
        $document = KycDocument::query();
        return $document->count();
    }

}
