<?php

use App\Models\User;
use App\Models\Piece;
use App\Services\DossierExportService;
use Livewire\Volt\Component;

new class extends Component {
    public User $user;

    public function mount(User $user)
    {
        // On charge l'utilisateur avec ses pièces associées
        $this->user = $user->load('pieces');
    }

    // Téléchargement d'une seule pièce (Livewire gère le retour de la response en direct)
    public function downloadPiece($pieceId, DossierExportService $exportService)
    {
        $piece = Piece::findOrFail($pieceId);
        return $exportService->downloadPiece($this->user, $piece);
    }

    // Téléchargement de tout le dossier
    public function downloadAll(DossierExportService $exportService)
    {
        return $exportService->downloadFullDossier($this->user);
    }

    public function with(): array
    {
        $allPieces = Piece::all();
        $userPieces = $this->user->pieces->keyBy('id'); // Pour une recherche rapide

        $stats = [
            'total' => $allPieces->count(),
            'validated' => 0,
            'missing' => 0,
            'percentage' => 0
        ];

        $piecesPossedees = [];
        $piecesManquantes = [];

        foreach ($allPieces as $piece) {
            $hasFiles = false;
            $files = [];

            // Vérification si l'utilisateur possède cette pièce avec des fichiers
            if ($userPieces->has($piece->id)) {
                $files = json_decode($userPieces->get($piece->id)->pivot->file_paths, true) ?? [];
                if (count($files) > 0) {
                    $hasFiles = true;
                }
            }

            if ($hasFiles) {
                $stats['validated']++;
                $piecesPossedees[] = [
                    'model' => $piece,
                    'files' => $files,
                    'timestamp' => $userPieces->get($piece->id)->pivot->created_at
                ];
            } else {
                $stats['missing']++;
                $piecesManquantes[] = $piece;
            }
        }

        $stats['percentage'] = $stats['total'] > 0 ? round(($stats['validated'] / $stats['total']) * 100) : 0;
        $colorClass = $stats['percentage'] == 100 ? 'bg-green-500' : ($stats['percentage'] >= 50 ? 'bg-yellow-400' : 'bg-red-500');

        return [
            'stats' => $stats,
            'colorClass' => $colorClass,
            'piecesPossedees' => collect($piecesPossedees),
            'piecesManquantes' => collect($piecesManquantes),
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto p-4 lg:p-8">

    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('users') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 mb-2 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
            </a>
            <h2 class="text-3xl font-bold text-gray-800">{{ $user->name }}</h2>
            <p class="text-gray-500">Matricule : <span class="font-semibold text-gray-700">{{ $user->matricule }}</span> | {{ $user->email }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('edit-user', $user->id) }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors shadow-sm flex items-center gap-2">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <button wire:click="downloadAll" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors shadow-sm flex items-center gap-2">
                <i class="fas fa-file-archive"></i> Télécharger tout le dossier (.zip)
                <span wire:loading wire:target="downloadAll"><i class="fas fa-spinner fa-spin ml-1"></i></span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col justify-center">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Progression du dossier</h3>
            <div class="flex items-end gap-3 mb-2">
                <span class="text-4xl font-black text-gray-800">{{ $stats['percentage'] }}%</span>
                <span class="text-sm text-gray-500 mb-1 font-medium">Complété</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                <div class="{{ $colorClass }} h-3 rounded-full transition-all duration-1000" style="width: {{ $stats['percentage'] }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6 flex items-center gap-4">
            <div class="w-14 h-14 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-2xl">
                <i class="fas fa-check-double"></i>
            </div>
            <div>
                <span class="block text-3xl font-black text-gray-800">{{ $stats['validated'] }}</span>
                <span class="text-sm font-bold text-green-600 uppercase tracking-wider">Pièces validées</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6 flex items-center gap-4">
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-2xl">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div>
                <span class="block text-3xl font-black text-gray-800">{{ $stats['missing'] }}</span>
                <span class="text-sm font-bold text-red-600 uppercase tracking-wider">Pièces manquantes</span>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2 border-b pb-2">
            <i class="fas fa-folder-open text-blue-500"></i> Documents disponibles
        </h3>

        @if($piecesPossedees->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($piecesPossedees as $data)
                    <div class="bg-white border border-green-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-1 h-full bg-green-500"></div>

                        <div class="flex justify-between items-start mb-4 pl-2">
                            <div>
                                <h4 class="font-bold text-gray-800">{{ $data['model']->name }}</h4>
                                <p class="text-xs text-gray-500 mt-1">Ajouté le {{ $data['timestamp']->format('d/m/Y') }}</p>
                            </div>
                            <button wire:click="downloadPiece({{ $data['model']->id }})" class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition-colors" title="Télécharger l'archive ZIP de cette pièce">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>

                        <div class="pl-2 space-y-2 mt-4">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Fichiers ({{ count($data['files']) }}) :</p>
                            @foreach($data['files'] as $file)
                                <a href="{{ Storage::url($file) }}" target="_blank" class="flex items-center gap-2 p-2 bg-gray-50 border border-gray-100 rounded hover:bg-blue-50 hover:border-blue-200 transition-colors group/link">
                                    <i class="fas fa-file-pdf text-red-500 text-lg"></i>
                                    <span class="text-sm text-gray-700 truncate group-hover/link:text-blue-700">{{ basename($file) }}</span>
                                    <i class="fas fa-external-link-alt ml-auto text-xs text-gray-400 group-hover/link:text-blue-500 opacity-0 group-hover/link:opacity-100 transition-opacity"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-8 text-center">
                <i class="fas fa-folder-minus text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500 font-medium">Ce dossier ne contient encore aucun document.</p>
            </div>
        @endif
    </div>

    @if($piecesManquantes->count() > 0)
        <div>
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2 border-b pb-2 opacity-80">
                <i class="fas fa-folder-minus text-red-400"></i> Pièces manquantes
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($piecesManquantes as $piece)
                    <div class="bg-gray-50 border border-gray-200 border-dashed rounded-xl p-4 flex items-start gap-3 opacity-70 hover:opacity-100 transition-opacity">
                        <i class="fas fa-times-circle text-red-400 mt-1"></i>
                        <div>
                            <h4 class="text-sm font-bold text-gray-600">{{ $piece->name }}</h4>
                            @if($piece->description)
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $piece->description }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
