<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\VitalSign;
use App\Models\Wound;
use App\Models\Treatment;
use App\Services\VoiceRecognitionService;
use App\Services\MedicalDataExtractorService;
use Illuminate\Support\Facades\Auth;

class VoiceCaptureController extends Controller
{
    protected $voiceRecognition;
    protected $dataExtractor;

    public function __construct(
        VoiceRecognitionService $voiceRecognition,
        MedicalDataExtractorService $dataExtractor
    ) {
        $this->voiceRecognition = $voiceRecognition;
        $this->dataExtractor = $dataExtractor;
    }
    public function startCapture(Request $request)
    {
        // Verificar si la palabra clave "MEDIC" fue detectada
        $audioData = $request->input('audio_data');
        
        if ($this->voiceRecognition->detectKeyword($audioData, 'MEDIC')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Captura de voz iniciada',
                'session_id' => uniqid()
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Palabra clave no detectada'
        ], 400);
    }

    public function processTranscription(Request $request)
    {
        $transcription = $request->input('transcription');
        $sessionId = $request->input('session_id');

        // Extraer información médica
        $medicalData = $this->dataExtractor->extract($transcription);

        // Crear o actualizar paciente
        $patient = $this->createOrUpdatePatient($medicalData, $sessionId);

        return response()->json([
            'status' => 'success',
            'patient_id' => $patient->id,
            'medical_data' => $medicalData
        ]);
    }

    private function createOrUpdatePatient($medicalData, $sessionId)
    {
        // Buscar paciente por código de emergencia o crear uno nuevo
        $patient = Patient::where('emergency_code', $medicalData['emergency_code'] ?? $sessionId)
            ->first();

        if (!$patient) {
            $patient = Patient::create([
                'emergency_code' => $medicalData['emergency_code'] ?? $sessionId,
                'name' => $medicalData['patient_name'] ?? null,
                'age' => $medicalData['age'] ?? null,
                'gender' => $medicalData['gender'] ?? null,
                'initial_assessment' => $medicalData['initial_assessment'] ?? null,
                'paramedic_id' => Auth::id() ?? 'paramedic_001'
            ]);
        }

        // Guardar signos vitales
        if (isset($medicalData['vital_signs'])) {
            VitalSign::create(array_merge(
                ['patient_id' => $patient->id],
                $medicalData['vital_signs']
            ));
        }

        // Guardar heridas
        if (isset($medicalData['wounds'])) {
            foreach ($medicalData['wounds'] as $woundData) {
                Wound::create(array_merge(
                    ['patient_id' => $patient->id],
                    $woundData
                ));
            }
        }

        // Guardar tratamientos
        if (isset($medicalData['treatments'])) {
            foreach ($medicalData['treatments'] as $treatmentData) {
                Treatment::create(array_merge(
                    ['patient_id' => $patient->id],
                    $treatmentData
                ));
            }
        }

        return $patient;
    }
    //
}
