<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\ProjectLead;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectLeadController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'name' => 'required|string|max:100',
            'email' => 'nullable|email',
            'mobile' => 'required|digits_between:10,15',
            'looking_for' => 'required|string',
            'preferred_bedrooms' => 'nullable|array',
            'consent' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $project = Project::find($request->project_id);

        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'Project not found',
            ]);
        }

        $lead = ProjectLead::create([
            'project_id' => $project->_id,
            'name' => $request->name,
            'email' => $request->email,
            'country_code' => $request->country_code ?? '+91',
            'mobile' => $request->mobile,
            'looking_for' => $request->looking_for,
            'preferred_bedrooms' => $request->preferred_bedrooms,
            'consent' => $request->consent,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'source' => 'website',
            'status' => 'new',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Lead submitted successfully',
            'data' => $lead
        ]);
    }
}