<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationAPIController extends Controller
{
    public function index()
    {
        $locations = Location::all();
        // Debug untuk melihat data
        // foreach($locations as $loc) {
        //     echo "ID: " . $loc->id . "<br>";
        // }
        return view('map', compact('locations'));
    }

    public function create()
    {
        return view('create_location');
    }

    // Menyimpan lokasi baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'risk_level' => 'required',
        ]);

        Location::create($request->all());
        return redirect()->route('locations.index');
    }

    // Menampilkan form untuk mengedit lokasi
    public function edit($id)
    {
        try {
            // Debug mode
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            
            // Log setiap langkah
            \Log::info('Accessing edit method with ID: ' . $id);
            
            $location = Location::findOrFail($id);
            \Log::info('Location found', $location->toArray());
            
            // Coba render view tanpa layout dulu
            return view('locations.edit')
                ->with('location', $location)
                ->with('debug', true);
                
        } catch (\Exception $e) {
            // Tampilkan error langsung
            dd([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    // Mengupdate lokasi
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'risk_level' => 'required|in:low,medium,high',
            'incident_time' => 'required|date',
            'incident_count' => 'required|integer|min:0',
        ]);

        try {
            $location = Location::findOrFail($id);
            $location->update($request->all());

            return redirect('/map')->with('success', 'Lokasi berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui lokasi: ' . $e->getMessage());
        }
    }

    // Menghapus lokasi
    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();
        return redirect()->route('locations.index');
    }
}