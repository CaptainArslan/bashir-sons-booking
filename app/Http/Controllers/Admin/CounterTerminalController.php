<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CounterTerminalController extends Controller
{
    public function index()
    {
        return view('admin.counter-terminals.index');
    }

    public function getData(Request $request)
    {
        return view('admin.counter-terminals.index');
    }

    public function create()
    {
        return view('admin.counter-terminals.create');
    }
    
    public function store(Request $request)
    {
        return view('admin.counter-terminals.create');
    }
    
    public function edit($id)
    {
        return view('admin.counter-terminals.edit');
    }
    
    public function update(Request $request, $id)
    {
        return view('admin.counter-terminals.edit');
    }

    public function destroy($id)
    {
        return view('admin.counter-terminals.index');
    }
}
