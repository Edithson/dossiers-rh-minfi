<?php

use App\Models\User;
use App\Models\Piece;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;

    public User $user;

    // Niveau 1 : Informations utilisateur
    public string $name = '';
    public string $email = '';
    public string $matricule = '';
    public string $password = ''; // Optionnel lors de la modif

    // Niveau 2 : Pièces et fichiers
    public array $selectedPieces = [];
    public array $existingPiecesFiles = []; // Fichiers déjà sur le serveur
    public array $newPieceFiles = [];       // Nouveaux fichiers uploadés
    public array $filesToDelete = [];       // Fichiers marqués pour suppression

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        // $this->email = $user->email;
        $this->matricule = $user->matricule;

        // Charger les pièces existantes de l'utilisateur
        foreach ($user->pieces as $piece) {
            $this->selectedPieces[$piece->id] = true;
            $this->existingPiecesFiles[$piece->id] = json_decode($piece->pivot->file_paths, true) ?? [];
        }
    }

    // --- NIVEAU 1 : Mise à jour des informations ---
    public function updateUserInfo()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $this->user->id,
            'matricule' => 'required|string|unique:users,matricule,' . $this->user->id,
            'password' => 'nullable|string|min:6',
        ]);

        $this->user->name = $this->name;
        // $this->user->email = $this->email;
        $this->user->matricule = $this->matricule;

        // if (!empty($this->password)) {
        //     $this->user->password = Hash::make($this->password);
        // }

        $this->user->save();
        session()->flash('message_info', 'Informations personnelles mises à jour.');
    }

    // --- NIVEAU 2 : Mise à jour des pièces ---
    public function stageForDeletion($pieceId, $index)
    {
        // 1. On récupère le chemin exact grâce à son index
        $path = $this->existingPiecesFiles[$pieceId][$index] ?? null;

        if ($path) {
            // 2. On l'ajoute à la corbeille pour la suppression finale
            $this->filesToDelete[] = $path;

            // 3. On le supprime du tableau
            unset($this->existingPiecesFiles[$pieceId][$index]);

            // 4. TRÈS IMPORTANT : On réindexe le tableau pour que Livewire comprenne
            // qu'il s'agit toujours d'une liste (array) et non d'un objet JSON.
            $this->existingPiecesFiles[$pieceId] = array_values($this->existingPiecesFiles[$pieceId]);
        }
    }

    public function updatePieces()
    {
        $this->validate([
            'newPieceFiles.*.*' => 'file|max:2048',
        ]);

        $syncData = [];

        foreach ($this->selectedPieces as $pieceId => $isSelected) {
            if ($isSelected) {
                // 1. Récupérer les anciens fichiers restants (non supprimés)
                $paths = $this->existingPiecesFiles[$pieceId] ?? [];

                // 2. Ajouter les nouveaux fichiers
                if (isset($this->newPieceFiles[$pieceId])) {
                    foreach ($this->newPieceFiles[$pieceId] as $file) {
                        $paths[] = $file->store('pieces_employes', 'public');
                    }
                }

                // Préparer les données pour la table pivot
                $syncData[$pieceId] = ['file_paths' => json_encode(array_values($paths))];
            } else {
                // Si la pièce a été décochée, on marque tous ses anciens fichiers pour suppression
                if (isset($this->existingPiecesFiles[$pieceId])) {
                    foreach ($this->existingPiecesFiles[$pieceId] as $path) {
                        $this->filesToDelete[] = $path;
                    }
                    unset($this->existingPiecesFiles[$pieceId]);
                }
            }
        }

        // 3. Appliquer la synchronisation en base de données
        $this->user->pieces()->sync($syncData);

        // 4. Suppression physique (Storage) des fichiers marqués
        foreach (array_unique($this->filesToDelete) as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        $this->filesToDelete = []; // Réinitialisation

        // 5. Nettoyage de l'interface
        $this->newPieceFiles = [];
        session()->flash('message_pieces', 'Dossier documentaire mis à jour avec succès.');
    }

    public function with(): array
    {
        return [
            'availablePieces' => Piece::all()
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto p-4 lg:p-8">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Édition du dossier</h2>
            <p class="text-gray-500 text-sm">Employé : {{ $user->name }} ({{ $user->matricule }})</p>
        </div>
        <a href="{{ route('users') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
        </a>
    </div>

    <form wire:submit="updateUserInfo" class="mb-10">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-user-edit text-blue-500 mr-2"></i> Informations Générales</h3>
                @if (session()->has('message_info'))
                    <span class="text-sm text-green-600 bg-green-100 px-3 py-1 rounded-full font-medium transition-all">
                        <i class="fas fa-check"></i> {{ session('message_info') }}
                    </span>
                @endif
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom et Prénom</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Matricule</label>
                    <input type="text" wire:model="matricule" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    @error('matricule') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse Email</label>
                    <input type="email" wire:model="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe (laisser vide pour ne pas modifier)</label>
                    <input type="password" wire:model="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" placeholder="••••••••">
                    @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div> --}}
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-save"></i> Enregistrer les infos
                    <span wire:loading wire:target="updateUserInfo"><i class="fas fa-spinner fa-spin"></i></span>
                </button>
            </div>
        </div>
    </form>

    <form wire:submit="updatePieces">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-folder-open text-yellow-500 mr-2"></i> Documents et Pièces</h3>
                    <p class="text-sm text-gray-500 mt-1">Gérez les documents fournis par l'employé.</p>
                </div>
                @if (session()->has('message_pieces'))
                    <span class="text-sm text-green-600 bg-green-100 px-3 py-1 rounded-full font-medium transition-all">
                        <i class="fas fa-check"></i> {{ session('message_pieces') }}
                    </span>
                @endif
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($availablePieces as $piece)
                        <div class="border {{ !empty($selectedPieces[$piece->id]) ? 'border-blue-400 bg-blue-50/30' : 'border-gray-200 bg-gray-50' }} rounded-xl p-5 flex flex-col justify-between transition-colors shadow-sm">

                            <div class="flex justify-between items-start mb-4">
                                <label class="flex items-start gap-3 cursor-pointer w-full">
                                    <input type="checkbox" wire:model.live="selectedPieces.{{ $piece->id }}" class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-800">{{ $piece->name }}</h4>
                                        @if($piece->description)
                                            <p class="text-xs text-gray-500 mt-1">{{ $piece->description }}</p>
                                        @endif
                                    </div>
                                </label>
                            </div>

                            @if(!empty($selectedPieces[$piece->id]))
                                <div class="mt-4 pt-4 border-t border-gray-200 border-dashed">

                                    @if(!empty($existingPiecesFiles[$piece->id]))
                                        <div class="mb-3">
                                            <p class="text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">Fichiers actuels :</p>
                                            <ul class="space-y-2">
                                                @foreach($existingPiecesFiles[$piece->id] as $index => $path)
                                                    <li class="text-sm bg-white border border-gray-200 rounded p-2 flex items-center justify-between group">
                                                        <a href="{{ Storage::url($path) }}" target="_blank" class="text-blue-600 hover:underline truncate w-4/5 flex items-center gap-2 text-xs">
                                                            <i class="fas fa-file-pdf text-red-500"></i> {{ basename($path) }}
                                                        </a>

                                                        <button type="button" wire:click="stageForDeletion({{ $piece->id }}, {{ $index }})" class="text-gray-400 hover:text-red-500 transition-colors" title="Supprimer ce fichier">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">Ajouter des copies :</p>
                                        <label class="cursor-pointer text-blue-600 hover:text-blue-800 text-xs font-medium flex items-center justify-center gap-2 bg-white px-3 py-2 rounded border border-blue-200 hover:border-blue-400 shadow-sm w-full transition-all">
                                            <i class="fas fa-upload"></i> Parcourir...
                                            <input type="file" wire:model="newPieceFiles.{{ $piece->id }}" class="hidden" multiple accept=".pdf,.jpg,.jpeg,.png">
                                        </label>

                                        <div wire:loading wire:target="newPieceFiles.{{ $piece->id }}" class="text-xs text-blue-500 mt-2 text-center w-full">
                                            <i class="fas fa-spinner fa-spin"></i> Traitement...
                                        </div>

                                        @if(isset($newPieceFiles[$piece->id]))
                                            <ul class="mt-2 space-y-1 border-l-2 border-green-400 pl-2">
                                                @foreach($newPieceFiles[$piece->id] as $file)
                                                    <li class="text-xs text-green-700 flex items-center gap-1">
                                                        <i class="fas fa-plus-circle"></i>
                                                        <span class="truncate w-40">{{ $file->getClientOriginalName() }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-500 flex items-center gap-2">
                    @if(count($filesToDelete) > 0)
                        <i class="fas fa-exclamation-triangle text-amber-500"></i>
                        <span class="text-amber-700 font-medium">{{ count($filesToDelete) }} fichier(s) en attente de suppression</span>
                    @else
                        <i class="fas fa-info-circle"></i> Les modifications seront appliquées au clic.
                    @endif
                </div>

                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                    <i class="fas fa-save"></i> Valider les pièces
                    <span wire:loading wire:target="updatePieces"><i class="fas fa-spinner fa-spin"></i></span>
                </button>
            </div>
        </div>
    </form>
</div>
