<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Rolecontroller extends Controller
{
    public function index()
    {
        return view('admin.roles.index');
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        return view('admin.roles.store');
    }

    public function edit($id)
    {
        return view('admin.roles.edit', compact('id'));
    }
    
    public function update(Request $request, $id)
    {
        return view('admin.roles.update', compact('id'));
    }

    public function destroy($id)
    {
        return view('admin.roles.destroy', compact('id'));
    }
}
