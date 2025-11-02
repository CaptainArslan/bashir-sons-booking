<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnnouncementAudienceTypeEnum;
use App\Enums\AnnouncementDisplayTypeEnum;
use App\Enums\AnnouncementPriorityEnum;
use App\Enums\AnnouncementStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.announcements.index');
    }

    /**
     * Get announcements data for DataTables.
     */
    public function getData(): JsonResponse
    {
        $announcements = Announcement::with(['readers'])
            ->select(['id', 'title', 'status', 'display_type', 'priority', 'audience_type', 'is_pinned', 'is_featured', 'is_active', 'start_date', 'end_date', 'created_at'])
            ->latest();

        return DataTables::of($announcements)
            ->addColumn('status_badge', function ($announcement) {
                $statusClass = match ($announcement->status) {
                    AnnouncementStatusEnum::ACTIVE => 'success',
                    AnnouncementStatusEnum::INACTIVE => 'warning',
                    default => 'secondary',
                };

                return '<span class="badge bg-'.$statusClass.'">'.$announcement->status->value.'</span>';
            })
            ->addColumn('display_type_badge', function ($announcement) {
                $typeClass = match ($announcement->display_type) {
                    AnnouncementDisplayTypeEnum::BANNER => 'primary',
                    AnnouncementDisplayTypeEnum::POPUP => 'info',
                    AnnouncementDisplayTypeEnum::NOTIFICATION => 'warning',
                    default => 'secondary',
                };

                return '<span class="badge bg-'.$typeClass.'">'.$announcement->display_type->value.'</span>';
            })
            ->addColumn('priority_badge', function ($announcement) {
                $priorityClass = match ($announcement->priority) {
                    AnnouncementPriorityEnum::HIGH => 'danger',
                    AnnouncementPriorityEnum::MEDIUM => 'warning',
                    AnnouncementPriorityEnum::LOW => 'success',
                    default => 'secondary',
                };

                return '<span class="badge bg-'.$priorityClass.'">'.$announcement->priority->value.'</span>';
            })
            ->addColumn('audience_type_badge', function ($announcement) {
                $audienceClass = match ($announcement->audience_type) {
                    AnnouncementAudienceTypeEnum::ALL => 'primary',
                    AnnouncementAudienceTypeEnum::ROLES => 'info',
                    AnnouncementAudienceTypeEnum::USERS => 'warning',
                    default => 'secondary',
                };

                return '<span class="badge bg-'.$audienceClass.'">'.$announcement->audience_type->value.'</span>';
            })
            ->addColumn('flags', function ($announcement) {
                $flags = [];
                if ($announcement->is_pinned) {
                    $flags[] = '<span class="badge bg-danger">Pinned</span>';
                }
                if ($announcement->is_featured) {
                    $flags[] = '<span class="badge bg-warning">Featured</span>';
                }
                if ($announcement->is_active) {
                    $flags[] = '<span class="badge bg-success">Active</span>';
                }

                return implode(' ', $flags);
            })
            ->addColumn('date_range', function ($announcement) {
                $startDate = $announcement->start_date ? $announcement->start_date->format('M d, Y') : 'N/A';
                $endDate = $announcement->end_date ? $announcement->end_date->format('M d, Y') : 'N/A';

                return $startDate.' - '.$endDate;
            })
            ->addColumn('actions', function ($announcement) {
                $actions = '<div class="btn-group" role="group">';

                if (auth()->user()->can('view announcements')) {
                    $actions .= '<a href="'.route('admin.announcements.show', $announcement->id).'" class="btn btn-sm btn-outline-primary" title="View">
                        <i class="bx bx-show"></i>
                    </a>';
                }

                if (auth()->user()->can('edit announcements')) {
                    $actions .= '<a href="'.route('admin.announcements.edit', $announcement->id).'" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bx bx-edit"></i>
                    </a>';

                    // Toggle status button
                    $statusText = $announcement->is_active ? 'Deactivate' : 'Activate';
                    $statusClass = $announcement->is_active ? 'btn-outline-danger' : 'btn-outline-success';
                    $actions .= '<button class="btn btn-sm '.$statusClass.'" onclick="toggleStatus('.$announcement->id.', '.($announcement->is_active ? 'true' : 'false').')" title="'.$statusText.'">
                        <i class="bx '.($announcement->is_active ? 'bx-pause' : 'bx-play').'"></i>
                    </button>';
                }

                if (auth()->user()->can('delete announcements')) {
                    $actions .= '<button class="btn btn-sm btn-outline-danger" onclick="deleteAnnouncement('.$announcement->id.')" title="Delete">
                        <i class="bx bx-trash"></i>
                    </button>';
                }

                $actions .= '</div>';

                return $actions;
            })
            ->rawColumns(['status_badge', 'display_type_badge', 'priority_badge', 'audience_type_badge', 'flags', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $users = User::select('id', 'name', 'email')->get();
        $roles = ['admin', 'manager', 'user']; // You can get this from your role system

        return view('admin.announcements.create', compact('users', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = auth()->user();

        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;

        // Convert string values to enum values
        $data['status'] = AnnouncementStatusEnum::from($data['status']);
        $data['display_type'] = AnnouncementDisplayTypeEnum::from($data['display_type']);
        $data['priority'] = AnnouncementPriorityEnum::from($data['priority']);
        $data['audience_type'] = AnnouncementAudienceTypeEnum::from($data['audience_type']);

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement = Announcement::create($data);

        // Handle audience users if audience_type is USERS
        if ($data['audience_type'] === AnnouncementAudienceTypeEnum::USERS && isset($data['audience_users'])) {
            $announcement->readers()->attach($data['audience_users']);
        }

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement): View
    {
        $announcement->load(['readers']);

        return view('admin.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement): View
    {
        $users = User::select('id', 'name', 'email')->get();
        $roles = ['admin', 'manager', 'user']; // You can get this from your role system

        return view('admin.announcements.edit', compact('announcement', 'users', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        // Convert string values to enum values
        $data['status'] = AnnouncementStatusEnum::from($data['status']);
        $data['display_type'] = AnnouncementDisplayTypeEnum::from($data['display_type']);
        $data['priority'] = AnnouncementPriorityEnum::from($data['priority']);
        $data['audience_type'] = AnnouncementAudienceTypeEnum::from($data['audience_type']);

        // Handle image upload if present
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($announcement->image) {
                \Storage::disk('public')->delete($announcement->image);
            }
            $data['image'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement->update($data);

        // Handle audience users if audience_type is USERS
        if ($data['audience_type'] === AnnouncementAudienceTypeEnum::USERS && isset($data['audience_users'])) {
            $announcement->readers()->sync($data['audience_users']);
        } else {
            $announcement->readers()->detach();
        }

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement): JsonResponse
    {
        try {
            // Delete image if exists
            if ($announcement->image) {
                \Storage::disk('public')->delete($announcement->image);
            }

            $announcement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Announcement deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting announcement: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle announcement status.
     */
    public function toggleStatus(Request $request, Announcement $announcement): JsonResponse
    {
        try {
            $announcement->update([
                'is_active' => ! $announcement->is_active,
                'updated_by' => auth()->id(),
            ]);

            $status = $announcement->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Announcement {$status} successfully!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating announcement status: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle pinned status.
     */
    public function togglePinned(Request $request, Announcement $announcement): JsonResponse
    {
        try {
            $announcement->update([
                'is_pinned' => ! $announcement->is_pinned,
                'updated_by' => auth()->id(),
            ]);

            $status = $announcement->is_pinned ? 'pinned' : 'unpinned';

            return response()->json([
                'success' => true,
                'message' => "Announcement {$status} successfully!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating announcement pinned status: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Request $request, Announcement $announcement): JsonResponse
    {
        try {
            $announcement->update([
                'is_featured' => ! $announcement->is_featured,
                'updated_by' => auth()->id(),
            ]);

            $status = $announcement->is_featured ? 'featured' : 'unfeatured';

            return response()->json([
                'success' => true,
                'message' => "Announcement {$status} successfully!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating announcement featured status: '.$e->getMessage(),
            ], 500);
        }
    }
}
