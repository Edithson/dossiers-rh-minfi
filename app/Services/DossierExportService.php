<?php

namespace App\Services;

use App\Models\User;
use App\Models\Piece;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DossierExportService
{
    /**
     * Crée le dossier temporaire s'il n'existe pas
     */
    private function ensureTempDirectoryExists(): string
    {
        $tempPath = storage_path('app/temp');
        if (!File::exists($tempPath)) {
            File::makeDirectory($tempPath, 0755, true);
        }
        return $tempPath;
    }

    /**
     * Télécharge une archive ZIP pour une seule pièce
     */
    public function downloadPiece(User $user, Piece $piece)
    {
        $this->ensureTempDirectoryExists();

        $zip = new ZipArchive;
        $fileName = "Piece_{$user->matricule}_" . Str::slug($piece->name) . ".zip";
        $tempFilePath = storage_path("app/temp/{$fileName}");

        if ($zip->open($tempFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

            $pivotData = $user->pieces()->where('piece_id', $piece->id)->first()?->pivot;
            $files = $pivotData ? json_decode($pivotData->file_paths, true) : [];

            // Structure : Matricule_NomEmploye / NomPiece_Timestamp / ...fichiers
            $baseFolder = "{$user->matricule}_" . Str::slug($user->name);
            $pieceFolder = $baseFolder . '/' . Str::slug($piece->name) . '_' . $piece->created_at->format('Ymd_His');

            foreach ($files as $filePath) {
                if (Storage::disk('public')->exists($filePath)) {
                    $absolutePath = Storage::disk('public')->path($filePath);
                    // On ajoute le fichier dans l'archive avec son chemin de dossier virtuel
                    $zip->addFile($absolutePath, $pieceFolder . '/' . basename($filePath));
                }
            }
            $zip->close();
        }

        // Retourne le fichier en téléchargement direct, puis le supprime du serveur temporaire
        return response()->download($tempFilePath)->deleteFileAfterSend(true);
    }

    /**
     * Télécharge une archive ZIP pour l'ensemble du dossier de l'employé
     */
    public function downloadFullDossier(User $user)
    {
        $this->ensureTempDirectoryExists();

        $zip = new ZipArchive;
        $fileName = "DossierComplet_{$user->matricule}_" . Str::slug($user->name) . ".zip";
        $tempFilePath = storage_path("app/temp/{$fileName}");

        if ($zip->open($tempFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

            $baseFolder = "{$user->matricule}_" . Str::slug($user->name);

            // On boucle sur toutes les pièces validées de l'utilisateur
            foreach ($user->pieces as $piece) {
                $files = json_decode($piece->pivot->file_paths, true) ?? [];

                if (count($files) > 0) {
                    $pieceFolder = $baseFolder . '/' . Str::slug($piece->name) . '_' . $piece->created_at->format('Ymd_His');

                    foreach ($files as $filePath) {
                        if (Storage::disk('public')->exists($filePath)) {
                            $absolutePath = Storage::disk('public')->path($filePath);
                            $zip->addFile($absolutePath, $pieceFolder . '/' . basename($filePath));
                        }
                    }
                }
            }
            $zip->close();
        }

        return response()->download($tempFilePath)->deleteFileAfterSend(true);
    }
}
