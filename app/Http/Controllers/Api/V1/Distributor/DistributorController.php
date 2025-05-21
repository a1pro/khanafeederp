<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\ClientDocument;
use Carbon\Carbon;
use Validation;




class DistributorController extends Controller
{
    public function distributorRegister(Request $request)
{
    DB::beginTransaction();

    try {
        // Save distributor info
        $distributor = new Client();
        $distributor->name = $request->name;
        $distributor->email = $request->email;
        $distributor->country = 'India';
        $distributor->state = $request->state;
        $distributor->district = $request->district;
        $distributor->tehsil = $request->tehsil;
        $distributor->village = $request->village;
        $distributor->phone = $request->phone;
        $distributor->tax_number = $request->tax_number;
        $distributor->adresse = $request->adresse;
        $distributor->save();

        $documents = [];

        for ($i = 1; $i <= 4; $i++) {
            $typeKey = "document{$i}_type";
            $numberKey = "document{$i}_number";
            $fileKey = "document{$i}_image"; // Accepts image(s) or pdf(s)

            if ($request->has($numberKey)) {
                $existingDoc = ClientDocument::where('document_number', $request->input($numberKey))->first();
                if ($existingDoc) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'status_code' => 409,
                        'message' => "Document number '{$request->input($numberKey)}' already exists.",
                    ]);
                }
            }

            if ($request->has($typeKey) || $request->has($numberKey) || $request->hasFile($fileKey)) {
                $doc = new ClientDocument();
                $doc->client_id = $distributor->id;
                $doc->document_type = $request->input($typeKey);
                $doc->document_number = $request->input($numberKey);

                if ($request->hasFile($fileKey)) {
                    $files = $request->file($fileKey);
                    if (!is_array($files)) {
                        $files = [$files]; // Normalize single file to array
                    }

                    foreach (array_slice($files, 0, 3) as $index => $file) {
                        if ($file->isValid()) {
                            $extension = $file->getClientOriginalExtension();
                            $filename = 'doc_' . uniqid() . '.' . $extension;
                            $path = $file->storeAs('documents', $filename, 'public');

                            $field = 'document_image' . ($index + 1); // dynamic column
                            $doc->$field = $path;
                        }
                    }
                }

                $doc->save();
                $documents[] = $doc;
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Distributor registered successfully',
            'data' => [
                'distributor' => $distributor,
                'documents' => $documents
            ]
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'status_code' => 500,
            'message' => 'Something went wrong: ' . $e->getMessage(),
        ]);
    }
}

}
