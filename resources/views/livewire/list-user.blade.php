<?php

use App\Models\User;
use App\Models\Piece;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {
    use WithPagination;

    // Critères de recherche et filtres
    public string $search = '';
    public string $pieceFilterId = '';
    public string $pieceFilterStatus = '';

    // Gestion de la modale de suppression
    public bool $showDeleteModal = false;
    public ?int $userToDelete = null;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingPieceFilterId() { $this->resetPage(); }
    public function updatingPieceFilterStatus() { $this->resetPage(); }

    // --- LOGIQUE DE LA MODALE ---
    public function confirmDelete($userId)
    {
        $this->userToDelete = $userId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->userToDelete = null;
    }

    public function executeDelete()
    {
        if (!$this->userToDelete) return;

        $user = User::with('pieces')->findOrFail($this->userToDelete);

        foreach ($user->pieces as $piece) {
            $files = json_decode($piece->pivot->file_paths, true) ?? [];
            foreach ($files as $file) {
                if (Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }
        }

        $user->delete();

        // Réinitialiser la modale et afficher le message
        $this->cancelDelete();
        session()->flash('message', 'L\'employé et tous ses documents ont été supprimés avec succès.');
    }

    public function with(): array
    {
        $query = User::with('pieces')->latest();

        // 1. Recherche
        if (!empty($this->search)) {
            $query->where(function (Builder $q) {
                $q->where('name', 'ilike', '%' . $this->search . '%') // 'ilike' est insensible à la casse sous PostgreSQL
                  ->orWhere('matricule', 'ilike', '%' . $this->search . '%');
            });
        }

        // 2. Filtre par Pièce avec correction pour PostgreSQL
        if (!empty($this->pieceFilterId) && !empty($this->pieceFilterStatus)) {

            $pieceCondition = function (Builder $q) {
                $q->where('pieces.id', $this->pieceFilterId)
                  ->whereNotNull('piece_users.file_paths')
                  // Correction ici : on vérifie que la longueur du tableau JSON est > 0
                  ->whereJsonLength('piece_users.file_paths', '>', 0);
            };

            if ($this->pieceFilterStatus === 'has') {
                $query->whereHas('pieces', $pieceCondition);
            } elseif ($this->pieceFilterStatus === 'missing') {
                $query->whereDoesntHave('pieces', $pieceCondition);
            }
        }

        return [
            'users' => $query->paginate(10),
            'availablePieces' => Piece::all(),
            'totalPiecesCount' => Piece::count()
        ];
    }
}; ?>

<div class="relative">
    @if (session()->has('message'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm">
            <i class="fas fa-check-circle"></i> {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-users text-blue-500 mr-2"></i> Liste du personnel
                </h3>
                <a href="{{ route('create-user') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                    <i class="fas fa-user-plus"></i> Ajouter un dossier
                </a>
            </div>

            <div class="flex flex-col md:flex-row gap-4 items-end bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                <div class="w-full md:w-1/3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Rechercher</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nom ou matricule..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div class="w-full md:w-1/3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Filtrer par pièce</label>
                    <select wire:model.live="pieceFilterId" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 text-gray-700 bg-white">
                        <option value="">-- Sélectionner une pièce --</option>
                        @foreach($availablePieces as $piece)
                            <option value="{{ $piece->id }}">{{ $piece->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-1/3 {{ empty($pieceFilterId) ? 'opacity-50 pointer-events-none' : '' }}">
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Statut de la pièce</label>
                    <select wire:model.live="pieceFilterStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 text-gray-700 bg-white">
                        <option value="">-- Choisir le statut --</option>
                        <option value="has">✔️ Dispose de cette pièce</option>
                        <option value="missing">❌ Ne dispose pas de cette pièce</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                        <th class="px-6 py-4 font-medium">Matricule</th>
                        <th class="px-6 py-4 font-medium">Nom et Prénom</th>
                        <th class="px-6 py-4 font-medium min-w-[200px]">Progression Dossier</th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse($users as $user)
                        @php
                            $validatedPieces = 0;
                            foreach($user->pieces as $piece) {
                                $files = json_decode($piece->pivot->file_paths, true);
                                if (is_array($files) && count($files) > 0) {
                                    $validatedPieces++;
                                }
                            }
                            $percentage = $totalPiecesCount > 0 ? round(($validatedPieces / $totalPiecesCount) * 100) : 0;
                            $colorClass = $percentage == 100 ? 'bg-green-500' : ($percentage >= 50 ? 'bg-yellow-400' : 'bg-red-500');
                        @endphp

                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $user->matricule }}</td>
                            <td class="px-6 py-4 font-medium text-gray-700">{{ $user->name }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex justify-between items-center text-xs font-semibold text-gray-600 mb-1">
                                        <span>{{ $percentage }}% Complété</span>
                                        <span>{{ $validatedPieces }} / {{ $totalPiecesCount }} Pièces</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                        <div class="{{ $colorClass }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('show-user', $user->id) }}"
                                        class="text-gray-500 hover:text-blue-600 bg-gray-100 hover:bg-blue-50 p-2 rounded transition-colors cursor-default hover:cursor-grab"
                                        title="Visualiser le dossier">
                                        <i class="fas fa-eye fa-fw"></i>
                                    </a>

                                    <a href="{{ route('edit-user', $user->id) }}"
                                        class="text-gray-500 hover:text-yellow-600 bg-gray-100 hover:bg-yellow-50 p-2 rounded transition-colors cursor-default hover:cursor-grab"
                                        title="Mettre à jour">
                                        <i class="fas fa-pen fa-fw"></i>
                                    </a>

                                    <button
                                        wire:click="confirmDelete({{ $user->id }})"
                                        class="text-gray-500 hover:text-red-600 bg-gray-100 hover:bg-red-50 p-2 rounded transition-colors cursor-default hover:cursor-grab"
                                        title="Supprimer">
                                        <i class="fas fa-trash-alt fa-fw"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 bg-gray-50/50">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-search text-4xl text-gray-300 mb-3"></i>
                                    <p class="text-lg font-medium text-gray-600">Aucun résultat trouvé</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-[2px] transition-opacity">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all">

                {{-- Header --}}
                <div class="px-6 pt-6 pb-4 text-center">
                    <div class="flex items-center justify-center w-14 h-14 mx-auto mb-4 bg-red-100 rounded-full ring-4 ring-red-50">
                        <i class="fas fa-trash-alt text-xl text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Confirmer la suppression</h3>
                    <p class="text-sm text-gray-500">
                        Êtes-vous sûr de vouloir supprimer définitivement ce dossier du personnel ?
                    </p>
                </div>

                {{-- Warning block --}}
                <div class="mx-6 mb-5 bg-red-50 border border-red-200 rounded-xl p-3 flex gap-3 items-start">
                    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 text-sm flex-shrink-0"></i>
                    <p class="text-xs text-red-700 leading-relaxed">
                        <span class="font-semibold">Action irréversible.</span> Le compte utilisateur ainsi que tous ses documents physiques stockés sur le serveur seront définitivement détruits.
                    </p>
                </div>

                {{-- Actions --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all">
                        Annuler
                    </button>
                    <button wire:click="executeDelete"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 active:bg-red-800 transition-all flex items-center gap-2 shadow-sm">
                        <i class="fas fa-trash-alt text-xs"></i>
                        Supprimer
                        <span wire:loading wire:target="executeDelete">
                            <i class="fas fa-spinner fa-spin text-xs"></i>
                        </span>
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>
