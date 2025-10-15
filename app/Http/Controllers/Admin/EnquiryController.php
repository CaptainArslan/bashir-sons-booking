<?php

namespace App\Http\Controllers\Admin;

use App\Models\Enquiry;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class EnquiryController extends Controller
{
    public function index()
    {
        return view('admin.enquiries.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $enquiries = Enquiry::query()
                ->select('id', 'name', 'email', 'phone', 'service', 'message', 'created_at')
                ->orderBy('created_at', 'desc');

            return DataTables::eloquent($enquiries)
                ->addColumn('contact_info', function ($enquiry) {
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-primary">' . e($enquiry->name) . '</span>
                                <small class="text-muted"><i class="bx bx-envelope me-1"></i>' . e($enquiry->email) . '</small>
                                <small class="text-muted"><i class="bx bx-phone me-1"></i>' . e($enquiry->phone) . '</small>
                            </div>';
                })
                ->addColumn('service_badge', function ($enquiry) {
                    if (!$enquiry->service) {
                        return '<span class="badge bg-secondary">No service</span>';
                    }
                    return '<span class="badge bg-info">' . e($enquiry->service) . '</span>';
                })
                ->addColumn('message_preview', function ($enquiry) {
                    return '<span class="text-muted">' . e(\Str::limit($enquiry->message, 100)) . '</span>';
                })
                ->addColumn('actions', function ($enquiry) {
                    $actions = '
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                    type="button" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <i class="bx bx-dots-horizontal-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" 
                                       href="' . route('admin.enquiries.show', $enquiry->id) . '">
                                        <i class="bx bx-show me-2"></i>View Details
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" 
                                       href="mailto:' . e($enquiry->email) . '?subject=Re: Your Enquiry #' . $enquiry->id . '">
                                        <i class="bx bx-envelope me-2"></i>Reply via Email
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" 
                                       href="tel:' . e($enquiry->phone) . '">
                                        <i class="bx bx-phone me-2"></i>Call Customer
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" 
                                       href="javascript:void(0)" 
                                       onclick="deleteEnquiry(' . $enquiry->id . ')">
                                        <i class="bx bx-trash me-2"></i>Delete Enquiry
                                    </a>
                                </li>
                            </ul>
                        </div>';

                    return $actions;
                })
                ->editColumn('created_at', fn($enquiry) => $enquiry->created_at->format('d M Y H:i'))
                ->escapeColumns([])
                ->rawColumns(['contact_info', 'service_badge', 'message_preview', 'actions'])
                ->make(true);
        }
    }

    public function show($id)
    {
        $enquiry = Enquiry::findOrFail($id);
        return view('admin.enquiries.show', compact('enquiry'));
    }

    public function destroy($id)
    {
        try {
            $enquiry = Enquiry::findOrFail($id);
            $enquiry->delete();
            return response()->json([
                'success' => true,
                'message' => 'Enquiry deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting enquiry: ' . $e->getMessage()
            ], 500);
        }
    }
}