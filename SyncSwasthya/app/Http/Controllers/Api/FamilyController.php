<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Family;
use App\Models\FamilyMember;

class FamilyController extends Controller
{
    /* =====================================================
        1) Get All Families + All Members
    ======================================================*/
    public function index()
    {
        return response()->json(
            Family::with('members')->get()
        );
    }


    /* =====================================================
        2) Create Family (Head record only required)
    ======================================================*/
    public function store(Request $request)
    {
        $validated = $request->validate([
            'head_name' => 'required|string|max:255',
            'village'   => 'required|string|max:255',

            // all medical data optional
            'demographics'     => 'nullable|array',
            'lifestyle'        => 'nullable|array',
            'vitals'           => 'nullable|array',
            'medical_history'  => 'nullable|array',
            'addictions'       => 'nullable|array',
            'mental_health'    => 'nullable|array',
            'vaccination'      => 'nullable|array',
        ]);

        $family = Family::create($validated);

        return response()->json([
            "success" => true,
            "message" => "Family Created Successfully",
            "family"  => $family
        ],201);
    }


    /* =====================================================
        3) View Single Family
    ======================================================*/
    public function show($id)
    {
        return response()->json(
            Family::with('members')->findOrFail($id)
        );
    }


    /* =====================================================
        4) Update Family (all optional except name)
    ======================================================*/
    public function update(Request $request, $id)
    {
        $family = Family::findOrFail($id);

        $validated = $request->validate([
            'head_name' => 'sometimes|string|max:255',
            'village'   => 'sometimes|string|max:255',

            'demographics'    => 'nullable|array',
            'lifestyle'       => 'nullable|array',
            'vitals'          => 'nullable|array',
            'medical_history' => 'nullable|array',
            'addictions'      => 'nullable|array',
            'mental_health'   => 'nullable|array',
            'vaccination'     => 'nullable|array',
        ]);

        $family->update($validated);

        return response()->json([
            "message" => "Family Updated Successfully",
            "family"  => $family
        ]);
    }


    /* =====================================================
        5) DELETE FAMILY
    ======================================================*/
    public function destroy($id)
    {
        Family::destroy($id);

        return response()->json([
            "message" => "Family Deleted Successfully"
        ]);
    }


    /* =====================================================
        6) ADD MEMBER (ONLY name required)
    ======================================================*/
    public function addMember(Request $request, $familyId)
    {
        $family = Family::findOrFail($familyId);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',    // only required
            'relation_with_head' => 'nullable|string|max:255',

            // optional — SAME DATA STRUCTURE AS HEAD
            'demographics'    => 'nullable|array',
            'lifestyle'       => 'nullable|array',
            'vitals'          => 'nullable|array',
            'medical_history' => 'nullable|array',
            'addictions'      => 'nullable|array',
            'mental_health'   => 'nullable|array',
            'vaccination'     => 'nullable|array',
        ]);

        $member = $family->members()->create($validated);

        return response()->json([
            "message" => "Family Member Added Successfully",
            "member"  => $member
        ],201);
    }


    /* =====================================================
        7) UPDATE MEMBER
    ======================================================*/
    public function updateMember(Request $request,$id)
    {
        $member = FamilyMember::findOrFail($id);

        $validated = $request->validate([
            'name'               => 'sometimes|string|max:255',
            'relation_with_head' => 'nullable|string|max:255',

            'demographics'    => 'nullable|array',
            'lifestyle'       => 'nullable|array',
            'vitals'          => 'nullable|array',
            'medical_history' => 'nullable|array',
            'addictions'      => 'nullable|array',
            'mental_health'   => 'nullable|array',
            'vaccination'     => 'nullable|array',
        ]);

        $member->update($validated);

        return response()->json([
            "message"=>"Member Updated Successfully",
            "member"=>$member
        ]);
    }


    /* =====================================================
        8) DELETE MEMBER
    ======================================================*/
    public function destroyMember($id)
    {
        FamilyMember::destroy($id);

        return response()->json([
            "message"=>"Member Removed Successfully"
        ]);
    }
}
