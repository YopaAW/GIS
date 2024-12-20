<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::all();
        return view('map', compact('locations'));
    }

    public function create()
    {
        return view('locations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'risk_level' => 'required',
            'address' => 'required',
            'incident_time' => 'required',
            'incident_count' => 'required|numeric'
        ]);

        Location::create($validated);

        return redirect('/')->with('success', 'Lokasi berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        try {
            $location->delete();
            return redirect('/map')->with('success', 'Lokasi berhasil dihapus');
        } catch (\Exception $e) {
            return redirect('/map')->with('error', 'Gagal menghapus lokasi');
        }
    }
}
