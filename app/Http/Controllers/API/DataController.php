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
    // public function analyze(Request $request)
    // {
    //     // Valider les données d'entrée
    //     $request->validate([
    //         'file_path' => 'required|string',
    //         'data' => 'required|array',
    //     ]);

    //     $filePath = $request->input('file_path');
    //     $data = json_encode($request->input('data'));

    //     // Construire la commande pour exécuter le script Python
    //     $command = ['python3', base_path('scripts/analyze_model.py'), $filePath, $data];

    //     // Exécuter le script Python
    //     $process = new Process($command);
    //     $process->run();

    //     // Vérifier si le script a échoué
    //     if (!$process->isSuccessful()) {
    //         throw new ProcessFailedException($process);
    //     }

    //     // Retourner le résultat de l'analyse
    //     $result = $process->getOutput();

    //     return response()->json(['result' => json_decode($result)]);
    // }


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

    // Vérifier si le retour de la commande contient des erreurs
    if (strpos($output, 'error') !== false || strpos($output, 'Error') !== false) {
        Log::error('Error output from command', ['output' => $output]);
        return response()->json(['error' => 'Error output from command', 'details' => $output], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Décoder le résultat JSON
    $predictions = json_decode($output, true);

    // Vérifier si le JSON est correctement décodé
    if ($predictions === null) {
        Log::error('Error decoding JSON output', ['output' => $output]);
        return response()->json(['error' => 'Error decoding JSON output from script', 'details' => $output], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
// Récupérer la donnée de la prédiction dans une variable
$predictionData = $predictions;

// Enregistrer le résultat de la prédiction dans $data->risk
// Assurer que la prédiction est un string avant de l'enregistrer
// $data->Risk = is_array($predictionData) ? json_encode($predictionData) : (string)$predictionData;
// $data->save();
    $data->update(
        ['Risk' => $predictionData],
    );
// Répondre avec les prédictions
return response()->json($predictionData);
}


}
