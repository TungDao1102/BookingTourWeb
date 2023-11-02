<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Brian2694\Toastr\Facades\Toastr;
use JsValidator;

class HotelController extends Controller
{
    public $validationRules = [
        'rating' => 'required'
    ];
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Toastr::success('Hotel added successfully!' );
        $hotels = Hotel::paginate(5);
        return view('admin.hotel.index', compact('hotels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){
        $validator = JsValidator::make($this->validationRules);
        return view('admin.hotel.create')->with([
            'validator' => $validator
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $request->validate([
        //     'imageUrl' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        // ]);
        $validator = JsValidator::make($this->validationRules);
        $image = $request->file('imageUrl')->store(
            'hotels/images', 'public'
        );
       
        Hotel::create($request->except('imageUrl') + ['imageUrl' => $image]);
        Toastr::success('Hotel added successfully!' );
        return redirect()->route('hotels.index');


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hotel $hotel)
    {

        return view('admin.hotel.edit',compact('hotel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hotel $hotel)
    {
        $request->validate([
            'imageUrl' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric',

        ]);

        if ($request->hasFile('imageUrl')) {
            File::delete('storage/' . $hotel->imageUrl);
            $image = $request->file('imageUrl')->store('hotels/images', 'public');
            $hotel->update($request->except('imageUrl') + ['imageUrl' => $image]);
        } else {
            $hotel->update($request->validated());
        }

        return redirect()->route('hotels.index')->with([
            'message' => 'Success Updated!',
            'alert-type' => 'info'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hotel $hotel)
    {
        File::delete('storage/'. $hotel->imageUrl);
        $hotel->delete();

        return redirect()->back()->with([
            'message' => 'Success Deleted !',
            'alert-type' => 'danger'
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $hotels = Hotel::where('hotelName', 'like', "%$query%")
            ->orWhere('description', 'like', "%$query%")
            ->orWhere('pricePerPerson', 'like', "%$query%")
            ->paginate(5);

        return view('admin.hotel.index', compact('hotels'));

    }

}
