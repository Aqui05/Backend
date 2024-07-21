<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use App\Models\HealthData;
use App\Models\Data;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;


class DataController extends Controller
{

    public function index()
    {
        return Data::all();
    }

    public function analyze(Request $request, $data_id)
    {
        // Trouver les données
        $data = Data::find($data_id);
        if (!$data) {
            return response()->json(['error' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }

        // Valider les données d'entrée
        $validatedData = $request->validate([
            'male' => $data->male ? 'required|boolean' : 'nullable|boolean',
            'age' => $data->age ? 'required|numeric' : 'nullable|numeric',
            'currentSmoker' => $data->currentSmoker ? 'required|boolean' : 'nullable|boolean',
            'cigsPerDay' => $data->cigsPerDay ? 'required|numeric' : 'nullable|numeric',
            'BPMeds' => $data->BPMeds ? 'required|numeric' : 'nullable|numeric',
            'diabetes' => $data->diabetes ? 'required|boolean' : 'nullable|boolean',
            'totChol' => $data->totChol ? 'required|numeric' : 'nullable|numeric',
            'sysBP' => $data->sysBP ? 'required|numeric' : 'nullable|numeric',
            'diaBP' => $data->diaBP ? 'required|numeric' : 'nullable|numeric',
            'BMI' => $data->BMI ? 'required|numeric' : 'nullable|numeric',
            'heartRate' => $data->heartRate ? 'required|numeric' : 'nullable|numeric',
            'glucose' => $data->glucose ? 'required|numeric' : 'nullable|numeric',
        ]);

        // Préparer les arguments pour l'exécution du script Python
        $modelPath = escapeshellarg(base_path('app/Pickel_RL_Model.pkl'));
        $dataJson = escapeshellarg(json_encode($data->toArray()));

        // Construire et exécuter la commande
        $command = "python3 " . escapeshellarg(base_path('scripts/analyze_model.py')) . " $modelPath $dataJson";
        Log::info('Executing command', ['command' => $command]);

        $output = shell_exec($command);

        // Vérifier si la sortie est vide ou une erreur
        if ($output === null) {
            Log::error('Error executing command', ['command' => $command]);
            return response()->json(['error' => 'Error executing analysis script'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Décoder le résultat JSON
        $predictions = json_decode(trim($output), true);

        // Vérifier si le JSON est correctement décodé
        if ($predictions === null && json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Error decoding JSON output', ['output' => $output, 'error' => json_last_error_msg()]);
            return response()->json(['error' => 'Error decoding JSON output from script', 'details' => $output], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Traiter le cas où la prédiction est une chaîne simple
        $predictionData = is_array($predictions) ? $predictions : [$predictions];

        // Mettre à jour la base de données avec la prédiction
        $data->update(['Risk' => json_encode($predictionData)]);

        // Répondre avec les prédictions
        return response()->json(['prediction' => $predictionData]);
    }

}
