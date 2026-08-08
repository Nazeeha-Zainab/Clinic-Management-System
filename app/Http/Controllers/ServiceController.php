<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fee' => 'required|numeric',
            'description' => 'required|string'
        ]);

        \App\Models\Service::create($request->all());

        return redirect()->back()->with('success', 'Service added successfully');
    }

    public function update(Request $request, \App\Models\Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fee' => 'required|numeric',
            'description' => 'required|string'
        ]);

        $service->update($request->all());

        return redirect()->back()->with('success', 'Service updated successfully');
    }

    public function destroy(\App\Models\Service $service)
    {
        $service->delete();
        return redirect()->back()->with('success', 'Service deleted successfully');
    }
}
