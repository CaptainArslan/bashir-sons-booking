<?php

namespace App\Http\Controllers\Admin;

use App\Models\Banner;
use App\Enums\BannerTypeEnum;
use App\Enums\BannerStatusEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        return view('admin.banners.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $banners = Banner::query()
                ->select('id', 'title', 'type', 'path', 'status', 'created_at');

            return DataTables::eloquent($banners)
                ->addColumn('formatted_title', function ($banner) {
                    return '<span class="fw-bold text-primary">' . e($banner->title) . '</span>';
                })
                ->addColumn('type_badge', function ($banner) {
                    $typeValue = $banner->type instanceof BannerTypeEnum ? $banner->type->value : $banner->type;
                    $typeName = BannerTypeEnum::getTypeName($typeValue);
                    $typeColor = BannerTypeEnum::getTypeColor($typeValue);
                    return '<span class="badge bg-' . $typeColor . '">' . e($typeName) . '</span>';
                })
                ->addColumn('image_preview', function ($banner) {
                    if ($banner->path) {
                        return '<img src="' . asset('storage/' . $banner->path) . '" alt="' . e($banner->title) . '" class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">';
                    }
                    return '<span class="text-muted">No image</span>';
                })
                ->addColumn('status_badge', function ($banner) {
                    $statusValue = $banner->status instanceof BannerStatusEnum ? $banner->status->value : $banner->status;
                    $statusName = BannerStatusEnum::getStatusName($statusValue);
                    $statusColor = BannerStatusEnum::getStatusColor($statusValue);
                    return '<span class="badge bg-' . $statusColor . '">' . e($statusName) . '</span>';
                })
                ->addColumn('actions', function ($banner) {
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
                                       href="' . route('admin.banners.edit', $banner->id) . '">
                                        <i class="bx bx-edit me-2"></i>Edit Banner
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" 
                                       href="javascript:void(0)" 
                                       onclick="deleteBanner(' . $banner->id . ')">
                                        <i class="bx bx-trash me-2"></i>Delete Banner
                                    </a>
                                </li>
                            </ul>
                        </div>';

                    return $actions;
                })
                ->editColumn('created_at', fn($banner) => $banner->created_at->format('d M Y'))
                ->escapeColumns([])
                ->rawColumns(['formatted_title', 'type_badge', 'image_preview', 'status_badge', 'actions'])
                ->make(true);
        }
    }

    public function create()
    {
        $types = BannerTypeEnum::getTypes();
        $statuses = BannerStatusEnum::getStatuses();
        return view('admin.banners.create', compact('types', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'type' => 'required|string|in:' . implode(',', BannerTypeEnum::getTypes()),
            'path' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|string|in:' . implode(',', BannerStatusEnum::getStatuses()),
        ], [
            'title.required' => 'Banner title is required',
            'title.string' => 'Banner title must be a string',
            'title.max' => 'Banner title must be less than 255 characters',
            'title.regex' => 'Banner title can only contain letters, numbers, spaces, hyphens, and underscores',
            'type.required' => 'Banner type is required',
            'type.string' => 'Banner type must be a string',
            'type.in' => 'Banner type must be a valid type',
            'path.required' => 'Banner image is required',
            'path.image' => 'File must be an image',
            'path.mimes' => 'Image must be jpeg, png, jpg, gif, or webp',
            'path.max' => 'Image size must be less than 2MB',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        try {
            // Handle file upload
            if ($request->hasFile('path')) {
                $path = $request->file('path')->store('banners', 'public');
                $validated['path'] = $path;
            }

            Banner::create([
                'title' => $validated['title'],
                'type' => $validated['type'],
                'path' => $validated['path'],
                'status' => $validated['status'],
            ]);

            return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create banner: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        $types = BannerTypeEnum::getTypes();
        $statuses = BannerStatusEnum::getStatuses();
        return view('admin.banners.edit', compact('banner', 'types', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'type' => 'required|string|in:' . implode(',', BannerTypeEnum::getTypes()),
            'path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|string|in:' . implode(',', BannerStatusEnum::getStatuses()),
        ], [
            'title.required' => 'Banner title is required',
            'title.string' => 'Banner title must be a string',
            'title.max' => 'Banner title must be less than 255 characters',
            'title.regex' => 'Banner title can only contain letters, numbers, spaces, hyphens, and underscores',
            'type.required' => 'Banner type is required',
            'type.string' => 'Banner type must be a string',
            'type.in' => 'Banner type must be a valid type',
            'path.image' => 'File must be an image',
            'path.mimes' => 'Image must be jpeg, png, jpg, gif, or webp',
            'path.max' => 'Image size must be less than 2MB',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.in' => 'Status must be a valid status',
        ]);

        try {
            $banner = Banner::findOrFail($id);

            // Handle file upload
            if ($request->hasFile('path')) {
                // Delete old image if exists
                if ($banner->path && Storage::disk('public')->exists($banner->path)) {
                    Storage::disk('public')->delete($banner->path);
                }
                $path = $request->file('path')->store('banners', 'public');
                $validated['path'] = $path;
            } else {
                unset($validated['path']);
            }

            $banner->update($validated);

            return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update banner: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);
            
            // Delete associated image file
            if ($banner->path && Storage::disk('public')->exists($banner->path)) {
                Storage::disk('public')->delete($banner->path);
            }

            $banner->delete();
            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting banner: ' . $e->getMessage()
            ], 500);
        }
    }
}