<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycDocument\KycDocument;
use App\Repositories\KycDocument\KycDocumentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class KycDocumentController extends Controller
{
    /**
     * @var KycDocumentRepository
     */
    protected $kycDocumentRepository;

    /**
     * KycDocumentController Constructor
     *
     * @param KycDocumentRepository $kycDocumentRepository
     *
     */
    public function __construct(KycDocumentRepository $kycDocumentRepository)
    {
        $this->kycDocumentRepository = $kycDocumentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $kycDocuments = $this->kycDocumentRepository->get();

        return Inertia::render('Admin/KycDocuments/Index', [
            'kycDocuments' => $kycDocuments,
            'filters' => request()->all(['search', 'referrer']),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function moderate(Request $request, KycDocument $document)
    {
        $this->kycDocumentRepository->moderate($document, $request->get('action'));

        return Redirect::route('admin.kyc.documents');
    }

}
