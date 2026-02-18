<?php

use App\Models\User;
use App\Models\Piece;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $matricule = '';
    public string $password = '';

    public array $selectedPieces = [];
    public array $pieceFiles = [];

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'matricule' => 'required|string|unique:users,matricule',
            'password' => 'required|string|min:6',
            'pieceFiles.*.*' => 'file|max:2048', // 2Mo max par fichier
        ]);

        // 1. Création de l'utilisateur
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'matricule' => $this->matricule,
            'password' => Hash::make($this->password),
        ]);

        // 2. Traitement des pièces et fichiers
        foreach ($this->selectedPieces as $pieceId => $isSelected) {
            if ($isSelected) {
                $filePaths = [];

                if (isset($this->pieceFiles[$pieceId])) {
                    foreach ($this->pieceFiles[$pieceId] as $file) {
                        // Stockage dans le disque public
                        $filePaths[] = $file->store('pieces_employes', 'public');
                    }
                }

                // Insertion dans la table pivot avec encodage JSON
                $user->pieces()->attach($pieceId, [
                    'file_paths' => json_encode($filePaths)
                ]);
            }
        }

        // 3. Message de succès et réinitialisation (ou redirection)
        session()->flash('message', 'Dossier de l\'employé créé avec succès.');
        $this->redirect('/', navigate: true); // Adapte la route selon tes besoins
    }

    // Fournir les pièces à la vue
    public function with(): array
    {
        return [
            'availablePieces' => Piece::all()
        ];
    }
}; ?>

<div>
    @if (session()->has('message'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas fa-check-circle"></i> {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-800">Nouvel Employé</h3>
                <p class="text-sm text-gray-500">Saisissez les informations de base</p>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom et Prénom</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Matricule</label>
                    <input type="text" wire:model="matricule" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('matricule') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse Email</label>
                    <input type="email" wire:model="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('email') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe provisoire</label>
                    <input type="password" wire:model="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('password') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Constitution du dossier</h3>
                    <p class="text-sm text-gray-500">Cochez les pièces puis importez les fichiers correspondants</p>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($availablePieces as $piece)
                        <div class="border {{ !empty($selectedPieces[$piece->id]) ? 'border-blue-300 bg-blue-50' : 'border-gray-200 bg-gray-50' }} rounded-lg p-4 flex flex-col justify-between transition-colors">

                            <div class="flex justify-between items-start mb-4">
                                <label class="flex items-start gap-3 cursor-pointer w-full">
                                    <input type="checkbox" wire:model.live="selectedPieces.{{ $piece->id }}" class="mt-1 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-800">{{ $piece->name }}</h4>
                                        @if($piece->description)
                                            <p class="text-xs text-gray-500 mt-1">{{ $piece->description }}</p>
                                        @endif
                                    </div>
                                </label>
                            </div>

                            @if(!empty($selectedPieces[$piece->id]))
                                <div class="mt-2 pt-3 border-t border-blue-200 border-dashed">
                                    <label class="cursor-pointer text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-2 bg-white px-3 py-2 rounded border border-blue-200 shadow-sm w-max">
                                        <i class="fas fa-upload"></i> Importer fichiers
                                        <input type="file" wire:model="pieceFiles.{{ $piece->id }}" class="hidden" multiple accept=".pdf,.jpg,.jpeg,.png">
                                    </label>

                                    <div wire:loading wire:target="pieceFiles.{{ $piece->id }}" class="text-xs text-blue-500 mt-2">
                                        <i class="fas fa-spinner fa-spin"></i> Traitement...
                                    </div>

                                    @if(isset($pieceFiles[$piece->id]))
                                        <ul class="mt-2 space-y-1">
                                            @foreach($pieceFiles[$piece->id] as $file)
                                                <li class="text-xs text-gray-700 flex items-center gap-1">
                                                    <i class="fas fa-file-pdf text-red-400"></i>
                                                    <span class="truncate w-40">{{ $file->getClientOriginalName() }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>
                @error('pieceFiles.*.*') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex justify-end gap-4 mb-8">
            <button type="button" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition-colors">
                Annuler
            </button>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors flex items-center gap-2 shadow-sm">
                <i class="fas fa-save"></i> Enregistrer l'employé
                <span wire:loading wire:target="save"><i class="fas fa-spinner fa-spin ml-2"></i></span>
            </button>
        </div>
    </form>
</div>
